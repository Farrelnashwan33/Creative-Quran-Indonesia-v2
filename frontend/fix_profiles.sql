-- Run this in your Supabase SQL Editor if your profiles table is missing these columns:
ALTER TABLE public.profiles ADD COLUMN IF NOT EXISTS avatar_url text;
ALTER TABLE public.profiles ADD COLUMN IF NOT EXISTS provider text;
