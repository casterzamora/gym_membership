import { motion } from 'framer-motion'
import { Dumbbell, Users, Heart, Zap, Target, Award, ArrowLeft } from 'lucide-react'
import { Button, Card } from '@/components'
import { useNavigate } from 'react-router-dom'

export default function About() {
  const navigate = useNavigate()

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2,
        delayChildren: 0.3,
      },
    },
  }

  const itemVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
  }

  const values = [
    { icon: Target, title: 'Mission-Driven', description: 'Committed to helping members achieve their fitness goals through expert guidance and world-class facilities.' },
    { icon: Users, title: 'Community First', description: 'Building a supportive community where everyone feels welcome, regardless of their fitness level.' },
    { icon: Heart, title: 'Holistic Wellness', description: 'We believe in comprehensive health that includes fitness, nutrition guidance, and mental well-being.' },
    { icon: Award, title: 'Excellence', description: 'Committed to maintaining the highest standards in equipment, facilities, and trainer expertise.' },
  ]

  const team = [
    { name: 'Sarah Johnson', role: 'General Manager', specialty: 'Fitness Management' },
    { name: 'Michael Chen', role: 'Head Trainer', specialty: 'Strength & Conditioning' },
    { name: 'Emma Rodriguez', role: 'Wellness Coach', specialty: 'Nutrition & Recovery' },
    { name: 'David Park', role: 'Facilities Manager', specialty: 'Equipment & Maintenance' },
  ]

  return (
    <div className="min-h-screen bg-dark-bg text-white overflow-hidden pt-20">
      {/* Animated Background */}
      <div className="fixed inset-0 -z-10">
        <div className="absolute top-1/4 left-10 w-72 h-72 bg-gold-bright/5 rounded-full blur-3xl"></div>
        <div className="absolute bottom-1/4 right-10 w-96 h-96 bg-accent-orange/5 rounded-full blur-3xl"></div>
      </div>

      {/* Hero Section */}
      <section className="py-20 px-4">
        <motion.div initial="hidden" animate="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.div variants={itemVariants} className="text-center mb-12">
            {/* Back Button */}
            <motion.button
              variants={itemVariants}
              onClick={() => navigate('/')}
              className="mb-6 flex items-center gap-2 text-gray-400 hover:text-gold-bright transition group"
            >
              <ArrowLeft size={18} className="group-hover:-translate-x-1 transition-transform" />
              <span>Back to Home</span>
            </motion.button>
            
            <h1 className="text-6xl md:text-7xl font-black mb-6 bg-gradient-to-r from-gold-bright via-gold-400 to-accent-orange bg-clip-text text-transparent">
              About Elevate Gym
            </h1>
            <p className="text-2xl text-gold-bright font-bold mb-6">Rise Above Limits</p>
            <p className="text-xl text-gray-300 max-w-3xl mx-auto mb-8">
              At Elevate Gym, we're more than just a fitness facility — we're a community dedicated to transforming lives through wellness, strength, and empowerment.
            </p>
          </motion.div>
        </motion.div>
      </section>

      {/* Our Story Section */}
      <section className="py-20 px-4 bg-dark-card/50">
        <motion.div initial="hidden" whileInView="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.div variants={itemVariants} className="grid md:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-4xl font-black mb-6 text-gold-bright">Our Story</h2>
              <p className="text-gray-300 mb-4 text-lg">
                Founded with a vision to revolutionize fitness in the Philippines, Elevate Gym was built on the belief that everyone deserves access to premium fitness facilities and expert guidance.
              </p>
              <p className="text-gray-300 mb-4 text-lg">
                What started as a small community gym has grown into a state-of-the-art fitness center serving thousands of members from all walks of life.
              </p>
              <p className="text-gray-300 text-lg">
                Our commitment to excellence, innovation, and member satisfaction drives everything we do. We're not just building stronger bodies — we're building stronger communities.
              </p>
            </div>
            <motion.div variants={itemVariants} className="relative">
              <div className="absolute inset-0 bg-gradient-to-r from-gold-bright/20 to-accent-orange/20 rounded-2xl blur-xl"></div>
              <div className="relative bg-dark-secondary rounded-2xl p-8 border border-gold-bright/30">
                <Dumbbell size={80} className="text-gold-bright mb-4" />
                <h3 className="text-3xl font-black text-gold-bright mb-4">Our Vision</h3>
                <p className="text-gray-300">
                  To be the most trusted fitness destination in the Philippines, where every member finds the strength to rise above their limits and achieve their dreams.
                </p>
              </div>
            </motion.div>
          </motion.div>
        </motion.div>
      </section>

      {/* Core Values */}
      <section className="py-20 px-4">
        <motion.div initial="hidden" whileInView="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.h2 variants={itemVariants} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Our Core Values
          </motion.h2>
          <motion.div variants={containerVariants} className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            {values.map((value, i) => (
              <motion.div key={i} variants={itemVariants}>
                <Card className="text-center h-full">
                  <value.icon size={48} className="mx-auto mb-4 text-gold-bright" />
                  <h3 className="text-2xl font-bold mb-3 text-gold-bright">{value.title}</h3>
                  <p className="text-gray-400">{value.description}</p>
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </motion.div>
      </section>

      {/* Why Choose Us */}
      <section className="py-20 px-4 bg-dark-card/50">
        <motion.div initial="hidden" whileInView="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.h2 variants={itemVariants} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Why Choose Elevate Gym?
          </motion.h2>
          <motion.div variants={containerVariants} className="grid md:grid-cols-2 gap-8">
            {[
              { title: '50+ Expert Trainers', description: 'Certified professionals with specialized expertise in various fitness disciplines.' },
              { title: 'State-of-the-Art Equipment', description: 'Premium gym equipment regularly maintained and updated for optimal performance.' },
              { title: '500+ Classes Monthly', description: 'Diverse classes ranging from HIIT to Yoga, available at various times throughout the week.' },
              { title: 'Personalized Programs', description: 'Custom fitness plans tailored to your goals, fitness level, and preferences.' },
              { title: 'Community Support', description: 'Join thousands of members with a shared passion for fitness and wellness.' },
              { title: 'Flexible Membership', description: 'Choose from Bronze, Silver, or Gold plans that fit your schedule and budget.' },
            ].map((item, i) => (
              <motion.div key={i} variants={itemVariants}>
                <Card className="border-l-4 border-l-gold-bright">
                  <h3 className="text-2xl font-bold mb-3 text-gold-bright">{item.title}</h3>
                  <p className="text-gray-300 text-lg">{item.description}</p>
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </motion.div>
      </section>

      {/* Team Section */}
      <section className="py-20 px-4">
        <motion.div initial="hidden" whileInView="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.h2 variants={itemVariants} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Our Leadership Team
          </motion.h2>
          <motion.div variants={containerVariants} className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            {team.map((member, i) => (
              <motion.div key={i} variants={itemVariants}>
                <Card className="text-center">
                  <div className="w-20 h-20 bg-gradient-to-br from-gold-bright to-accent-orange rounded-full mx-auto mb-4 flex items-center justify-center">
                    <Users size={40} className="text-dark-bg" />
                  </div>
                  <h3 className="text-2xl font-bold mb-2 text-gold-bright">{member.name}</h3>
                  <p className="font-semibold text-white mb-2">{member.role}</p>
                  <p className="text-gray-400">{member.specialty}</p>
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </motion.div>
      </section>

      {/* Stats Section */}
      <section className="py-20 px-4 bg-dark-card/50">
        <motion.div initial="hidden" whileInView="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.div variants={containerVariants} className="grid md:grid-cols-4 gap-6">
            {[
              { value: '10K+', label: 'Active Members' },
              { value: '50+', label: 'Expert Trainers' },
              { value: '500+', label: 'Classes Monthly' },
              { value: '100%', label: 'Member Satisfaction' },
            ].map((stat, i) => (
              <motion.div key={i} variants={itemVariants} className="text-center">
                <div className="text-5xl font-black text-gold-bright mb-3">{stat.value}</div>
                <div className="text-gray-300 text-lg font-semibold">{stat.label}</div>
              </motion.div>
            ))}
          </motion.div>
        </motion.div>
      </section>

      {/* CTA Section */}
      <section className="py-20 px-4">
        <motion.div initial={{ opacity: 0, scale: 0.9 }} whileInView={{ opacity: 1, scale: 1 }} className="max-w-4xl mx-auto bg-gradient-to-r from-gold-bright/20 to-accent-orange/20 rounded-2xl p-12 border border-gold-bright/30 text-center">
          <h2 className="text-4xl font-black mb-4">Ready to Rise Above Your Limits?</h2>
          <p className="text-xl text-gray-300 mb-8">
            Join Elevate Gym today and become part of our thriving fitness community
          </p>
          <div className="flex gap-4 justify-center flex-wrap">
            <Button variant="primary" size="lg" onClick={() => navigate('/register')}>
              Start Your Journey
            </Button>
            <Button variant="secondary" size="lg" onClick={() => navigate('/')}>
              Back to Home
            </Button>
          </div>
        </motion.div>
      </section>
    </div>
  )
}
