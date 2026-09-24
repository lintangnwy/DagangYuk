import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Request interceptor to add auth token if it exists (for non-sanctum SPA auth, but with sanctum cookie it might still need it if using token)
api.interceptors.request.use(config => {
  const token = localStorage.getItem('dagang_token')
  
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
