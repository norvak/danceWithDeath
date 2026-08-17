<script setup lang="ts">
defineProps<{
  modelValue: string
  min: string
  loading: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <section class="booking-section" aria-labelledby="date-heading">
    <div class="section-heading">
      <div>
        <h2 id="date-heading">Choose your date</h2>
        <p>Appointments are available Monday through Friday.</p>
      </div>
    </div>

    <label class="date-field">
      <span>Appointment date</span>
      <input
        :value="modelValue"
        type="date"
        :min="min"
        :disabled="loading"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      >
    </label>
  </section>
</template>
