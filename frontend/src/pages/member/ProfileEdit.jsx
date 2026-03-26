import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { membersAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { User, Mail, Phone, Calendar, ArrowLeft } from 'lucide-react'
import { Button, Card, LoadingSpinner } from '@/components'
import toast from 'react-hot-toast'

export default function ProfileEdit() {
  const { user, loading: authLoading } = useContext(AuthContext)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [memberData, setMemberData] = useState(null)
  
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    phone: '',
    date_of_birth: '',
  })
  
  const navigate = useNavigate()

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login')
      return
    }
    
    if (user) {
      fetchMemberData()
    }
  }, [user, authLoading])

  const fetchMemberData = async () => {
    try {
      setLoading(true)
      const response = await membersAPI.get(user.id)
      const data = response.data.data
      setMemberData(data)
      setFormData({
        first_name: data.first_name,
        last_name: data.last_name,
        phone: data.phone || '',
        date_of_birth: data.date_of_birth || '',
      })
    } catch (err) {
      console.error('Error fetching member data:', err)
      setError(err.response?.data?.message || 'Failed to load profile')
    } finally {
      setLoading(false)
    }
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSaving(true)
    setError('')

    try {
      const response = await membersAPI.update(memberData.id, formData)
      setMemberData(response.data.data)
      toast.success('Profile updated successfully!')
    } catch (err) {
      console.error('Update error:', err)
      const message = err.response?.data?.message || 'Failed to update profile'
      setError(message)
      toast.error(message)
    } finally {
      setSaving(false)
    }
  }

  if (authLoading || loading) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center">
        <LoadingSpinner />
      </div>
    )
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-2xl mx-auto px-4 py-8">
        {/* Header */}
        <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="mb-8">
          <button
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-2 text-gold-bright hover:text-gold-400 mb-4 font-semibold transition-colors"
          >
            <ArrowLeft size={20} />
            Back to Dashboard
          </button>
          <h1 className="text-4xl font-black text-white mb-2">Edit Profile</h1>
          <p className="text-gray-400">Update your personal information</p>
        </motion.div>

        {/* Error Message */}
        {error && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="mb-6">
            <Card className="bg-red-500/10 border-red-500/30 p-4">
              <p className="text-red-300">{error}</p>
            </Card>
          </motion.div>
        )}

        {/* Profile Form */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <Card>
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Email (Read-only) */}
              <div>
                <label className="block text-sm font-semibold text-gray-300 mb-2">
                  <Mail size={16} className="inline mr-2" />
                  Email
                </label>
                <input
                  type="email"
                  value={user?.email || ''}
                  disabled
                  className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-gray-400 cursor-not-allowed opacity-50"
                />
                <p className="text-xs text-gray-500 mt-1">Email cannot be changed</p>
              </div>

              {/* First Name */}
              <div>
                <label className="block text-sm font-semibold text-gray-300 mb-2">
                  <User size={16} className="inline mr-2" />
                  First Name
                </label>
                <input
                  type="text"
                  name="first_name"
                  value={formData.first_name}
                  onChange={handleChange}
                  className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                  placeholder="John"
                  required
                  disabled={saving}
                />
              </div>

              {/* Last Name */}
              <div>
                <label className="block text-sm font-semibold text-gray-300 mb-2">
                  <User size={16} className="inline mr-2" />
                  Last Name
                </label>
                <input
                  type="text"
                  name="last_name"
                  value={formData.last_name}
                  onChange={handleChange}
                  className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                  placeholder="Doe"
                  required
                  disabled={saving}
                />
              </div>

              {/* Phone */}
              <div>
                <label className="block text-sm font-semibold text-gray-300 mb-2">
                  <Phone size={16} className="inline mr-2" />
                  Phone Number
                </label>
                <input
                  type="tel"
                  name="phone"
                  value={formData.phone}
                  onChange={handleChange}
                  className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                  placeholder="(555) 123-4567"
                  disabled={saving}
                />
              </div>

              {/* Date of Birth */}
              <div>
                <label className="block text-sm font-semibold text-gray-300 mb-2">
                  <Calendar size={16} className="inline mr-2" />
                  Date of Birth
                </label>
                <input
                  type="date"
                  name="date_of_birth"
                  value={formData.date_of_birth}
                  onChange={handleChange}
                  className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white focus:outline-none focus:border-gold-bright transition-colors"
                  required
                  disabled={saving}
                />
              </div>

              {/* Action Buttons */}
              <div className="flex gap-4 pt-6 border-t border-gray-700">
                <Button
                  type="submit"
                  variant="primary"
                  size="lg"
                  className="flex-1"
                  isLoading={saving}
                  disabled={saving}
                >
                  Save Changes
                </Button>
                <Button
                  type="button"
                  variant="secondary"
                  size="lg"
                  className="flex-1"
                  disabled={saving}
                  onClick={() => navigate('/dashboard')}
                >
                  Cancel
                </Button>
              </div>
            </form>
          </Card>
        </motion.div>
      </div>
    </div>
  )
}
