<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import { goto } from '$app/navigation';
    import { authToken, authUser, fetchWithAuth, guestMode } from '$lib/auth';
    import { Loader2 } from '@lucide/svelte';

    let error = $state('');

    onMount(async () => {
        const token = $page.url.searchParams.get('token');
        const authError = $page.url.searchParams.get('error');

        if (authError) {
            error = 'Otentikasi gagal. Silakan coba lagi.';
            setTimeout(() => {
                goto('/login');
            }, 3000);
            return;
        }

        if (!token) {
            error = 'Token tidak valid.';
            setTimeout(() => {
                goto('/login');
            }, 3000);
            return;
        }

        try {
            // Set token temporarily to fetch user data
            $authToken = token;
            $guestMode = false;

            // Fetch user profile
            const res = await fetchWithAuth('http://localhost:8000/api/v1/me');
            
            if (res.ok) {
                const data = await res.json();
                $authUser = data.data || data; // Handle different response formats
                goto('/home');
            } else {
                error = 'Gagal mengambil data profil.';
                $authToken = null;
                setTimeout(() => {
                    goto('/login');
                }, 3000);
            }
        } catch (err) {
            error = 'Terjadi kesalahan sistem.';
            $authToken = null;
            setTimeout(() => {
                goto('/login');
            }, 3000);
        }
    });
</script>

<div class="min-h-screen flex items-center justify-center p-4 lg:p-8 animate-fade-in bg-[#08221D]">
    <div class="w-full max-w-md bg-[#0C2C27] rounded-[20px] shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-8 text-center border border-[#10B981]/10">
        {#if error}
            <div class="text-red-400 font-medium mb-4">
                {error}
            </div>
            <p class="text-zinc-400 text-sm">Mengalihkan ke halaman login...</p>
        {:else}
            <Loader2 class="w-12 h-12 text-[#10B981] animate-spin mx-auto mb-4" />
            <h2 class="text-xl font-bold text-white mb-2">Login Berhasil!</h2>
            <p class="text-zinc-400 text-sm">Sedang memuat profil Anda...</p>
        {/if}
    </div>
</div>
