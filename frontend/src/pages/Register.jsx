import { useState, useContext, useEffect } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { AuthContext } from '@/context/AuthContext'
import { authAPI, plansAPI } from '@/services/api'
import { motion } from 'framer-motion'
import { AlertCircle, CheckCircle } from 'lucide-react'
import toast from 'react-hot-toast'

export default function Register() {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirm, setPasswordConfirm] = useState('')
  const [planId, setPlanId] = useState('')
  const [plans, setPlans] = useState([])
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const [loading, setLoading] = useState(false)
  const [plansLoading, setPlansLoading] = useState(true)
  const { login } = useContext(AuthContext)
  const navigate = useNavigate()

  useEffect(() => {
    fetchPlans()
  }, [])

  const fetchPlans = async () => {
    try {
      console.log('Fetching membership plans...')
      const response = await plansAPI.list()
      console.log('Plans fetched:', response.data)
      if (response.data.data) {
        setPlans(response.data.data)
        if (response.data.data.length > 0) {
          setPlanId(response.data.data[0].id)
        }
      }
    } catch (err) {
      console.error('Failed to fetch plans:', err)
      setError('Failed to load membership plans. Please try again.')
      toast.error('Failed to load plans')
    } finally {
      setPlansLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    // Validation
    if (!name || !email || !password) {
      setError('Please fill in all fields')
      return
    }

    if (password !== passwordConfirm) {
      setError('Passwords do not match')
      return
    }

    if (password.length < 8) {
      setError('Password must be at least 8 characters')
      return
    }

    setLoading(true)
    setError('')
    setSuccess('')

    try {
      console.log('Attempting registration with:', { name, email, plan_id: planId })
      const registerResponse = await authAPI.register({
        name,
        email,
        password,
        password_confirmation: passwordConfirm,
        plan_id: planId ? parseInt(planId) : null,
      })

      console.log('Registration response:', registerResponse)

      if (registerResponse.data.success) {
        setSuccess('Account created successfully! Redirecting...')
        toast.success('Welcome to GymFlow!')
        const user = registerResponse.data.data.user
        const token = registerResponse.data.data.token
        
        console.log(`Registration succeeded for ${user.email}`)
        
        // Call login to update AuthContext
        login(user, token)
        
        // Redirect to dashboard
        setTimeout(() => {
          navigate('/dashboard')
        }, 500)
      } else {
        throw new Error(registerResponse.data.message || 'Registration failed')
      }
    } catch (err) {
      console.error('Registration error:', err)
      console.error('Error response:', err.response?.data)
      
      // Handle validation errors
      if (err.response?.data?.errors) {
        const errors = err.response.data.errors
        const errorMessages = Object.entries(errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n')
        setError(errorMessages)
        toast.error('Validation error - check form')
      } else {
        const message = err.response?.data?.message || err.message || 'Registration failed'
        setError(message)
        toast.error(message)
      }
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
          {/* Header */}
          <motion.div variants={itemVariants} className="text-center mb-8">
            <h1 className="text-4xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent mb-2">
              Join GymFlow
            </h1>
            <p className="text-gray-400">Start your fitness journey today</p>
          </motion.div>

          {/* Error Message */}
          {error && (
            <motion.div variants={itemVariants}>
              <div className="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg flex gap-3">
                <AlertCircle size={20} className="text-red-400 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-red-300 font-medium">{error}</p>
              </div>
            </motion.div>
          )}

          {/* Success Message */}
          {success && (
            <motion.div variants={itemVariants}>
              <div className="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg flex gap-3">
                <CheckCircle size={20} className="text-green-400 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-green-300 font-medium">{success}</p>
              </div>
            </motion.div>
          )}

          {/* Form Card */}
          <motion.div variants={itemVariants}>
            <div className="bg-dark-card border border-gold-bright/20 rounded-lg p-8">
              <form onSubmit={handleSubmit} className="space-y-4">
                {/* Full Name */}
                <div>
                  <label htmlFor="name" className="block text-sm font-bold text-gold-500 mb-2">
                    Full Name
                  </label>
                  <input
                    id="name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-gold-bright transition"
                    placeholder="John Doe"
                    required
                  />
                </div>

                {/* Email */}
                <div>
                  <label htmlFor="email" className="block text-sm font-bold text-gold-500 mb-2">
                    Email Address
                  </label>
                  <input
                    id="email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-gold-bright transition"
                    placeholder="you@example.com"
                    required
                  />
                </div>

                {/* Password */}
                <div>
                  <label htmlFor="password" className="block text-sm font-bold text-gold-500 mb-2">
                    Password
                  </label>
                  <input
                    id="password"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-gold-bright transition"
                    placeholder="••••••••"
                    required
                  />
                  <p className="text-xs text-gray-400 mt-1">Minimum 8 characters</p>
                </div>

                {/* Confirm Password */}
                <div>
                  <label htmlFor="passwordConfirm" className="block text-sm font-bold text-gold-500 mb-2">
                    Confirm Password
                  </label>
                  <input
                    id="passwordConfirm"
                    type="password"
                    value={passwordConfirm}
                    onChange={(e) => setPasswordConfirm(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-gold-bright transition"
                    placeholder="••••••••"
                    required
                  />
                </div>

                {/* Membership Plan */}
                <div>
                  <label htmlFor="plan" className="block text-sm font-bold text-gold-500 mb-2">
                    Membership Plan (optional)
                  </label>
                  {plansLoading ? (
                    <div className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-gray-400">
                      Loading plans...
                    </div>
                  ) : plans.length > 0 ? (
                    <select
                      id="plan"
                      value={planId}
                      onChange={(e) => setPlanId(e.target.value)}
                      className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition"
                    >
                      <option value="">-- Select a plan --</option>
                      {plans.map((plan) => (
                        <option key={plan.id} value={plan.id}>
                          {plan.plan_name || plan.name} - ${plan.price}/month
                        </option>
                      ))}
                    </select>
                  ) : (
                    <div className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-gray-400">
                      No plans available
                    </div>
                  )}
                </div>

                {/* Submit Button */}
                <button
                  type="submit"
                  disabled={loading || plansLoading}
                  className="w-full px-4 py-3 bg-gradient-to-r from-gold-600 to-gold-500 text-black font-bold rounded-lg hover:from-gold-500 hover:to-gold-400 transition disabled:opacity-50 disabled:cursor-not-allowed mt-6"
                >
                  {loading ? 'Creating Account...' : 'Create Account'}
                </button>
              </form>
            </div>
          </motion.div>

          {/* Sign In Link */}
          <motion.div variants={itemVariants} className="mt-6 text-center">
            <p className="text-gray-400">
              Already have an account?{' '}
              <Link to="/login" className="text-gold-bright hover:text-gold-500 font-bold transition">
                Sign in here
              </Link>
            </p>
          </motion.div>
        </motion.div>
      </div>
    </div>
  )
}
