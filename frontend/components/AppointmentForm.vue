<script setup lang="ts">
import type { AppointmentPayload } from '~/types/appointment'

const props = defineProps<{
  date: string
  time: string
  loading: boolean
  message: string
  errors: Record<string, string[]>
}>()

const emit = defineEmits<{
  submit: [payload: AppointmentPayload]
  'clear-error': [field: string]
}>()

const name = ref('')
const email = ref('')

function submit(): void {
  emit('submit', {
    name: name.value,
    email: email.value,
    appointment_date: props.date,
    appointment_time: props.time
  })
}
</script>

<template>
  <section class="booking-section" aria-labelledby="details-heading">
    <div class="section-heading">
      <div>
        <h2 id="details-heading">Enter your details</h2>
        <p>One appointment is permitted per email address.</p>
      </div>
    </div>

    <form class="details-form" @submit.prevent="submit">
      <div v-if="message" class="form-alert" role="alert">
        <strong>{{ message }}</strong>
        <span v-if="errors.email">{{ errors.email[0] }}</span>
        <span v-else-if="errors.appointment_time">{{ errors.appointment_time[0] }}</span>
      </div>

      <label>
        <span>Name</span>
        <input
          v-model="name"
          name="name"
          type="text"
          autocomplete="name"
          maxlength="120"
          placeholder="Your full name"
          :aria-invalid="Boolean(errors.name)"
          required
          @input="emit('clear-error', 'name')"
        >
        <small v-if="errors.name" class="field-error">{{ errors.name[0] }}</small>
      </label>

      <label>
        <span>Email address</span>
        <input
          v-model="email"
          name="email"
          type="email"
          autocomplete="email"
          placeholder="you@example.com"
          :aria-invalid="Boolean(errors.email)"
          required
          @input="emit('clear-error', 'email')"
        >
        <small v-if="errors.email" class="field-error">{{ errors.email[0] }}</small>
      </label>

      <div class="appointment-summary">
        <span>Your appointment</span>
        <strong v-if="date && time">{{ date }} at {{ time }}</strong>
        <strong v-else>Select a date and time</strong>
      </div>

      <small v-if="errors.appointment_time" class="field-error">
        {{ errors.appointment_time[0] }}
      </small>

      <button class="submit-button" type="submit" :disabled="!date || !time || loading">
        <span v-if="loading">Booking...</span>
        <span v-else>Book appointment</span>
      </button>
    </form>
  </section>
</template>
