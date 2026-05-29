export interface Surah {
  nomor: number;
  nama: string;
  namaLatin: string;
  jumlahAyat: number;
  tempatTurun: string;
  arti: string;
  deskripsi: string;
  audioFull: Record<string, string>;
}

export interface Ayah {
  nomorAyat: number;
  teksArab: string;
  teksLatin: string;
  teksIndonesia: string;
  audio: Record<string, string>;
}

export interface SurahDetail extends Surah {
  ayat: Ayah[];
  suratSebelumnya: SurahLink | boolean;
  suratSelanjutnya: SurahLink | boolean;
}

export interface SurahLink {
  nomor: number;
  nama: string;
  namaLatin: string;
  jumlahAyat: number;
}

export interface TafsirAyah {
  ayat: number;
  teks: string;
}

export interface TafsirDetail {
  nomor: number;
  nama: string;
  namaLatin: string;
  jumlahAyat: number;
  tempatTurun: string;
  arti: string;
  deskripsi: string;
  tafsir: TafsirAyah[];
}

export interface PrayerTimes {
  Fajr: string;
  Sunrise: string;
  Dhuhr: string;
  Asr: string;
  Sunset: string;
  Maghrib: string;
  Isha: string;
  Imsak: string;
  Midnight: string;
}

export interface DateMeta {
  readable: string;
  timestamp: string;
  hijri: {
    date: string;
    day: string;
    weekday: { en: string; ar: string };
    month: { number: number; en: string; ar: string };
    year: string;
    designation: { abbreviated: string; expanded: string };
  };
  gregorian: {
    date: string;
    day: string;
    weekday: { en: string };
    month: { number: number; en: string };
    year: string;
  };
}

export interface PrayerData {
  timings: PrayerTimes;
  date: DateMeta;
}

// Fetching functions
const EQURAN_BASE_URL = 'https://equran.id/api/v2';
const ALADHAN_BASE_URL = 'https://api.aladhan.com/v1';

export async function fetchSurahs(): Promise<Surah[]> {
  try {
    const res = await fetch(`${EQURAN_BASE_URL}/surat`);
    if (!res.ok) throw new Error('Failed to fetch Surahs');
    const data = await res.json();
    return data.data;
  } catch (error) {
    console.error('Error fetching surahs:', error);
    // Return empty list or fallback to check offline
    throw error;
  }
}

export async function fetchSurahDetail(number: number): Promise<SurahDetail> {
  try {
    const res = await fetch(`${EQURAN_BASE_URL}/surat/${number}`);
    if (!res.ok) throw new Error(`Failed to fetch Surah ${number}`);
    const data = await res.json();
    return data.data;
  } catch (error) {
    console.error(`Error fetching surah ${number} detail:`, error);
    throw error;
  }
}

export async function fetchTafsir(number: number): Promise<TafsirDetail> {
  try {
    const res = await fetch(`${EQURAN_BASE_URL}/tafsir/${number}`);
    if (!res.ok) throw new Error(`Failed to fetch Tafsir for Surah ${number}`);
    const data = await res.json();
    return data.data;
  } catch (error) {
    console.error(`Error fetching tafsir for surah ${number}:`, error);
    throw error;
  }
}

export async function fetchPrayerTimes(lat: number, lon: number): Promise<PrayerData> {
  try {
    const today = new Date();
    const dateStr = `${today.getDate()}-${today.getMonth() + 1}-${today.getFullYear()}`;
    const res = await fetch(`${ALADHAN_BASE_URL}/timings/${dateStr}?latitude=${lat}&longitude=${lon}&method=20`);
    if (!res.ok) throw new Error('Failed to fetch prayer times');
    const data = await res.json();
    return data.data;
  } catch (error) {
    console.error('Error fetching prayer times:', error);
    throw error;
  }
}

export async function fetchPrayerTimesByCity(city: string, country: string = 'Indonesia'): Promise<PrayerData> {
  try {
    const today = new Date();
    const dateStr = `${today.getDate()}-${today.getMonth() + 1}-${today.getFullYear()}`;
    const res = await fetch(`${ALADHAN_BASE_URL}/timingsByCity/${dateStr}?city=${encodeURIComponent(city)}&country=${encodeURIComponent(country)}&method=20`);
    if (!res.ok) throw new Error('Failed to fetch prayer times');
    const data = await res.json();
    return data.data;
  } catch (error) {
    console.error('Error fetching prayer times by city:', error);
    throw error;
  }
}
