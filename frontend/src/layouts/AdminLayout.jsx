import React, { useState, useContext } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Menu, X, Users, Dumbbell, BookOpen, Zap, CreditCard, BarChart3, Settings, LogOut } from 'lucide-react';
import Button from '@/components/Button';
import { AuthContext } from '@/context/AuthContext';

const AdminLayout = ({ children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const location = useLocation();
  const { user, logout } = useContext(AuthContext);
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const adminMenuItems = [
    { name: 'Dashboard', path: '/admin/dashboard', icon: BarChart3 },
    { name: 'Reports', path: '/admin/reports', icon: BarChart3 },
    { name: 'Members', path: '/admin/members', icon: Users },
    { name: 'Trainers', path: '/admin/trainers', icon: Dumbbell },
    { name: 'Classes', path: '/admin/classes', icon: BookOpen },
    { name: 'Plans', path: '/admin/plans', icon: Settings },
    { name: 'Equipment', path: '/admin/equipment', icon: Zap },
    { name: 'Payments', path: '/admin/payments', icon: CreditCard },
  ];

  const isActive = (path) => location.pathname === path;

  return (
    <div className="flex h-screen bg-dark-bg">
      {/* Sidebar */}
      <div
        className={`${
          sidebarOpen ? 'w-64' : 'w-20'
        } bg-gray-900 text-white transition-all duration-300 flex flex-col`}
      >
        {/* Header */}
        <div className="p-4 border-b border-gray-700 flex items-center justify-between">
          {sidebarOpen && <h1 className="text-lg font-bold">Admin Panel</h1>}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="p-1 hover:bg-gray-800 rounded-lg"
          >
            {sidebarOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>

        {/* Menu Items */}
        <nav className="flex-1 p-4 space-y-2">
          {adminMenuItems.map((item) => {
            const Icon = item.icon;
            return (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center gap-3 px-4 py-2.5 rounded-lg transition ${
                  isActive(item.path)
                    ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50'
                    : 'text-gray-300 hover:bg-gray-800'
                }`}
                title={!sidebarOpen ? item.name : ''}
              >
                <Icon size={20} />
                {sidebarOpen && <span className="text-sm">{item.name}</span>}
              </Link>
            );
          })}
        </nav>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Top Bar */}
        <div className="bg-gray-900 border-b border-gray-700 p-4 shadow-sm">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold text-white">
              {adminMenuItems.find(item => isActive(item.path))?.name || 'Admin'}
            </h1>
            <div className="flex items-center gap-4">
              <div className="text-sm text-gray-400">
                {user?.name} ({user?.role})
              </div>
              <button
                onClick={handleLogout}
                className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
              >
                <LogOut size={18} />
                Logout
              </button>
            </div>
          </div>
        </div>

        {/* Content Area */}
        <div className="flex-1 overflow-auto">
          <div className="p-6">
            {children}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminLayout;
