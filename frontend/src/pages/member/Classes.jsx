import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { classesAPI, membersAPI, attendanceAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { Card, Button, LoadingSpinner, Badge } from '@/components'
import toast from 'react-hot-toast'
import { BookOpen, Calendar, Zap } from 'lucide-react'
import { motion } from 'framer-motion'

export default function Classes() {
  const { user, loading: authLoading } = useContext(AuthContext)
  const [classes, setClasses] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [selectedClass, setSelectedClass] = useState(null)
  const [enrolledClasses, setEnrolledClasses] = useState([])
  const [enrolling, setEnrolling] = useState(false)
  const [memberData, setMemberData] = useState(null)
  const navigate = useNavigate()

  useEffect(() => {
    if (user) {
      fetchAllData()
    }
  }, [user])

  const fetchAllData = async () => {
    try {
      setLoading(true)
      setError('')
      
      console.log('📥 Fetching member data for user:', user?.id)
      // Fetch member data - use member_id from auth payload if available, otherwise use user.id
      const memberId = user.member_id || user.id
      const memberRes = await membersAPI.get(memberId)
      setMemberData(memberRes.data.data)
      console.log('✅ Member data loaded:', memberRes.data.data)
      
      console.log('📥 Fetching classes with attendance info...')
      const classesRes = await classesAPI.list()
      console.log('✅ Classes loaded:', classesRes.data.data)
      
      if (classesRes.data.data) {
        // Ensure classes is an array
        const classesArray = Array.isArray(classesRes.data.data) ? classesRes.data.data : [classesRes.data.data]
        setClasses(classesArray)
      }
      
      console.log('📥 Fetching attendance records...')
      const attendanceRes = await attendanceAPI.list()
      console.log('✅ Attendance loaded:', attendanceRes.data.data)
      
      if (attendanceRes.data.data && memberRes.data.data) {
        const memberAttendance = attendanceRes.data.data.filter(
          (a) => a.member_id === memberRes.data.data.id
        )

        const scheduleIds = new Set(memberAttendance.map((a) => a.schedule_id))
        const enrolledClassIds = (Array.isArray(classesRes.data.data) ? classesRes.data.data : [])
          .filter((c) => Array.isArray(c.schedules) && c.schedules.some((s) => scheduleIds.has(s.id)))
          .map((c) => c.id)

        setEnrolledClasses([...new Set(enrolledClassIds)])
        console.log('✅ Enrolled classes:', enrolledClassIds)
      }
    } catch (err) {
      console.error('❌ Error fetching data:', err)
      setError(err.response?.data?.message || err.message || 'Failed to load classes')
      toast.error('Failed to load classes and enrollment data')
    } finally {
      setLoading(false)
    }
  }

  const handleEnroll = async (fitnessClass) => {
    if (!memberData) {
      toast.error('Member data not loaded')
      return
    }

    if (fitnessClass.is_full) {
      toast.error('This class is full')
      return
    }

    try {
      setEnrolling(true)
      console.log('🔄 Enrolling member', memberData.id, 'in class', fitnessClass.id)
      
      const response = await attendanceAPI.checkIn({
        member_id: memberData.id,
        class_id: fitnessClass.id,
      })
      
      console.log('✅ Enrollment response:', response)
      
      if (response.status === 200 || response.status === 201) {
        setEnrolledClasses([...enrolledClasses, fitnessClass.id])
        setSelectedClass(null)
        toast.success(`✓ Successfully enrolled in ${fitnessClass.class_name}!`, {
          duration: 3000,
          icon: '🎉'
        })
        // Refresh classes to update enrollment counts
        setTimeout(() => fetchAllData(), 1000)
      }
    } catch (err) {
      console.error('❌ Enrollment error:', err)
      let message = 'Failed to enroll in class'
      if (err.response?.data?.message) {
        message = err.response.data.message
      }
      toast.error(message, { duration: 4000 })
    } finally {
      setEnrolling(false)
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

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: { staggerChildren: 0.1 },
    },
  }

  const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.3 } },
  }

  return (
    <div className="min-h-screen bg-dark-bg pt-20 pb-12">
      <div className="max-w-7xl mx-auto px-4">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-white mb-2 flex items-center gap-3">
            <BookOpen className="w-10 h-10 text-gold-400" />
            Fitness Classes
          </h1>
          <p className="text-gray-400">
            Explore sessions, check schedule details, and enroll with one click.
          </p>
        </div>

        {/* Classes Grid */}
        {classes.length === 0 ? (
          <div className="text-center py-12">
            <BookOpen className="w-16 h-16 text-gray-600 mx-auto mb-4" />
            <p className="text-gray-400 text-lg">No classes available yet</p>
          </div>
        ) : (
          <motion.div
            className="grid md:grid-cols-2 lg:grid-cols-3 gap-6"
            variants={containerVariants}
            initial="hidden"
            animate="visible"
          >
            {classes.map((fitnessClass) => {
              const isEnrolled = enrolledClasses?.includes(fitnessClass.id)
              const firstSchedule = fitnessClass.schedules?.[0]
              const scheduleDate = firstSchedule?.date ? new Date(firstSchedule.date).toLocaleDateString() : 'TBD'
              const scheduleTime = firstSchedule?.class_time || 'TBD'

              return (
                <motion.div key={fitnessClass.id} variants={itemVariants}>
                  <Card className="overflow-hidden hover:border-gold/30 transition h-full">
                    <div className="flex justify-between items-start mb-4">
                      <h3 className="text-lg font-bold text-white flex-1">{fitnessClass.class_name}</h3>
                      <div className="flex flex-col items-end gap-2">
                        {fitnessClass.is_special && (
                          <Badge variant="difficulty-advanced" size="sm">Gold only</Badge>
                        )}
                        <Badge variant="difficulty-beginner" size="sm">New</Badge>
                      </div>
                    </div>
                    
                    <div className="space-y-3 text-gray-300">
                      {/* Date */}
                      <div className="flex items-center gap-2">
                        <Calendar size={16} className="text-gold" />
                        <span>{scheduleDate}</span>
                      </div>
                      
                      {/* Time */}
                      <div className="flex items-center gap-2">
                        <Zap size={16} className="text-gold" />
                        <span>{scheduleTime}</span>
                      </div>
                      
                      {/* Trainer */}
                      <div className="pt-2 border-t border-gray-700">
                        <p className="text-sm text-gray-400 mb-1">Trainer</p>
                        <p className="font-semibold">
                          {fitnessClass.trainer?.name || 'TBD'}
                        </p>
                      </div>

                      {Array.isArray(fitnessClass.membership_plans) && fitnessClass.membership_plans.length > 0 && (
                        <div className="pt-2 border-t border-gray-700">
                          <p className="text-sm text-gray-400 mb-1">Eligible Memberships</p>
                          <p className="font-semibold text-gold-300">
                            {fitnessClass.membership_plans.map((plan) => plan.plan_name || plan.name).join(', ')}
                          </p>
                        </div>
                      )}
                    </div>

                    {/* Capacity */}
                    <div className="mt-4 pt-4 border-t border-gray-700">
                      <div className="flex justify-between items-center mb-2">
                        <span className="font-semibold">{fitnessClass.current_enrolled}/{fitnessClass.max_participants}</span>
                        <span className="text-xs text-gold">Spots Available</span>
                      </div>
                      <div className="w-full h-1.5 bg-gray-700 rounded-full overflow-hidden">
                        <div 
                          className="h-full bg-gold-500"
                          style={{ width: `${Math.min((fitnessClass.current_enrolled / fitnessClass.max_participants) * 100, 100)}%` }}
                        />
                      </div>
                    </div>

                    {/* Action Button */}
                    {isEnrolled ? (
                      <Button 
                        variant="primary" 
                        className="w-full mt-4 bg-green-600 hover:bg-green-700" 
                        size="sm"
                        disabled
                      >
                        ✓ Already Enrolled
                      </Button>
                    ) : (
                      <div className="grid grid-cols-2 gap-2 mt-4">
                        <Button
                          variant="secondary"
                          className="w-full"
                          size="sm"
                          onClick={() => setSelectedClass(fitnessClass)}
                        >
                          Details
                        </Button>
                        <Button 
                          variant="primary" 
                          className="w-full" 
                          size="sm"
                          onClick={() => handleEnroll(fitnessClass)}
                          disabled={fitnessClass.is_full || enrolling}
                        >
                          {enrolling ? 'Enrolling...' : fitnessClass.is_full ? 'Class Full' : 'Enroll'}
                        </Button>
                      </div>
                    )}
                  </Card>
                </motion.div>
              )
            })
            }
          </motion.div>
        )}

        {/* Modal */}
        {selectedClass && (
          <div
            className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
            onClick={() => setSelectedClass(null)}
          >
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              onClick={(e) => e.stopPropagation()}
              className="bg-dark-bg border border-gold/20 rounded-xl max-w-lg w-full p-8 max-h-[90vh] overflow-y-auto"
            >
              <div className="flex items-start justify-between mb-6">
                <div>
                  <h2 className="text-2xl font-bold text-white mb-2">
                    {selectedClass.class_name}
                  </h2>
                  {selectedClass.trainer?.name && (
                    <p className="text-gray-400">
                      Trainer: <span className="text-gold font-semibold">{selectedClass.trainer.name}</span>
                    </p>
                  )}
                </div>
                <button
                  onClick={() => setSelectedClass(null)}
                  className="text-gray-400 hover:text-white text-2xl"
                >
                  ✕
                </button>
              </div>

              {/* Badges */}
              <div className="flex gap-2 mb-6">
                {selectedClass.is_full && (
                  <Badge variant="error">Class is Full</Badge>
                )}
                {enrolledClasses?.includes(selectedClass.id) && (
                  <Badge variant="success">You are Enrolled</Badge>
                )}
              </div>

              {/* Description */}
              <p className="text-gray-300 mb-6">{selectedClass.description || 'No description available'}</p>

              {/* Details Grid */}
              <div className="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-900/50 rounded-lg border border-gray-800">
                <div>
                  <p className="text-sm text-gray-500 mb-1">Schedule</p>
                  <p className="text-white font-semibold">
                    {selectedClass.schedules?.[0]?.class_time || 'TBD'}
                  </p>
                  <p className="text-xs text-gray-400">
                    {selectedClass.schedules?.[0]?.duration}min
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-500 mb-1">Specialization</p>
                  <p className="text-white font-semibold">
                    {selectedClass.trainer?.specialization || 'N/A'}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-500 mb-1">Max Capacity</p>
                  <p className="text-white font-semibold">{selectedClass.max_participants}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500 mb-1">Currently Enrolled</p>
                  <p className="text-gold font-semibold">{selectedClass.current_enrolled}</p>
                </div>
              </div>

              {/* Enrollment Stats */}
              <div className="mb-6">
                <div className="flex justify-between mb-2">
                  <span className="text-gray-300">Enrollment Status</span>
                  <span className="text-white font-bold">
                    {selectedClass.current_enrolled}/{selectedClass.max_participants}
                  </span>
                </div>
                <div className="w-full bg-gray-700 h-3 rounded-full overflow-hidden mb-2">
                  <div
                    className={`h-full transition-all ${
                      selectedClass.is_full ? 'bg-red-500' : 'bg-gold'
                    }`}
                    style={{
                      width: `${Math.min(selectedClass.enrollment_percentage, 100)}%`,
                    }}
                  />
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-400">{selectedClass.enrollment_percentage}% full</span>
                  <span
                    className={
                      selectedClass.is_full ? 'text-red-400 font-semibold' : 'text-green-400'
                    }
                  >
                    {selectedClass.remaining_slots > 0
                      ? `${selectedClass.remaining_slots} slots available`
                      : 'No slots available'}
                  </span>
                </div>
              </div>

              {/* Equipment */}
              {selectedClass.equipment && selectedClass.equipment.length > 0 && (
                <div className="mb-6">
                  <p className="text-sm text-gray-500 mb-3">Equipment Used</p>
                  <div className="flex flex-wrap gap-2">
                    {selectedClass.equipment.map((eq) => (
                      <span
                        key={eq.id}
                        className="text-sm bg-gold/20 text-gold px-3 py-1 rounded-full"
                      >
                        {eq.equipment_name || eq.name}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              {/* Buttons */}
              <div className="flex gap-3 pt-6 border-t border-gray-800">
                {enrolledClasses?.includes(selectedClass.id) ? (
                  <button
                    disabled
                    className="flex-1 py-3 px-4 rounded-lg bg-gold/20 text-gold font-semibold"
                  >
                    ✓ Already Enrolled
                  </button>
                ) : (
                  <button
                    onClick={() => handleEnroll(selectedClass)}
                    disabled={selectedClass.is_full || enrolling}
                    className={`flex-1 py-3 px-4 rounded-lg font-semibold transition-all ${
                      selectedClass.is_full
                        ? 'bg-gray-700 text-gray-500 cursor-not-allowed'
                        : 'bg-gold text-black hover:bg-gold/90 active:scale-95'
                    }`}
                  >
                    {enrolling ? 'Enrolling...' : 'Enroll in Class'}
                  </button>
                )}
                <button
                  onClick={() => setSelectedClass(null)}
                  className="flex-1 py-3 px-4 rounded-lg bg-gray-800 text-white hover:bg-gray-700"
                >
                  Close
                </button>
              </div>
            </motion.div>
          </div>
        )}
      </div>
    </div>
  )
}
