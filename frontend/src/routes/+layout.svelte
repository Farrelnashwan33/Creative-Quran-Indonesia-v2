<script lang="ts">
  import '../app.css';
  import { invalidate } from '$app/navigation';
  import { onMount } from 'svelte';

  let { data, children } = $props();
  let supabase = $derived(data?.supabase);
  let session = $derived(data?.session);

  onMount(() => {
    const { data: { subscription } } = supabase.auth.onAuthStateChange((event, _session) => {
      if (event === 'SIGNED_IN' || event === 'SIGNED_OUT' || event === 'TOKEN_REFRESHED') {
        if (_session?.expires_at !== session?.expires_at) {
          invalidate('supabase:auth');
        }
      }
    });

    return () => subscription.unsubscribe();
  });
</script>

{@render children()}
