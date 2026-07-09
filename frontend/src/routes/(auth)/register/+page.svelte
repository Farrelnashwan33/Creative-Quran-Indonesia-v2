<script lang="ts">
    import { fetchWithAuth, authToken, authUser } from '$lib/auth';
    import { User, Lock, Mail, Loader2, AtSign } from '@lucide/svelte';
    import { goto } from '$app/navigation';

    let name = $state('');
    let username = $state('');
    let email = $state('');
    let password = $state('');
    let password_confirmation = $state('');
    let agreeToTerms = $state(false);
    
    let isLoading = $state(false);
    let errorMessage = $state('');

    async function handleRegister(e: Event) {
        e.preventDefault();
        
        if (password !== password_confirmation) {
            errorMessage = 'Password dan konfirmasi password tidak cocok.';
            return;
        }

        if (!agreeToTerms) {
            errorMessage = 'Anda harus menyetujui syarat & kebijakan kami.';
            return;
        }

        isLoading = true;
        errorMessage = '';

        try {
            const res = await fetchWithAuth('http://localhost:8000/api/v1/register', {
                method: 'POST',
                body: JSON.stringify({ name, username, email, password, password_confirmation })
            });

            const data = await res.json();

            if (!res.ok) {
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                } else {
                    errorMessage = data.message || 'Pendaftaran gagal. Silakan coba lagi.';
                }
                return;
            }

            // Successfully registered! Let's redirect to email verification page
            // Or log them in directly if API returns token
            if (data.success && data.data) {
                $authToken = data.data.access_token;
                $authUser = data.data.user;
                goto('/verify-email');
            } else if (data.access_token) {
                $authToken = data.access_token;
                $authUser = data.user;
                goto('/verify-email');
            } else {
                goto('/verify-email');
            }
        } catch (err) {
            errorMessage = 'Terjadi kesalahan saat menghubungi server.';
        } finally {
            isLoading = false;
        }
    }
</script>

<svelte:head>
    <title>Daftar Akun - Creative Qur'an Indonesia</title>
</svelte:head>

<div class="min-h-screen flex items-center justify-center p-4 py-8 animate-fade-in relative overflow-hidden">
    <!-- Background glow effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] right-[10%] w-[40%] h-[40%] bg-[#10B981] opacity-[0.15] blur-[100px] rounded-full mix-blend-screen"></div>
        <div class="absolute bottom-[10%] left-[10%] w-[40%] h-[40%] bg-[#059669] opacity-[0.15] blur-[100px] rounded-full mix-blend-screen"></div>
    </div>

    <!-- Main Card -->
    <div class="w-full max-w-md bg-[#0C2C27] rounded-[20px] shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-8 border border-[#10B981]/10 relative z-10">
        
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-white flex items-center justify-center gap-2 mb-2">
                Assalamu'alaikum <span class="animate-wiggle">👋</span>
            </h1>
            <p class="text-zinc-400">Buat Akun Baru</p>
        </div>

        {#if errorMessage}
            <div class="mb-6 p-4 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-medium animate-fade-in flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {errorMessage}
            </div>
        {/if}

        <form onsubmit={handleRegister} class="space-y-4">
            <!-- Nama Lengkap -->
            <div class="space-y-2">
                <label for="name" class="text-sm font-medium text-zinc-300">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <User class="h-5 w-5 text-zinc-500" />
                    </div>
                    <input
                        id="name"
                        type="text"
                        bind:value={name}
                        required
                        class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] transition-all"
                    />
                </div>
            </div>

            <!-- Username -->
            <div class="space-y-2">
                <label for="username" class="text-sm font-medium text-zinc-300">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <AtSign class="h-5 w-5 text-zinc-500" />
                    </div>
                    <input
                        id="username"
                        type="text"
                        bind:value={username}
                        required
                        class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] transition-all"
                    />
                </div>
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-zinc-300">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Mail class="h-5 w-5 text-zinc-500" />
                    </div>
                    <input
                        id="email"
                        type="email"
                        bind:value={email}
                        required
                        class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] transition-all"
                    />
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-zinc-300">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Lock class="h-5 w-5 text-zinc-500" />
                    </div>
                    <input
                        id="password"
                        type="password"
                        bind:value={password}
                        required
                        minlength="8"
                        class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] transition-all"
                    />
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="space-y-2">
                <label for="password_confirmation" class="text-sm font-medium text-zinc-300">Konfirmasi Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Lock class="h-5 w-5 text-zinc-500" />
                    </div>
                    <input
                        id="password_confirmation"
                        type="password"
                        bind:value={password_confirmation}
                        required
                        minlength="8"
                        class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] transition-all"
                    />
                </div>
            </div>

            <div class="flex items-center pt-2 pb-2">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="relative flex items-center justify-center mt-0.5 shrink-0">
                        <input type="checkbox" bind:checked={agreeToTerms} class="peer sr-only" />
                        <div class="w-5 h-5 border-2 border-zinc-500 rounded-[6px] peer-checked:bg-[#10B981] peer-checked:border-[#10B981] transition-colors"></div>
                        <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="text-sm text-zinc-400 leading-snug">
                        Saya menyetujui <a href="#" class="text-[#10B981] hover:underline">syarat & kebijakan</a> yang berlaku
                    </span>
                </label>
            </div>

            <button
                type="submit"
                disabled={isLoading}
                class="w-full mt-2 flex items-center justify-center gap-2 bg-[#10B981] hover:bg-[#059669] text-white font-bold py-4 rounded-[16px] shadow-[0_10px_25px_rgba(16,185,129,0.25)] active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
            >
                {#if isLoading}
                    <Loader2 class="w-5 h-5 animate-spin" />
                    <span>Memproses...</span>
                {:else}
                    <span>Daftar</span>
                {/if}
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-zinc-400">
            Sudah punya akun? 
            <a href="/login" class="text-[#10B981] hover:text-[#059669] font-bold transition-colors">
                Masuk
            </a>
        </div>
        
    </div>
</div>

<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }
    .animate-wiggle {
        display: inline-block;
        animation: wiggle 2s infinite ease-in-out;
        transform-origin: 70% 70%;
    }
</style>
