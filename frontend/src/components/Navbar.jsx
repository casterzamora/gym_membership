import { useContext } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { AuthContext } from '@/context/AuthContext'
import { ElevateGymLogo } from '@/components'

export default function Navbar() {
  const { user, logout } = useContext(AuthContext)
  const navigate = useNavigate()

  const handleLogout = () => {
    logout()
    navigate('/')
  }

  return (
    <nav className="bg-black/85 backdrop-blur-md border-b border-gold-700/30 shadow-lg fixed w-full top-0 z-40">
      <div className="max-w-7xl mx-auto px-6 py-3.5 flex justify-between items-center">
        <Link to="/" className="flex items-center gap-2">
          <ElevateGymLogo size={40} />
          <span className="text-2xl font-bold text-gold-400 tracking-wide">Elevate Gym</span>
        </Link>

        <div className="flex items-center gap-6">
          {user ? (
            <>
              {user.role === 'member' && (
                <>
                  <Link to="/dashboard" className="text-gray-100 hover:text-gold-300 transition">Dashboard</Link>
                  <Link to="/classes" className="text-gray-100 hover:text-gold-300 transition">Classes</Link>
                  <Link to="/attendance" className="text-gray-100 hover:text-gold-300 transition">Attendance</Link>
                  <Link to="/profile" className="text-gray-100 hover:text-gold-300 transition">Profile</Link>
                </>
              )}
              {user.role === 'admin' && (
                <Link to="/admin/dashboard" className="text-gray-100 hover:text-gold-300 transition font-semibold">Admin Panel</Link>
              )}
              {user.role === 'trainer' && (
                <>
                  <Link to="/trainer/dashboard" className="text-gray-100 hover:text-gold-300 transition">Dashboard</Link>
                  <Link to="/trainer/classes" className="text-gray-100 hover:text-gold-300 transition">My Classes</Link>
                  <Link to="/trainer/schedules" className="text-gray-100 hover:text-gold-300 transition">Schedules</Link>
                  <Link to="/trainer/members" className="text-gray-100 hover:text-gold-300 transition">My Students</Link>
                </>
              )}
              <div className="text-sm text-gray-400 border-l border-gray-700 pl-4">{user.displayName || user.name}</div>
              <button
                onClick={handleLogout}
                className="px-4 py-2 bg-gold-600 text-black font-semibold rounded border border-gold-500 hover:bg-gold-500 transition"
              >
                Logout
              </button>
            </>
          ) : (
            <>
              <Link to="/" className="text-gray-100 hover:text-gold-300 transition">Plans</Link>
              <Link to="/about" className="text-gray-100 hover:text-gold-300 transition">About</Link>
              <Link to="/login" className="text-gray-100 hover:text-gold-300 transition">Sign In</Link>
              <Link
                to="/register"
                className="px-4 py-2 bg-gold-600 text-black font-semibold rounded border border-gold-500 hover:bg-gold-500 transition"
              >
                Join Now
              </Link>
            </>
          )}
        </div>
      </div>
    </nav>
  )
}
