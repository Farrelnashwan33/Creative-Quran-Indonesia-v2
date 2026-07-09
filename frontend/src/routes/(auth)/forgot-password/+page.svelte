<script lang="ts">
    import { Mail, ArrowLeft, Loader2 } from '@lucide/svelte';
    import { goto } from '$app/navigation';

    let email = $state('');
    let isLoading = $state(false);
    let successMessage = $state('');

    async function handleReset(e: Event) {
        e.preventDefault();
        isLoading = true;
        
        // Simulate API call for now (can be integrated with Laravel password reset later)
        setTimeout(() => {
            isLoading = false;
            successMessage = 'Tautan reset password telah dikirim ke email Anda.';
        }, 1500);
    }
</script>

<svelte:head>
    <title>Lupa Password - Creative Qur'an Indonesia</title>
</svelte:head>

<div class="min-h-screen flex items-center justify-center p-4 animate-fade-in relative overflow-hidden">
    <!-- Background glow effects -->
    <div class="absolute top-[20%] left-[20%] w-[40%] h-[40%] bg-[#10B981] opacity-[0.15] blur-[100px] rounded-full mix-blend-screen pointer-events-none"></div>

    <div class="w-full max-w-md bg-[#0C2C27] rounded-[20px] shadow-[0_20px_50px_rgba(0,0,0,0.25)] p-8 border border-[#10B981]/10 relative z-10">
        
        <button 
            onclick={() => history.back()} 
            class="w-10 h-10 rounded-full bg-[#123B35] hover:bg-[#0F312B] flex items-center justify-center text-zinc-400 hover:text-white transition-colors mb-6"
        >
            <ArrowLeft class="w-5 h-5" />
        </button>

        <h1 class="text-2xl font-bold text-white mb-2">Lupa Password</h1>
        <p class="text-zinc-400 text-sm mb-8 leading-relaxed">
            Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang password Anda.
        </p>

        {#if successMessage}
            <div class="mb-6 p-4 rounded-[12px] bg-[#10B981]/10 border border-[#10B981]/20 text-[#10B981] text-sm font-medium animate-fade-in flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {successMessage}
            </div>
        {/if}

        <form onsubmit={handleReset} class="space-y-6">
            <div class="space-y-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <Mail class="h-5 w-5 text-zinc-500" />
                    </div>
                    <input
                        id="email"
                        type="email"
                        bind:value={email}
                        required
                        placeholder="nama@email.com"
                        disabled={!!successMessage}
                        class="w-full pl-11 pr-4 py-3.5 bg-[#123B35] border border-transparent rounded-[16px] text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#10B981] transition-all disabled:opacity-50"
                    />
                </div>
            </div>

            <button
                type="submit"
                disabled={isLoading || !!successMessage}
                class="w-full flex items-center justify-center gap-2 bg-[#10B981] hover:bg-[#059669] text-white font-bold py-4 rounded-[16px] shadow-[0_10px_25px_rgba(16,185,129,0.25)] active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
            >
                {#if isLoading}
                    <Loader2 class="w-5 h-5 animate-spin" />
                    <span>Memproses...</span>
                {:else if successMessage}
                    <span>Tautan Terkirim</span>
                {:else}
                    <span>Kirim Link Reset</span>
                {/if}
            </button>
        </form>
    </div>
</div>
