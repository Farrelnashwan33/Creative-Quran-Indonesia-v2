<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { fetchSurahDetail, fetchTafsir, fetchSurahs, fetchPrayerTimes, fetchPrayerTimesByCity, type SurahDetail, type TafsirDetail, type Ayah, type Surah } from '$lib/api';
  import { settings, lastRead, favorites, readingHistory, readingStats, defaultSettings, type AppSettings, type FavoriteAyah } from '$lib/stores';
  import { 
    ArrowLeft, 
    Play, 
    Pause, 
    BookOpen, 
    Heart, 
    Share2, 
    Copy, 
    Book, 
    Settings as SettingsIcon,
    ChevronDown,
    ChevronUp,
    Volume2,
    ChevronsUpDown,
    Check,
    ListRestart,
    Download,
    List,
    ChevronsDown,
    Calendar,
    Compass,
    Languages
  } from '@lucide/svelte';

  const surahId = $derived(Number($page.params.id));
  
  let surah = $state<SurahDetail | null>(null);
  let tafsir = $state<TafsirDetail | null>(null);
  let loading = $state(true);
  let error = $state<string | null>(null);
  
  // Settings values from store
  let currentSettings = $state<AppSettings>({ ...defaultSettings });
  onMount(() => {
    const unsubSettings = settings.subscribe(val => currentSettings = val);
    return () => unsubSettings();
  });

  // Favorites & Bookmarks logic
  let favList = $state<FavoriteAyah[]>([]);
  onMount(() => {
    const unsubFav = favorites.subscribe(val => favList = val);
    return () => unsubFav();
  });

  // Track active verse details for audio playback
  let activeAyahNum = $state<number | null>(null);
  let audioPlayer = $state<HTMLAudioElement | null>(null);
  let isPlaying = $state(false);
  let expandedTafsirAyah = $state<number | null>(null);
  let expandedPerKataAyah = $state<number | null>(null);
  let perKataCache = $state<Record<number, any[]>>({});
  let loadingPerKata = $state(false);

  async function togglePerKata(ayahNumber: number) {
    if (expandedPerKataAyah === ayahNumber) {
      expandedPerKataAyah = null;
      return;
    }
    
    expandedPerKataAyah = ayahNumber;
    
    if (perKataCache[ayahNumber]) return;
    
    loadingPerKata = true;
    try {
      const res = await fetch(`https://api.quran.com/api/v4/verses/by_key/${surahId}:${ayahNumber}?words=true&word_fields=text_uthmani&language=id`);
      if (res.ok) {
        const data = await res.json();
        if (data && data.verse && data.verse.words) {
          perKataCache[ayahNumber] = data.verse.words.filter((w: any) => w.char_type_name === 'word');
        }
      }
    } catch (e) {
      console.error("Error fetching word-by-word:", e);
    } finally {
      loadingPerKata = false;
    }
  }

  // Copy success indicator
  let copiedAyah = $state<number | null>(null);
  let sharedAyah = $state<number | null>(null);

  // Toast notifications
  let toastMessage = $state<string | null>(null);
  let showToast = $state(false);

  function triggerToast(msg: string) {
    toastMessage = msg;
    showToast = true;
    setTimeout(() => {
      showToast = false;
    }, 2500);
  }

  // Toolbar states
  let allSurahs = $state<Surah[]>([]);
  let showNavigationModal = $state(false);
  let showAgendaModal = $state(false);
  let selectedSurahNum = $state(surahId);
  let selectedAyahNumInput = $state(1);
  let selectedHalamanNum = $state(26);

  // Auto scroll states
  let autoScrollActive = $state(false);
  let autoScrollIntervalId: any = null;

  // Agenda / Sholat states
  let prayerTimesData = $state<any>(null);

  async function loadAgendaPrayerTimes() {
    let lat = -6.2088;
    let lon = 106.8456;
    let cityName = 'Jakarta';
    
    const stored = localStorage.getItem('quran_location');
    if (stored) {
      const loc = JSON.parse(stored);
      lat = loc.latitude;
      lon = loc.longitude;
      cityName = loc.cityName;
    }
    
    try {
      const data = await fetchPrayerTimes(lat, lon);
      prayerTimesData = data.timings;
    } catch (e) {
      try {
        const data = await fetchPrayerTimesByCity(cityName);
        prayerTimesData = data.timings;
      } catch (err) {
        console.error(err);
      }
    }
  }

  function toggleAutoScroll() {
    autoScrollActive = !autoScrollActive;
    if (autoScrollActive) {
      autoScrollIntervalId = setInterval(() => {
        if (typeof window !== 'undefined') {
          window.scrollBy({ top: 1, behavior: 'auto' });
        }
      }, 40);
    } else {
      if (autoScrollIntervalId) {
        clearInterval(autoScrollIntervalId);
        autoScrollIntervalId = null;
      }
    }
  }

  function jumpToAyah(tafsirMode = false) {
    showNavigationModal = false;
    if (selectedSurahNum !== surahId) {
      goto(`/quran/${selectedSurahNum}`).then(() => {
        setTimeout(() => {
          const el = document.getElementById(`ayah-${selectedAyahNumInput}`);
          if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          if (tafsirMode) {
            expandedTafsirAyah = selectedAyahNumInput;
          }
        }, 800);
      });
    } else {
      const el = document.getElementById(`ayah-${selectedAyahNumInput}`);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      if (tafsirMode) {
        expandedTafsirAyah = selectedAyahNumInput;
      }
    }
  }

  onMount(async () => {
    try {
      // Parallel loading for fast performance
      const [detailData, tafsirData, surahsList] = await Promise.all([
        fetchSurahDetail(surahId),
        fetchTafsir(surahId),
        fetchSurahs().catch(() => [])
      ]);
      
      surah = detailData;
      tafsir = tafsirData;
      allSurahs = surahsList;
      selectedSurahNum = detailData.nomor;

      // Update Last Read
      lastRead.set({
        surahNumber: detailData.nomor,
        ayahNumber: 1,
        surahName: detailData.namaLatin,
        surahTranslation: detailData.arti,
        timestamp: new Date().toLocaleDateString('id-ID', { hour: '2-digit', minute: '2-digit' })
      });

      // Update history list
      readingHistory.update(list => {
        const item = {
          id: `${detailData.nomor}-1-${Date.now()}`,
          surahNumber: detailData.nomor,
          ayahNumber: 1,
          surahName: detailData.namaLatin,
          timestamp: new Date().toLocaleDateString('id-ID')
        };
        // Avoid duplicate history of the same surah in close succession
        const filtered = list.filter(x => x.surahNumber !== detailData.nomor);
        return [item, ...filtered].slice(0, 20);
      });

      // Update reading statistics count for today
      updateStats();

      // Load agenda prayer times
      loadAgendaPrayerTimes();

    } catch (e) {
      error = "Gagal memuat detail surah atau tafsir.";
    } finally {
      loading = false;
    }
  });

  function updateStats() {
    const today = new Date().toISOString().split('T')[0];
    readingStats.update(list => {
      const idx = list.findIndex(s => s.date === today);
      if (idx !== -1) {
        list[idx].count += 1; // Count reading session
        return [...list];
      } else {
        return [...list, { date: today, count: 1 }];
      }
    });
  }

  // Audio mapping helper for Qori selection
  // equran.id v2 API returns qori mapping options 1 to 5.
  // 01 -> Abdurrahman Al-Sudais
  // 02 -> Abdul Basit
  // 03 -> Mishary Rashid Al-Afasy
  // 04 -> Hani Al-Rifai
  // 05 -> Abdullah Al-Juhany
  function getAudioUrl(ayah: Ayah): string {
    const qoriMap = {
      'juhany': '01',
      'qasim': '02',
      'sudais': '03',
      'dossari': '04',
      'afasy': '05',
      'aldosari': '06'
    };
    const key = qoriMap[currentSettings.qori] || '05';
    return ayah.audio[key] || Object.values(ayah.audio)[0];
  }

  // Play a specific verse
  function playVerse(ayah: Ayah) {
    if (audioPlayer) {
      audioPlayer.pause();
    }

    if (activeAyahNum === ayah.nomorAyat && isPlaying) {
      isPlaying = false;
      return;
    }

    activeAyahNum = ayah.nomorAyat;
    const url = getAudioUrl(ayah);

    audioPlayer = new Audio(url);
    audioPlayer.play();
    isPlaying = true;

    // Handle end of playback - auto play next verse
    audioPlayer.onended = () => {
      playNext();
    };

    // Auto-scroll to active card
    scrollToVerse(ayah.nomorAyat);
  }

  function playNext() {
    if (!surah || activeAyahNum === null) return;
    const nextIdx = activeAyahNum; // Index is 0-based, so next verse is index = activeAyahNum
    if (nextIdx < surah.ayat.length) {
      const nextAyah = surah.ayat[nextIdx];
      playVerse(nextAyah);
    } else {
      isPlaying = false;
      activeAyahNum = null;
      triggerToast("Khatam Surah ini.");
    }
  }

  function togglePlayAll() {
    if (!surah) return;
    if (isPlaying) {
      if (audioPlayer) audioPlayer.pause();
      isPlaying = false;
    } else {
      const startAyah = activeAyahNum ? surah.ayat[activeAyahNum - 1] : surah.ayat[0];
      playVerse(startAyah);
    }
  }

  function scrollToVerse(num: number) {
    setTimeout(() => {
      const el = document.getElementById(`ayah-${num}`);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 100);
  }

  // Clipboard functionality
  function copyAyah(ayah: Ayah) {
    const textToCopy = `${ayah.teksArab}\n\n${ayah.teksLatin}\n\nTerjemahan: ${ayah.teksIndonesia} (QS. ${surah?.namaLatin}: ${ayah.nomorAyat})`;
    navigator.clipboard.writeText(textToCopy);
    copiedAyah = ayah.nomorAyat;
    triggerToast("Ayat berhasil disalin!");
    setTimeout(() => {
      copiedAyah = null;
    }, 2000);
  }

  // Favorites toggling
  function toggleFavorite(ayah: Ayah) {
    if (!surah) return;
    const exists = favList.some(f => f.surahNumber === surahId && f.ayahNumber === ayah.nomorAyat);
    
    if (exists) {
      favorites.update(list => list.filter(f => !(f.surahNumber === surahId && f.ayahNumber === ayah.nomorAyat)));
      triggerToast("Ayat dihapus dari Favorit.");
    } else {
      const item: FavoriteAyah = {
        surahNumber: surahId,
        ayahNumber: ayah.nomorAyat,
        surahName: surah.namaLatin,
        arabicText: ayah.teksArab,
        translation: ayah.teksIndonesia,
        timestamp: new Date().toLocaleDateString('id-ID')
      };
      favorites.update(list => [...list, item]);
      triggerToast("Ayat ditambahkan ke Favorit.");
    }
  }

  function isFavorite(ayahNum: number): boolean {
    return favList.some(f => f.surahNumber === surahId && f.ayahNumber === ayahNum);
  }

  // Share functionality
  function shareAyah(ayah: Ayah) {
    if (navigator.share) {
      navigator.share({
        title: `Creative Qur'an - QS. ${surah?.namaLatin} Ayat ${ayah.nomorAyat}`,
        text: `QS. ${surah?.namaLatin} Ayat ${ayah.nomorAyat}\n\n${ayah.teksArab}\n\n${ayah.teksIndonesia}`,
        url: window.location.href
      }).catch(console.error);
    } else {
      copyAyah(ayah);
      triggerToast("Tautan / teks disalin untuk dibagikan!");
    }
  }

  function getTafsirText(ayahNum: number): string {
    if (!tafsir) return "Tafsir tidak tersedia.";
    const match = tafsir.tafsir.find(t => t.ayat === ayahNum);
    return match ? match.teks : "Tafsir untuk ayat ini tidak ditemukan.";
  }

  // Highlights Tajwid dynamically (mock representation for demonstration of colorful tajwid feature)
  // In a production app, the string can be parsed for specific characters or rules.
  // Here we wrap typical tajwid words dynamically.
  function processTajwid(text: string, enabled: boolean): string {
    if (!enabled) return text;
    // Replace typical vowel markers/patterns with color spans for visual tajwid feedback
    // This is a premium mockup representation of colorful Tajwid text engine
    return text
      .replace(/ّ/g, '<span class="tajwid-ghunnah">ّ</span>')
      .replace(/ْ/g, '<span class="tajwid-qalqalah">ْ</span>')
      .replace(/ٰ/g, '<span class="tajwid-mad">ٰ</span>')
      .replace(/ً/g, '<span class="tajwid-ikhfa">ً</span>');
  }
</script>

<div class="space-y-6 relative pb-20">

  <!-- TOAST ALERTS -->
  {#if showToast}
    <div class="fixed top-20 left-1/2 -translate-x-1/2 px-5 py-3.5 bg-emerald-600 border border-emerald-500/30 text-white text-xs font-bold rounded-2xl shadow-xl z-50 animate-fade-in flex items-center gap-2">
      <Check class="w-4 h-4 text-emerald-100" />
      <span>{toastMessage}</span>
    </div>
  {/if}

  <!-- TOP BAR BACK NAVIGATION -->
  <div class="flex items-center justify-between pb-2">
    <a href="/quran" class="inline-flex items-center gap-2.5 text-zinc-400 hover:text-zinc-200 group">
      <div class="w-9 h-9 rounded-xl glass border border-white/5 flex items-center justify-center group-hover:border-emerald-500/20">
        <ArrowLeft class="w-4.5 h-4.5" />
      </div>
      <span class="text-xs font-bold">Kembali ke Daftar</span>
    </a>
    
    <a href="/settings" class="inline-flex items-center gap-2 text-zinc-400 hover:text-zinc-200">
      <SettingsIcon class="w-4.5 h-4.5" />
      <span class="text-xs font-semibold">Tampilan</span>
    </a>
  </div>

  {#if loading}
    <!-- Reader Skeleton -->
    <div class="space-y-6">
      <div class="glass border border-white/5 rounded-3xl p-6 lg:p-8 animate-pulse space-y-4">
        <div class="h-6 bg-white/5 rounded w-1/3 mx-auto"></div>
        <div class="h-4 bg-white/5 rounded w-1/4 mx-auto"></div>
        <div class="h-10 bg-white/5 rounded w-2/3 mx-auto"></div>
      </div>

      <div class="space-y-4">
        {#each Array(3) as _}
          <div class="glass border border-white/5 rounded-2xl p-5 space-y-4 animate-pulse">
            <div class="flex justify-between items-center">
              <div class="w-8 h-8 rounded-full bg-white/5"></div>
              <div class="w-20 h-8 bg-white/5 rounded-lg"></div>
            </div>
            <div class="h-12 bg-white/5 rounded w-3/4 ml-auto"></div>
            <div class="h-4 bg-white/5 rounded w-1/2"></div>
          </div>
        {/each}
      </div>
    </div>
  {:else if error || !surah}
    <div class="glass border border-white/5 rounded-3xl p-8 text-center max-w-md mx-auto space-y-4">
      <p class="text-sm text-zinc-400 font-medium">{error || 'Gagal memuat surah.'}</p>
      <a href="/quran" class="inline-block px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-lg">
        Kembali
      </a>
    </div>
  {:else}
    <!-- SURAH HEADER CARD -->
    <div class="relative overflow-hidden rounded-3xl p-6 lg:p-8 bg-gradient-to-tr from-emerald-950/70 to-emerald-900/60 border border-emerald-500/20 shadow-xl text-center space-y-4">
      <div class="absolute inset-0 opacity-5 bg-repeat bg-[size:30px] pointer-events-none islamic-bg"></div>
      
      <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-400/20 text-emerald-400 text-[10px] font-bold tracking-widest uppercase">
          {surah.tempatTurun} • {surah.jumlahAyat} Ayat
        </span>
        <h2 class="text-3xl font-extrabold text-white tracking-wide mt-3">{surah.namaLatin}</h2>
        <p class="text-sm text-zinc-300 font-medium">{surah.arti}</p>
        
        <!-- Bismillah Header -->
        {#if surahId !== 1 && surahId !== 9}
          <div class="pt-6 pb-2">
            <span class="font-arabic-utsmani text-2xl text-white font-medium block">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span>
            <span class="text-[10px] text-zinc-500 font-semibold block mt-1">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang</span>
          </div>
        {/if}
      </div>

      <!-- Header actions -->
      <div class="relative z-10 pt-4 flex items-center justify-center gap-3">
        <button 
          onclick={togglePlayAll}
          class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-xs px-5 py-3 rounded-2xl shadow-lg shadow-emerald-950/30"
        >
          {#if isPlaying}
            <Pause class="w-4 h-4 text-white" />
            <span>Pause Murottal</span>
          {:else}
            <Play class="w-4 h-4 text-white" />
            <span>Putar Audio Surah</span>
          {/if}
        </button>
      </div>
    </div>

    <!-- AYAH LIST -->
    <div class="space-y-4">
      {#each surah.ayat as ayah}
        {@const isActive = activeAyahNum === ayah.nomorAyat}
        <div 
          id={`ayah-${ayah.nomorAyat}`}
          class="glass border rounded-3xl p-5 lg:p-6 transition-all duration-300 flex flex-col gap-6 relative
            {isActive 
              ? 'border-emerald-500/30 bg-emerald-950/10 shadow-lg shadow-emerald-500/5' 
              : 'border-white/5 hover:border-white/10 bg-white/[0.02]'}"
        >
          <!-- Verse Control Bar -->
          <div class="flex items-center justify-between pb-3 border-b border-white/5">
            <!-- Verse number marker -->
            <div class="w-8 h-8 rounded-xl bg-emerald-600/10 border border-emerald-500/20 flex items-center justify-center font-extrabold text-xs text-emerald-400">
              {ayah.nomorAyat}
            </div>

            <!-- Verse Action Buttons -->
            <div class="flex items-center gap-1">
              <!-- Play Murottal per Ayah -->
              <button 
                onclick={() => playVerse(ayah)}
                class="p-2 rounded-xl text-zinc-500 hover:text-emerald-400 hover:bg-white/5 active:scale-90"
                title="Putar Murottal"
              >
                {#if isActive && isPlaying}
                  <Pause class="w-4.5 h-4.5 text-emerald-400 animate-pulse" />
                {:else}
                  <Play class="w-4.5 h-4.5" />
                {/if}
              </button>

              <!-- Bookmark/Fav -->
              <button 
                onclick={() => toggleFavorite(ayah)}
                class="p-2 rounded-xl active:scale-90 {isFavorite(ayah.nomorAyat) ? 'text-rose-500 hover:text-rose-400' : 'text-zinc-500 hover:text-rose-500'}"
                title="Simpan Favorit"
              >
                <Heart class="w-4.5 h-4.5" fill={isFavorite(ayah.nomorAyat) ? 'currentColor' : 'none'} />
              </button>

              <!-- Share -->
              <button 
                onclick={() => shareAyah(ayah)}
                class="p-2 rounded-xl text-zinc-500 hover:text-blue-400 hover:bg-white/5 active:scale-90"
                title="Bagikan"
              >
                <Share2 class="w-4.5 h-4.5" />
              </button>

              <!-- Copy -->
              <button 
                onclick={() => copyAyah(ayah)}
                class="p-2 rounded-xl text-zinc-500 hover:text-emerald-400 hover:bg-white/5 active:scale-90"
                title="Salin Ayat"
              >
                <Copy class="w-4.5 h-4.5" />
              </button>

              <!-- Tafsir Toggle -->
              <button 
                onclick={() => {
                  expandedPerKataAyah = null; // Close per kata if open
                  expandedTafsirAyah = expandedTafsirAyah === ayah.nomorAyat ? null : ayah.nomorAyat;
                }}
                class="p-2 rounded-xl text-zinc-500 hover:text-amber-500 hover:bg-white/5 active:scale-90 {expandedTafsirAyah === ayah.nomorAyat ? 'text-amber-400 bg-white/5' : ''}"
                title="Buka Tafsir"
              >
                <Book class="w-4.5 h-4.5" />
              </button>

              <!-- Tafsir Per Kata (Word-by-word) Toggle -->
              <button 
                onclick={() => {
                  expandedTafsirAyah = null; // Close main tafsir if open
                  togglePerKata(ayah.nomorAyat);
                }}
                class="p-2 rounded-xl text-zinc-500 hover:text-emerald-400 hover:bg-white/5 active:scale-90 {expandedPerKataAyah === ayah.nomorAyat ? 'text-emerald-400 bg-white/5' : ''}"
                title="Tafsir Per Kata"
              >
                <Languages class="w-4.5 h-4.5" />
              </button>
            </div>
          </div>

          <!-- ARABIC TEXT -->
          <div class="text-right py-2 leading-loose">
            <p 
              class="text-white font-arabic-utsmani" 
              style="font-size: {currentSettings.arabicFontSize}px; font-family: {currentSettings.arabicScript === 'utsmani' ? 'var(--font-arabic-utsmani)' : 'var(--font-arabic-indopak)'}; line-height: 2.2;"
              dir="rtl"
            >
              {@html processTajwid(ayah.teksArab, currentSettings.tajwidColored)}
              {#if currentSettings.arabicNumberVisible}
                <span class="inline-flex items-center justify-center font-sans text-xs border border-emerald-500/30 text-emerald-400 w-6.5 h-6.5 rounded-full mr-2 select-none" dir="ltr">
                  {ayah.nomorAyat}
                </span>
              {/if}
            </p>
          </div>

          <!-- TRANSLITERATION (LATIN) -->
          {#if currentSettings.latinEnabled}
            <p 
              class="text-emerald-400/90 font-medium italic leading-relaxed"
              style="font-size: {currentSettings.latinFontSize}px"
            >
              {ayah.teksLatin}
            </p>
          {/if}

          <!-- INDONESIAN TRANSLATION -->
          {#if currentSettings.translationEnabled}
            <p 
              class="text-zinc-300 leading-relaxed font-normal"
              style="font-size: {currentSettings.translationFontSize}px"
            >
              {ayah.teksIndonesia}
            </p>
          {/if}

          <!-- EXPANDABLE TAFSIR SECTION -->
          {#if expandedTafsirAyah === ayah.nomorAyat}
            <div class="mt-2 p-5 rounded-2xl bg-emerald-500/[0.02] border border-emerald-500/10 space-y-3 animate-slide-up">
              <div class="flex items-center justify-between pb-2 border-b border-white/5">
                <span class="text-[10px] font-bold tracking-wider text-emerald-400 uppercase">Tafsir Al-Jalalain</span>
                <button 
                  onclick={() => expandedTafsirAyah = null} 
                  class="text-[10px] font-bold text-zinc-500 hover:text-white"
                >
                  Tutup
                </button>
              </div>
              <p class="text-xs text-zinc-400 leading-relaxed font-medium">
                {getTafsirText(ayah.nomorAyat)}
              </p>
            </div>
          {/if}

          <!-- EXPANDABLE PER KATA (WORD BY WORD) SECTION -->
          {#if expandedPerKataAyah === ayah.nomorAyat}
            <div class="mt-2 p-5 rounded-2xl bg-emerald-950/20 border border-emerald-500/10 space-y-3 animate-slide-up">
              <div class="flex items-center justify-between pb-2 border-b border-white/5">
                <span class="text-[10px] font-bold tracking-wider text-emerald-400 uppercase">Tafsir / Terjemahan Per Kata</span>
                <button 
                  onclick={() => expandedPerKataAyah = null} 
                  class="text-[10px] font-bold text-zinc-500 hover:text-white"
                >
                  Tutup
                </button>
              </div>

              {#if loadingPerKata && !perKataCache[ayah.nomorAyat]}
                <div class="flex items-center gap-2 py-4 justify-center">
                  <div class="w-4 h-4 rounded-full border-2 border-t-emerald-400 border-emerald-500/20 animate-spin"></div>
                  <span class="text-xs text-zinc-500 font-semibold animate-pulse">Memuat arti kata...</span>
                </div>
              {:else if perKataCache[ayah.nomorAyat]}
                <div class="flex flex-wrap gap-2 justify-end py-2" dir="rtl">
                  {#each perKataCache[ayah.nomorAyat] as word}
                    <div class="glass border border-white/5 rounded-2xl p-2.5 flex flex-col items-center justify-center min-w-[75px] text-center space-y-1">
                      <!-- Arabic Word -->
                      <span class="text-base font-arabic-utsmani text-white select-none">{word.text_uthmani || word.text}</span>
                      <!-- Transliteration -->
                      {#if word.transliteration && word.transliteration.text}
                        <span class="text-[8px] text-emerald-400/85 font-medium italic select-none" dir="ltr">
                          {word.transliteration.text}
                        </span>
                      {/if}
                      <!-- Indonesian Translation -->
                      <span class="text-[9px] text-zinc-400 font-bold leading-tight select-none" dir="ltr">
                        {word.translation ? word.translation.text : ''}
                      </span>
                    </div>
                  {/each}
                </div>
              {:else}
                <p class="text-xs text-rose-400 text-center font-bold py-2">Gagal memuat tafsir per kata.</p>
              {/if}
            </div>
          {/if}
        </div>
      {/each}
    </div>
  {/if}

  <!-- FLOATING PERSISTENT PLAYER -->
  {#if activeAyahNum !== null && surah}
    <div class="fixed bottom-20 md:bottom-6 left-4 right-4 md:left-auto md:right-8 md:w-[360px] glass-emerald border border-emerald-500/30 p-4 rounded-3xl z-40 shadow-2xl flex items-center gap-4 animate-slide-up">
      <div class="w-11 h-11 rounded-2xl bg-emerald-600 flex items-center justify-center shadow-lg text-white">
        <Volume2 class="w-5.5 h-5.5 animate-bounce" />
      </div>
      
      <div class="flex-1 min-w-0">
        <h4 class="text-xs font-bold text-white truncate">{surah.namaLatin} • Ayah {activeAyahNum}</h4>
        <span class="text-[9px] text-zinc-400 font-bold uppercase tracking-wider block mt-0.5">Qori: Sheik {currentSettings.qori}</span>
      </div>

      <div class="flex items-center gap-1 shrink-0">
        <!-- Close Player -->
        <button 
          onclick={() => { if(audioPlayer) audioPlayer.pause(); isPlaying = false; activeAyahNum = null; }}
          class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/5"
          title="Tutup Player"
        >
          <ListRestart class="w-4.5 h-4.5" />
        </button>

        <!-- Play/Pause Toggle -->
        <button 
          onclick={() => {
            if (audioPlayer) {
              if (isPlaying) {
                audioPlayer.pause();
                isPlaying = false;
              } else {
                audioPlayer.play();
                isPlaying = true;
              }
            }
          }}
          class="w-10 h-10 rounded-xl bg-white text-emerald-950 flex items-center justify-center hover:scale-105 active:scale-95 shadow-md"
          title="Mainkan/Jeda"
        >
          {#if isPlaying}
            <Pause class="w-4.5 h-4.5 text-emerald-950" fill="currentColor" />
          {:else}
            <Play class="w-4.5 h-4.5 text-emerald-950" fill="currentColor" />
          {/if}
        </button>
      </div>
    </div>
  {/if}

  <!-- BOTTOM READER TOOLBAR -->
  <div class="fixed bottom-0 left-0 right-0 glass border-t border-white/5 py-3 px-4 flex justify-around items-center z-40 backdrop-blur-lg md:max-w-md md:mx-auto md:bottom-4 md:rounded-2xl md:border">
    <!-- Isi (Index) -->
    <button 
      onclick={() => showNavigationModal = true}
      class="flex flex-col items-center justify-center gap-1 text-zinc-400 hover:text-emerald-400 active:scale-95 transition-all"
    >
      <List class="w-5 h-5" />
      <span class="text-[9px] font-bold">Isi</span>
    </button>

    <!-- Scroll Otomatis -->
    <button 
      onclick={toggleAutoScroll}
      class="flex flex-col items-center justify-center gap-1 active:scale-95 transition-all
        {autoScrollActive ? 'text-emerald-400 font-extrabold' : 'text-zinc-400 hover:text-emerald-400'}"
    >
      <ChevronsDown class="w-5 h-5 {autoScrollActive ? 'animate-bounce' : ''}" />
      <span class="text-[9px] font-bold">Scroll Otomatis</span>
    </button>

    <!-- Putar Audio (Murottal toggle) -->
    <button 
      onclick={togglePlayAll}
      class="flex flex-col items-center justify-center gap-1 active:scale-95 transition-all
        {isPlaying ? 'text-emerald-400 font-extrabold' : 'text-zinc-400 hover:text-emerald-400'}"
    >
      {#if isPlaying}
        <Pause class="w-5 h-5 animate-pulse" />
      {:else}
        <Play class="w-5 h-5" />
      {/if}
      <span class="text-[9px] font-bold">Putar Audio</span>
    </button>

    <!-- Agenda (Jadwal Sholat popup) -->
    <button 
      onclick={() => showAgendaModal = true}
      class="flex flex-col items-center justify-center gap-1 text-zinc-400 hover:text-emerald-400 active:scale-95 transition-all"
    >
      <Calendar class="w-5 h-5" />
      <span class="text-[9px] font-bold">Agenda</span>
    </button>
  </div>

  <!-- NAVIGATION INDEX MODAL (ISI) -->
  {#if showNavigationModal}
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end md:items-center justify-center p-4 z-50 animate-fade-in">
      <div class="glass-emerald border border-emerald-500/20 p-6 rounded-t-3xl md:rounded-3xl max-w-sm w-full space-y-6 shadow-2xl relative">
        <!-- Close button top-right -->
        <button 
          onclick={() => showNavigationModal = false} 
          class="absolute top-4 right-4 text-xs font-bold text-zinc-400 hover:text-white"
        >
          Batal
        </button>

        <h3 class="font-extrabold text-sm text-white tracking-wide border-b border-white/5 pb-2 text-center">Navigasi Al-Qur'an</h3>
        
        <!-- Input Row -->
        <div class="grid grid-cols-3 gap-3">
          <!-- Surat column -->
          <div class="flex flex-col gap-1.5">
            <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Surat</span>
            <select 
              bind:value={selectedSurahNum}
              class="w-full py-2.5 px-2 rounded-xl glass border border-white/10 text-xs font-semibold text-white focus:outline-none focus:border-emerald-500 bg-emerald-950"
            >
              {#each allSurahs as s}
                <option value={s.nomor} class="bg-zinc-950 text-white">{s.nomor}. {s.namaLatin}</option>
              {/each}
            </select>
          </div>

          <!-- Ayat column -->
          <div class="flex flex-col gap-1.5">
            <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Ayat</span>
            <input 
              type="number" 
              bind:value={selectedAyahNumInput}
              min="1" 
              max={allSurahs.find(s => s.nomor === selectedSurahNum)?.jumlahAyat || 286}
              class="w-full py-2 px-3 rounded-xl glass border border-white/10 text-xs font-semibold text-white text-center focus:outline-none focus:border-emerald-500"
            />
          </div>

          <!-- Halaman column -->
          <div class="flex flex-col gap-1.5">
            <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">Halaman</span>
            <input 
              type="number" 
              bind:value={selectedHalamanNum}
              min="1" 
              max="604"
              class="w-full py-2 px-3 rounded-xl glass border border-white/10 text-xs font-semibold text-white text-center focus:outline-none focus:border-emerald-500"
            />
          </div>
        </div>

        <!-- Action Buttons matching screenshot exactly -->
        <div class="grid grid-cols-2 gap-4 pt-2">
          <button 
            onclick={() => jumpToAyah(true)}
            class="py-3.5 rounded-2xl glass border border-white/10 text-xs font-bold text-zinc-300 hover:text-white active:scale-95 transition-all text-center"
          >
            Ke Tafsir
          </button>
          
          <button 
            onclick={() => jumpToAyah(false)}
            class="py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold active:scale-95 transition-all text-center"
          >
            Ke Surat
          </button>
        </div>
      </div>
    </div>
  {/if}

  <!-- AGENDA PRAYER TIMES MODAL -->
  {#if showAgendaModal}
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fade-in">
      <div class="glass-emerald border border-emerald-500/20 p-6 rounded-3xl max-w-sm w-full space-y-4 shadow-2xl relative">
        <button 
          onclick={() => showAgendaModal = false} 
          class="absolute top-4 right-4 text-xs font-bold text-zinc-400 hover:text-white"
        >
          Tutup
        </button>

        <h3 class="font-extrabold text-sm text-white tracking-wide border-b border-white/5 pb-2 text-center flex items-center justify-center gap-2">
          <Compass class="w-4 h-4 text-emerald-400" />
          Agenda Jadwal Sholat
        </h3>
        
        {#if prayerTimesData}
          <div class="divide-y divide-white/5 text-xs font-semibold">
            {#each [
              { label: 'Subuh', val: prayerTimesData.Fajr },
              { label: 'Dzuhur', val: prayerTimesData.Dhuhr },
              { label: 'Ashar', val: prayerTimesData.Asr },
              { label: 'Maghrib', val: prayerTimesData.Maghrib },
              { label: 'Isya', val: prayerTimesData.Isha }
            ] as shol}
              <div class="flex justify-between items-center py-2.5">
                <span class="text-zinc-400">{shol.label}</span>
                <span class="text-white font-bold">{shol.val} WIB</span>
              </div>
            {/each}
          </div>
        {:else}
          <p class="text-xs text-zinc-500 text-center py-4">Memuat jadwal sholat...</p>
        {/if}
      </div>
    </div>
  {/if}

</div>
