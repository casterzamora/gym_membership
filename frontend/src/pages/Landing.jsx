import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Dumbbell, Users, Calendar, Zap, Trophy } from 'lucide-react'
import { Button, Card, Badge } from '@/components'

export default function Landing() {
  const navigate = useNavigate()
  
  const features = [
    { icon: Dumbbell, title: 'World-Class Equipment', description: 'State-of-the-art fitness equipment for every workout' },
    { icon: Users, title: 'Expert Trainers', description: 'Certified professionals ready to guide your journey' },
    { icon: Calendar, title: 'Flexible Classes', description: 'Hundreds of classes scheduled throughout the week' },
  ]

  const plans = [
    { 
      name: 'Bronze', 
      price: '$29', 
      period: '/month',
      features: ['5 classes/week', 'Equipment access', 'Basic support'],
      popular: false 
    },
    { 
      name: 'Silver', 
      price: '$49', 
      period: '/month',
      features: ['Unlimited classes', 'Equipment access', 'Priority support', '1 personal training/month'],
      popular: true 
    },
    { 
      name: 'Gold', 
      price: '$79', 
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

      {/* Hero Section */}
      <section className="pt-20 pb-32 px-4">
        <motion.div initial="hidden" animate="visible" variants={containerVariants} className="max-w-6xl mx-auto">
          <motion.div variants={itemVariants} className="text-center mb-16">
            <motion.div 
              animate={{ rotate: 360 }} 
              transition={{ duration: 20, repeat: Infinity, ease: 'linear' }}
              className="inline-block mb-6"
            >
              <Dumbbell size={64} className="text-gold-bright" />
            </motion.div>
            <h1 className="text-6xl md:text-7xl font-black mb-6 bg-gradient-to-r from-gold-bright via-gold-400 to-accent-orange bg-clip-text text-transparent">
              Transform Your Fitness
            </h1>
            <p className="text-xl text-gray-300 max-w-2xl mx-auto mb-8">
              Join thousands of members who've achieved their fitness goals with our premium gym experience
            </p>
            <div className="flex gap-4 justify-center flex-wrap">
              <Button variant="primary" size="lg" onClick={() => navigate('/register')}>
                Start Your Journey
              </Button>
              <Button variant="secondary" size="lg" onClick={() => navigate('/login')}>
                Sign In
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
      <section className="py-32 px-4">
        <div className="max-w-6xl mx-auto">
          <motion.h2 initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Why Choose Gold's Gym
          </motion.h2>
          <motion.div variants={containerVariants} initial="hidden" whileInView="visible" className="grid md:grid-cols-3 gap-8">
            {features.map((feature, i) => (
              <motion.div key={i} variants={itemVariants}>
                <Card className="text-center">
                  <feature.icon size={48} className="mx-auto mb-4 text-gold-bright" />
                  <h3 className="text-2xl font-bold mb-3">{feature.title}</h3>
                  <p className="text-gray-400">{feature.description}</p>
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Pricing Section */}
      <section className="py-32 px-4">
        <div className="max-w-6xl mx-auto">
          <motion.h2 initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} className="text-5xl font-black text-center mb-6 text-gold-bright">
            Membership Plans
          </motion.h2>
          <p className="text-center text-gray-300 mb-16 max-w-2xl mx-auto">
            Choose the perfect plan for your fitness goals
          </p>
          
          <motion.div variants={containerVariants} initial="hidden" whileInView="visible" className="grid md:grid-cols-3 gap-8">
            {plans.map((plan, i) => (
              <motion.div key={i} variants={itemVariants} className={plan.popular ? 'md:scale-105' : ''}>
                <Card className={`${plan.popular ? 'ring-2 ring-gold-bright' : ''} relative`}>
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
                </Card>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Testimonials Section */}
      <section className="py-32 px-4">
        <div className="max-w-6xl mx-auto">
          <motion.h2 initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} className="text-5xl font-black text-center mb-16 text-gold-bright">
            Success Stories
          </motion.h2>
          <motion.div variants={containerVariants} initial="hidden" whileInView="visible" className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {[
              { name: 'Sarah', quote: 'Lost 30 lbs and feeling stronger than ever!' },
              { name: 'Mike', quote: 'Best trainers. Amazing facilities. Life changing.' },
              { name: 'Emma', quote: 'Found my fitness family at Gold\'s Gym' },
            ].map((testimonial, i) => (
              <motion.div key={i} variants={itemVariants}>
                <Card>
                  <div className="flex items-center gap-1 mb-4">
                    {[...Array(5)].map((_, j) => (
                      <Trophy key={j} size={16} className="text-gold-bright" fill="currentColor" />
                    ))}
                  </div>
                  <p className="text-gray-300 mb-4">"{testimonial.quote}"</p>
                  <p className="font-bold text-gold-bright">{testimonial.name}</p>
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
    </div>
  )
}
