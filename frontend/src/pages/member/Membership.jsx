import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { membersAPI, plansAPI, paymentsAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { CreditCard, Calendar, CheckCircle, ArrowLeft, AlertCircle } from 'lucide-react'
import { Button, Card, LoadingSpinner, Badge } from '@/components'
import toast from 'react-hot-toast'

export default function Membership() {
  const { user, loading: authLoading } = useContext(AuthContext)
  const [loading, setLoading] = useState(true)
  const [memberData, setMemberData] = useState(null)
  const [currentPlan, setCurrentPlan] = useState(null)
  const [allPlans, setAllPlans] = useState([])
  const [paymentHistory, setPaymentHistory] = useState([])
  const [error, setError] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login')
      return
    }
    
    if (user) {
      fetchMembershipData()
    }
  }, [user, authLoading])

  const fetchMembershipData = async () => {
    try {
      setLoading(true)
      
      // Fetch member data
      const memberRes = await membersAPI.get(user.id)
      const member = memberRes.data.data
      setMemberData(member)
      
      // Fetch all plans
      const plansRes = await plansAPI.list()
      const plans = plansRes.data.data || []
      setAllPlans(plans)
      
      // Find current plan
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
      console.error('Error fetching membership data:', err)
      setError(err.response?.data?.message || 'Failed to load membership information')
    } finally {
      setLoading(false)
    }
  }

  const handleUpgradePlan = (planId) => {
    if (planId === memberData.plan_id) {
      toast.info('You are already on this plan')
      return
    }
    toast.info('Plan upgrade feature coming soon')
    // In the future, this will redirect to payment page
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
      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Header */}
        <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="mb-8">
          <button
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-2 text-gold-bright hover:text-gold-400 mb-4 font-semibold transition-colors"
          >
            <ArrowLeft size={20} />
            Back to Dashboard
          </button>
          <h1 className="text-4xl font-black text-white mb-2">Membership Management</h1>
          <p className="text-gray-400">View and manage your fitness plan</p>
        </motion.div>

        {/* Error Message */}
        {error && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="mb-6">
            <Card className="bg-red-500/10 border-red-500/30 p-4 flex gap-3">
              <AlertCircle size={20} className="text-red-400 flex-shrink-0 mt-0.5" />
              <p className="text-red-300">{error}</p>
            </Card>
          </motion.div>
        )}

        {/* Current Plan Section */}
        {currentPlan && (
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="mb-12">
            <h2 className="text-2xl font-bold text-white mb-4">Current Plan</h2>
            <Card className="bg-gradient-to-br from-gold-600/20 to-gold-500/10 border-gold-500/30">
              <div className="flex items-start justify-between mb-6">
                <div>
                  <h3 className="text-3xl font-bold text-gold-bright mb-2">{currentPlan.plan_name}</h3>
                  <p className="text-gray-300">{currentPlan.description}</p>
                </div>
                <Badge variant="success" className="text-lg px-4 py-2">Active</Badge>
              </div>

              <div className="grid md:grid-cols-3 gap-6 py-6 border-y border-gold-500/20">
                <div>
                  <p className="text-gold-500 text-sm font-bold mb-1">PRICE</p>
                  <p className="text-2xl font-bold text-white">${currentPlan.price}</p>
                  <p className="text-xs text-gray-400">per {currentPlan.duration}</p>
                </div>
                <div>
                  <p className="text-gold-500 text-sm font-bold mb-1">DURATION</p>
                  <p className="text-2xl font-bold text-white">{currentPlan.duration}</p>
                </div>
                <div>
                  <p className="text-gold-500 text-sm font-bold mb-1">STATUS</p>
                  <div className="flex items-center gap-2">
                    <CheckCircle size={20} className="text-green-500" />
                    <span className="text-white font-semibold">Active</span>
                  </div>
                </div>
              </div>

              <div className="mt-6 pt-6 border-t border-gold-500/20">
                <p className="text-gray-400 text-sm mb-4">Manage your membership</p>
                <div className="flex gap-4">
                  <Button variant="primary" onClick={() => navigate('/profile/edit')}>
                    Edit Profile
                  </Button>
                  <Button variant="secondary">
                    Renew Plan
                  </Button>
                </div>
              </div>
            </Card>
          </motion.div>
        )}

        {/* Available Plans Section */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }} className="mb-12">
          <h2 className="text-2xl font-bold text-white mb-4">Other Membership Plans</h2>
          <div className="grid md:grid-cols-3 gap-6">
            {allPlans.filter(p => p.id !== memberData?.plan_id).map((plan) => (
              <Card key={plan.id} className="hover:border-gold-bright/50 transition">
                <h3 className="text-xl font-bold text-gold-bright mb-3">{plan.plan_name}</h3>
                <p className="text-gray-400 text-sm mb-4">{plan.description}</p>
                
                <div className="my-6 py-6 border-y border-gray-700">
                  <p className="text-3xl font-bold text-white">${plan.price}</p>
                  <p className="text-xs text-gray-400">per {plan.duration}</p>
                </div>

                <Button 
                  variant="primary" 
                  className="w-full"
                  onClick={() => handleUpgradePlan(plan.id)}
                >
                  Switch to this Plan
                </Button>
              </Card>
            ))}
          </div>
        </motion.div>

        {/* Payment History */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}>
          <h2 className="text-2xl font-bold text-white mb-4">Payment History</h2>
          
          {paymentHistory.length === 0 ? (
            <Card className="text-center py-12">
              <CreditCard size={48} className="mx-auto text-gold-bright/30 mb-4" />
              <p className="text-gray-400">No payment history yet</p>
            </Card>
          ) : (
            <Card>
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="border-b border-gray-700">
                    <tr>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Date</th>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Amount</th>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Status</th>
                      <th className="text-left py-3 px-4 text-gold-500 font-bold">Plan</th>
                    </tr>
                  </thead>
                  <tbody>
                    {paymentHistory.map((payment) => (
                      <tr key={payment.id} className="border-b border-gray-700 hover:bg-dark-secondary/50 transition">
                        <td className="py-3 px-4 text-white">{new Date(payment.created_at).toLocaleDateString()}</td>
                        <td className="py-3 px-4 text-gold-bright font-semibold">${payment.amount}</td>
                        <td className="py-3 px-4">
                          <Badge variant={payment.status === 'completed' ? 'success' : 'warning'}>
                            {payment.status}
                          </Badge>
                        </td>
                        <td className="py-3 px-4 text-gray-300">{payment.plan || 'N/A'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </Card>
          )}
        </motion.div>
      </div>
    </div>
  )
}
