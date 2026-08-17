import type {
  ApiErrorResponse,
  AppointmentPayload,
  AppointmentResponse,
  AvailabilityResponse
} from '~/types/appointment'

export function useAppointments() {
  const config = useRuntimeConfig()

  async function getAvailability(date: string): Promise<AvailabilityResponse> {
    return await $fetch<AvailabilityResponse>(`${config.public.apiBaseUrl}/availability`, {
      query: { date }
    })
  }

  async function bookAppointment(payload: AppointmentPayload): Promise<AppointmentResponse> {
    return await $fetch<AppointmentResponse>(`${config.public.apiBaseUrl}/appointments`, {
      method: 'POST',
      body: payload
    })
  }

  function getError(error: unknown): ApiErrorResponse {
    if (error && typeof error === 'object' && 'data' in error) {
      return (error as { data?: ApiErrorResponse }).data ?? {}
    }

    return { message: 'Something went wrong. Please try again.' }
  }

  return {
    getAvailability,
    bookAppointment,
    getError
  }
}
