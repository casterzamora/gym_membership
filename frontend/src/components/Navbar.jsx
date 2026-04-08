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
    <nav className="bg-gray-950 border-b border-gold-600 shadow-lg fixed w-full top-0 z-40">
      <div className="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <Link to="/" className="flex items-center gap-2">
          <ElevateGymLogo size={40} />
          <span className="text-2xl font-bold text-gold-500">Elevate Gym</span>
        </Link>

        <div className="flex items-center gap-6">
          {user ? (
            <>
              {user.role === 'member' && (
                <>
                  <Link to="/dashboard" className="text-white hover:text-gold-400 transition">Dashboard</Link>
                  <Link to="/classes" className="text-white hover:text-gold-400 transition">Classes</Link>
                  <Link to="/profile" className="text-white hover:text-gold-400 transition">Profile</Link>
                </>
              )}
              {user.role === 'admin' && (
                <Link to="/admin/dashboard" className="text-white hover:text-gold-400 transition font-bold">Admin Panel</Link>
              )}
              {user.role === 'trainer' && (
                <>
                  <Link to="/trainer/dashboard" className="text-white hover:text-gold-400 transition">Dashboard</Link>
                  <Link to="/trainer/classes" className="text-white hover:text-gold-400 transition">My Classes</Link>
                  <Link to="/trainer/members" className="text-white hover:text-gold-400 transition">My Students</Link>
                </>
              )}
              <div className="text-sm text-gray-400">{user.displayName || user.name}</div>
              <button
                onClick={handleLogout}
                className="px-4 py-2 bg-gold-600 text-black font-bold rounded hover:bg-gold-500 transition"
              >
                Logout
              </button>
            </>
          ) : (
            <>
              <Link to="/" className="text-white hover:text-gold-400 transition">Plans</Link>
              <Link to="/about" className="text-white hover:text-gold-400 transition">About</Link>
              <Link to="/login" className="text-white hover:text-gold-400 transition">Sign In</Link>
              <Link
                to="/register"
                className="px-4 py-2 bg-gold-600 text-black font-bold rounded hover:bg-gold-500 transition"
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
