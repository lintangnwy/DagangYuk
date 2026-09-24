import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token if it exists
api.interceptors.request.use(config => {
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type']
  }

  const token = localStorage.getItem('dagang_token') || localStorage.getItem('token')
  
  // Don't send token for public endpoints
  const publicEndpoints = ['/login', '/midtrans/webhook']
  const isPublicEndpoint = publicEndpoints.some(endpoint => 
    config.url?.includes(endpoint) || config.url === endpoint
  )
  
  if (token && !isPublicEndpoint) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('dagang_token')
      localStorage.removeItem('dagang_user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
