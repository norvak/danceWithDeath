export type SlotStatus = 'available' | 'occupied' | 'unavailable'

export interface TimeSlot {
  time: string
  available: boolean
  status: SlotStatus
}

export interface AvailabilityResponse {
  date: string
  slots: TimeSlot[]
}

export interface AppointmentPayload {
  name: string
  email: string
  appointment_date: string
  appointment_time: string
}

export interface Appointment {
  id: number
  name: string
  email: string
  date: string
  time: string
}

export interface AppointmentResponse {
  message: string
  data: Appointment
}

export interface ApiErrorResponse {
  message?: string
  errors?: Record<string, string[]>
}
