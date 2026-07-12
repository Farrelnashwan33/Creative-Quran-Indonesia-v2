import { createClient } from '@supabase/supabase-js'

const supabaseUrl = 'https://zpiuojnlxfbulnmgfvbq.supabase.co'
const supabaseAnonKey = 'sb_publishable_UfSvWPAXnT0r8CXJh8WeKQ_PaNrJnO_'

const supabase = createClient(supabaseUrl, supabaseAnonKey)

async function test() {
  const { data, error } = await supabase.auth.signUp({
    email: 'test' + Date.now() + '@example.com',
    password: 'password123',
    options: {
      data: {
        full_name: 'Test User',
        username: 'testuser'
      }
    }
  })
  console.log('Error:', error)
  console.log('Data:', data)
}

test()
