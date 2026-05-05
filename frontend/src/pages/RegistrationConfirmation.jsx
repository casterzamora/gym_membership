import { Link, useLocation } from 'react-router-dom'

export default function RegistrationConfirmation() {
  const location = useLocation()
  const email = location.state?.email

  return (
    <div className="min-h-screen bg-dark-bg text-white flex items-center justify-center px-4">
      <div className="max-w-lg w-full bg-dark-card border border-gold-bright/20 rounded-xl p-8 text-center">
        <h1 className="text-3xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent">
          Account Activated
        </h1>
        <p className="text-gray-300 mt-4">
          Payment was successful and your account is now active.
        </p>
        <p className="text-gray-400 mt-2">
          {email ? `A confirmation email was sent to ${email}.` : 'A confirmation email has been triggered for your account.'}
        </p>

        <div className="mt-8">
          <Link
            to="/login"
            className="inline-block px-6 py-3 bg-gradient-to-r from-gold-600 to-gold-500 text-black font-bold rounded-lg hover:from-gold-500 hover:to-gold-400 transition"
          >
            Continue to Login
          </Link>
        </div>
      </div>
    </div>
  )
}
