import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Dumbbell, Users, Calendar, Zap, Trophy } from 'lucide-react'
import { Button, Card, Badge, ElevateGymLogo } from '@/components'

export default function Landing() {
  const navigate = useNavigate()
  
  const features = [
    { icon: Dumbbell, title: 'World-Class Equipment', description: 'State-of-the-art fitness machines and free weights updated regularly for optimal performance and results' },
    { icon: Users, title: 'Expert Trainers', description: 'Certified professionals ready to guide your journey with personalized training programs and expert advice' },
    { icon: Calendar, title: 'Flexible Classes', description: 'Hundreds of classes scheduled throughout the week at various times to fit your lifestyle' },
  ]

  const plans = [
    { 
      name: 'Bronze', 
      price: '₱750', 
      period: '/month',
      features: ['5 classes/week', 'Equipment access', 'Basic support'],
      popular: false 
    },
    { 
      name: 'Silver', 
      price: '₱1,000', 
      period: '/month',
      features: ['Unlimited classes', 'Equipment access', 'Priority support', '1 personal training/month'],
      popular: true 
    },
    { 
      name: 'Gold', 
      price: '₱1,500', 
      period: '/month',
      features: ['Unlimited everything', '4 personal trainings/month', 'Nutrition guidance', 'VIP support'],
      popular: false 
    },
  ]

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

  return (
    <div className="min-h-screen bg-dark-bg text-white overflow-hidden">
      {/* Animated Background */}
      <div className="fixed inset-0 -z-10">
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-96 bg-gold-bright/10 rounded-full blur-3xl"></div>
        <div className="absolute bottom-1/2 right-10 w-72 h-72 bg-accent-orange/5 rounded-full blur-3xl"></div>
      </div>

      {/* Hero Section with Enhanced Background */}
      <section className="pt-20 pb-32 px-4 relative overflow-hidden min-h-screen flex items-center">
        {/* Background Image */}
        <div 
          className="absolute inset-0 z-0"
          style={{
            backgroundImage: 'url(/images/hero-bg.jpg)',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundAttachment: 'fixed',
          }}
        />
        
        {/* Dark overlay for readability */}
        <div className="absolute inset-0 bg-black/60 z-0"></div>
        
        {/* Animated Background Pattern (overlay) */}
        <div className="absolute inset-0 opacity-10 z-0">
          <div className="absolute top-10 left-1/4 w-40 h-40 border-2 border-gold-bright rounded-full"></div>
          <div className="absolute bottom-20 right-1/4 w-32 h-32 border-2 border-accent-orange rounded-full"></div>
          <div className="absolute top-1/3 right-10 w-24 h-24 border-2 border-gold-bright rotate-45"></div>
        </div>
        
        <motion.div initial="hidden" animate="visible" variants={containerVariants} className="max-w-6xl mx-auto relative z-10 w-full">
          <motion.div variants={itemVariants} className="text-center mb-16">
            {/* Logo */}
            <div className="flex justify-center mb-8">
              <ElevateGymLogo size={96} className="animate-pulse drop-shadow-lg" />
            </div>
            
            <h1 className="text-6xl md:text-7xl font-black mb-6 bg-gradient-to-r from-gold-bright via-gold-400 to-accent-orange bg-clip-text text-transparent drop-shadow-lg">
              Elevate Gym
            </h1>
            <p className="text-2xl text-gold-bright font-bold mb-8 drop-shadow-lg">Rise Above Limits</p>
            <div className="flex gap-4 justify-center flex-wrap mb-8">
              <Button variant="secondary" size="lg" onClick={() => navigate('/about')}>
                About
              </Button>
              <Button variant="secondary" size="lg" onClick={() => {
                window.scrollTo({ top: window.innerHeight, behavior: 'smooth' })
              }}>
                Plans
              </Button>
              <Button variant="primary" size="lg" onClick={() => navigate('/login')}>
                Sign In
              </Button>
              <Button variant="primary" size="lg" onClick={() => navigate('/register')}>
                Start Your Journey
              </Button>
            </div>
          </motion.div>

          {/* Stats */}
          <motion.div variants={containerVariants} className="grid md:grid-cols-3 gap-6 my-16">
            {[
              { value: '10K+', label: 'Active Members' },
              { value: '500+', label: 'Classes Monthly' },
              { value: '50+', label: 'Expert Trainers' },
            ].map((stat, i) => (
              <motion.div key={i} variants={itemVariants} className="text-center">
                <div className="text-4xl font-black text-gold-bright mb-2">{stat.value}</div>
                <div className="text-gray-400">{stat.label}</div>
              </motion.div>
            ))}
          </motion.div>
        </motion.div>
      </section>

      {/* Features Section */}
      <section className="py-32 px-4 bg-gradient-to-br from-dark-card/30 to-dark-bg">
        <div className="max-w-6xl mx-auto">
          <motion.h2 initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Why Choose Elevate Gym
          </motion.h2>
          <motion.div variants={containerVariants} initial="hidden" whileInView="visible" className="grid md:grid-cols-3 gap-8">
            {/* World-Class Equipment */}
            <motion.div variants={itemVariants} className="group">
              <Card className="text-center h-full relative overflow-hidden">
                {/* Equipment Background Pattern */}
                <svg className="absolute inset-0 w-full h-full opacity-10 group-hover:opacity-15 transition-opacity" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <pattern id="equipment-pattern" patternUnits="userSpaceOnUse" width="100" height="100">
                      {/* Dumbbells */}
                      <circle cx="20" cy="30" r="8" fill="#f59e0b"/>
                      <rect x="28" y="26" width="24" height="8" fill="#f59e0b"/>
                      <circle cx="60" cy="30" r="8" fill="#f59e0b"/>
                      
                      {/* Barbell */}
                      <circle cx="15" cy="70" r="5" fill="#f59e0b"/>
                      <rect x="20" y="66" width="30" height="8" fill="#f59e0b"/>
                      <circle cx="55" cy="70" r="5" fill="#f59e0b"/>
                      
                      {/* Weights */}
                      <rect x="70" y="20" width="12" height="20" fill="#f59e0b"/>
                      <rect x="70" y="50" width="12" height="20" fill="#f59e0b"/>
                    </pattern>
                  </defs>
                  <rect width="400" height="400" fill="url(#equipment-pattern)"/>
                </svg>
                <div className="relative z-10">
                  <Dumbbell size={64} className="mx-auto mb-4 text-gold-bright group-hover:scale-110 transition-transform duration-300" />
                  <h3 className="text-2xl font-bold mb-3 group-hover:text-gold-bright transition-colors">World-Class Equipment</h3>
                  <p className="text-gray-400 group-hover:text-gray-300 transition-colors">State-of-the-art fitness machines and free weights updated regularly for optimal performance and results</p>
                </div>
              </Card>
            </motion.div>

            {/* Expert Trainers */}
            <motion.div variants={itemVariants} className="group">
              <Card className="text-center h-full relative overflow-hidden">
                {/* Trainers Background Pattern */}
                <svg className="absolute inset-0 w-full h-full opacity-10 group-hover:opacity-15 transition-opacity" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <pattern id="trainers-pattern" patternUnits="userSpaceOnUse" width="100" height="100">
                      {/* Person 1 */}
                      <circle cx="25" cy="15" r="6" fill="#f59e0b"/>
                      <rect x="20" y="22" width="10" height="15" fill="#f59e0b"/>
                      <rect x="17" y="25" width="4" height="12" fill="#f59e0b"/>
                      <rect x="29" y="25" width="4" height="12" fill="#f59e0b"/>
                      <rect x="18" y="37" width="4" height="10" fill="#f59e0b"/>
                      <rect x="28" y="37" width="4" height="10" fill="#f59e0b"/>
                      
                      {/* Person 2 */}
                      <circle cx="70" cy="20" r="6" fill="#f59e0b"/>
                      <rect x="65" y="27" width="10" height="15" fill="#f59e0b"/>
                      <rect x="62" y="30" width="4" height="12" fill="#f59e0b"/>
                      <rect x="74" y="30" width="4" height="12" fill="#f59e0b"/>
                      <rect x="63" cy="42" width="4" height="10" fill="#f59e0b"/>
                      <rect x="73" y="42" width="4" height="10" fill="#f59e0b"/>
                      
                      {/* Handshake element */}
                      <path d="M 40 35 Q 45 30 50 35" stroke="#f59e0b" strokeWidth="2" fill="none"/>
                    </pattern>
                  </defs>
                  <rect width="400" height="400" fill="url(#trainers-pattern)"/>
                </svg>
                <div className="relative z-10">
                  <Users size={64} className="mx-auto mb-4 text-gold-bright group-hover:scale-110 transition-transform duration-300" />
                  <h3 className="text-2xl font-bold mb-3 group-hover:text-gold-bright transition-colors">Expert Trainers</h3>
                  <p className="text-gray-400 group-hover:text-gray-300 transition-colors">Certified professionals ready to guide your journey with personalized training programs and expert advice</p>
                </div>
              </Card>
            </motion.div>

            {/* Flexible Classes */}
            <motion.div variants={itemVariants} className="group">
              <Card className="text-center h-full relative overflow-hidden">
                {/* Calendar Background Pattern */}
                <svg className="absolute inset-0 w-full h-full opacity-10 group-hover:opacity-15 transition-opacity" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <pattern id="calendar-pattern" patternUnits="userSpaceOnUse" width="100" height="100">
                      {/* Calendar grid 1 */}
                      <rect x="10" y="10" width="35" height="35" fill="none" stroke="#f59e0b" strokeWidth="1"/>
                      <line x1="10" y1="20" x2="45" y2="20" stroke="#f59e0b" strokeWidth="0.5"/>
                      <line x1="10" y1="30" x2="45" y2="30" stroke="#f59e0b" strokeWidth="0.5"/>
                      <line x1="20" y1="10" x2="20" y2="45" stroke="#f59e0b" strokeWidth="0.5"/>
                      <line x1="30" y1="10" x2="30" y2="45" stroke="#f59e0b" strokeWidth="0.5"/>
                      
                      {/* Calendar grid 2 */}
                      <rect x="55" y="50" width="35" height="35" fill="none" stroke="#f59e0b" strokeWidth="1"/>
                      <line x1="55" y1="60" x2="90" y2="60" stroke="#f59e0b" strokeWidth="0.5"/>
                      <line x1="55" y1="70" x2="90" y2="70" stroke="#f59e0b" strokeWidth="0.5"/>
                      <line x1="65" y1="50" x2="65" y2="85" stroke="#f59e0b" strokeWidth="0.5"/>
                      <line x1="75" y1="50" x2="75" y2="85" stroke="#f59e0b" strokeWidth="0.5"/>
                      
                      {/* Time indicators */}
                      <circle cx="25" cy="60" r="3" fill="#f59e0b"/>
                      <circle cx="70" cy="25" r="3" fill="#f59e0b"/>
                    </pattern>
                  </defs>
                  <rect width="400" height="400" fill="url(#calendar-pattern)"/>
                </svg>
                <div className="relative z-10">
                  <Calendar size={64} className="mx-auto mb-4 text-gold-bright group-hover:scale-110 transition-transform duration-300" />
                  <h3 className="text-2xl font-bold mb-3 group-hover:text-gold-bright transition-colors">Flexible Classes</h3>
                  <p className="text-gray-400 group-hover:text-gray-300 transition-colors">Hundreds of classes scheduled throughout the week at various times to fit your lifestyle</p>
                </div>
              </Card>
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* Pricing Section */}
      <section className="py-32 px-4 relative">
        {/* Background decoration */}
        <div className="absolute inset-0 opacity-5">
          <div className="absolute top-1/2 left-1/4 w-96 h-96 border-2 border-gold-bright rounded-full blur-2xl"></div>
          <div className="absolute top-20 right-1/3 w-80 h-80 border-2 border-accent-orange rounded-full blur-2xl"></div>
        </div>
        
        <div className="max-w-6xl mx-auto relative z-10">
          <motion.h2 initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} className="text-5xl font-black text-center mb-6 text-gold-bright">
            Membership Plans
          </motion.h2>
          <p className="text-center text-gray-300 mb-16 max-w-2xl mx-auto">
            Choose the perfect plan for your fitness goals
          </p>
          
          <motion.div variants={containerVariants} initial="hidden" whileInView="visible" className="grid md:grid-cols-3 gap-8">
            {plans.map((plan, i) => (
              <motion.div key={i} variants={itemVariants} className={plan.popular ? 'md:scale-105' : ''}>
                <Card className={`${plan.popular ? 'ring-2 ring-gold-bright' : ''} relative overflow-hidden`}>
                  {/* Background gradient */}
                  <div className="absolute inset-0 opacity-5">
                    {plan.popular && <div className="absolute inset-0 bg-gradient-to-br from-gold-bright to-accent-orange"></div>}
                    {!plan.popular && <div className="absolute inset-0 bg-gradient-to-br from-gold-bright/50 to-transparent"></div>}
                  </div>
                  <div className="relative z-10">
                    {plan.popular && (
                      <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                        <Badge variant="success">MOST POPULAR</Badge>
                      </div>
                    )}
                    <h3 className="text-3xl font-bold mb-2">{plan.name}</h3>
                    <div className="mb-6">
                      <span className="text-5xl font-black text-gold-bright">{plan.price}</span>
                      <span className="text-gray-400">{plan.period}</span>
                    </div>
                    <ul className="space-y-3 mb-8">
                      {plan.features.map((feature, j) => (
                        <li key={j} className="flex items-center gap-2 text-gray-300">
                          <Zap size={16} className="text-gold-bright flex-shrink-0" />
                          {feature}
                        </li>
                      ))}
                    </ul>
                    <Button variant={plan.popular ? 'primary' : 'secondary'} className="w-full" onClick={() => navigate('/register')}>
                      Get Started
                    </Button>
                  </div>
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Testimonials Section */}
      <section className="py-32 px-4 bg-gradient-to-br from-dark-card/30 to-dark-bg">
        <div className="max-w-6xl mx-auto">
          <motion.h2 initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Success Stories
          </motion.h2>
          <motion.div variants={containerVariants} initial="hidden" whileInView="visible" className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {[
              { name: 'Sarah', quote: 'Lost 30 lbs and feeling stronger than ever!' },
              { name: 'Mike', quote: 'Best trainers. Amazing facilities. Life changing.' },
              { name: 'Emma', quote: 'Found my fitness family at Elevate Gym' },
            ].map((testimonial, i) => (
              <motion.div key={i} variants={itemVariants} className="group">
                <Card className="h-full relative overflow-hidden">
                  {/* Hover background effect */}
                  <div className="absolute inset-0 opacity-0 group-hover:opacity-10 bg-gradient-to-br from-gold-bright to-accent-orange transition-opacity"></div>
                  <div className="relative z-10">
                    <div className="flex items-center gap-1 mb-4">
                      {[...Array(5)].map((_, j) => (
                        <Trophy key={j} size={16} className="text-gold-bright" fill="currentColor" />
                      ))}
                    </div>
                    <p className="text-gray-300 mb-4 text-lg">"{testimonial.quote}"</p>
                    <p className="font-bold text-gold-bright text-lg">{testimonial.name}</p>
                  </div>
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-32 px-4">
        <div className="max-w-4xl mx-auto text-center">
          <motion.div initial={{ opacity: 0, scale: 0.9 }} whileInView={{ opacity: 1, scale: 1 }} className="bg-gradient-to-r from-gold-bright/20 to-accent-orange/20 rounded-2xl p-12 border border-gold-bright/30">
            <h2 className="text-4xl font-black mb-4">Ready to Transform?</h2>
            <p className="text-xl text-gray-300 mb-8">Join our community and start your fitness journey today</p>
            <Button variant="primary" size="lg" onClick={() => navigate('/register')}>
              Sign Up Now
            </Button>
          </motion.div>
        </div>
      </section>

      {/* Footer Navigation */}
      <section className="py-16 px-4 bg-dark-card/50 border-t border-gold-bright/20">
        <div className="max-w-6xl mx-auto">
          <motion.div initial="hidden" whileInView="visible" variants={containerVariants} className="grid md:grid-cols-3 gap-8 text-center">
            <motion.div variants={itemVariants}>
              <Button variant="secondary" size="lg" className="w-full" onClick={() => navigate('/about')}>
                Learn About Us
              </Button>
              <p className="text-gray-400 mt-3">Discover our story and values</p>
            </motion.div>
            <motion.div variants={itemVariants}>
              <Button variant="secondary" size="lg" className="w-full" onClick={() => {
                window.scrollTo({ top: 0, behavior: 'smooth' })
                navigate('/')
              }}>
                View Membership Plans
              </Button>
              <p className="text-gray-400 mt-3">See our Bronze, Silver & Gold plans</p>
            </motion.div>
            <motion.div variants={itemVariants}>
              <Button variant="primary" size="lg" className="w-full" onClick={() => navigate('/login')}>
                Sign In
              </Button>
              <p className="text-gray-400 mt-3">Access your account</p>
            </motion.div>
          </motion.div>
        </div>
      </section>
    </div>
  )
}
