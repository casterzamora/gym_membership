import React, { createContext, useState, useEffect, useRef } from 'react'

export const AuthContext = createContext()

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(localStorage.getItem('token'))
  const [loading, setLoading] = useState(true)
  const skipValidationRef = useRef(false)

  useEffect(() => {
    // If we're skipping validation, clear the flag and return early
    if (skipValidationRef.current) {
      console.log('AuthContext: Skipping validation on next token update')
      skipValidationRef.current = false
      setLoading(false)
      return
    }

    if (token) {
      console.log('AuthContext: Token exists, validating...')
      validateToken()
    } else {
      console.log('AuthContext: No token, not loading')
      setLoading(false)
    }
  }, [token])

  const validateToken = async () => {
    try {
      const response = await fetch('/api/v1/me', {
        headers: { Authorization: `Bearer ${token}` }
      })
      if (response.ok) {
        const data = await response.json()
        const userData = data.data
        const displayName = userData.first_name ? `${userData.first_name} ${userData.last_name}` : userData.name
        console.log('AuthContext: Token valid, setting user:', displayName)
        setUser({
          ...userData,
          displayName,
          role: userData.role || 'member'
        })
      } else if (response.status === 401) {
        console.log('AuthContext: Token invalid (401), logging out')
        logout()
      } else {
        // For 500 or other errors, log but don't logout - token might still be valid
        console.warn('AuthContext: Token validation returned', response.status, '- keeping user logged in')
      }
    } catch (error) {
      console.error('AuthContext: Token validation failed:', error)
      // Don't logout on network errors - keep the user logged in
    } finally {
      setLoading(false)
    }
  }

  const login = (userData, newToken) => {
    console.log('AuthContext.login: Starting login for:', userData.email)
    // Set flag to skip validation
    skipValidationRef.current = true
    
    // Set the token first
    localStorage.setItem('token', newToken)
    setToken(newToken)
    
    // Handle both member and user formats
    const displayName = userData.first_name 
      ? `${userData.first_name} ${userData.last_name}` 
      : userData.name
    
    const userRole = userData.role || 'member'
    
    const userData_normalized = {
      ...userData,
      displayName,
      role: userRole,
      // Normalize email field for both user and member
      email: userData.email,
    }
    
    setUser(userData_normalized)
    setLoading(false)
    
    console.log('AuthContext.login: Complete. User:', displayName, 'Role:', userRole)
  }

  const logout = () => {
    console.log('AuthContext: Logging out')
    setUser(null)
    setToken(null)
    localStorage.removeItem('token')
    setLoading(false)
  }

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}
