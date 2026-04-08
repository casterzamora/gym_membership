import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { StatCard } from '@/components';
import api from '@/services/api';
import { Users, Dumbbell, BookOpen, CreditCard } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { motion } from 'framer-motion';

const AdminDashboard = () => {
  const navigate = useNavigate();
  const [stats, setStats] = useState({
    totalMembers: 0,
    totalTrainers: 0,
    totalClasses: 0,
    totalRevenue: 0,
  });
  const [loading, setLoading] = useState(true);
  const [chartData, setChartData] = useState([]);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      setLoading(true);
      const [membersRes, trainersRes, classesRes, paymentsRes] = await Promise.all([
        api.membersAPI.list().catch(() => ({ data: { data: [] } })),
        api.trainersAPI.list().catch(() => ({ data: { data: [] } })),
        api.classesAPI.list().catch(() => ({ data: { data: [] } })),
        api.paymentsAPI?.list?.().catch(() => ({ data: { data: [] } })),
      ]);

      const members = membersRes.data.data?.length || 0;
      const trainers = trainersRes.data.data?.length || 0;
      const classes = classesRes.data.data?.length || 0;
      const payments = paymentsRes?.data?.data || [];
      const revenue = payments.reduce((sum, p) => sum + (parseFloat(p.amount_paid) || 0), 0);

      setStats({
        totalMembers: members,
        totalTrainers: trainers,
        totalClasses: classes,
        totalRevenue: revenue,
      });

      // Generate mock chart data for last 7 days
      const data = Array.from({ length: 7 }, (_, i) => ({
        day: `Day ${i + 1}`,
        members: Math.floor(Math.random() * members),
        revenue: Math.floor(Math.random() * 5000),
      }));
      setChartData(data);
    } catch (err) {
      console.error('Failed to load stats', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-8">
      {/* Header with Welcome Animation */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="mb-8">
        <h1 className="text-4xl font-bold text-white mb-2">Admin Dashboard</h1>
        <p className="text-gray-400 text-lg">Real-time overview of your gym operations</p>
      </motion.div>

      {/* Stats Grid */}
      <motion.div 
        initial={{ opacity: 0 }} 
        animate={{ opacity: 1 }} 
        transition={{ staggerChildren: 0.1 }}
        className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
      >
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <button 
            onClick={() => navigate('/admin/members')}
            className="w-full cursor-pointer hover:opacity-90 transition-opacity"
            title="Click to manage members"
          >
            <StatCard
              label="Total Members"
              value={stats.totalMembers}
              icon={Users}
              trend={12}
            />
          </button>
        </motion.div>
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <StatCard
            label="Total Trainers"
            value={stats.totalTrainers}
            icon={Dumbbell}
            trend={5}
          />
        </motion.div>
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <StatCard
            label="Active Classes"
            value={stats.totalClasses}
            icon={BookOpen}
            trend={8}
          />
        </motion.div>
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <StatCard
            label="Total Revenue"
            value={`$${parseFloat(stats.totalRevenue).toFixed(2)}`}
            icon={CreditCard}
            trend={15}
          />
        </motion.div>
      </motion.div>

      {/* Charts */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <h2 className="text-xl font-bold text-white mb-6">System Activity</h2>
          {chartData.length > 0 ? (
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                <XAxis dataKey="day" stroke="#999" />
                <YAxis stroke="#999" />
                <Tooltip contentStyle={{ backgroundColor: '#1F1F1F', border: '1px solid #FFD700' }} />
                <Legend wrapperStyle={{ color: '#999' }} />
                <Line type="monotone" dataKey="members" stroke="#3b82f6" />
                <Line type="monotone" dataKey="revenue" stroke="#10b981" />
              </LineChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-12 text-gray-400">Loading chart data...</div>
          )}
        </div>
      </motion.div>
    </div>
  );
};

export default AdminDashboard;
