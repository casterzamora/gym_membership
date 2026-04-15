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
  const [workloadSummary, setWorkloadSummary] = useState([]);
  const [classPopularity, setClassPopularity] = useState([]);

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

      const [summaryRes, revenueRes, classPopularityRes] = await Promise.all([
        api.trainersAPI.workloadSummary().catch(() => ({ data: { data: [] } })),
        api.reportsAPI.revenue({ group_by: 'month' }).catch(() => ({ data: { data: { series: [], total_revenue: 0 } } })),
        api.reportsAPI.classPopularity({ limit: 5 }).catch(() => ({ data: { data: [] } })),
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

      const revenueSeries = revenueRes.data?.data?.series || [];
      const realRevenue = Number(revenueRes.data?.data?.total_revenue || 0);

      setStats((prev) => ({
        ...prev,
        totalRevenue: realRevenue,
      }));

      setChartData(revenueSeries.map((row) => ({
        day: row.period,
        revenue: Number(row.total_revenue || 0),
        payments: Number(row.payment_count || 0),
      })));
      setWorkloadSummary(summaryRes.data?.data || []);
      setClassPopularity(classPopularityRes.data?.data || []);
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
          <h2 className="text-xl font-bold text-white mb-6">Revenue Trend</h2>
          {chartData.length > 0 ? (
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                <XAxis dataKey="day" stroke="#999" />
                <YAxis stroke="#999" />
                <Tooltip contentStyle={{ backgroundColor: '#1F1F1F', border: '1px solid #FFD700' }} />
                <Legend wrapperStyle={{ color: '#999' }} />
                <Line type="monotone" dataKey="revenue" stroke="#10b981" name="Revenue" />
                <Line type="monotone" dataKey="payments" stroke="#3b82f6" name="Payments" />
              </LineChart>
            </ResponsiveContainer>
          ) : (
            <div className="text-center py-12 text-gray-400">No revenue trend data available.</div>
          )}
        </div>
      </motion.div>

      {/* Trainer Workload Summary */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <h2 className="text-xl font-bold text-white mb-4">Trainer Workload Summary</h2>
          {workloadSummary.length === 0 ? (
            <div className="text-gray-400 py-8 text-center">No trainer workload data available yet.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b border-gray-700 text-gray-300 text-sm">
                    <th className="py-3 pr-4">Trainer</th>
                    <th className="py-3 pr-4">Classes</th>
                    <th className="py-3 pr-4">Schedules</th>
                    <th className="py-3 pr-4">Attendance</th>
                    <th className="py-3 pr-4">Avg / Schedule</th>
                  </tr>
                </thead>
                <tbody>
                  {workloadSummary.map((row) => (
                    <tr key={row.trainer_id} className="border-b border-gray-800 text-sm text-gray-100">
                      <td className="py-3 pr-4 font-medium">{row.trainer_name}</td>
                      <td className="py-3 pr-4">{row.total_classes}</td>
                      <td className="py-3 pr-4">{row.total_schedules}</td>
                      <td className="py-3 pr-4">{row.total_attendance_records}</td>
                      <td className="py-3 pr-4">{Number(row.average_attendance_per_schedule || 0).toFixed(2)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <h2 className="text-xl font-bold text-white mb-4">Top Class Popularity</h2>
          {classPopularity.length === 0 ? (
            <div className="text-gray-400 py-8 text-center">No class popularity data available yet.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b border-gray-700 text-gray-300 text-sm">
                    <th className="py-3 pr-4">Class</th>
                    <th className="py-3 pr-4">Schedules</th>
                    <th className="py-3 pr-4">Attendance</th>
                    <th className="py-3 pr-4">Avg / Schedule</th>
                    <th className="py-3 pr-4">Capacity Utilization</th>
                  </tr>
                </thead>
                <tbody>
                  {classPopularity.map((row) => (
                    <tr key={row.class_id} className="border-b border-gray-800 text-sm text-gray-100">
                      <td className="py-3 pr-4 font-medium">{row.class_name}</td>
                      <td className="py-3 pr-4">{row.total_schedules}</td>
                      <td className="py-3 pr-4">{row.total_attendance_records}</td>
                      <td className="py-3 pr-4">{Number(row.average_attendance_per_schedule || 0).toFixed(2)}</td>
                      <td className="py-3 pr-4">{Number(row.capacity_utilization_percent || 0).toFixed(2)}%</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </motion.div>
    </div>
  );
};

export default AdminDashboard;
