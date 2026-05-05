import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { authAPI, membersAPI, paymentMethodsAPI, plansAPI } from '@/services/api'
import { AlertCircle, CheckCircle } from 'lucide-react'
import toast from 'react-hot-toast'

function isCardMethod(methodName) {
  return (methodName || '').toLowerCase().includes('card')
}

function getPaymentInstructions(methodName) {
  const normalized = (methodName || '').toLowerCase()

  if (normalized.includes('gcash')) {
    return {
      title: 'GCash payment instructions',
      description: 'Pay the exact membership amount to the GCash account shown below, then save the reference number from the app or SMS receipt.',
      details: ['GCash number: 09XX XXX XXXX', 'Account name: GymFlow Fitness Center', 'After payment, enter the GCash reference number below.'],
      accent: 'from-cyan-500/20 to-blue-500/10',
    }
  }

  if (normalized.includes('bank')) {
    return {
      title: 'Bank transfer instructions',
      description: 'Transfer the exact amount to the gym bank account, then use the transfer reference or upload receipt details in the reference field.',
      details: ['Bank name: Example Bank', 'Account name: GymFlow Fitness Center', 'Account number: 1234-5678-9012', 'After transfer, enter the transaction ID or receipt number.'],
      accent: 'from-sky-500/20 to-indigo-500/10',
    }
  }

  if (normalized.includes('cash')) {
    return {
      title: 'Cash payment instructions',
      description: 'Pay at the front desk or cashier only after you arrive at the gym. Ask the staff for your receipt before leaving.',
      details: ['Pay only to authorized gym staff.', 'Keep the receipt for your records.', 'Enter the receipt number in the reference field if one is given.'],
      accent: 'from-amber-500/20 to-orange-500/10',
    }
  }

  if (normalized.includes('card')) {
    return {
      title: 'Card payment instructions',
      description: 'Card payments should be processed through a secure gateway, not by manually typing card details unless the gateway flow is enabled.',
      details: ['Use the secure card checkout flow when available.', 'Do not share your card details outside the secure payment step.', 'If card gateway is not configured, choose GCash or bank transfer.'],
      accent: 'from-rose-500/20 to-red-500/10',
    }
  }

  return {
    title: 'Payment instructions',
    description: 'Choose a payment method to see the exact steps and where to pay.',
    details: ['Keep your receipt or transaction ID after payment.', 'Use the correct reference only after payment is completed.'],
    accent: 'from-gold-bright/10 to-accent-orange/10',
  }
}

