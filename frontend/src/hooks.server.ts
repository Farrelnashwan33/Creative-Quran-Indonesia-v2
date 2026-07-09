import { createServerClient } from '@supabase/ssr'
import { type Handle, redirect } from '@sveltejs/kit'

const protectedRoutes = ['/home', '/premium', '/quran', '/search', '/settings', '/sholat', '/dashboard']
const authRoutes = ['/login', '/register', '/forgot-password', '/verify-email']

export const handle: Handle = async ({ event, resolve }) => {
  event.locals.supabase = createServerClient(import.meta.env.VITE_SUPABASE_URL, import.meta.env.VITE_SUPABASE_ANON_KEY, {
    cookies: {
      getAll: () => event.cookies.getAll(),
      setAll: (cookiesToSet) => {
        cookiesToSet.forEach(({ name, value, options }) => {
          event.cookies.set(name, value, { ...options, path: '/' })
        })
      },
    },
  })

  event.locals.safeGetSession = async () => {
    const {
      data: { session },
    } = await event.locals.supabase.auth.getSession()
    if (!session) {
      return { session: null, user: null }
    }

    const {
      data: { user },
      error,
    } = await event.locals.supabase.auth.getUser()
    if (error) {
      return { session: null, user: null }
    }

    return { session, user }
  }

  const { session, user } = await event.locals.safeGetSession()
  event.locals.session = session
  event.locals.user = user

  const path = event.url.pathname

  // Redirect users who are NOT logged in but trying to access protected routes
  const isProtectedRoute = protectedRoutes.some(route => path.startsWith(route))
  if (!session && isProtectedRoute) {
    throw redirect(303, '/login')
  }

  // Redirect users who ARE logged in but trying to access auth routes
  const isAuthRoute = authRoutes.some(route => path === route || path.startsWith(`${route}/`))
  if (session && isAuthRoute) {
    throw redirect(303, '/home')
  }

  return resolve(event, {
    filterSerializedResponseHeaders(name) {
      return name === 'content-range' || name === 'x-supabase-api-version'
    },
  })
}
