import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { membersAPI, schedulesAPI, attendanceAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { BarChart, Bar, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts'
import { Calendar, Flame, Zap, TrendingUp, ArrowRight } from 'lucide-react'
import { Button, StatCard, Card, Badge, LoadingSpinner } from '@/components'

export default function Dashboard() {
  const { user, loading: authLoading } = useContext(AuthContext)
  const [memberData, setMemberData] = useState(null)
  const [upcomingClasses, setUpcomingClasses] = useState([])
  const [stats, setStats] = useState({ totalAttendance: 0, thisMonth: 0, streak: 0 })
  const [chartData, setChartData] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    if (!authLoading && !user) {
      console.log('Dashboard: No user detected, redirecting to login')
      navigate('/login')
      return
    }
    
    if (user) {
      console.log('Dashboard: User detected, fetching data for:', user.displayName || user.email)
      fetchDashboardData()
    }
  }, [user, authLoading, navigate])

  const fetchDashboardData = async () => {
    try {
      setLoading(true)
      
      const memberRes = await membersAPI.get(user.id)
      setMemberData(memberRes.data.data)

      const schedulesRes = await schedulesAPI.list()
      if (schedulesRes.data.data) {
        setUpcomingClasses(schedulesRes.data.data.filter(s => new Date(s.class_date) > new Date()).slice(0, 6))
      }

      const attendanceRes = await attendanceAPI.list()
      if (attendanceRes.data.data) {
        const totalAttendance = attendanceRes.data.data.length
        const thisMonth = attendanceRes.data.data.filter(att => {
          const attDate = new Date(att.recorded_at || att.created_at)
          const now = new Date()
          return attDate.getMonth() === now.getMonth() && attDate.getFullYear() === now.getFullYear()
        }).length
        
        setStats({ totalAttendance, thisMonth, streak: Math.floor(Math.random() * 8) + 1 })
        
        // Generate chart data
        const weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4']
        setChartData(weeks.map((week, i) => ({
          name: week,
          attendance: Math.floor(Math.random() * 7) + 1,
        })))
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load dashboard')
    } finally {
      setLoading(false)
    }
  }

  if (authLoading || loading) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center">
        <LoadingSpinner />
      </div>
    )
  }

  if (error) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg">
        <div className="max-w-6xl mx-auto px-4 py-8">
          <Card className="border-red-500/50 bg-red-500/10">
            <div className="text-red-400 font-semibold">{error}</div>
          </Card>
        </div>
      </div>
    )
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg">
      <div className="w-full px-4 py-8">
        {/* Header with Welcome Animation */}
        <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="mb-12">
          <h1 className="text-5xl font-black bg-gradient-to-r from-gold-bright to-accent-orange bg-clip-text text-transparent mb-2">
            Welcome back, {user?.name}! 💪
          </h1>
          <p className="text-gray-400 text-lg">Here's your fitness progress at a glance</p>
        </motion.div>

        {/* Stats Grid */}
        <motion.div 
          initial={{ opacity: 0 }} 
          animate={{ opacity: 1 }} 
          transition={{ staggerChildren: 0.1 }}
          className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12"
        >
          <StatCard 
            icon={Calendar} 
            label="MEMBERSHIP PLAN" 
            value={memberData?.plan?.plan_name || 'Active'}
            gradient="from-gold-500 to-gold-600"
          />
          <StatCard 
            icon={TrendingUp} 
            label="TOTAL VISITS" 
            value={stats.totalAttendance}
            trend={15}
            gradient="from-blue-500 to-blue-600"
          />
          <StatCard 
            icon={Zap} 
            label="THIS MONTH" 
            value={stats.thisMonth}
            trend={8}
            gradient="from-purple-500 to-purple-600"
          />
          <StatCard 
            icon={Flame} 
            label="CURRENT STREAK" 
            value={`${stats.streak} days`}
            trend={stats.streak > 5 ? 25 : 5}
            gradient="from-orange-500 to-red-600"
          />
        </motion.div>

        {/* Charts Section */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }} 
          animate={{ opacity: 1, y: 0 }}
          className="grid md:grid-cols-2 gap-6 mb-12"
        >
          <Card>
            <h3 className="text-xl font-bold text-white mb-6">Weekly Attendance</h3>
            <ResponsiveContainer width="100%" height={250}>
              <BarChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#333" />
                <XAxis dataKey="name" stroke="#999" />
                <YAxis stroke="#999" />
                <Tooltip contentStyle={{ backgroundColor: '#1F1F1F', border: '1px solid #FFD700' }} />
                <Bar dataKey="attendance" fill="#FFD700" radius={[8, 8, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </Card>

          <Card>
            <h3 className="text-xl font-bold text-white mb-6">Fitness Goals</h3>
            <div className="space-y-4">
              {[
                { label: 'Cardio Sessions', progress: 75 },
                { label: 'Strength Training', progress: 60 },
                { label: 'Classes Attended', progress: 85 },
              ].map((goal, i) => (
                <motion.div key={i} initial={{ width: 0 }} animate={{ width: '100%' }} transition={{ delay: i * 0.1 }}>
                  <div className="flex justify-between mb-2">
                    <span className="text-sm font-semibold text-gray-300">{goal.label}</span>
                    <span className="text-sm font-bold text-gold-bright">{goal.progress}%</span>
                  </div>
                  <div className="w-full h-2 bg-dark-secondary rounded-full overflow-hidden">
                    <motion.div 
                      className="h-full bg-gradient-to-r from-gold-bright to-accent-orange rounded-full"
                      initial={{ width: 0 }}
                      animate={{ width: `${goal.progress}%` }}
                      transition={{ delay: i * 0.2, duration: 1 }}
                    />
                  </div>
                </motion.div>
              ))}
            </div>
          </Card>
        </motion.div>

        {/* Upcoming Classes */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-3xl font-bold text-white">Upcoming Classes</h2>
            <Button variant="secondary" size="sm" onClick={() => navigate('/classes')}>
              View All <ArrowRight size={16} />
            </Button>
          </div>

          {upcomingClasses.length === 0 ? (
            <Card className="text-center py-12">
              <Calendar size={48} className="mx-auto text-gold-bright/30 mb-4" />
              <p className="text-gray-400 mb-4">No upcoming classes scheduled</p>
              <Button onClick={() => navigate('/classes')}>Browse Classes</Button>
            </Card>
          ) : (
            <motion.div 
              className="grid md:grid-cols-2 lg:grid-cols-3 gap-6"
              initial="hidden"
              animate="visible"
              variants={{
                hidden: { opacity: 0 },
                visible: {
                  opacity: 1,
                  transition: { staggerChildren: 0.1 },
                },
              }}
            >
              {upcomingClasses.map((schedule, i) => (
                <motion.div
                  key={schedule.id}
                  variants={{
                    hidden: { opacity: 0, y: 20 },
                    visible: { opacity: 1, y: 0 },
                  }}
                >
                  <Card>
                    <div className="flex justify-between items-start mb-4">
                      <h3 className="text-xl font-bold text-gold-bright">{schedule.fitnessClass?.class_name}</h3>
                      <Badge variant="difficulty-beginner">New</Badge>
                    </div>
                    
                    <div className="space-y-3 text-gray-300">
                      <div className="flex items-center gap-2">
                        <Calendar size={16} className="text-gold-bright" />
                        <span>{new Date(schedule.class_date).toLocaleDateString()}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Zap size={16} className="text-gold-bright" />
                        <span>{schedule.start_time} - {schedule.end_time}</span>
                      </div>
                      <div className="pt-2 border-t border-gray-700">
                        <p className="text-sm text-gray-400 mb-2">Trainer</p>
                        <p className="font-semibold">{schedule.fitnessClass?.trainer?.user?.name || 'TBD'}</p>
                      </div>
                    </div>

                    <div className="mt-4 pt-4 border-t border-gray-700">
                      <div className="text-sm text-gray-400 mb-2">Capacity</div>
                      <div className="flex justify-between items-center mb-2">
                        <span className="font-semibold">{schedule.current_enrollment || 0}/{schedule.fitnessClass?.max_participants}</span>
                        <span className="text-xs text-gold-bright">Spots Available</span>
                      </div>
                      <div className="w-full h-1.5 bg-dark-secondary rounded-full overflow-hidden">
                        <div 
                          className="h-full bg-gradient-to-r from-gold-bright to-accent-orange"
                          style={{ width: `${((schedule.current_enrollment || 0) / schedule.fitnessClass?.max_participants) * 100}%` }}
                        />
                      </div>
                    </div>

                    <Button variant="primary" className="w-full mt-4" size="sm">
                      Enroll Now
                    </Button>
                  </Card>
                </motion.div>
              ))}
            </motion.div>
          )}
        </motion.div>

        {/* Quick Actions Footer */}
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.5 }} className="mt-12 grid md:grid-cols-3 gap-4">
          <Button variant="primary" size="lg" className="w-full" onClick={() => navigate('/classes')}>
            Browse Classes
          </Button>
          <Button variant="secondary" size="lg" className="w-full" onClick={() => navigate('/profile')}>
            My Profile
          </Button>
          <Button variant="secondary" size="lg" className="w-full">
            Upgrade After Expiry
          </Button>
        </motion.div>
      </div>
    </div>
  )
}
