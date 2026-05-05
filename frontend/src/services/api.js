import axios from 'axios'

const API_BASE_URL = '/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Add token to requests (except public checkout endpoints)
api.interceptors.request.use((config) => {
  const publicEndpoints = ['/checkout/session', '/checkout/complete', '/register', '/login', '/verify-email', '/resend-verification-email', '/forgot-password', '/reset-password']
  const isPublicEndpoint = publicEndpoints.some(endpoint => config.url.includes(endpoint))
  
  const token = localStorage.getItem('token')
  if (token && !isPublicEndpoint) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle response errors (do NOT auto-redirect on 401 - let components handle auth)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Just return the error - don't auto-redirect on 401
    // Components like Login, AuthContext, etc. should handle 401s appropriately
    return Promise.reject(error)
  }
)

export const authAPI = {
  register: (data) => api.post('/register', data),
  checkoutSession: (checkoutToken) => api.post('/checkout/session', { checkout_token: checkoutToken }),
  completeCheckout: (data) => api.post('/checkout/complete', data),
  verifyEmail: (data) => api.post('/verify-email', data),
  resendVerificationEmail: (data) => api.post('/resend-verification-email', data),
  forgotPassword: (data) => api.post('/forgot-password', data),
  resetPassword: (data) => api.post('/reset-password', data),
  login: (email, password) => api.post('/login', { email, password }),
  logout: () => api.post('/v1/logout'),
  me: () => api.get('/v1/me'),
}

export const membersAPI = {
  list: () => api.get('/v1/members'),
  search: (q) => api.get('/v1/members/search', { params: { q } }),
  get: (id) => api.get(`/v1/members/${id}`),
  create: (data) => api.post('/v1/members', data),
  update: (id, data) => api.put(`/v1/members/${id}`, data),
  renew: (id, data = {}) => api.post(`/v1/members/${id}/renew`, data),
  upgrade: (id, data) => api.post(`/v1/members/${id}/upgrade`, data),
  delete: (id) => api.delete(`/v1/members/${id}`),
}

export const classesAPI = {
  list: () => api.get('/v1/classes'),
  get: (id) => api.get(`/v1/classes/${id}`),
  create: (data) => api.post('/v1/classes', data),
  update: (id, data) => api.put(`/v1/classes/${id}`, data),
  delete: (id) => api.delete(`/v1/classes/${id}`),
}

export const schedulesAPI = {
  list: () => api.get('/v1/schedules'),
  get: (id) => api.get(`/v1/schedules/${id}`),
  create: (data) => api.post('/v1/schedules', data),
  update: (id, data) => api.put(`/v1/schedules/${id}`, data),
  delete: (id) => api.delete(`/v1/schedules/${id}`),
}

export const attendanceAPI = {
  checkIn: (data) => api.post('/v1/attendance/check-in', data),
  checkOut: (data) => api.post('/v1/attendance/check-out', data),
  list: (params = {}) => api.get('/v1/attendance', { params }),
  mark: (data) => api.post('/v1/attendance', data),
  update: (data) => api.put('/v1/attendance', data),
  unenrollMember: (memberId) => api.delete(`/v1/attendance/unenroll/${memberId}`),
}

export const plansAPI = {
  list: () => api.get('/v1/plans'),
  get: (id) => api.get(`/v1/plans/${id}`),
  create: (data) => api.post('/v1/plans', data),
  update: (id, data) => api.put(`/v1/plans/${id}`, data),
  delete: (id) => api.delete(`/v1/plans/${id}`),
}

export const trainersAPI = {
  list: () => api.get('/v1/trainers'),
  get: (id) => api.get(`/v1/trainers/${id}`),
  workload: (id) => api.get(`/v1/trainers/${id}/workload`),
  workloadSummary: () => api.get('/v1/trainers/workload-summary'),
  create: (data) => api.post('/v1/trainers', data),
  update: (id, data) => api.put(`/v1/trainers/${id}`, data),
  delete: (id) => api.delete(`/v1/trainers/${id}`),
  removeCertification: (trainerId, certId) => api.delete(`/v1/trainers/${trainerId}/certifications/${certId}`),
  resetPassword: (id) => api.post(`/v1/trainers/${id}/reset-password`),
  changePassword: (id, data) => api.post(`/v1/trainers/${id}/change-password`, data),
}

export const equipmentAPI = {
  list: () => api.get('/v1/equipment'),
  get: (id) => api.get(`/v1/equipment/${id}`),
  create: (data) => api.post('/v1/equipment', data),
  update: (id, data) => api.put(`/v1/equipment/${id}`, data),
  delete: (id) => api.delete(`/v1/equipment/${id}`),
}

export const equipmentTrackingAPI = {
  list: (params = {}) => api.get('/v1/equipment-tracking', { params }),
  get: (id) => api.get(`/v1/equipment-tracking/${id}`),
  create: (data) => api.post('/v1/equipment-tracking', data),
  update: (id, data) => api.patch(`/v1/equipment-tracking/${id}`, data),
  delete: (id) => api.delete(`/v1/equipment-tracking/${id}`),
  getClassEquipment: (classId) => api.get(`/v1/classes/${classId}/equipment`),
  markAsInUse: (id) => api.post(`/v1/equipment-tracking/${id}/mark-in-use`),
  markAsReturned: (id) => api.post(`/v1/equipment-tracking/${id}/mark-returned`),
}

export const paymentsAPI = {
  list: () => api.get('/v1/payments'),
  get: (id) => api.get(`/v1/payments/${id}`),
  create: (data) => api.post('/v1/payments', data),
  update: (id, data) => api.put(`/v1/payments/${id}`, data),
  delete: (id) => api.delete(`/v1/payments/${id}`),
}

export const paymentMethodsAPI = {
  list: () => api.get('/v1/payment-methods'),
  get: (id) => api.get(`/v1/payment-methods/${id}`),
}

export const reportsAPI = {
  revenue: (params = {}) => api.get('/v1/reports/revenue', { params }),
  classPopularity: (params = {}) => api.get('/v1/reports/class-popularity', { params }),
  lowAttendanceMembers: (params = {}) => api.get('/v1/reports/low-attendance-members', { params }),
}

export default {
  authAPI,
  membersAPI,
  classesAPI,
  schedulesAPI,
  attendanceAPI,
  plansAPI,
  trainersAPI,
  equipmentAPI,
  equipmentTrackingAPI,
  paymentsAPI,
  paymentMethodsAPI,
  reportsAPI,
  request: api,
}
