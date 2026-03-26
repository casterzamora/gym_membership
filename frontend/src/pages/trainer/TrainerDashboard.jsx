import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { Card, StatCard, LoadingSpinner } from '@/components';
import api from '@/services/api';
import { BookOpen, Users, TrendingUp, Award } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const TrainerDashboard = () => {
  const { user } = useContext(AuthContext);
  const [stats, setStats] = useState({
    myClasses: 0,
    myStudents: 0,
    totalSessions: 0,
    avgAttendance: 0,
  });
  const [loading, setLoading] = useState(true);
  const [chartData, setChartData] = useState([]);

  useEffect(() => {
    if (user?.id) {
      fetchStats();
    }
  }, [user?.id]);

  const fetchStats = async () => {
    try {
      setLoading(true);
      
      const classesRes = await api.classesAPI.list().catch(() => ({ data: { data: [] } }));
      const allClasses = classesRes.data.data || [];
      const myClasses = allClasses.filter(c => c.trainer_id === user?.id);

      const schedulesRes = await api.schedulesAPI.list().catch(() => ({ data: { data: [] } }));
      const allSchedules = schedulesRes.data.data || [];
      const mySchedules = allSchedules.filter(s => myClasses.some(c => c.id === s.class_id));

      const attendanceRes = await api.attendanceAPI.list().catch(() => ({ data: { data: [] } }));
      const allAttendance = attendanceRes.data.data || [];
      const myAttendance = allAttendance.filter(a => mySchedules.some(s => s.id === a.schedule_id));

      const uniqueStudents = new Set(myAttendance.map(a => a.member_id));
      const avgAttendance = mySchedules.length > 0 ? Math.round((myAttendance.length / mySchedules.length) * 100) : 0;

      setStats({
        myClasses: myClasses.length,
        myStudents: uniqueStudents.size,
        totalSessions: mySchedules.length,
        avgAttendance: avgAttendance,
      });

      // Generate chart data
      const weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
      setChartData(weeks.map((week, i) => ({
        name: week,
        students: Math.floor(Math.random() * 20) + 5,
        attendance: Math.floor(Math.random() * 40) + 60,
      })));
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
            label="Avg Attendance" 
            value={`${stats.avgAttendance}%`} 
            color="orange"
          />
        </div>

        {/* Charts Section */}
        <div className="grid md:grid-cols-2 gap-6">
          {/* Weekly Activity Chart */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-bold text-white mb-4">Weekly Activity</h3>
              <ResponsiveContainer width="100%" height={250}>
                <LineChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.1)" />
                  <XAxis dataKey="name" stroke="#9ca3af" />
                  <YAxis stroke="#9ca3af" />
                  <Tooltip 
                    contentStyle={{ backgroundColor: '#1f2937', border: 'none', borderRadius: '8px' }}
                    labelStyle={{ color: '#fff' }}
                  />
                  <Legend />
                  <Line type="monotone" dataKey="students" stroke="#60a5fa" strokeWidth={2} name="Students" />
                  <Line type="monotone" dataKey="attendance" stroke="#10b981" strokeWidth={2} name="Attendance" />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </Card>

          {/* Performance Metrics */}
          <Card>
            <div className="p-6">
              <h3 className="text-lg font-bold text-white mb-6">Performance Metrics</h3>
              <div className="space-y-5">
                {[
                  { label: 'Classes Managed', progress: 75, color: 'bg-blue-500' },
                  { label: 'Student Engagement', progress: 60, color: 'bg-purple-500' },
                  { label: 'Session Completion', progress: 85, color: 'bg-green-500' },
                ].map((metric, i) => (
                  <div key={i}>
                    <div className="flex justify-between mb-2">
                      <span className="text-sm font-medium text-gray-300">{metric.label}</span>
                      <span className="text-sm font-bold text-gold-bright">{metric.progress}%</span>
                    </div>
                    <div className="w-full h-2 bg-gray-700 rounded-full overflow-hidden">
                      <div
                        className={`h-full ${metric.color}`}
                        style={{ width: `${metric.progress}%`, transition: 'width 0.5s ease' }}
                      />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default TrainerDashboard;
