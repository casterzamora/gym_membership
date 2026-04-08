import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { membersAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'

export default function Profile() {
  const { user, loading: authLoading, logout } = useContext(AuthContext)
  const [memberData, setMemberData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [editMode, setEditMode] = useState(false)
  const [formData, setFormData] = useState({})
  const navigate = useNavigate()

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login')
      return
    }
    
    if (user) {
      fetchProfileData()
    }
  }, [user, authLoading])

  const fetchProfileData = async () => {
    try {
      setLoading(true)
      const response = await membersAPI.get(user.id)
      setMemberData(response.data.data)
      setFormData({
        name: response.data.data.user?.name || '',
        email: response.data.data.user?.email || '',
        phone: response.data.data.phone || '',
        date_of_birth: response.data.data.date_of_birth || '',
      })
    } catch (err) {
      console.error('Failed to fetch profile:', err)
    } finally {
      setLoading(false)
    }
  }

  const handleLogout = async () => {
    try {
      logout()
      navigate('/')
    } catch (err) {
      console.error('Logout failed:', err)
    }
  }

  if (authLoading || loading) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center">
        <div className="text-gray-400 text-center">
          <div className="text-lg">Loading profile...</div>
        </div>
      </div>
    )
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg">
      <div className="max-w-2xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-white mb-2">My Profile</h1>
          <p className="text-gray-400">Manage your account information</p>
        </div>

        {/* Profile Card */}
        <div className="bg-gray-800 border border-gold-600 rounded p-8">
          {/* User Avatar */}
          <div className="text-center mb-8">
            <div className="w-24 h-24 mx-auto bg-gradient-to-br from-gold-500 to-gold-600 rounded-full flex items-center justify-center mb-4">
              <span className="text-5xl">💪</span>
            </div>
            <h2 className="text-2xl font-bold text-white">{user?.name}</h2>
            <p className="text-gray-400">{user?.email}</p>
          </div>

          {/* Membership Info */}
          <div className="mb-8 p-6 bg-gray-700 rounded border border-gold-600/30">
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <div className="text-gold-500 text-sm font-bold mb-1">CURRENT PLAN</div>
                <div className="text-white">{memberData?.plan?.plan_name || 'N/A'}</div>
              </div>
              <div>
                <div className="text-gold-500 text-sm font-bold mb-1">STATUS</div>
                <div className="text-white">{memberData?.plan?.name || 'Active'}</div>
              </div>
              <div>
                <div className="text-gold-500 text-sm font-bold mb-1">MEMBER SINCE</div>
                <div className="text-white">
                  {memberData?.created_at ? new Date(memberData.created_at).toLocaleDateString() : 'N/A'}
                </div>
              </div>
              <div>
                <div className="text-gold-500 text-sm font-bold mb-1">RENEWAL DATE</div>
                <div className="text-white">
                  {memberData?.membership?.end_date ? new Date(memberData.membership.end_date).toLocaleDateString() : 'N/A'}
                </div>
              </div>
            </div>
          </div>

          {/* Contact Info */}
          <div className="mb-8">
            <h3 className="text-xl font-bold text-white mb-4">Contact Information</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm text-gray-400 mb-1">Email</label>
                <div className="px-4 py-2 bg-gray-700 rounded text-white">
                  {user?.email}
                </div>
              </div>
              <div>
                <label className="block text-sm text-gray-400 mb-1">Phone</label>
                <div className="px-4 py-2 bg-gray-700 rounded text-white">
                  {memberData?.phone || 'Not provided'}
                </div>
              </div>
              <div>
                <label className="block text-sm text-gray-400 mb-1">Date of Birth</label>
                <div className="px-4 py-2 bg-gray-700 rounded text-white">
                  {memberData?.date_of_birth || 'Not provided'}
                </div>
              </div>
            </div>
          </div>

          {/* Actions */}
          <div className="flex gap-4">
            <button className="flex-1 px-4 py-3 bg-gold-600 text-black font-bold rounded hover:bg-gold-500 transition">
              Upgrade Plan
            </button>
            <button
              onClick={handleLogout}
              className="flex-1 px-4 py-3 bg-gray-700 text-white font-bold rounded border border-gold-600 hover:bg-gray-600 transition"
            >
              Sign Out
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
