<script setup lang="ts">
import type {
  Appointment,
  AppointmentPayload,
  TimeSlot,
} from "~/types/appointment";

const { getAvailability, bookAppointment, getError } = useAppointments();

const selectedDate = ref("");
const selectedTime = ref("");
const slots = ref<TimeSlot[]>([]);
const availabilityLoading = ref(false);
const bookingLoading = ref(false);
const pageError = ref("");
const bookingError = ref("");
const formErrors = ref<Record<string, string[]>>({});
const confirmation = ref<Appointment | null>(null);
const formResetKey = ref(0);
const resettingAfterBooking = ref(false);

const today = computed(() => formatLocalDate(new Date()));

function formatLocalDate(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

function getInitialDate(): string {
  const date = new Date();

  while (date.getDay() === 0 || date.getDay() === 6) {
    date.setDate(date.getDate() + 1);
  }

  return formatLocalDate(date);
}

async function loadAvailability(): Promise<void> {
  selectedTime.value = "";
  slots.value = [];
  pageError.value = "";
  bookingError.value = "";
  formErrors.value = {};
  confirmation.value = null;

  if (!selectedDate.value) {
    return;
  }

  availabilityLoading.value = true;

  try {
    const response = await getAvailability(selectedDate.value);
    slots.value = response.slots;
  } catch (error) {
    const apiError = getError(error);
    pageError.value =
      apiError.errors?.date?.[0] ??
      apiError.message ??
      "Availability could not be loaded.";
  } finally {
    availabilityLoading.value = false;
  }
}

function selectTime(time: string): void {
  selectedTime.value = time;
  formErrors.value = {};
  bookingError.value = "";
  confirmation.value = null;
}

function clearFormError(field: string): void {
  if (formErrors.value[field]) {
    const remainingErrors = { ...formErrors.value };
    delete remainingErrors[field];
    formErrors.value = remainingErrors;
  }

  if (Object.keys(formErrors.value).length === 0) {
    bookingError.value = "";
  }
}

async function submitAppointment(payload: AppointmentPayload): Promise<void> {
  bookingLoading.value = true;
  pageError.value = "";
  bookingError.value = "";
  formErrors.value = {};
  confirmation.value = null;

  try {
    const response = await bookAppointment(payload);
    confirmation.value = response.data;
    resettingAfterBooking.value = true;
    selectedDate.value = "";
    selectedTime.value = "";
    slots.value = [];
    formResetKey.value += 1;
  } catch (error) {
    const apiError = getError(error);
    formErrors.value = apiError.errors ?? {};
    bookingError.value =
      apiError.message ?? "The appointment could not be booked.";
  } finally {
    bookingLoading.value = false;
  }
}

watch(selectedDate, async () => {
  if (resettingAfterBooking.value) {
    resettingAfterBooking.value = false;
    return;
  }

  await loadAvailability();
});

onMounted(() => {
  selectedDate.value = getInitialDate();
});
</script>

<template>
  <div class="site-shell">
    <header class="hero">
      <div id="top" class="hero-content">
        <h1>Choose your hour.<br /><em>Make it count.</em></h1>
        <p class="hero-copy">
          Reserve one quiet hour with the inevitable. Weekdays only, from nine
          in the morning until seven at night.
        </p>
        <a class="hero-link" href="#booking"
          >Reserve your appointment <span>↓</span></a
        >
      </div>

      <div class="hero-symbol" aria-hidden="true">
        <span>✦</span>
      </div>
    </header>

    <main id="booking" class="booking-layout">
      <div class="booking-intro">
        <p class="eyebrow">Reservation</p>
        <h2>Set the date.</h2>
        <p>
          Choose an available weekday and tell us where to send the
          confirmation.
        </p>
      </div>

      <div class="booking-card">
        <DateSelector
          v-model="selectedDate"
          :min="today"
          :loading="availabilityLoading"
        />

        <div v-if="pageError" class="alert" role="alert">
          <strong>We could not complete that request.</strong>
          <span>{{ pageError }}</span>
        </div>

        <TimeSlotGrid
          :slots="slots"
          :selected-time="selectedTime"
          :loading="availabilityLoading"
          @select="selectTime"
        />

        <AppointmentForm
          :key="formResetKey"
          :date="selectedDate"
          :time="selectedTime"
          :loading="bookingLoading"
          :message="bookingError"
          :errors="formErrors"
          @submit="submitAppointment"
          @clear-error="clearFormError"
        />

        <div v-if="confirmation" class="confirmation" role="status">
          <span class="confirmation-mark">✓</span>
          <div>
            <p class="eyebrow">Appointment confirmed</p>
            <h3>We will be waiting.</h3>
            <p>
              {{ confirmation.name }}, your appointment is set for
              <strong>{{ confirmation.date }} at {{ confirmation.time }}</strong
              >.
            </p>
          </div>
        </div>
      </div>
    </main>

    <!-- <footer>
      <span>Dance with Death</span>
    </footer> -->
  </div>
</template>

<style>
:root {
  color-scheme: dark;
  --ink: #0b0b0b;
  --surface: #121111;
  --surface-raised: #181616;
  --paper: #ede8dd;
  --muted: #99938a;
  --line: #302d2a;
  --accent: #ad2f2f;
  --accent-bright: #d14a43;
  --serif: Georgia, "Times New Roman", serif;
  --sans:
    Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
    sans-serif;
}

* {
  box-sizing: border-box;
}

html {
  scroll-behavior: smooth;
}

body {
  margin: 0;
  color: var(--paper);
  background: var(--ink);
  font-family: var(--sans);
}

button,
input {
  font: inherit;
}

button,
a,
input {
  -webkit-tap-highlight-color: transparent;
}

.site-shell {
  min-height: 100vh;
  overflow: hidden;
  background:
    radial-gradient(
      circle at 82% 13%,
      rgba(173, 47, 47, 0.13),
      transparent 24rem
    ),
    var(--ink);
}

.hero {
  position: relative;
  min-height: 720px;
  border-bottom: 1px solid var(--line);
}

.hero-content {
  position: relative;
  z-index: 1;
  width: min(1180px, calc(100% - 48px));
  margin: 0 auto;
  padding-top: 140px;
}

.eyebrow {
  margin: 0 0 18px;
  color: var(--accent-bright);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.hero h1 {
  max-width: 850px;
  margin: 0;
  font-family: var(--serif);
  font-size: clamp(58px, 8.5vw, 112px);
  font-weight: 400;
  letter-spacing: -0.055em;
  line-height: 0.88;
}

.hero h1 em {
  color: var(--accent-bright);
  font-weight: 400;
}

.hero-copy {
  max-width: 520px;
  margin: 38px 0 30px;
  color: var(--muted);
  font-size: 17px;
  line-height: 1.7;
}

.hero-link {
  display: inline-flex;
  gap: 14px;
  align-items: center;
  padding-bottom: 7px;
  color: var(--paper);
  border-bottom: 1px solid var(--accent);
  font-size: 13px;
  letter-spacing: 0.05em;
  text-decoration: none;
}

.hero-symbol {
  position: absolute;
  top: 120px;
  right: max(5vw, 30px);
  display: grid;
  width: min(34vw, 450px);
  aspect-ratio: 1;
  place-items: center;
  color: rgba(173, 47, 47, 0.1);
  border: 1px solid rgba(173, 47, 47, 0.12);
  border-radius: 50%;
  font-size: clamp(180px, 26vw, 380px);
  transform: rotate(12deg);
}

.booking-layout {
  display: grid;
  grid-template-columns: minmax(230px, 0.55fr) minmax(0, 1fr);
  gap: clamp(48px, 8vw, 120px);
  width: min(1180px, calc(100% - 48px));
  margin: 0 auto;
  padding: 120px 0;
}

.booking-intro {
  align-self: start;
  position: sticky;
  top: 40px;
}

.booking-intro h2 {
  margin: 0 0 20px;
  font-family: var(--serif);
  font-size: clamp(38px, 5vw, 58px);
  font-weight: 400;
  letter-spacing: -0.04em;
}

.booking-intro > p:last-child {
  max-width: 310px;
  margin: 0;
  color: var(--muted);
  line-height: 1.7;
}

.booking-card {
  border-top: 1px solid var(--line);
}

.booking-section {
  padding: 40px 0;
  border-bottom: 1px solid var(--line);
}

.section-heading {
  margin-bottom: 28px;
}

.section-heading h2 {
  margin: 0 0 7px;
  font-family: var(--serif);
  font-size: 30px;
  font-weight: 400;
}

.section-heading p {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.5;
}

.date-field,
.details-form > label {
  display: grid;
  gap: 9px;
  color: #c7c0b5;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.date-field {
  margin-left: 0;
}

input {
  width: 100%;
  padding: 15px 16px;
  color: var(--paper);
  border: 1px solid var(--line);
  border-radius: 2px;
  outline: none;
  background: var(--surface);
  transition:
    border-color 160ms ease,
    background 160ms ease;
}

input:focus {
  border-color: var(--accent);
  background: var(--surface-raised);
}

input[aria-invalid="true"] {
  border-color: var(--accent-bright);
}

.slot-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 10px;
  margin-left: 0;
}

.time-slot {
  display: grid;
  gap: 6px;
  justify-items: start;
  min-height: 72px;
  padding: 14px;
  color: var(--paper);
  border: 1px solid var(--line);
  border-radius: 2px;
  background: transparent;
  cursor: pointer;
  transition:
    border-color 160ms ease,
    background 160ms ease,
    transform 160ms ease;
}

.time-slot:hover:not(:disabled) {
  border-color: #665e56;
  background: var(--surface-raised);
  transform: translateY(-2px);
}

.time-slot small {
  color: var(--muted);
  font-size: 9px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.time-slot.selected {
  border-color: var(--accent-bright);
  background: rgba(173, 47, 47, 0.13);
}

.time-slot.occupied,
.time-slot.unavailable {
  color: #6f6a64;
  border-style: dashed;
  cursor: not-allowed;
}

.slot-skeleton {
  min-height: 72px;
  border-radius: 2px;
  background: linear-gradient(100deg, #151414 20%, #211f1d 45%, #151414 70%);
  background-size: 220% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  to {
    background-position-x: -220%;
  }
}

.slot-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  margin: 18px 0 0;
  color: var(--muted);
  font-size: 10px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.slot-legend span {
  display: inline-flex;
  gap: 7px;
  align-items: center;
}

.legend-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #817b73;
}

.legend-dot.occupied {
  background: #4a4743;
}
.legend-dot.selected {
  background: var(--accent-bright);
}

.details-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 22px;
  margin-left: 0;
}

.form-alert {
  grid-column: 1 / -1;
  display: grid;
  gap: 7px;
  padding: 16px 18px;
  color: #d6cfc4;
  border: 1px solid rgba(209, 74, 67, 0.5);
  border-left: 3px solid var(--accent-bright);
  background: rgba(173, 47, 47, 0.1);
  font-size: 13px;
  line-height: 1.5;
}

.form-alert strong {
  color: var(--accent-bright);
  font-size: 14px;
}

.form-alert span {
  color: #b8b0a5;
}

.appointment-summary {
  grid-column: 1 / -1;
  display: flex;
  justify-content: space-between;
  padding: 17px 0;
  color: var(--muted);
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
  font-size: 12px;
}

.appointment-summary strong {
  color: var(--paper);
  font-weight: 500;
}

.submit-button {
  grid-column: 1 / -1;
  min-height: 54px;
  color: #fff;
  border: 1px solid var(--accent);
  border-radius: 2px;
  background: var(--accent);
  cursor: pointer;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  transition:
    background 160ms ease,
    transform 160ms ease;
}

.submit-button:hover:not(:disabled) {
  background: var(--accent-bright);
  transform: translateY(-1px);
}

.submit-button:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.field-error {
  color: var(--accent-bright);
  font-size: 11px;
  font-weight: 400;
  letter-spacing: normal;
  text-transform: none;
}

.alert,
.confirmation {
  display: flex;
  gap: 15px;
  margin: 26px 0;
  padding: 18px 20px;
  border: 1px solid rgba(209, 74, 67, 0.4);
  background: rgba(173, 47, 47, 0.08);
}

.alert {
  flex-direction: column;
  margin-left: 0;
  color: #d6cfc4;
  font-size: 13px;
}

.alert strong {
  color: var(--accent-bright);
}

.confirmation {
  align-items: flex-start;
  margin-top: 32px;
  border-color: rgba(114, 141, 99, 0.45);
  background: rgba(114, 141, 99, 0.08);
}

.confirmation-mark {
  display: grid;
  flex: 0 0 38px;
  height: 38px;
  place-items: center;
  color: #b6cbaa;
  border: 1px solid #728d63;
  border-radius: 50%;
}

.confirmation h3 {
  margin: 0 0 8px;
  font-family: var(--serif);
  font-size: 26px;
  font-weight: 400;
}

.confirmation p:last-child {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.6;
}

footer {
  display: flex;
  justify-content: space-between;
  width: min(1180px, calc(100% - 48px));
  margin: 0 auto;
  padding: 30px 0 44px;
  color: var(--muted);
  border-top: 1px solid var(--line);
  font-family: var(--serif);
}

footer small {
  font-family: var(--sans);
  font-size: 11px;
}

@media (max-width: 800px) {
  .hero {
    min-height: 650px;
  }
  .hero-content {
    padding-top: 110px;
  }
  .hero-symbol {
    top: 240px;
    right: -140px;
    width: 390px;
  }
  .booking-layout {
    grid-template-columns: 1fr;
    padding: 80px 0;
  }
  .booking-intro {
    position: static;
  }
  .slot-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 560px) {
  .hero-content,
  .booking-layout,
  footer {
    width: min(100% - 32px, 1180px);
  }

  .hero {
    min-height: 610px;
  }
  .hero h1 {
    font-size: 56px;
  }
  .hero-copy {
    font-size: 15px;
  }
  .details-form {
    grid-template-columns: 1fr;
  }
  footer {
    gap: 12px;
    flex-direction: column;
  }
}

@media (prefers-reduced-motion: reduce) {
  html {
    scroll-behavior: auto;
  }
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
  }
}
</style>
