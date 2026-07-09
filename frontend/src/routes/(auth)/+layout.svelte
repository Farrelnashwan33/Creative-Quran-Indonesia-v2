<script lang="ts">
  import '../../app.css';
  let { children } = $props();
</script>

<div class="min-h-screen bg-[#031E1A] text-white overflow-hidden">
  {@render children()}
</div>
