import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { classesAPI, membersAPI, attendanceAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'
import { Card, Button, LoadingSpinner } from '@/components'
import toast from 'react-hot-toast'
import { BookOpen } from 'lucide-react'

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
    if (!authLoading && !user) {
      navigate('/login')
      return
    }
    
    if (user) {
      fetchAllData()
    }
  }, [user, authLoading])

  const fetchAllData = async () => {
    try {
      setLoading(true)
      setError('')
      
      // Get member data first
      console.log('📥 Fetching member data for user:', user?.id)
      const memberRes = await membersAPI.get(user.id)
      setMemberData(memberRes.data.data)
      console.log('✅ Member data loaded:', memberRes.data.data)
      
      // Get classes
      console.log('📥 Fetching classes...')
      const classesRes = await classesAPI.list()
      console.log('✅ Classes loaded:', classesRes.data.data)
      if (classesRes.data.data) {
        setClasses(classesRes.data.data)
      }
      
      // Get enrolled classes
      console.log('📥 Fetching attendance records...')
      const attendanceRes = await attendanceAPI.list()
      console.log('✅ Attendance loaded:', attendanceRes.data.data)
      if (attendanceRes.data.data) {
        // Extract unique class IDs from attendance records
        const classIds = attendanceRes.data.data
          .filter(a => a.member_id === memberRes.data.data.id)
          .map(a => a.schedule?.class_id || a.schedule?.fitness_class_id)
          .filter(Boolean)
        setEnrolledClasses([...new Set(classIds)])
        console.log('✅ Enrolled classes:', classIds)
      }
    } catch (err) {
      console.error('❌ Classes fetch error:', err)
      console.error('Response status:', err.response?.status)
      console.error('Response data:', err.response?.data)
      setError(err.response?.data?.message || err.message || 'Failed to load classes')
    } finally {
      setLoading(false)
    }
  }

  const handleEnroll = async (fitnessClass) => {
    if (!memberData) {
      toast.error('Member data not loaded')
      return
    }

    try {
      setEnrolling(true)
      console.log('🔄 Enrolling member', memberData.id, 'in class', fitnessClass.id)
      
      // Enroll in the class
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
      } else {
        throw new Error('Unexpected response status')
      }
    } catch (err) {
      console.error('❌ Enrollment error details:', err)
      console.error('Error response data:', err.response?.data)
      
      let message = 'Failed to enroll in class'
      if (err.response?.data?.message) {
        message = err.response.data.message
      } else if (err.response?.data?.error) {
        message = err.response.data.error
      } else if (err.message) {
        message = err.message
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

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-6xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-12">
          <div className="flex items-center gap-3 mb-2">
            <BookOpen size={36} className="text-gold-bright" />
            <h1 className="text-4xl font-bold text-white">
              Our Classes
            </h1>
          </div>
          <p className="text-gray-400">
            Explore our range of fitness classes designed for all levels
          </p>
        </div>

        {/* Classes Grid */}
        {classes.length === 0 ? (
          <div className="text-center py-12 text-gray-400">
            No classes available at the moment
          </div>
        ) : (
          <div className="grid md:grid-cols-3 gap-6 mb-12">
            {classes.map((fitnessClass) => {
              const isEnrolled = enrolledClasses.includes(fitnessClass.id)
              return (
                <Card 
                  key={fitnessClass.id}
                  className="overflow-hidden hover:border-gold-bright/50 transition cursor-pointer"
                >
                  {/* Class Header */}
                  <div className="bg-gradient-to-r from-gold-600 to-gold-500 p-6">
                    <h3 className="text-2xl font-bold text-black">{fitnessClass.class_name}</h3>
                    <p className="text-black/80 mt-1">{fitnessClass.description}</p>
                  </div>

                  {/* Class Details */}
                  <div className="p-6 space-y-4">
                    {/* Instructor */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">INSTRUCTOR</div>
                      <div className="text-white">{fitnessClass.trainer?.user?.name || 'TBD'}</div>
                    </div>

                    {/* Category */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">CATEGORY</div>
                      <div className="text-white capitalize">{fitnessClass.category || 'General'}</div>
                    </div>

                    {/* Capacity */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">CAPACITY</div>
                      <div className="text-white">{fitnessClass.max_participants || fitnessClass.capacity} spots available</div>
                    </div>

                    {/* CTA Button */}
                    <button
                      onClick={() => {
                        if (!isEnrolled) {
                          handleEnroll(fitnessClass)
                        } else {
                          toast.info('You are already enrolled in this class')
                        }
                      }}
                      disabled={isEnrolled || enrolling}
                      className={`w-full mt-6 px-4 py-3 font-bold rounded transition ${
                        isEnrolled 
                          ? 'bg-green-600 text-white cursor-not-allowed opacity-75'
                          : 'bg-gold-600 text-black hover:bg-gold-500'
                      }`}
                    >
                      {isEnrolled ? '✓ Enrolled' : enrolling ? 'Enrolling...' : 'Enroll Now'}
                    </button>
                  </div>
                </Card>
              )
            })}
          </div>
        )}

        {/* Class Detail Modal */}
        {selectedClass && (
          <div
            className="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50"
            onClick={() => setSelectedClass(null)}
          >
            <Card
              className="max-w-2xl w-full max-h-96 overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="bg-gradient-to-r from-gold-600 to-gold-500 p-6 flex justify-between items-start">
                <div>
                  <h2 className="text-3xl font-bold text-black">{selectedClass.class_name}</h2>
                </div>
                <button
                  onClick={() => setSelectedClass(null)}
                  className="text-black text-2xl font-bold hover:text-black/70 w-8 h-8 flex items-center justify-center"
                >
                  ✕
                </button>
              </div>

              <div className="p-6">
                <p className="text-gray-300 mb-6">{selectedClass.description}</p>

                <div className="grid grid-cols-2 gap-4 mb-6">
                  <div>
                    <div className="text-gold-500 text-sm font-bold mb-1">INSTRUCTOR</div>
                    <div className="text-white">{selectedClass.trainer?.user?.name || 'TBD'}</div>
                  </div>
                  <div>
                    <div className="text-gold-500 text-sm font-bold mb-1">CATEGORY</div>
                    <div className="text-white capitalize">{selectedClass.category || 'General'}</div>
                  </div>
                  <div>
                    <div className="text-gold-500 text-sm font-bold mb-1">CLASS SIZE</div>
                    <div className="text-white">{selectedClass.max_participants || selectedClass.capacity} participants</div>
                  </div>
                  <div>
                    <div className="text-gold-500 text-sm font-bold mb-1">STATUS</div>
                    <div className="text-white">{enrolledClasses.includes(selectedClass.id) ? 'Enrolled' : 'Available'}</div>
                  </div>
                </div>

                <Button 
                  onClick={() => {
                    handleEnroll(selectedClass)
                    setSelectedClass(null)
                  }}
                  disabled={enrolledClasses.includes(selectedClass.id) || enrolling}
                  className="w-full"
                >
                  {enrolledClasses.includes(selectedClass.id) ? 'Already Enrolled' : 'Enroll Now'}
                </Button>
              </div>
            </Card>
          </div>
        )}
      </div>
    </div>
  )
}
