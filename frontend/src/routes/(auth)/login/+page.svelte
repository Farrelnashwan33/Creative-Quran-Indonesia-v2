<script lang="ts">
    import { BookOpen, User, Lock, ArrowRight, Loader2, Mail } from '@lucide/svelte';
    import { enhance } from '$app/forms';
    import { supabase } from '$lib/supabase';

    let { form } = $props();
    let isLoading = $state(false);
    let errorMessage = $derived(form?.error || '');

    async function handleGoogleLogin() {
        const { error } = await supabase.auth.signInWithOAuth({
            provider: 'google',
            options: {
                redirectTo: `${window.location.origin}/auth/v1/callback`
            }
        });

        if (error) {
            console.error('Google login error:', error);
        }
    }

    async function handleAppleLogin() {
        const { error } = await supabase.auth.signInWithOAuth({
            provider: 'apple',
            options: {
                redirectTo: `${window.location.origin}/auth/v1/callback`
            }
        });

        if (error) {
            console.error('Apple login error:', error);
        }
    }
</script>

<svelte:head>
    <title>Login - Creative Qur'an Indonesia</title>
</svelte:head>

<div class="min-h-screen flex items-center justify-center p-4 lg:p-8 animate-fade-in relative overflow-hidden">
    <!-- Background glow effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-[#10B981] opacity-[0.15] blur-[120px] rounded-full mix-blend-screen"></div>
        <div class="absolute top-[60%] -right-[10%] w-[50%] h-[50%] bg-[#059669] opacity-[0.15] blur-[120px] rounded-full mix-blend-screen"></div>
    </div>

    <!-- Main Card -->
    <div class="w-full max-w-5xl bg-[#0C2C27] rounded-[20px] shadow-[0_20px_50px_rgba(0,0,0,0.25)] flex flex-col md:flex-row overflow-hidden border border-[#10B981]/10 relative z-10">
        
        <!-- Left Column: Branding / Illustration (Hidden on mobile) -->
        <div class="hidden md:flex md:w-1/2 bg-[#08221D] flex-col items-center justify-center p-12 relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] border-[40px] border-[#10B981]/5 rounded-full"></div>
            
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-gradient-to-tr from-[#059669] to-[#10B981] rounded-3xl flex items-center justify-center shadow-[0_15px_30px_rgba(16,185,129,0.2)] mb-8 animate-float">
                    <BookOpen class="w-12 h-12 text-white" />
                </div>
                <h2 class="text-3xl font-black text-white mb-2 tracking-wide">Creative Qur'an</h2>
                <h3 class="text-[#10B981] font-bold tracking-[0.2em] uppercase text-sm mb-6">Indonesia</h3>
                <p class="text-zinc-400 text-sm max-w-xs leading-relaxed">
                    Lebih dari sekadar membaca. Mari pelajari dan hafalkan Al-Qur'an dengan antarmuka yang indah dan fitur yang modern.
                </p>
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="w-full md:w-1/2 p-8 lg:p-12 flex flex-col justify-center">
            
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white flex items-center gap-2 mb-2">
                    Assalamu'alaikum <span class="animate-wiggle">👋</span>
                </h1>
                <p class="text-zinc-400">Selamat Datang Kembali</p>
            </div>

            {#if errorMessage}
                <div class="mb-6 p-4 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-medium animate-fade-in flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {errorMessage}
                </div>
            {/if}

            <form method="POST" action="?/login" use:enhance={() => { isLoading = true; return async ({ update }) => { isLoading = false; update(); } }} class="space-y-5">
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-zinc-300">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <Mail class="h-5 w-5 text-zinc-500" />
                        </div>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value={form?.email ?? ''}
                            required
                            placeholder="nama@email.com"
                            class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] focus:border-transparent transition-all"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="password" class="text-sm font-medium text-zinc-300">Password</label>
                        <a href="/forgot-password" class="text-xs font-medium text-[#10B981] hover:text-[#059669] transition-colors">
                            Lupa Password?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <Lock class="h-5 w-5 text-zinc-500" />
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] focus:border-transparent transition-all"
                        />
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="remember" class="peer sr-only" />
                            <div class="w-5 h-5 border-2 border-zinc-500 rounded-[6px] peer-checked:bg-[#10B981] peer-checked:border-[#10B981] transition-colors"></div>
                            <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <span class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">Ingat Saya</span>
                    </label>
                </div>

                <button
                    type="submit"
                    disabled={isLoading}
                    class="w-full mt-4 flex items-center justify-center gap-2 bg-[#10B981] hover:bg-[#059669] text-white font-bold py-4 rounded-[16px] shadow-[0_10px_25px_rgba(16,185,129,0.25)] active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    {#if isLoading}
                        <Loader2 class="w-5 h-5 animate-spin" />
                        <span>Memproses...</span>
                    {:else}
                        <span>Masuk</span>
                    {/if}
                </button>
            </form>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/10"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-[#0C2C27] px-4 text-zinc-500 uppercase tracking-wider">atau</span>
                </div>
            </div>

            <!-- Social Logins -->
            <div class="space-y-3">
                <button type="button" onclick={handleGoogleLogin} class="w-full flex items-center justify-center gap-3 bg-[#123B35] hover:bg-[#0F312B] text-white py-3.5 rounded-[16px] transition-all border border-white/5 font-medium">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </button>
                <button type="button" onclick={handleAppleLogin} class="w-full flex items-center justify-center gap-3 bg-[#123B35] hover:bg-[#0F312B] text-white py-3.5 rounded-[16px] transition-all border border-white/5 font-medium">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16.365 21.435c-1.575 1.14-3.18 1.125-4.53.015-1.56-1.275-2.61-3.66-2.73-5.25-.09-1.32.33-2.67 1.14-3.6 1.485-1.74 3.93-2.22 5.535-.78 1.545 1.425 2.22 3.615 1.965 5.595-.195 1.44-1.26 2.925-2.385 4.02zm-3.3-11.415c-.48-1.545.69-3.465 2.505-4.05 1.65-.54 3.42.3 4.035 1.95.435 1.185.06 2.655-1.02 3.36-1.545 1.005-3.72.69-4.59-1.26z"/>
                        <path d="M12.148 23.978c-.73-.082-1.465-.246-2.184-.504-2.112-.76-4.103-2.457-5.35-4.56C3.125 16.398 2.378 13.067 3.322 9.878c.84-2.825 2.766-5.263 5.378-6.786 1.83-1.066 3.92-1.482 6.02-.916 1.487.404 2.923 1.258 4.148 2.36.994.896 1.872 2.012 2.518 3.208 1.472 2.732 1.636 5.86.376 8.706-1.127 2.548-3.167 4.606-5.632 5.856-1.246.63-2.6.993-3.983 1.07-1.157.065-2.348-.12-3.447-.417l-.547-.18z"/>
                    </svg>
                    Continue with Apple
                </button>
            </div>

            <div class="mt-8 text-center text-sm text-zinc-400">
                Belum punya akun? 
                <a href="/register" class="text-[#10B981] hover:text-[#059669] font-bold transition-colors">
                    Daftar
                </a>
            </div>
            
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
