<script lang="ts">
    import { authUser } from '$lib/auth';
    import { MailCheck, RefreshCw, LogOut } from '@lucide/svelte';
    import { goto } from '$app/navigation';

    let isResending = $state(false);
    let message = $state('');

    // Fallback if no user is found in store
    let email = $derived($authUser?.email || 'email@anda.com');

    async function handleResend() {
        isResending = true;
        message = '';
        
        // Simulate API call for now
        setTimeout(() => {
            isResending = false;
            message = 'Email verifikasi baru telah dikirim.';
        }, 1500);
    }

    function handleLogout() {
        // Clear local storage / stores
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        window.location.href = '/login';
    }
</script>

<svelte:head>
    <title>Verifikasi Email - Creative Qur'an Indonesia</title>
</svelte:head>

<div class="min-h-screen flex items-center justify-center p-4 animate-fade-in relative overflow-hidden">
    <!-- Background glow effects -->
    <div class="absolute top-[20%] right-[20%] w-[40%] h-[40%] bg-[#10B981] opacity-[0.15] blur-[100px] rounded-full mix-blend-screen pointer-events-none"></div>

    <div class="w-full max-w-md bg-[#0C2C27] rounded-[20px] shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-10 border border-[#10B981]/10 relative z-10 flex flex-col items-center text-center">
        
        <div class="w-20 h-20 bg-[#123B35] rounded-full flex items-center justify-center mb-6 animate-float">
            <MailCheck class="w-10 h-10 text-[#10B981]" />
        </div>

        <h1 class="text-2xl font-bold text-white mb-4">Cek Email Anda</h1>
        
        <p class="text-zinc-400 text-sm mb-2 leading-relaxed">
            Kami telah mengirimkan link verifikasi ke email
        </p>
        <p class="text-[#10B981] font-medium mb-8 text-lg">
            {email}
        </p>

        {#if message}
            <div class="w-full mb-6 p-4 rounded-[12px] bg-[#10B981]/10 border border-[#10B981]/20 text-[#10B981] text-sm font-medium animate-fade-in">
                {message}
            </div>
        {/if}

        <div class="w-full space-y-4">
            <a 
                href="https://gmail.com" 
                target="_blank"
                rel="noopener noreferrer"
                class="w-full flex items-center justify-center gap-2 bg-[#10B981] hover:bg-[#059669] text-white font-bold py-4 rounded-[16px] shadow-[0_10px_25px_rgba(16,185,129,0.25)] active:scale-[0.98] transition-all"
            >
                Buka Gmail
            </a>

            <div class="relative py-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/10"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-[#0C2C27] px-4 text-zinc-500 uppercase tracking-wider">atau</span>
                </div>
            </div>

            <button 
                onclick={handleResend}
                disabled={isResending}
                class="w-full flex items-center justify-center gap-2 bg-[#123B35] hover:bg-[#0F312B] text-white font-medium py-3.5 rounded-[16px] transition-all border border-white/5 disabled:opacity-70"
            >
                {#if isResending}
                    <RefreshCw class="w-5 h-5 animate-spin" />
                    Mengirim...
                {:else}
                    Kirim ulang email
                {/if}
            </button>
            
            <button 
                onclick={handleLogout}
                class="mt-4 text-xs text-zinc-500 hover:text-zinc-300 transition-colors flex items-center justify-center gap-1 mx-auto"
            >
                <LogOut class="w-3 h-3" /> Kembali ke Login
            </button>
        </div>
    </div>
</div>
