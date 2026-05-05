import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { Card, StatCard, LoadingSpinner } from '@/components';
import api from '@/services/api';
import { BookOpen, Users, TrendingUp, Award } from 'lucide-react';
import { BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const TrainerDashboard = () => {
  const { user } = useContext(AuthContext);
  const [stats, setStats] = useState({
    myClasses: 0,
    myStudents: 0,
    totalSessions: 0,
    avgAttendance: 0,
  });
  const [loading, setLoading] = useState(true);
  const [attendanceStatusData, setAttendanceStatusData] = useState([]);
  const [summaryTrendData, setSummaryTrendData] = useState([]);

  useEffect(() => {
    if (user?.trainer_id) {
      fetchStats();
    }
  }, [user?.trainer_id]);

  const fetchStats = async () => {
    try {
      setLoading(true);

      const workloadRes = await api.trainersAPI.workload(user.trainer_id);
      const workload = workloadRes.data?.data || {};
      const metrics = workload.metrics || {};
      const byStatus = workload.attendance_by_status || {};

      setStats({
        myClasses: metrics.total_classes || 0,
        myStudents: metrics.unique_members || 0,
        totalSessions: metrics.total_schedules || 0,
        avgAttendance: metrics.average_attendance_per_schedule || 0,
      });

      setAttendanceStatusData([
        { name: 'Present', value: byStatus.Present || 0 },
        { name: 'Late', value: byStatus.Late || 0 },
        { name: 'Absent', value: byStatus.Absent || 0 },
      ]);

      setSummaryTrendData([
        { name: 'Classes', value: metrics.total_classes || 0 },
        { name: 'Schedules', value: metrics.total_schedules || 0 },
        { name: 'Upcoming', value: metrics.upcoming_schedules || 0 },
        { name: 'Members', value: metrics.unique_members || 0 },
      ]);
    } catch (err) {
      console.error('Failed to load stats', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-white mb-2">Trainer Dashboard</h1>
          <p className="text-gray-400">Welcome back, {user?.name}! Track your classes and students</p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard 
            icon={BookOpen} 
            label="My Classes" 
            value={stats.myClasses} 
            color="blue"
          />
          <StatCard 
            icon={Users} 
            label="My Students" 
            value={stats.myStudents} 
            color="purple"
          />
          <StatCard 
            icon={TrendingUp} 
            label="Total Sessions" 
            value={stats.totalSessions} 
            color="green"
          />
          <StatCard 
            icon={Award} 
            label="Avg Attendance / Session" 
            value={Number(stats.avgAttendance).toFixed(2)} 
            color="orange"
          />
        </div>

        {/* Charts Section */}
        <div className="grid md:grid-cols-2 gap-6">
          {/* Weekly Activity Chart */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-bold text-white mb-4">Attendance Status</h3>
              <ResponsiveContainer width="100%" height={250}>
                <BarChart data={attendanceStatusData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.1)" />
                  <XAxis dataKey="name" stroke="#9ca3af" />
                  <YAxis stroke="#9ca3af" />
                  <Tooltip 
                    contentStyle={{ backgroundColor: '#1f2937', border: 'none', borderRadius: '8px' }}
                    labelStyle={{ color: '#fff' }}
                  />
                  <Legend />
                  <Bar dataKey="value" fill="#60a5fa" name="Records" radius={[6, 6, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </Card>

          {/* Performance Metrics */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-bold text-white mb-4">Workload Snapshot</h3>
              <ResponsiveContainer width="100%" height={250}>
                <LineChart data={summaryTrendData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.1)" />
                  <XAxis dataKey="name" stroke="#9ca3af" />
                  <YAxis stroke="#9ca3af" />
                  <Tooltip
                    contentStyle={{ backgroundColor: '#1f2937', border: 'none', borderRadius: '8px' }}
                    labelStyle={{ color: '#fff' }}
                  />
                  <Line type="monotone" dataKey="value" stroke="#10b981" strokeWidth={3} name="Total" />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default TrainerDashboard;
