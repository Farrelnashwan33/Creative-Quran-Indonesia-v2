import { createClient } from '@supabase/supabase-js'

const supabaseUrl = 'https://zpiuojnlxfbulnmgfvbq.supabase.co'
const supabaseAnonKey = 'sb_publishable_UfSvWPAXnT0r8CXJh8WeKQ_PaNrJnO_'

const supabase = createClient(supabaseUrl, supabaseAnonKey)

async function test() {
  const { data, error } = await supabase.from('profiles').select('*').limit(1)
  console.log('Error:', error)
  console.log('Data:', data)
}

test()
