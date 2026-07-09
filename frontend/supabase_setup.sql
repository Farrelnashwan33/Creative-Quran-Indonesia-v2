-- ==========================================
-- SUPABASE AUTH & PROFILES SCHEMA
-- Idempotent script (bisa dijalankan berulang kali tanpa error)
-- ==========================================

-- 1. Create profiles table if not exists
create table if not exists public.profiles (
  id uuid not null references auth.users on delete cascade,
  full_name text,
  email text,
  avatar_url text,
  provider text,
  created_at timestamp with time zone default timezone('utc'::text, now()) not null,
  updated_at timestamp with time zone default timezone('utc'::text, now()) not null,
  primary key (id)
);

-- 2. Drop existing policies to ensure idempotency
drop policy if exists "Public profiles are viewable by everyone." on public.profiles;
drop policy if exists "Users can insert their own profile." on public.profiles;
drop policy if exists "Users can update own profile." on public.profiles;

-- 3. Enable RLS and recreate policies
alter table public.profiles enable row level security;

create policy "Public profiles are viewable by everyone." 
  on public.profiles for select 
  using (true);

create policy "Users can insert their own profile." 
  on public.profiles for insert 
  with check (auth.uid() = id);

create policy "Users can update own profile." 
  on public.profiles for update 
  using (auth.uid() = id);

-- 4. Create or Replace Function for handling new users (Trigger)
-- This uses UPSERT (insert ... on conflict) to prevent duplicate key errors
create or replace function public.handle_new_user()
returns trigger as $$
declare
  _provider text;
  _full_name text;
  _avatar_url text;
begin
  -- Get provider from app_metadata
  _provider := new.app_metadata->>'provider';
  
  -- Extract full_name intelligently
  _full_name := coalesce(
    new.raw_user_meta_data->>'full_name', 
    new.raw_user_meta_data->>'name', 
    new.raw_user_meta_data->>'custom_claims'->>'name',
    ''
  );

  -- Extract avatar_url intelligently
  _avatar_url := coalesce(
    new.raw_user_meta_data->>'avatar_url', 
    new.raw_user_meta_data->>'picture', 
    ''
  );

  -- UPSERT into public.profiles
  insert into public.profiles (id, full_name, email, avatar_url, provider, created_at, updated_at)
  values (
    new.id, 
    _full_name,
    new.email,
    _avatar_url,
    coalesce(_provider, 'email'),
    now(),
    now()
  )
  on conflict (id) do update set
    full_name = excluded.full_name,
    email = excluded.email,
    avatar_url = excluded.avatar_url,
    provider = excluded.provider,
    updated_at = now();
    
  return new;
end;
$$ language plpgsql security definer;

-- 5. Drop trigger if exists and recreate it
drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
  after insert on auth.users
  for each row execute procedure public.handle_new_user();
