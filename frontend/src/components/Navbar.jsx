import { useContext } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { AuthContext } from '@/context/AuthContext'

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
          <div className="text-3xl font-bold text-gold-500">💪</div>
          <span className="text-2xl font-bold text-gold-500">GymFlow</span>
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
              <div className="text-sm text-gray-400">{user.name}</div>
              <button
                onClick={handleLogout}
                className="px-4 py-2 bg-gold-600 text-black font-bold rounded hover:bg-gold-500 transition"
              >
                Logout
              </button>
            </>
          ) : (
            <>
              <Link to="/login" className="text-white hover:text-gold-400 transition">Login</Link>
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
