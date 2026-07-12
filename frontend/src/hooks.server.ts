import { createServerClient } from '@supabase/ssr'
import { type Handle, redirect } from '@sveltejs/kit'

// Auth routes: redirect logged-in users away from these
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

  const isAuthRoute = authRoutes.some(route => path === route || path.startsWith(`${route}/`))
  
  // Define protected routes (everything except auth routes, api routes, and root '/')
  // We'll treat anything that is not '/' and not authRoute as a protected dashboard route
  const isPublicRoute = path === '/' || isAuthRoute || path.startsWith('/api') || path.startsWith('/auth')

  if (!session) {
    // If not logged in and trying to access dashboard routes, redirect to login
    if (!isPublicRoute) {
      throw redirect(303, '/login')
    }
  } else {
    // If logged in and trying to access auth pages OR the welcome page ('/'), redirect to dashboard
    if (isAuthRoute || path === '/') {
      throw redirect(303, '/home')
    }
  }

  return resolve(event, {
    filterSerializedResponseHeaders(name) {
      return name === 'content-range' || name === 'x-supabase-api-version'
    },
  })
}
