import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { authAPI } from '@/services/api'
import { motion } from 'framer-motion'
import { Mail, ArrowLeft, CheckCircle, AlertCircle } from 'lucide-react'
import { Button, Card } from '@/components'
import toast from 'react-hot-toast'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')
    setSuccess('')

    try {
      const response = await authAPI.forgotPassword({ email })
      if (response.data.success) {
        setSuccess(response.data.message || 'If that email exists, a reset link has been sent.')
        toast.success('Password reset email sent')
      } else {
        throw new Error(response.data.message || 'Failed to send reset email')
      }
    } catch (err) {
      const message = err.response?.data?.message || err.message || 'Failed to send reset email'
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
              <Mail size={30} />
            </div>
            <h1 className="text-4xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent mb-2">
              Forgot Password
            </h1>
            <p className="text-gray-400">Enter your email and we&apos;ll send a reset link.</p>
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
                  <label htmlFor="email" className="block text-sm font-semibold text-gray-300 mb-2">
                    Email Address
                  </label>
                  <input
                    id="email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                    placeholder="you@example.com"
                    required
                    disabled={loading}
                  />
                </div>

                <Button type="submit" variant="primary" size="lg" className="w-full" isLoading={loading} disabled={loading}>
                  {loading ? 'Sending reset link...' : 'Send Reset Link'}
                </Button>
              </form>
            </Card>
          </motion.div>
        </motion.div>
      </div>
    </div>
  )
}
