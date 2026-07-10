import { createBrowserClient, isBrowser, parse } from '@supabase/ssr'
import type { LayoutLoad } from './$types'

export const load: LayoutLoad = async ({ fetch, data, depends }) => {
  depends('supabase:auth')

  const supabase = createBrowserClient(import.meta.env.VITE_SUPABASE_URL, import.meta.env.VITE_SUPABASE_ANON_KEY, {
    global: {
      fetch,
    },
  })

  const {
    data: { session },
  } = await supabase.auth.getSession()

  return { supabase, session, user: data.user }
}
