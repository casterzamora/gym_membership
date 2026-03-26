import React, { useState, useEffect } from 'react';
import { StatCard } from '@/components';
import api from '@/services/api';
import { Users, Dumbbell, BookOpen, CreditCard } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const AdminDashboard = () => {
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
      const revenue = payments.reduce((sum, p) => sum + (p.amount_paid || 0), 0);

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
      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          label="Total Members"
          value={stats.totalMembers}
          icon={Users}
          trend={12}
        />
        <StatCard
          label="Total Trainers"
          value={stats.totalTrainers}
          icon={Dumbbell}
          trend={5}
        />
        <StatCard
          label="Active Classes"
          value={stats.totalClasses}
          icon={BookOpen}
          trend={8}
        />
        <StatCard
          label="Total Revenue"
          value={`$${stats.totalRevenue.toLocaleString()}`}
          icon={CreditCard}
          trend={15}
        />
      </div>

      {/* Charts */}
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-xl font-bold text-gray-900 mb-4">System Activity</h2>
        {chartData.length > 0 ? (
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="day" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="members" stroke="#3b82f6" />
              <Line type="monotone" dataKey="revenue" stroke="#10b981" />
            </LineChart>
          </ResponsiveContainer>
        ) : (
          <div className="text-center py-12 text-gray-500">Loading chart data...</div>
        )}
      </div>
    </div>
  );
};

export default AdminDashboard;
