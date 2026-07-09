<script lang="ts">
  import { onMount } from 'svelte'
  import { goto } from '$app/navigation'
  import { supabase } from '$lib/supabase'

  let errorMsg = $state('')

  onMount(() => {
    // Supabase client automatically handles the hash fragment from OAuth
    const initSession = async () => {
      const { data: { session }, error } = await supabase.auth.getSession()

      if (error) {
        console.error('Error fetching session:', error.message)
        errorMsg = error.message
        return
      }

      if (session) {
        // Successfully authenticated, redirect to dashboard
        goto('/home')
      } else {
        // Setup listener in case session is established slightly later via hash parsing
        const { data: { subscription } } = supabase.auth.onAuthStateChange((event, session) => {
          if (event === 'SIGNED_IN' && session) {
            goto('/home')
          }
        })

        // Timeout fallback if no session is created
        setTimeout(() => {
          const checkSession = async () => {
            const { data } = await supabase.auth.getSession()
            if (!data.session) {
              errorMsg = 'Gagal memverifikasi sesi login. Silakan coba lagi.'
              setTimeout(() => goto('/login'), 3000)
            }
          }
          checkSession()
        }, 2000)

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

<div class="flex items-center justify-center min-h-screen bg-gray-50">
  <div class="text-center p-8 bg-white shadow-lg rounded-xl max-w-md w-full">
    {#if errorMsg}
      <div class="text-red-500 mb-4">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="font-medium">{errorMsg}</p>
        <p class="text-sm mt-2 text-gray-500">Mengarahkan kembali ke halaman login...</p>
      </div>
    {:else}
      <div class="animate-pulse">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <h2 class="text-xl font-semibold text-gray-800">Memverifikasi Login...</h2>
        <p class="text-gray-500 mt-2">Mohon tunggu sebentar sementara kami mengautentikasi akun Anda.</p>
      </div>
    {/if}
  </div>
</div>
