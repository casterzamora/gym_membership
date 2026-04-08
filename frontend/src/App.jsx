import { useContext } from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { AuthContext } from '@/context/AuthContext'
import MemberLayout from '@/layouts/MemberLayout'
import AdminLayout from '@/layouts/AdminLayout'
import TrainerLayout from '@/layouts/TrainerLayout'

// Pages - Public
import Landing from '@/pages/Landing'
import Login from '@/pages/Login'
import Register from '@/pages/Register'
import About from '@/pages/About'

// Pages - Member
import Dashboard from '@/pages/member/Dashboard'
import Classes from '@/pages/member/Classes'
import Profile from '@/pages/member/Profile'

// Pages - Admin
import AdminDashboard from '@/pages/admin/AdminDashboard'
import MembersManagement from '@/pages/admin/MembersManagement'
import TrainersManagement from '@/pages/admin/TrainersManagement'
import ClassesManagement from '@/pages/admin/ClassesManagement'
import EquipmentManagement from '@/pages/admin/EquipmentManagement'
import PlansManagement from '@/pages/admin/PlansManagement'
import PaymentsManagement from '@/pages/admin/PaymentsManagement'

// Pages - Trainer
import TrainerDashboard from '@/pages/trainer/TrainerDashboard'
import TrainerClasses from '@/pages/trainer/TrainerClasses'
import TrainerMembers from '@/pages/trainer/TrainerMembers'

// Protected Route Wrapper - For Members
function MemberRoute({ children }) {
  const { user, loading } = useContext(AuthContext)

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-gray-400">Loading...</div>
      </div>
    )
  }

  if (!user) {
    console.log('MemberRoute: No user, redirecting to login')
    return <Navigate to="/login" replace />
  }
  
  if (user.role === 'admin') {
    console.log('MemberRoute: Is admin, redirecting to admin dashboard')
    return <Navigate to="/admin/dashboard" replace />
  }

  if (user.role === 'trainer') {
    console.log('MemberRoute: Is trainer, redirecting to trainer dashboard')
    return <Navigate to="/trainer/dashboard" replace />
  }

  console.log('MemberRoute: Member access granted for', user.displayName || user.name)
  return <MemberLayout>{children}</MemberLayout>
}

// Admin Route Wrapper
function AdminRoute({ children }) {
  const { user, loading } = useContext(AuthContext)

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-gray-400">Loading...</div>
      </div>
    )
  }

  if (!user) {
    console.log('AdminRoute: No user, redirecting to login')
    return <Navigate to="/login" replace />
  }

  console.log('AdminRoute: Checking user - role =', user.role)
  
  if (user.role !== 'admin') {
    console.log('AdminRoute: Not admin, role is', user.role, 'redirecting appropriately')
    if (user.role === 'trainer') {
      return <Navigate to="/trainer/dashboard" replace />
    }
    return <Navigate to="/dashboard" replace />
  }

  console.log('AdminRoute: Admin access granted for', user.displayName || user.name)
  return <AdminLayout>{children}</AdminLayout>
}

// Trainer Route Wrapper
function TrainerRoute({ children }) {
  const { user, loading } = useContext(AuthContext)

  if (loading) {
    console.log('TrainerRoute: Still loading...')
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-gray-400">Loading...</div>
      </div>
    )
  }

  if (!user) {
    console.log('TrainerRoute: No user, redirecting to login')
    return <Navigate to="/login" replace />
  }

  console.log('TrainerRoute: Checking user - role =', user.role, 'name =', user.displayName || user.name)
  
  if (user.role !== 'trainer') {
    console.log('TrainerRoute: User role is', user.role, 'not trainer, should redirect to', user.role === 'admin' ? '/admin/dashboard' : '/dashboard')
    // Redirect to appropriate dashboard based on role
    if (user.role === 'admin') {
      return <Navigate to="/admin/dashboard" replace />
    }
    return <Navigate to="/dashboard" replace />
  }

  console.log('TrainerRoute: Trainer access granted for', user.displayName || user.name)
  return <TrainerLayout>{children}</TrainerLayout>
}


export default function App() {
  const { loading } = useContext(AuthContext)

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-gray-400">Initializing...</div>
      </div>
    )
  }

  return (
    <BrowserRouter future={{ v7_relativeSplatPath: true, v7_startTransition: true }}>
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={<Landing />} />
        <Route path="/about" element={<About />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />

        {/* Member Routes */}
        <Route
          path="/dashboard"
          element={
            <MemberRoute>
              <Dashboard />
            </MemberRoute>
          }
        />
        <Route
          path="/classes"
          element={
            <MemberRoute>
              <Classes />
            </MemberRoute>
          }
        />
        <Route
          path="/profile"
          element={
            <MemberRoute>
              <Profile />
            </MemberRoute>
          }
        />

        {/* Admin Routes */}
        <Route
          path="/admin/dashboard"
          element={
            <AdminRoute>
              <AdminDashboard />
            </AdminRoute>
          }
        />
        <Route
          path="/admin/members"
          element={
            <AdminRoute>
              <MembersManagement />
            </AdminRoute>
          }
        />
        <Route
          path="/admin/trainers"
          element={
            <AdminRoute>
              <TrainersManagement />
            </AdminRoute>
          }
        />
        <Route
          path="/admin/classes"
          element={
            <AdminRoute>
              <ClassesManagement />
            </AdminRoute>
          }
        />
        <Route
          path="/admin/equipment"
          element={
            <AdminRoute>
              <EquipmentManagement />
            </AdminRoute>
          }
        />
        <Route
          path="/admin/plans"
          element={
            <AdminRoute>
              <PlansManagement />
            </AdminRoute>
          }
        />
        <Route
          path="/admin/payments"
          element={
            <AdminRoute>
              <PaymentsManagement />
            </AdminRoute>
          }
        />

        {/* Trainer Routes */}
        <Route
          path="/trainer/dashboard"
          element={
            <TrainerRoute>
              <TrainerDashboard />
            </TrainerRoute>
          }
        />
        <Route
          path="/trainer/classes"
          element={
            <TrainerRoute>
              <TrainerClasses />
            </TrainerRoute>
          }
        />
        <Route
          path="/trainer/members"
          element={
            <TrainerRoute>
              <TrainerMembers />
            </TrainerRoute>
          }
        />

        {/* Catch all - redirect to home */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
