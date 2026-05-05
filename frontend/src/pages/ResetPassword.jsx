import { useEffect, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { authAPI } from '@/services/api'
import { motion } from 'framer-motion'
import { Lock, ArrowLeft, CheckCircle, AlertCircle } from 'lucide-react'
import { Button, Card } from '@/components'
import toast from 'react-hot-toast'

export default function ResetPassword() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [token, setToken] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')

  useEffect(() => {
    setEmail(searchParams.get('email') || '')
    setToken(searchParams.get('token') || '')
  }, [searchParams])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')
    setSuccess('')

    try {
      const response = await authAPI.resetPassword({
        email,
        token,
        password,
        password_confirmation: passwordConfirmation,
      })

      if (response.data.success) {
        setSuccess(response.data.message || 'Password reset successfully')
        toast.success('Password updated')
        setTimeout(() => navigate('/login'), 1500)
      } else {
        throw new Error(response.data.message || 'Failed to reset password')
      }
    } catch (err) {
      const message = err.response?.data?.message || err.message || 'Failed to reset password'
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
      transition: { staggerChildren: 0.1, delayChildren: 0.2 },
    },
  }

  const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
  }

  return (
    <div className="min-h-screen bg-dark-bg relative overflow-hidden">
      <div className="fixed inset-0 -z-10">
        <div className="absolute top-1/4 left-10 w-72 h-72 bg-gold-bright/5 rounded-full blur-3xl"></div>
        <div className="absolute bottom-1/4 right-10 w-96 h-96 bg-accent-orange/5 rounded-full blur-3xl"></div>
      </div>

      <div className="min-h-screen flex items-center justify-center px-4 py-12">
        <motion.div className="w-full max-w-md" variants={containerVariants} initial="hidden" animate="visible">
          <motion.button
            variants={itemVariants}
            onClick={() => navigate('/login')}
            className="mb-6 flex items-center gap-2 text-gray-400 hover:text-gold-bright transition group"
          >
            <ArrowLeft size={18} className="group-hover:-translate-x-1 transition-transform" />
            <span>Back to Login</span>
          </motion.button>

          <motion.div variants={itemVariants} className="text-center mb-8">
            <div className="inline-flex items-center justify-center mb-4 w-16 h-16 rounded-full bg-gold-bright/10 text-gold-bright">
              <Lock size={30} />
            </div>
            <h1 className="text-4xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent mb-2">
              Reset Password
            </h1>
            <p className="text-gray-400">Create a new password for {email || 'your account'}.</p>
          </motion.div>

          {error && (
            <motion.div variants={itemVariants}>
              <Card className="mb-6 bg-red-500/10 border-red-500/30 p-4 flex gap-3">
                <AlertCircle size={20} className="text-red-400 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-red-300 font-medium">{error}</p>
              </Card>
            </motion.div>
          )}

          {success && (
            <motion.div variants={itemVariants}>
              <Card className="mb-6 bg-accent-teal/10 border-accent-teal/30 p-4 flex gap-3">
                <CheckCircle size={20} className="text-accent-teal flex-shrink-0 mt-0.5" />
                <p className="text-sm text-accent-teal font-medium">{success}</p>
              </Card>
            </motion.div>
          )}

          <motion.div variants={itemVariants}>
            <Card>
              <form onSubmit={handleSubmit} className="space-y-5">
                <div>
                  <label htmlFor="password" className="block text-sm font-semibold text-gray-300 mb-2">
                    New Password
                  </label>
                  <input
                    id="password"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                    placeholder="Enter a new password"
                    required
                    disabled={loading}
                  />
                </div>

                <div>
                  <label htmlFor="passwordConfirmation" className="block text-sm font-semibold text-gray-300 mb-2">
                    Confirm New Password
                  </label>
                  <input
                    id="passwordConfirmation"
                    type="password"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                    placeholder="Confirm your new password"
                    required
                    disabled={loading}
                  />
                </div>

                <Button type="submit" variant="primary" size="lg" className="w-full" isLoading={loading} disabled={loading || !email || !token}>
                  {loading ? 'Resetting password...' : 'Reset Password'}
                </Button>
              </form>
            </Card>
          </motion.div>
        </motion.div>
      </div>
    </div>
  )
}
