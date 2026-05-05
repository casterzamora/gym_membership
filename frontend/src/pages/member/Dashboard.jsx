import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { membersAPI, schedulesAPI, attendanceAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts'
import { Calendar, Flame, Zap, TrendingUp, ArrowRight } from 'lucide-react'
import { Button, StatCard, Card, Badge, LoadingSpinner } from '@/components'

export default function Dashboard() {
  const { user, loading: authLoading } = useContext(AuthContext)
  const [memberData, setMemberData] = useState(null)
  const [todaysSchedule, setTodaysSchedule] = useState([])
  const [stats, setStats] = useState({ totalAttendance: 0, thisMonth: 0, streak: 0 })
  const [chartData, setChartData] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    if (user) {
      fetchDashboardData()
    }
  }, [user])

  const fetchDashboardData = async () => {
    try {
      setLoading(true)
      
      // Get member data - use member_id from auth payload if available, otherwise use user.id
      const memberId = user.member_id || user.id
      const memberRes = await membersAPI.get(memberId)
      setMemberData(memberRes.data.data)

      // Get today's class schedules
      const schedulesRes = await schedulesAPI.list()
      if (schedulesRes.data?.data) {
        // Filter for today's schedules only
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        const tomorrow = new Date(today)
        tomorrow.setDate(tomorrow.getDate() + 1)
        
        const todaySchedules = schedulesRes.data.data.filter(s => {
          const dateStr = s.class_date || s.schedule_date
          if (!dateStr) return false
          const scheduleDate = new Date(dateStr)
          scheduleDate.setHours(0, 0, 0, 0)
          return scheduleDate.getTime() === today.getTime()
        })
        setTodaysSchedule(todaySchedules)
      }

      // Get attendance records for this member
      const attendanceRes = await attendanceAPI.list()
      if (attendanceRes.data?.data) {
        // Filter attendance for current member only
        const memberAttendance = attendanceRes.data.data.filter(
          att => att.member_id === memberRes.data.data.id
        )
        
        const totalAttendance = memberAttendance.length
        
        // Count this month's attendance
        const now = new Date()
        const thisMonth = memberAttendance.filter(att => {
          const attDate = new Date(att.recorded_at || att.created_at)
          return attDate.getMonth() === now.getMonth() && attDate.getFullYear() === now.getFullYear()
        }).length

        // Calculate attendance streak (days in a row with attendance)
        const attendanceDates = memberAttendance
          .map(att => new Date(att.recorded_at || att.created_at).toDateString())
          .sort((a, b) => new Date(b) - new Date(a))
        
        let streak = 0
        if (attendanceDates.length > 0) {
          let currentDate = new Date(attendanceDates[0])
          streak = 1
          
          for (let i = 1; i < attendanceDates.length; i++) {
            const prevDate = new Date(attendanceDates[i])
            const daysDiff = Math.floor((currentDate - prevDate) / (1000 * 60 * 60 * 24))
            
            if (daysDiff === 1) {
              streak++
              currentDate = prevDate
            } else {
              break
            }
          }
        }
        
        setStats({ totalAttendance, thisMonth, streak })
        
        // Generate real attendance chart data for last 4 weeks
        const weeks = []
        const today = new Date()
        for (let i = 3; i >= 0; i--) {
          const weekStart = new Date(today)
          weekStart.setDate(today.getDate() - (today.getDay() + 7 * i))
          const weekEnd = new Date(weekStart)
          weekEnd.setDate(weekStart.getDate() + 6)
          
          const weekAttendance = memberAttendance.filter(att => {
            const attDate = new Date(att.recorded_at || att.created_at)
            return attDate >= weekStart && attDate <= weekEnd
          }).length
          
          weeks.push({
            name: `Week ${4 - i}`,
            attendance: weekAttendance
          })
        }
        setChartData(weeks)
      }
    } catch (err) {
      console.error('Dashboard error:', err)
      console.error('Error response:', err.response?.data)
      setError(err.response?.data?.message || err.message || 'Failed to load dashboard')
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
          <h1 className="text-4xl md:text-5xl font-bold text-white mb-2">
            Welcome back, {user?.name}
          </h1>
          <p className="text-gray-400 text-lg">Here is your performance snapshot and today&apos;s training lineup.</p>
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
                <Bar dataKey="attendance" fill="#f59e0b" radius={[8, 8, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </Card>

          <Card>
            <h3 className="text-xl font-bold text-white mb-6">Attendance Overview</h3>
            <div className="h-64 flex flex-col justify-center items-center text-center">
              <div className="text-6xl font-black text-gold-bright mb-4">{stats.totalAttendance}</div>
              <p className="text-gray-400 mb-4">Total sessions attended</p>
              <div className="w-full border-t border-gray-700 pt-4">
                <div className="text-sm text-gray-400 mb-1">This Month: <span className="text-gold-bright font-bold">{stats.thisMonth}</span></div>
                <div className="text-sm text-gray-400">Current Streak: <span className="text-gold-bright font-bold">{stats.streak} days</span></div>
              </div>
            </div>
          </Card>
        </motion.div>

        {/* Today's Schedule */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-3xl font-bold text-white">Today's Schedule</h2>
            <Button variant="secondary" size="sm" onClick={() => navigate('/classes')}>
              Browse Classes <ArrowRight size={16} />
            </Button>
          </div>

          {todaysSchedule.length === 0 ? (
            <Card className="text-center py-12">
              <Calendar size={48} className="mx-auto text-gold-bright/30 mb-4" />
              <p className="text-gray-400 mb-4">No classes scheduled for today</p>
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
              {todaysSchedule.map((schedule, i) => (
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
                        <p className="font-semibold">
                          {schedule.fitnessClass?.trainer?.user?.name || 
                           `${schedule.fitnessClass?.trainer?.first_name || ''} ${schedule.fitnessClass?.trainer?.last_name || ''}`.trim() || 
                           'TBD'}
                        </p>
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
                          className="h-full bg-gold-500"
                          style={{ width: `${((schedule.current_enrollment || 0) / schedule.fitnessClass?.max_participants) * 100}%` }}
                        />
                      </div>
                    </div>

                    <Button variant="primary" className="w-full mt-4" size="sm" onClick={() => navigate('/classes')}>
                      View Details
                    </Button>
                  </Card>
                </motion.div>
              ))}
            </motion.div>
          )}
        </motion.div>

        {/* Quick Actions Footer */}
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.5 }} className="mt-12 grid md:grid-cols-4 gap-4">
          <Button variant="primary" size="lg" className="w-full" onClick={() => navigate('/classes')}>
            Browse Classes
          </Button>
          <Button variant="secondary" size="lg" className="w-full" onClick={() => navigate('/profile')}>
            Edit Profile
          </Button>
          <Button variant="secondary" size="lg" className="w-full" onClick={() => navigate('/profile#membership')}>
            My Membership
          </Button>
          <Button variant="secondary" size="lg" className="w-full" onClick={() => navigate('/profile#membership')}>
            Upgrade After Expiry
          </Button>
        </motion.div>
      </div>
    </div>
  )
}
