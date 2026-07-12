import { fail, redirect } from '@sveltejs/kit'
import type { Actions } from './$types'

export const actions: Actions = {
  signUp: async ({ request, locals: { supabase } }) => {
    const formData = await request.formData()
    const name = formData.get('name') as string
    const username = formData.get('username') as string
    const email = formData.get('email') as string
    const password = formData.get('password') as string
    const passwordConfirm = formData.get('passwordConfirm') as string

    if (!name || !username || !email || !password || !passwordConfirm) {
      return fail(400, { error: 'Semua kolom wajib diisi', name, username, email })
    }

    if (password !== passwordConfirm) {
      return fail(400, { error: 'Password tidak cocok', name, username, email })
    }

    const { data: signUpData, error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: {
          full_name: name,
          username: username,
        },
      },
    })

    if (error) {
      console.error('SignUp error:', error)
      return fail(400, {
        error: error.message ?? 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.',
        name,
        username,
        email
      })
    }

    // Check if email confirmation is required.
    // When email confirmation is ON  → signUpData.session is null
    // When email confirmation is OFF → signUpData.session is set (auto-login by Supabase)
    //
    // In BOTH cases we sign the user out immediately and redirect to /login
    // with a success query param so they must explicitly log in.
    // This prevents auto-login into the dashboard after register.
    if (signUpData.session) {
      // Email confirmation is disabled — Supabase auto-created a session.
      // Sign it out immediately so the user is forced to log in manually.
      await supabase.auth.signOut()
    }

    const needsEmailConfirmation = !signUpData.session

    throw redirect(303, needsEmailConfirmation
      ? '/login?registered=1&confirm=1'
      : '/login?registered=1'
    )
  },
}
