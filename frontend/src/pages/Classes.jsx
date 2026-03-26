import { useState, useContext, useEffect } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { classesAPI } from '@/services/api'
import { useNavigate } from 'react-router-dom'

export default function Classes() {
  const { user, loading: authLoading } = useContext(AuthContext)
  const [classes, setClasses] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [selectedClass, setSelectedClass] = useState(null)
  const navigate = useNavigate()

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login')
      return
    }
    
    if (user) {
      fetchClasses()
    }
  }, [user, authLoading])

  const fetchClasses = async () => {
    try {
      setLoading(true)
      const response = await classesAPI.list()
      if (response.data.data) {
        setClasses(response.data.data)
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load classes')
    } finally {
      setLoading(false)
    }
  }

  if (authLoading || loading) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center">
        <div className="text-gray-400 text-center">
          <div className="text-lg">Loading classes...</div>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg">
        <div className="max-w-6xl mx-auto px-4 py-8">
          <div className="p-4 bg-red-900 border border-red-600 rounded text-red-200">
            {error}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg">
      <div className="max-w-6xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-12">
          <h1 className="text-4xl font-bold text-white mb-2">
            Our Classes
          </h1>
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
            {classes.map((fitnessClass) => (
              <div
                key={fitnessClass.id}
                onClick={() => setSelectedClass(fitnessClass)}
                className="bg-gray-800 border border-gold-600 rounded overflow-hidden hover:border-gold-500 cursor-pointer transition hover:shadow-lg hover:shadow-gold-600/20"
              >
                {/* Class Header */}
                <div className="bg-gradient-to-r from-gold-600 to-gold-500 p-6">
                  <h3 className="text-2xl font-bold text-black">{fitnessClass.class_name}</h3>
                  <p className="text-black/80 mt-1">{fitnessClass.description}</p>
                </div>

                {/* Class Details */}
                <div className="p-6">
                  <div className="space-y-4">
                    {/* Instructor */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">INSTRUCTOR</div>
                      <div className="text-white">{fitnessClass.trainer?.user?.name || 'TBD'}</div>
                    </div>

                    {/* Difficulty */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">LEVEL</div>
                      <div className="text-white">
                        <span className="inline-block bg-gray-700 px-3 py-1 rounded text-sm">
                          {fitnessClass.difficulty_level || 'All Levels'}
                        </span>
                      </div>
                    </div>

                    {/* Duration */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">DURATION</div>
                      <div className="text-white">{fitnessClass.duration_minutes} minutes</div>
                    </div>

                    {/* Max Participants */}
                    <div>
                      <div className="text-gold-500 text-sm font-bold mb-1">CAPACITY</div>
                      <div className="text-white">{fitnessClass.max_participants} spots available</div>
                    </div>
                  </div>

                  {/* CTA Button */}
                  <button className="w-full mt-6 px-4 py-3 bg-gold-600 text-black font-bold rounded hover:bg-gold-500 transition">
                    View Schedules
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Class Detail Modal */}
        {selectedClass && (
          <div
            className="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50"
            onClick={() => setSelectedClass(null)}
          >
            <div
              className="bg-gray-800 border border-gold-600 rounded max-w-2xl w-full max-h-96 overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="bg-gradient-to-r from-gold-600 to-gold-500 p-6">
                <h2 className="text-3xl font-bold text-black">{selectedClass.name}</h2>
                <button
                  onClick={() => setSelectedClass(null)}
                  className="absolute top-4 right-4 text-black text-2xl font-bold hover:text-black/70"
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
                    <div className="text-gold-500 text-sm font-bold mb-1">LEVEL</div>
                    <div className="text-white">{selectedClass.difficulty_level || 'All Levels'}</div>
                  </div>
                  <div>
                    <div className="text-gold-500 text-sm font-bold mb-1">DURATION</div>
                    <div className="text-white">{selectedClass.duration_minutes} minutes</div>
                  </div>
                  <div>
                    <div className="text-gold-500 text-sm font-bold mb-1">CAPACITY</div>
                    <div className="text-white">{selectedClass.max_participants} spots</div>
                  </div>
                </div>

                <button className="w-full px-4 py-3 bg-gold-600 text-black font-bold rounded hover:bg-gold-500 transition">
                  Enroll Now
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
