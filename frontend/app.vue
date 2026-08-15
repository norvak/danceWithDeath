<script setup lang="ts">
const config = useRuntimeConfig()
</script>

<template>
  <main>
    <h1>Dance with Death</h1>
    <p>The development environment is ready.</p>
    <small>API: {{ config.public.apiBaseUrl }}</small>
  </main>
</template>

<style>
body {
  margin: 0;
  color: #f7f7f7;
  background: #111111;
  font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}

main {
  display: grid;
  min-height: 100vh;
  place-content: center;
  gap: 0.75rem;
  padding: 2rem;
  text-align: center;
}

h1,
p {
  margin: 0;
}

small {
  color: #aaaaaa;
}
</style>
