import { fail, redirect } from '@sveltejs/kit'
import type { Actions } from './$types'

export const actions: Actions = {
  register: async ({ request, locals: { supabase } }) => {
    const formData = await request.formData()
    const name = formData.get('name') as string
    const email = formData.get('email') as string
    const password = formData.get('password') as string
    const passwordConfirm = formData.get('passwordConfirm') as string

    if (!name || !email || !password || !passwordConfirm) {
      return fail(400, { error: 'Semua kolom wajib diisi', name, email })
    }

    if (password !== passwordConfirm) {
      return fail(400, { error: 'Password tidak cocok', name, email })
    }

    const { error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: {
          full_name: name,
        },
      },
    })

    if (error) {
      return fail(400, { error: error.message, name, email })
    }

    // Usually after signup with email verification, it redirects to a confirmation page.
    // If no email verification is required, they might be logged in immediately.
    // Let's redirect to /login with a success message or /home directly.
    throw redirect(303, '/home')
  },
}
