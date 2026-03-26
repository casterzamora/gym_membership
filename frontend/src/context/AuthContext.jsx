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
        console.log('AuthContext: Token valid, setting user:', data.data.name)
        setUser(data.data)
      } else {
        console.log('AuthContext: Token invalid, logging out')
        logout()
      }
    } catch (error) {
      console.error('AuthContext: Token validation failed:', error)
      logout()
    } finally {
      setLoading(false)
    }
  }

  const login = (userData, newToken) => {
    // Set flag FIRST to ensure it's set before token update triggers effect
    console.log('AuthContext.login: Starting login for', userData.name)
    skipValidationRef.current = true
    
    // Update all state at once
    setUser(userData)
    localStorage.setItem('token', newToken)
    setToken(newToken)
    setLoading(false)
    
    console.log('AuthContext.login: Complete. User role:', userData.role)
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
