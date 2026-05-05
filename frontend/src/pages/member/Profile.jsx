import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { membersAPI, plansAPI, paymentsAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { Edit2, Check, X, CreditCard, Calendar, CheckCircle, AlertCircle, LogOut } from 'lucide-react'
import toast from 'react-hot-toast'

export default function Profile() {
  const { user, loading: authLoading, logout } = useContext(AuthContext)
  const [memberData, setMemberData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [editMode, setEditMode] = useState(false)
  const [saving, setSaving] = useState(false)
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    phone: '',
    date_of_birth: '',
  })
  const [allPlans, setAllPlans] = useState([])
  const [currentPlan, setCurrentPlan] = useState(null)
  const [paymentHistory, setPaymentHistory] = useState([])
  const [renewing, setRenewing] = useState(false)
  const [upgrading, setUpgrading] = useState(false)
  const [upgradePlanId, setUpgradePlanId] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    if (user) {
      fetchProfileData()
    }
  }, [user])

  const fetchProfileData = async () => {
    try {
      setLoading(true)
      // Fetch member data - use member_id from auth payload if available, otherwise use user.id
      const memberId = user.member_id || user.id
      const response = await membersAPI.get(memberId)
      const member = response.data.data
      setMemberData(member)
      setFormData({
        first_name: member.first_name || '',
        last_name: member.last_name || '',
        phone: member.phone || '',
        date_of_birth: member.date_of_birth || '',
      })
      
      // Fetch plans
      const plansRes = await plansAPI.list()
      const plans = plansRes.data.data || []
      setAllPlans(plans)
      if (member.plan_id) {
        const current = plans.find(p => p.id === member.plan_id)
        setCurrentPlan(current)
      }
      
      // Fetch payment history
      try {
        const paymentsRes = await paymentsAPI.list()
        if (paymentsRes.data?.data) {
          const memberPayments = paymentsRes.data.data.filter(p => p.member_id === member.id)
          setPaymentHistory(memberPayments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)))
        }
      } catch (err) {
        console.error('Error fetching payments:', err)
      }
    } catch (err) {
      console.error('Failed to fetch profile:', err)
      toast.error('Failed to load profile')
    } finally {
      setLoading(false)
    }
  }

  const handleSaveProfile = async () => {
    if (!formData.first_name.trim() || !formData.last_name.trim()) {
      toast.error('First and last name are required')
      return
    }

    try {
      setSaving(true)
      console.log('Updating member', memberData.id, 'with:', formData)
      
      const response = await membersAPI.update(memberData.id, formData)
      console.log('Update response:', response)
      
      setMemberData(response.data.data || {
        ...memberData,
        ...formData
      })
      setEditMode(false)
      toast.success('Profile updated successfully')
    } catch (err) {
      console.error('Failed to save profile:', err)
      console.error('Error response:', err.response?.data)
      toast.error(err.response?.data?.message || 'Failed to save profile')
    } finally {
      setSaving(false)
    }
  }

  const handleCancel = () => {
    setFormData({
      first_name: memberData.first_name || '',
      last_name: memberData.last_name || '',
      phone: memberData.phone || '',
      date_of_birth: memberData.date_of_birth || '',
    })
    setEditMode(false)
  }

  const handleLogout = async () => {
    try {
      logout()
      navigate('/')
    } catch (err) {
      console.error('Logout failed:', err)
    }
  }

  const handleRenewMembership = async () => {
    if (!memberData?.id) return

    navigate(`/checkout?flow=renew&member_id=${memberData.id}`)
  }

  const handleUpgradeMembership = async () => {
    if (!memberData?.id || !upgradePlanId) {
      toast.error('Please select a plan to upgrade')
      return
    }

    navigate(`/checkout?flow=upgrade&member_id=${memberData.id}&plan_id=${upgradePlanId}`)
  }

  const getMembershipExpiryDate = () => {
    const rawExpiry = memberData?.membership?.end_date || memberData?.membership_end || memberData?.membership_end_date
    if (!rawExpiry) return null

    const expiryDate = new Date(rawExpiry)
    if (Number.isNaN(expiryDate.getTime())) return null

    return expiryDate
  }

  const getDaysLeft = () => {
    const expiryDate = getMembershipExpiryDate()
    if (!expiryDate) return null

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const normalizedExpiry = new Date(expiryDate)
    normalizedExpiry.setHours(0, 0, 0, 0)

    return Math.ceil((normalizedExpiry.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
  }

  const canUpgradeNow = () => {
    const expiryDate = getMembershipExpiryDate()
    if (!expiryDate) return false

    return new Date() >= expiryDate
  }

  const canRenewNow = () => {
    const expiryDate = getMembershipExpiryDate()
    if (!expiryDate) return false

    return new Date() >= expiryDate
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
      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-white mb-2">My Account</h1>
          <p className="text-gray-400">Manage your profile, membership and payments</p>
        </div>

        {/* Profile Card */}
        <div className="bg-dark-secondary border border-gold-600/20 rounded-lg p-8 shadow-xl shadow-black/20">
          {/* User Avatar */}
          <div className="text-center mb-8">
            <div className="w-24 h-24 mx-auto bg-gold-600 rounded-full flex items-center justify-center mb-4 border border-gold-500/80">
              <span className="text-4xl text-black font-bold">EG</span>
            </div>
            <h2 className="text-2xl font-bold text-white">{user?.name}</h2>
            <p className="text-gray-400">{user?.email}</p>
          </div>

          {/* Membership Info Summary */}
          <div className="mb-8 p-6 bg-dark-bg rounded border border-gold-600/20">
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <div className="text-gold-400 text-sm font-bold mb-1">CURRENT PLAN</div>
                <div className="text-white">{currentPlan?.plan_name || 'N/A'}</div>
              </div>
              <div>
                <div className="text-gold-400 text-sm font-bold mb-1">STATUS</div>
                <div className="flex items-center gap-2">
                  <CheckCircle size={16} className="text-green-400" />
                  <span className="text-white">Active</span>
                </div>
              </div>
              <div>
                <div className="text-gold-400 text-sm font-bold mb-1">MEMBER SINCE</div>
                <div className="text-white">
                  {memberData?.created_at ? new Date(memberData.created_at).toLocaleDateString() : 'N/A'}
                </div>
              </div>
              <div>
                <div className="text-gold-400 text-sm font-bold mb-1">EXPIRATION DATE</div>
                <div className="text-white">
                  {getMembershipExpiryDate() ? getMembershipExpiryDate().toLocaleDateString() : 'N/A'}
                </div>
              </div>
              <div>
                <div className="text-gold-400 text-sm font-bold mb-1">DAYS LEFT</div>
                <div className="text-white">
                  {getDaysLeft() === null
                    ? 'N/A'
                    : getDaysLeft() > 0
                      ? `${getDaysLeft()} day${getDaysLeft() === 1 ? '' : 's'} left`
                      : getDaysLeft() === 0
                        ? 'Expires today'
                        : `Expired ${Math.abs(getDaysLeft())} day${Math.abs(getDaysLeft()) === 1 ? '' : 's'} ago`}
                </div>
              </div>
              <div>
                <div className="text-gold-400 text-sm font-bold mb-1">PRICE</div>
                <div className="text-gold-300 font-bold">PHP {currentPlan?.price || '0'}/{currentPlan?.duration || 'month'}</div>
              </div>
            </div>
          </div>

          {/* Personal Info */}
          <div className="mb-8">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-xl font-bold text-white">Personal Information</h3>
              {!editMode && (
                <button
                  onClick={() => setEditMode(true)}
                  className="flex items-center gap-2 text-gold-300 hover:text-gold-200 transition"
                >
                  <Edit2 size={18} />
                  <span>Edit</span>
                </button>
              )}
            </div>
            
            <div className="space-y-4">
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm text-gray-400 mb-2">First Name</label>
                  {editMode ? (
                    <input
                      type="text"
                      value={formData.first_name}
                      onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
                      className="w-full px-4 py-2 bg-dark-bg rounded text-white border border-gold-bright/30 focus:border-gold-bright outline-none transition"
                      placeholder="First name"
                    />
                  ) : (
                    <div className="px-4 py-2 bg-dark-bg rounded text-white border border-gray-700">
                      {memberData?.first_name || 'Not provided'}
                    </div>
                  )}
                </div>
                <div>
                  <label className="block text-sm text-gray-400 mb-2">Last Name</label>
                  {editMode ? (
                    <input
                      type="text"
                      value={formData.last_name}
                      onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
                      className="w-full px-4 py-2 bg-dark-bg rounded text-white border border-gold-bright/30 focus:border-gold-bright outline-none transition"
                      placeholder="Last name"
                    />
                  ) : (
                    <div className="px-4 py-2 bg-dark-bg rounded text-white border border-gray-700">
                      {memberData?.last_name || 'Not provided'}
                    </div>
                  )}
                </div>
              </div>
              <div>
                <label className="block text-sm text-gray-400 mb-2">Email</label>
                <div className="px-4 py-2 bg-dark-bg rounded text-white border border-gray-700 opacity-50">
                  {user?.email}
                </div>
                <p className="text-xs text-gray-500 mt-1">Email cannot be changed</p>
              </div>
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm text-gray-400 mb-2">Phone</label>
                  {editMode ? (
                    <input
                      type="tel"
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      className="w-full px-4 py-2 bg-dark-bg rounded text-white border border-gold-bright/30 focus:border-gold-bright outline-none transition"
                      placeholder="Phone number"
                    />
                  ) : (
                    <div className="px-4 py-2 bg-dark-bg rounded text-white border border-gray-700">
                      {memberData?.phone || 'Not provided'}
                    </div>
                  )}
                </div>
                <div>
                  <label className="block text-sm text-gray-400 mb-2">Date of Birth</label>
                  {editMode ? (
                    <input
                      type="date"
                      value={formData.date_of_birth}
                      onChange={(e) => setFormData({ ...formData, date_of_birth: e.target.value })}
                      className="w-full px-4 py-2 bg-dark-bg rounded text-white border border-gold-bright/30 focus:border-gold-bright outline-none transition"
                    />
                  ) : (
                    <div className="px-4 py-2 bg-dark-bg rounded text-white border border-gray-700">
                      {memberData?.date_of_birth ? new Date(memberData.date_of_birth).toLocaleDateString() : 'Not provided'}
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Edit/Save/Logout Actions */}
          <div className="flex gap-4 pt-6 border-t border-gold-600/20">
            {editMode ? (
              <>
                <button
                  onClick={handleSaveProfile}
                  disabled={saving}
                  className="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white font-bold rounded hover:bg-green-700 transition disabled:opacity-50"
                >
                  <Check size={18} />
                  {saving ? 'Saving...' : 'Save Changes'}
                </button>
                <button
                  onClick={handleCancel}
                  className="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gray-700 text-white font-bold rounded border border-gold-600/20 hover:bg-gray-600 transition"
                >
                  <X size={18} />
                  Cancel
                </button>
              </>
            ) : (
              <button
                onClick={handleLogout}
                className="flex items-center justify-center gap-2 ml-auto px-6 py-3 bg-gray-700 text-white font-bold rounded border border-gold-600/20 hover:bg-gray-600 transition"
              >
                <LogOut size={18} />
                Sign Out
              </button>
            )}
          </div>
        </div>

        {/* Membership Section */}
        <div id="membership" className="mt-12">
          <h2 className="text-2xl font-bold text-white mb-6">Membership Management</h2>
          
          {/* Current Plan Details */}
          {currentPlan && (
            <div className="bg-dark-secondary border border-gold-500/30 rounded-lg p-8 mb-8">
              <div className="flex items-start justify-between mb-6">
                <div>
                  <h3 className="text-3xl font-bold text-gold-300 mb-2">{currentPlan.plan_name}</h3>
                  <p className="text-gray-300">{currentPlan.description}</p>
                </div>
                <div className="flex items-center gap-2 px-4 py-2 bg-green-500/20 border border-green-500 rounded-full">
                  <CheckCircle size={18} className="text-green-400" />
                  <span className="text-green-300 font-semibold">Active</span>
                </div>
              </div>

              <div className="grid md:grid-cols-3 gap-6 py-6 border-y border-gold-500/20">
                <div>
                  <p className="text-gold-500 text-sm font-bold mb-1">PRICE</p>
                  <p className="text-2xl font-bold text-white">PHP {currentPlan.price}</p>
                  <p className="text-xs text-gray-400">per {currentPlan.duration_months || currentPlan.duration || 'N/A'} month(s)</p>
                </div>
                <div>
                  <p className="text-gold-500 text-sm font-bold mb-1">DURATION</p>
                  <p className="text-2xl font-bold text-white">{currentPlan.duration_months || currentPlan.duration || 'N/A'} month(s)</p>
                </div>
                <div>
                  <p className="text-gold-500 text-sm font-bold mb-1">MEMBER SINCE</p>
                  <p className="text-white font-semibold">{memberData?.created_at ? new Date(memberData.created_at).toLocaleDateString() : 'N/A'}</p>
                </div>
              </div>

              <div className="mt-6">
                <button
                  onClick={handleRenewMembership}
                  disabled={renewing}
                  className="px-5 py-2.5 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition disabled:opacity-50"
                >
                  {renewing ? 'Renewing...' : 'Renew Current Plan'}
                </button>
              </div>

              <div className="mt-4 p-4 rounded-lg border border-amber-400/30 bg-amber-500/10 flex gap-3">
                <AlertCircle size={18} className="text-amber-300 flex-shrink-0 mt-0.5" />
                <div className="text-sm text-amber-100">
                  <p className="font-semibold">Renewal notice</p>
                  <p>
                    {canRenewNow()
                      ? 'Your membership is eligible for renewal now.'
                      : 'You can only renew once your current membership expires.'}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Other Plans Grid */}
          {allPlans.length > 1 && (
            <div className="mb-8">
              <h3 className="text-xl font-bold text-white mb-4">Other Available Plans</h3>
              <div className="grid md:grid-cols-2 gap-6">
                {allPlans.filter(p => p.id !== memberData?.plan_id).map((plan) => (
                  <div key={plan.id} className="bg-dark-secondary border border-gold-bright/20 rounded-lg p-6 hover:border-gold-bright/50 transition">
                    <h4 className="text-lg font-bold text-gold-bright mb-2">{plan.plan_name}</h4>
                    <p className="text-gray-400 text-sm mb-4">{plan.description}</p>
                    <div className="py-4 border-y border-gray-700 mb-4">
                      <p className="text-2xl font-bold text-white">PHP {plan.price}</p>
                      <p className="text-xs text-gray-400">per {plan.duration_months || plan.duration || 'N/A'} month(s)</p>
                    </div>
                    <div className="space-y-2">
                      <button
                        onClick={() => setUpgradePlanId(String(plan.id))}
                        className={`w-full px-4 py-2 font-bold rounded transition ${
                          String(plan.id) === String(upgradePlanId)
                            ? 'bg-gold-500 text-black'
                            : 'bg-gold-600 text-black hover:bg-gold-500'
                        }`}
                      >
                        {String(plan.id) === String(upgradePlanId) ? 'Selected for Upgrade' : 'Select This Plan'}
                      </button>
                    </div>
                  </div>
                ))}
              </div>

              <div className="mt-6 flex items-center gap-3">
                <button
                  onClick={handleUpgradeMembership}
                  disabled={upgrading || !upgradePlanId}
                  className="px-5 py-2.5 bg-gold-600 text-black font-semibold rounded hover:bg-gold-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {upgrading ? 'Upgrading...' : 'Upgrade Membership'}
                </button>
                {!upgradePlanId && <span className="text-sm text-gray-400">Select a plan first</span>}
              </div>

              <div className="mt-4 p-4 rounded-lg border border-amber-400/30 bg-amber-500/10 flex gap-3">
                <AlertCircle size={18} className="text-amber-300 flex-shrink-0 mt-0.5" />
                <div className="text-sm text-amber-100">
                  <p className="font-semibold">Upgrade notice</p>
                  <p>
                    {canUpgradeNow()
                      ? 'Your membership is eligible for upgrade now.'
                      : 'You can only upgrade once your current membership expires.'}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Payment History */}
          <div>
            <h3 className="text-xl font-bold text-white mb-4">Payment History</h3>
            {paymentHistory.length === 0 ? (
              <div className="bg-dark-secondary border border-gold-bright/20 rounded-lg p-8 text-center">
                <CreditCard size={48} className="mx-auto text-gold-bright/30 mb-4" />
                <p className="text-gray-400">No payment history yet</p>
              </div>
            ) : (
              <div className="bg-dark-secondary border border-gold-bright/20 rounded-lg overflow-hidden">
                <table className="w-full">
                  <thead className="bg-dark-bg border-b border-gold-bright/20">
                    <tr>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Date</th>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Amount</th>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Status</th>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Plan</th>
                    </tr>
                  </thead>
                  <tbody>
                    {paymentHistory.map((payment) => (
                      <tr key={payment.id} className="border-b border-gold-bright/10 hover:bg-dark-bg/50 transition">
                        <td className="py-3 px-4 text-white">{new Date(payment.created_at).toLocaleDateString()}</td>
                        <td className="py-3 px-4 text-gold-bright font-semibold">${payment.amount}</td>
                        <td className="py-3 px-4">
                          <span className={`px-3 py-1 rounded-full text-sm font-semibold ${payment.status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'}`}>
                            {payment.status}
                          </span>
                        </td>
                        <td className="py-3 px-4 text-gray-300">{payment.description || 'N/A'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
