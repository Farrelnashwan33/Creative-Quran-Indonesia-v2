<script lang="ts">
  import { onMount } from 'svelte'
  import { goto } from '$app/navigation'

  let { data } = $props();

  let errorMsg = $state('')

  let debugInfo = $state('')

  onMount(() => {
    debugInfo = `URL: ${window.location.search} ${window.location.hash}`
    
    // Supabase client automatically handles the hash fragment from OAuth
    const initSession = async () => {
      const { data: { user }, error } = await data.supabase.auth.getUser()

      if (error && error.message !== 'Auth session missing!') {
        console.error('Error fetching user:', error.message)
      }

      if (user) {
        // Successfully authenticated
        const provider = user.app_metadata?.provider || 'google';
        const { error: profileError } = await data.supabase.from('profiles').upsert({
          id: user.id,
          full_name: user.user_metadata?.full_name || user.user_metadata?.name || '',
          avatar_url: user.user_metadata?.avatar_url || '',
          email: user.email,
          provider: provider,
          created_at: user.created_at,
        }, { onConflict: 'id' });

        if (profileError) {
           console.error('Failed to create/update profile:', profileError);
        }
        
        goto('/home')
      } else {
        // Setup listener in case session is established slightly later via hash parsing
        const { data: { subscription } } = data.supabase.auth.onAuthStateChange(async (event, session) => {
          debugInfo += `\nAuth event: ${event}`
          if (event === 'SIGNED_IN') {
            const { data: { user: authUser } } = await data.supabase.auth.getUser()
            if (authUser) {
              const provider = authUser.app_metadata?.provider || 'google';
              await data.supabase.from('profiles').upsert({
                id: authUser.id,
                full_name: authUser.user_metadata?.full_name || authUser.user_metadata?.name || '',
                avatar_url: authUser.user_metadata?.avatar_url || '',
                email: authUser.email,
                provider: provider,
                created_at: authUser.created_at,
              }, { onConflict: 'id' });
            }
            goto('/home')
          }
        })

        // Timeout fallback if no session is created
        setTimeout(() => {
          const checkSession = async () => {
            const { data: { user } } = await data.supabase.auth.getUser()
            if (!user) {
              errorMsg = 'Gagal memverifikasi sesi login. Silakan coba lagi.'
            } else {
              goto('/home')
            }
          }
          checkSession()
        }, 5000)

        return subscription
      }
    }

    let sub: any;
    initSession().then(subscription => {
      sub = subscription;
    });

    return () => {
      if (sub) sub.unsubscribe()
    }
  })
</script>

<svelte:head>
    <title>Memverifikasi - Creative Qur'an Indonesia</title>
</svelte:head>

<div class="flex items-center justify-center min-h-screen bg-[#08221D]">
  <div class="text-center p-8 bg-[#0C2C27] shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-[#10B981]/10 rounded-xl max-w-md w-full">
    {#if errorMsg}
      <div class="text-red-400 mb-4 animate-fade-in">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="font-medium">{errorMsg}</p>
        <p class="text-xs mt-2 text-zinc-400 break-words">{debugInfo}</p>
      </div>
    {:else}
      <div class="animate-pulse">
        <div class="w-12 h-12 border-4 border-[#10B981] border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <h2 class="text-xl font-semibold text-white">Memverifikasi Login...</h2>
        <p class="text-zinc-400 mt-2">Mohon tunggu sebentar sementara kami mengautentikasi akun Anda.</p>
        <p class="text-xs mt-4 text-zinc-500 break-words">{debugInfo}</p>
      </div>
    {/if}
  </div>
</div>
