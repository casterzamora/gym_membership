import { useState, useContext } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { AuthContext } from '@/context/AuthContext'
import { authAPI } from '@/services/api'
import { motion } from 'framer-motion'
import { Eye, EyeOff, LogIn, AlertCircle, CheckCircle } from 'lucide-react'
import { Button, Card } from '@/components'
import toast from 'react-hot-toast'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [rememberMe, setRememberMe] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const [loading, setLoading] = useState(false)
  const { login } = useContext(AuthContext)
  const navigate = useNavigate()

  const handleDemoLogin = () => {
    setEmail('admin@gym.com')
    setPassword('password')
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!email || !password) {
      setError('Email and password are required')
      return
    }
    
    setLoading(true)
    setError('')
    setSuccess('')

    try {
      console.log(`Attempting login with ${email}...`)
      const response = await authAPI.login(email, password)
      console.log('Login response:', response)
      
      if (response.data.success) {
        setSuccess('Login successful! Redirecting...')
        toast.success('Welcome back!')
        const user = response.data.data.user
        const token = response.data.data.token
        console.log(`Login succeeded for ${user.email} (${user.role})`)
        
        // Call login to update AuthContext
        login(user, token)
        
        // Redirect based on user role - give React time to process state updates
        const redirectPath = user.role === 'admin' ? '/admin/dashboard' 
                            : user.role === 'trainer' ? '/trainer/dashboard' 
                            : '/dashboard'
        console.log(`Redirecting to ${redirectPath}`)
        
        // Use a slightly longer timeout to ensure AuthContext state is updated
        setTimeout(() => {
          console.log(`Performing redirect to ${redirectPath}`)
          navigate(redirectPath)
        }, 300)
      } else {
        throw new Error(response.data.message || 'Login failed')
      }
    } catch (err) {
      console.error('Login error:', err)
      const message = err.response?.data?.message || err.message || 'Login failed. Please try again.'
      setError(message)
      toast.error(message)
    } finally {
      setLoading(false)
    }
  }

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
        delayChildren: 0.2,
      },
    },
  }

  const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
  }

  return (
    <div className="min-h-screen bg-dark-bg relative overflow-hidden">
      {/* Animated Background */}
      <div className="fixed inset-0 -z-10">
        <div className="absolute top-1/4 left-10 w-72 h-72 bg-gold-bright/5 rounded-full blur-3xl"></div>
        <div className="absolute bottom-1/4 right-10 w-96 h-96 bg-accent-orange/5 rounded-full blur-3xl"></div>
      </div>

      <div className="min-h-screen flex items-center justify-center px-4 py-12">
        <motion.div 
          className="w-full max-w-md"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          {/* Logo/Header */}
          <motion.div variants={itemVariants} className="text-center mb-8">
            <motion.div 
              animate={{ rotate: 360 }} 
              transition={{ duration: 20, repeat: Infinity, ease: 'linear' }}
              className="inline-block mb-4"
            >
              <LogIn size={52} className="text-gold-bright" />
            </motion.div>
            <h1 className="text-4xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent mb-2">
              Welcome Back
            </h1>
            <p className="text-gray-400">Sign in to your Gold's Gym account</p>
          </motion.div>

          {/* Demo Login Info */}
          <motion.div variants={itemVariants}>
            <Card className="mb-6 bg-accent-teal/10 border-accent-teal/30 p-4">
              <div className="flex gap-3">
                <CheckCircle size={20} className="text-accent-teal flex-shrink-0 mt-0.5" />
                <div>
                  <p className="text-sm font-semibold text-accent-teal mb-2">Demo Account Available</p>
                  <button 
                    type="button"
                    onClick={handleDemoLogin}
                    className="text-sm text-accent-teal hover:underline font-semibold"
                  >
                    Click to auto-fill demo credentials →
                  </button>
                </div>
              </div>
            </Card>
          </motion.div>

          {/* Error Message */}
          {error && (
            <motion.div variants={itemVariants}>
              <Card className="mb-6 bg-red-500/10 border-red-500/30 p-4 flex gap-3">
                <AlertCircle size={20} className="text-red-400 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-red-300 font-medium">{error}</p>
              </Card>
            </motion.div>
          )}

          {/* Success Message */}
          {success && (
            <motion.div variants={itemVariants}>
              <Card className="mb-6 bg-accent-teal/10 border-accent-teal/30 p-4 flex gap-3">
                <CheckCircle size={20} className="text-accent-teal flex-shrink-0 mt-0.5" />
                <p className="text-sm text-accent-teal font-medium">{success}</p>
              </Card>
            </motion.div>
          )}

          {/* Login Form */}
          <motion.div variants={itemVariants}>
            <Card>
              <form onSubmit={handleSubmit} className="space-y-5">
                {/* Email Input */}
                <div>
                  <label htmlFor="email" className="block text-sm font-semibold text-gray-300 mb-2">
                    Email Address
                  </label>
                  <input
                    id="email"
                    type="email"
                    name="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                    placeholder="you@example.com"
                    required
                    disabled={loading}
                  />
                </div>

                {/* Password Input */}
                <div>
                  <label htmlFor="password" className="block text-sm font-semibold text-gray-300 mb-2">
                    Password
                  </label>
                  <div className="relative">
                    <input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      name="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors pr-10"
                      placeholder="••••••••"
                      required
                      disabled={loading}
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gold-bright transition-colors"
                      disabled={loading}
                    >
                      {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                    </button>
                  </div>
                </div>

                {/* Remember Me & Forgot Password */}
                <div className="flex items-center justify-between text-sm">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={rememberMe}
                      onChange={(e) => setRememberMe(e.target.checked)}
                      className="w-4 h-4 rounded border-gold-bright/30 text-gold-bright"
                      disabled={loading}
                    />
                    <span className="text-gray-400">Remember me</span>
                  </label>
                  <button 
                    type="button"
                    className="text-gold-bright hover:text-gold-400 font-semibold transition-colors"
                    disabled={loading}
                  >
                    Forgot password?
                  </button>
                </div>

                {/* Submit Button */}
                <Button 
                  type="submit"
                  variant="primary" 
                  size="lg"
                  className="w-full"
                  isLoading={loading}
                  disabled={loading}
                >
                  {loading ? 'Signing in...' : 'Sign In'}
                </Button>
              </form>
            </Card>
          </motion.div>

          {/* Sign Up Link */}
          <motion.div variants={itemVariants} className="mt-6 text-center">
            <p className="text-gray-400">
              Don't have an account?{' '}
              <Link to="/register" className="text-gold-bright hover:text-gold-400 font-bold transition-colors">
                Create one
              </Link>
            </p>
          </motion.div>

          {/* Footer */}
          <motion.div variants={itemVariants} className="mt-8 pt-6 border-t border-gold-bright/10 text-center">
            <p className="text-xs text-gray-500">
              Need help? Contact support@goldsgym.com
            </p>
          </motion.div>
        </motion.div>
      </div>
    </div>
  )
}
