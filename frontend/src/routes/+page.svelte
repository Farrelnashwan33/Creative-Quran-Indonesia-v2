<script lang="ts">
  import { UserCircle2, ArrowRight } from '@lucide/svelte';
  import { goto } from '$app/navigation';
</script>

<svelte:head>
  <title>Welcome - Creative Qur'an Indonesia</title>
</svelte:head>

<div class="min-h-screen bg-[#031E1A] text-white flex flex-col items-center justify-center p-6 relative overflow-hidden">
  <!-- Gradient Background Effects -->
  <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-[#10B981] opacity-20 blur-[120px] rounded-full mix-blend-screen pointer-events-none"></div>
  <div class="absolute top-[40%] -right-[20%] w-[60%] h-[60%] bg-[#059669] opacity-20 blur-[100px] rounded-full mix-blend-screen pointer-events-none"></div>

  <!-- Content Container -->
  <div class="z-10 w-full max-w-sm flex flex-col items-center animate-fade-in">
    <!-- App Logo -->
    <img src="/favicon.jpeg" alt="Creative Qur'an Logo" class="w-20 h-20 rounded-2xl shadow-[0_20px_50px_rgba(16,185,129,0.3)] mb-6 animate-float object-cover" />

    <!-- Title & Subtitle -->
    <h1 class="text-3xl font-black mb-1 tracking-wide text-center">
      Creative Qur'an
    </h1>
    <h2 class="text-[#10B981] text-lg font-bold tracking-[0.2em] mb-6 uppercase">
      Indonesia
    </h2>
    
    <p class="text-zinc-400 text-center text-sm font-medium leading-relaxed mb-10 px-4">
      Belajar, Menghafal, dan Memahami Al-Qur'an dengan lebih mudah.
    </p>

    <!-- Buttons -->
    <div class="w-full space-y-4">
      <a 
        href="/login" 
        class="w-full flex items-center justify-center bg-[#10B981] hover:bg-[#059669] text-white font-bold py-4 rounded-[20px] shadow-[0_10px_30px_rgba(16,185,129,0.3)] transition-all active:scale-[0.98]"
      >
        Masuk
      </a>
      
      <a 
        href="/register" 
        class="w-full flex items-center justify-center bg-[#0C2C27] hover:bg-[#123B35] border border-[#10B981]/30 text-white font-bold py-4 rounded-[20px] transition-all active:scale-[0.98]"
      >
        Daftar
      </a>
    </div>


  </div>
</div>
