<script setup lang="ts">
import type { TimeSlot } from '~/types/appointment'

defineProps<{
  slots: TimeSlot[]
  selectedTime: string
  loading: boolean
}>()

const emit = defineEmits<{
  select: [time: string]
}>()
</script>

<template>
  <section class="booking-section" aria-labelledby="time-heading">
    <div class="section-heading">
      <div>
        <h2 id="time-heading">Select a time</h2>
        <p>Each appointment lasts one hour.</p>
      </div>
    </div>

    <div v-if="loading" class="slot-grid" aria-label="Loading available times">
      <span v-for="index in 10" :key="index" class="slot-skeleton" />
    </div>

    <div v-else class="slot-grid">
      <button
        v-for="slot in slots"
        :key="slot.time"
        type="button"
        class="time-slot"
        :class="{
          selected: selectedTime === slot.time,
          occupied: slot.status === 'occupied',
          unavailable: slot.status === 'unavailable'
        }"
        :disabled="!slot.available"
        :aria-pressed="selectedTime === slot.time"
        @click="emit('select', slot.time)"
      >
        <span>{{ slot.time }}</span>
        <small>{{ slot.status }}</small>
      </button>
    </div>

    <div class="slot-legend" aria-label="Time slot legend">
      <span><i class="legend-dot available" /> Available</span>
      <span><i class="legend-dot occupied" /> Occupied</span>
      <span><i class="legend-dot selected" /> Selected</span>
    </div>
  </section>
</template>
