import { writable } from 'svelte/store';

// We store the token in localStorage
const storedToken = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
const storedUser = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('auth_user') || 'null') : null;
const storedGuest = typeof window !== 'undefined' ? localStorage.getItem('guest_mode') === 'true' : false;

export const authToken = writable<string | null>(storedToken);
export const authUser = writable<any | null>(storedUser);
export const guestMode = writable<boolean>(storedGuest);

// Setup subscription to save to localStorage
if (typeof window !== 'undefined') {
    authToken.subscribe(token => {
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
    });

    authUser.subscribe(user => {
        if (user) {
            localStorage.setItem('auth_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('auth_user');
        }
    });

    guestMode.subscribe(isGuest => {
        if (isGuest) {
            localStorage.setItem('guest_mode', 'true');
        } else {
            localStorage.removeItem('guest_mode');
        }
    });
}

// Helper fetch function that attaches token
export async function fetchWithAuth(url: string, options: RequestInit = {}) {
    const token = localStorage.getItem('auth_token');
    
    const headers = new Headers(options.headers || {});
    headers.set('Content-Type', 'application/json');
    headers.set('Accept', 'application/json');
    
    if (token) {
        headers.set('Authorization', `Bearer ${token}`);
    }

    const response = await fetch(url, {
        ...options,
        headers
    });

    return response;
}