export default function Checkout() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const checkoutToken = searchParams.get('token') || ''
  const checkoutFlow = searchParams.get('flow') || ''
  const memberId = searchParams.get('member_id') || ''
  const planId = searchParams.get('plan_id') || ''
  const isMembershipFlow = checkoutFlow === 'renew' || checkoutFlow === 'upgrade'

  const [sessionLoading, setSessionLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [methodsLoading, setMethodsLoading] = useState(true)
  const [sessionInfo, setSessionInfo] = useState(null)
  const [memberInfo, setMemberInfo] = useState(null)
  const [selectedPlan, setSelectedPlan] = useState(null)
  const [paymentMethods, setPaymentMethods] = useState([])
  const [error, setError] = useState('')

  const [formData, setFormData] = useState({
    full_name: '',
    email: '',
    payment_method_id: '',
    card_number: '',
    card_exp_month: '',
    card_exp_year: '',
    card_cvv: '',
    payment_reference: '',
  })

  useEffect(() => {
    if (checkoutToken) {
      validateSession()
      fetchPaymentMethods()
      return
    }

    if (isMembershipFlow) {
      loadMembershipCheckout()
      return
    }

    setError('Missing checkout token. Please register again.')
    setSessionLoading(false)
  }, [checkoutToken, isMembershipFlow])

  const validateSession = async () => {
    setSessionLoading(true)
    setError('')

    try {
      const response = await authAPI.checkoutSession(checkoutToken)
      if (!response.data?.success) {
        throw new Error(response.data?.message || 'Invalid checkout session')
      }

      setSessionInfo(response.data.data)
    } catch (err) {
      const message = err.response?.data?.message || err.message || 'Unable to validate checkout session'
      setError(message)
      toast.error(message)
    } finally {
      setSessionLoading(false)
    }
  }

  const loadMembershipCheckout = async () => {
    setSessionLoading(true)
    setError('')

    try {
      if (!memberId) {
        throw new Error('Missing member id')
      }

      const memberResponse = await membersAPI.get(memberId)
      const member = memberResponse.data?.data
      if (!member) {
        throw new Error('Member profile could not be loaded')
      }

      setMemberInfo(member)

      const plansResponse = await plansAPI.list()
      const plans = plansResponse.data?.data || []

      if (checkoutFlow === 'upgrade') {
        const plan = plans.find((item) => String(item.id) === String(planId))
        if (!plan) {
          throw new Error('Selected upgrade plan could not be found')
        }
        setSelectedPlan(plan)
      } else {
        setSelectedPlan(member.plan || plans.find((item) => String(item.id) === String(member.plan_id)) || null)
      }
    } catch (err) {
      const message = err.response?.data?.message || err.message || 'Unable to load membership checkout'
      setError(message)
      toast.error(message)
    } finally {
      setSessionLoading(false)
      setMethodsLoading(false)
    }
  }

  const fetchPaymentMethods = async () => {
    setMethodsLoading(true)

    try {
      const response = await paymentMethodsAPI.list()
      const methods = response.data?.data || []
      setPaymentMethods(methods)
    } catch (err) {
      const message = err.response?.data?.message || 'Failed to load payment methods'
      setError(message)
      toast.error(message)
    } finally {
      setMethodsLoading(false)
    }
  }

  const selectedMethod = useMemo(() => {
    return paymentMethods.find((method) => String(method.payment_method_id) === String(formData.payment_method_id)) || null
  }, [paymentMethods, formData.payment_method_id])

  const requireCardFields = isCardMethod(selectedMethod?.method_name)
  const paymentInstructions = getPaymentInstructions(selectedMethod?.method_name)

  const getMembershipExpiryDate = () => {
    const rawExpiry = memberInfo?.membership?.end_date || memberInfo?.membership_end || memberInfo?.membership_end_date
    if (!rawExpiry) return null

    const expiryDate = new Date(rawExpiry)
    if (Number.isNaN(expiryDate.getTime())) return null

    return expiryDate
  }

  const canProceedWithMembershipAction = () => {
    const expiryDate = getMembershipExpiryDate()
    if (!expiryDate) return false

    return new Date() >= expiryDate
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

  const onChange = (event) => {
    const { name, value } = event.target
    setFormData((prev) => ({ ...prev, [name]: value }))
  }

  const validateRequiredFields = () => {
    const required = [
      'full_name',
      'email',
      'payment_method_id',
    ]

    for (const key of required) {
      if (!String(formData[key] || '').trim()) {
        return 'Please complete all required checkout fields'
      }
    }

    if (requireCardFields) {
      if (!formData.card_number || !formData.card_exp_month || !formData.card_exp_year || !formData.card_cvv) {
        return 'Please complete all required card fields'
      }
    } else if (!formData.payment_reference) {
      return 'Payment reference is required for this payment method'
    }

    return null
  }

  const handleSubmit = async (event) => {
    event.preventDefault()

    if (isMembershipFlow) {
      if (!canProceedWithMembershipAction()) {
        const expiryDate = getMembershipExpiryDate()
        setError(
          expiryDate
            ? `You can only ${checkoutFlow} once your current membership expires on ${expiryDate.toLocaleDateString()}.`
            : `You can only ${checkoutFlow} once your current membership expires.`
        )
        return
      }

      setSubmitting(true)
      setError('')

      try {
        if (checkoutFlow === 'renew') {
          await membersAPI.renew(memberId)
          toast.success('Membership renewed successfully')
        } else {
          await membersAPI.upgrade(memberId, { new_plan_id: Number(planId) })
          toast.success('Membership upgraded successfully')
        }

        navigate('/profile#membership', { replace: true })
      } catch (err) {
        const message = err.response?.data?.message || err.message || 'Membership checkout failed. Please retry.'
        setError(message)
        toast.error(message)
      } finally {
        setSubmitting(false)
      }

      return
    }

    const validationMessage = validateRequiredFields()
    if (validationMessage) {
      setError(validationMessage)
      toast.error(validationMessage)
      return
    }

    setSubmitting(true)
    setError('')

    try {
      const payload = {
        checkout_token: checkoutToken,
        ...formData,
      }

      const response = await authAPI.completeCheckout(payload)
      if (!response.data?.success) {
        throw new Error(response.data?.message || 'Checkout failed')
      }

      toast.success('Payment successful. Your account is now active.')
      navigate('/register/confirmation', { replace: true, state: { email: formData.email } })
    } catch (err) {
      const message = err.response?.data?.message || err.message || 'Payment failed. Please retry.'
      setError(message)
      toast.error(message)
    } finally {
      setSubmitting(false)
    }
  }

  if (sessionLoading) {
    return <div className="min-h-screen bg-dark-bg text-white flex items-center justify-center">Loading checkout...</div>
  }

  if (isMembershipFlow) {
    const expiryDate = getMembershipExpiryDate()
    const daysLeft = getDaysLeft()
    const actionLabel = checkoutFlow === 'renew' ? 'Renew Membership' : 'Upgrade Membership'
    const actionTitle = checkoutFlow === 'renew' ? 'Renew Membership Checkout' : 'Upgrade Membership Checkout'

    return (
      <div className="min-h-screen bg-dark-bg text-white py-10 px-4">
        <div className="max-w-2xl mx-auto bg-dark-card border border-gold-bright/20 rounded-xl p-6 sm:p-8">
          <h1 className="text-3xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent">
            {actionTitle}
          </h1>
          <p className="text-gray-400 mt-2">
            Review your membership details before confirming {checkoutFlow === 'renew' ? 'renewal' : 'the upgrade'}.
          </p>

          <div className="mt-4 p-4 rounded-lg border border-gold-bright/20 bg-dark-secondary">
            <p className="text-sm text-gray-300">Current Membership</p>
            <p className="text-lg font-bold text-gold-bright">{memberInfo?.plan?.plan_name || 'N/A'}</p>
            <p className="text-sm text-gray-300">
              Expires: {expiryDate ? expiryDate.toLocaleDateString() : 'N/A'}
            </p>
            <p className="text-sm text-gray-300">
              Days left:{' '}
              {daysLeft === null
                ? 'N/A'
                : daysLeft > 0
                  ? `${daysLeft} day${daysLeft === 1 ? '' : 's'} left`
                  : daysLeft === 0
                    ? 'Expires today'
                    : `Expired ${Math.abs(daysLeft)} day${Math.abs(daysLeft) === 1 ? '' : 's'} ago`}
            </p>
          </div>

          {checkoutFlow === 'upgrade' && selectedPlan && (
            <div className="mt-4 p-4 rounded-lg border border-gold-bright/20 bg-dark-secondary">
              <p className="text-sm text-gray-300">Selected Upgrade Plan</p>
              <p className="text-lg font-bold text-gold-bright">{selectedPlan.plan_name}</p>
              <p className="text-sm text-gray-300">Amount Due: PHP {selectedPlan.price}</p>
            </div>
          )}

          <div className="mt-5 p-4 bg-amber-500/10 border border-amber-400/30 rounded-lg flex gap-3">
            <AlertCircle size={20} className="text-amber-300 flex-shrink-0 mt-0.5" />
            <p className="text-sm text-amber-100">
              {canProceedWithMembershipAction()
                ? `Your membership is eligible. Click ${actionLabel} to continue.`
                : 'You can only renew or upgrade once your current membership expires.'}
            </p>
          </div>

          <div className="mt-6 flex gap-3">
            <button
              onClick={handleSubmit}
              disabled={submitting || !canProceedWithMembershipAction()}
              className="flex-1 px-4 py-3 bg-gradient-to-r from-gold-600 to-gold-500 text-black font-bold rounded-lg hover:from-gold-500 hover:to-gold-400 transition disabled:opacity-50"
            >
              {submitting ? 'Processing...' : actionLabel}
            </button>
            <button
              onClick={() => navigate('/profile#membership')}
              className="px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white font-semibold"
            >
              Back
            </button>
          </div>

          {error && (
            <div className="mt-5 p-4 bg-red-500/10 border border-red-500/30 rounded-lg flex gap-3">
              <AlertCircle size={20} className="text-red-400 flex-shrink-0 mt-0.5" />
              <p className="text-sm text-red-300">{error}</p>
            </div>
          )}
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-dark-bg text-white py-10 px-4">
      <div className="max-w-2xl mx-auto bg-dark-card border border-gold-bright/20 rounded-xl p-6 sm:p-8">
        <h1 className="text-3xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent">
          Complete Checkout
        </h1>
        <p className="text-gray-400 mt-2">
          Finish payment to activate your account and receive your confirmation email.
        </p>

        {sessionInfo?.plan && (
          <div className="mt-4 p-4 rounded-lg border border-gold-bright/20 bg-dark-secondary">
            <p className="text-sm text-gray-300">Selected Plan</p>
            <p className="text-lg font-bold text-gold-bright">{sessionInfo.plan.name}</p>
            <p className="text-sm text-gray-300">Amount Due: PHP {sessionInfo.plan.price}</p>
          </div>
        )}

        {error && (
          <div className="mt-5 p-4 bg-red-500/10 border border-red-500/30 rounded-lg flex gap-3">
            <AlertCircle size={20} className="text-red-400 flex-shrink-0 mt-0.5" />
            <p className="text-sm text-red-300">{error}</p>
          </div>
        )}

        {!error && (
          <div className="mt-5 p-4 bg-green-500/10 border border-green-500/30 rounded-lg flex gap-3">
            <CheckCircle size={20} className="text-green-400 flex-shrink-0 mt-0.5" />
            <p className="text-sm text-green-300">All fields are manual entry. Nothing is auto-filled from authentication.</p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="mt-6 space-y-4">
          <div>
            <label className="block text-sm font-bold text-gold-500 mb-2">Full Name</label>
            <input
              name="full_name"
              value={formData.full_name}
              onChange={onChange}
              required
              className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
            />
          </div>

          <div>
            <label className="block text-sm font-bold text-gold-500 mb-2">Email</label>
            <input
              name="email"
              type="email"
              value={formData.email}
              onChange={onChange}
              required
              className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
            />
          </div>

          <div>
            <label className="block text-sm font-bold text-gold-500 mb-2">Payment Method</label>
            <select
              name="payment_method_id"
              value={formData.payment_method_id}
              onChange={onChange}
              required
              disabled={methodsLoading}
              className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
            >
              <option value="">Select payment method</option>
              {paymentMethods.map((method) => (
                <option key={method.payment_method_id} value={method.payment_method_id}>
                  {method.method_name}
                </option>
              ))}
            </select>
          </div>

          <div className={`rounded-lg border border-gold-bright/20 bg-gradient-to-br ${paymentInstructions.accent} p-4`}>
            <p className="text-sm font-bold text-white mb-2">{paymentInstructions.title}</p>
            <p className="text-sm text-gray-200 mb-3">{paymentInstructions.description}</p>
            <ul className="space-y-1 text-sm text-gray-300">
              {paymentInstructions.details.map((item) => (
                <li key={item} className="flex gap-2">
                  <span className="text-gold-bright">•</span>
                  <span>{item}</span>
                </li>
              ))}
            </ul>
          </div>

          {requireCardFields ? (
            <>
              <div>
                <label className="block text-sm font-bold text-gold-500 mb-2">Card Number</label>
                <input
                  name="card_number"
                  value={formData.card_number}
                  onChange={onChange}
                  required
                  className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
                />
              </div>

              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block text-sm font-bold text-gold-500 mb-2">Exp MM</label>
                  <input
                    name="card_exp_month"
                    value={formData.card_exp_month}
                    onChange={onChange}
                    required
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-bold text-gold-500 mb-2">Exp YYYY</label>
                  <input
                    name="card_exp_year"
                    value={formData.card_exp_year}
                    onChange={onChange}
                    required
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-bold text-gold-500 mb-2">CVV</label>
                  <input
                    name="card_cvv"
                    value={formData.card_cvv}
                    onChange={onChange}
                    required
                    className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
                  />
                </div>
              </div>
            </>
          ) : (
            <div>
              <label className="block text-sm font-bold text-gold-500 mb-2">
                Payment Reference / Transaction ID
              </label>
              <input
                name="payment_reference"
                value={formData.payment_reference}
                onChange={onChange}
                required
                className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg"
                placeholder="Enter the reference from GCash, bank transfer, or receipt"
              />
              <p className="mt-2 text-xs text-gray-400">
                Enter this only after you have paid and received the transaction ID or receipt number.
              </p>
            </div>
          )}

          <button
            type="submit"
            disabled={submitting || !checkoutToken || methodsLoading}
            className="w-full px-4 py-3 bg-gradient-to-r from-gold-600 to-gold-500 text-black font-bold rounded-lg hover:from-gold-500 hover:to-gold-400 transition disabled:opacity-50"
          >
            {submitting ? 'Processing Payment...' : 'Pay and Activate Account'}
          </button>

          <p className="text-sm text-center text-gray-400">
            If payment fails, you can correct details and retry immediately.
          </p>

          <p className="text-sm text-center">
            <Link to="/register" className="text-gold-bright hover:text-gold-500">Back to registration</Link>
          </p>
        </form>
      </div>
    </div>
  )
}
