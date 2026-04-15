import { useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Dumbbell, Users, Calendar, Zap, Trophy, ArrowRight } from 'lucide-react'
import { Button, Card, Badge, ElevateGymLogo } from '@/components'

export default function Landing() {
  const navigate = useNavigate()

  const features = [
    {
      icon: Dumbbell,
      title: 'Elite Equipment Floor',
      description: 'Premium machines, free weights, and dedicated zones maintained for serious training sessions.',
    },
    {
      icon: Users,
      title: 'Coaches Who Deliver',
      description: 'Experienced trainers focused on measurable progress, form quality, and long-term results.',
    },
    {
      icon: Calendar,
      title: 'Schedules That Fit Life',
      description: 'Flexible class times from early mornings to late evenings so consistency is easier to keep.',
    },
  ]

  const plans = [
    {
      name: 'Bronze',
      price: '₱750',
      period: '/month',
      features: ['5 classes/week', 'Equipment access', 'Basic support'],
      popular: false,
    },
    {
      name: 'Silver',
      price: '₱1,000',
      period: '/month',
      features: ['Unlimited classes', 'Equipment access', 'Priority support', '1 coaching session/month'],
      popular: true,
    },
    {
      name: 'Gold',
      price: '₱1,500',
      period: '/month',
      features: ['Unlimited everything', '4 coaching sessions/month', 'Nutrition guidance', 'VIP support'],
      popular: false,
    },
  ]

  const testimonials = [
    { name: 'Sarah', quote: 'Lost 30 lbs and finally feel strong and confident again.' },
    { name: 'Mike', quote: 'Great coaching and great energy. This place changed my routine for good.' },
    { name: 'Emma', quote: 'Clean facility, professional staff, and a community that keeps me motivated.' },
  ]

  return (
    <div className="min-h-screen text-white bg-dark-bg">
      <section className="relative min-h-screen flex items-center pt-24 pb-20 px-4 overflow-hidden">
        <div
          className="absolute inset-0"
          style={{
            backgroundImage: 'linear-gradient(to right, rgba(0,0,0,0.82), rgba(0,0,0,0.58)), url(/images/hero-bg.jpg)',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,158,11,0.18),transparent_45%)]" />

        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.45 }}
          className="max-w-6xl mx-auto w-full relative z-10"
        >
          <div className="max-w-3xl">
            <div className="mb-8 flex items-center gap-4">
              <ElevateGymLogo size={88} />
              <div>
                <p className="text-gold-300 tracking-[0.2em] uppercase text-sm">Elevate Gym</p>
                <p className="text-gray-300">Rise Above Limits</p>
              </div>
            </div>

            <h1 className="text-5xl md:text-7xl font-bold leading-[0.94] mb-6">
              Premium Training.
              <span className="block text-gold-400">Serious Results.</span>
            </h1>

            <p className="text-lg md:text-xl text-gray-200 max-w-2xl mb-10 leading-relaxed">
              Built for members who want structure, consistency, and performance. Train with top-tier equipment,
              proven coaches, and a gym floor designed for progress.
            </p>

            <div className="flex flex-wrap gap-4">
              <Button variant="primary" size="lg" onClick={() => navigate('/register')}>
                Join Now
              </Button>
              <Button variant="secondary" size="lg" onClick={() => navigate('/login')}>
                Member Sign In
              </Button>
              <Button
                variant="secondary"
                size="lg"
                onClick={() => window.scrollTo({ top: window.innerHeight, behavior: 'smooth' })}
              >
                View Plans <ArrowRight size={16} />
              </Button>
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4 mt-16 max-w-2xl">
            {[
              { value: '10K+', label: 'Active Members' },
              { value: '500+', label: 'Classes Monthly' },
              { value: '50+', label: 'Expert Trainers' },
            ].map((stat) => (
              <div key={stat.label} className="bg-black/45 border border-gold-600/20 rounded-lg p-4">
                <div className="text-2xl md:text-3xl font-bold text-gold-300">{stat.value}</div>
                <div className="text-sm text-gray-300">{stat.label}</div>
              </div>
            ))}
          </div>
        </motion.div>
      </section>

      <section className="py-24 px-4">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-4xl md:text-5xl text-center mb-12">Why Members Stay</h2>
          <div className="grid md:grid-cols-3 gap-6">
            {features.map((feature) => {
              const Icon = feature.icon
              return (
                <Card key={feature.title} className="h-full">
                  <Icon className="text-gold-400 mb-4" size={36} />
                  <h3 className="text-2xl mb-2">{feature.title}</h3>
                  <p className="text-gray-300 leading-relaxed">{feature.description}</p>
                </Card>
              )
            })}
          </div>
        </div>
      </section>

      <section className="py-24 px-4 bg-black/30 border-y border-gold-600/15">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-4xl md:text-5xl text-center mb-2">Membership Plans</h2>
          <p className="text-center text-gray-400 mb-12">Transparent pricing. No gimmicks. Pick what fits your training pace.</p>

          <div className="grid md:grid-cols-3 gap-6">
            {plans.map((plan) => (
              <Card
                key={plan.name}
                className={`relative h-full ${plan.popular ? 'border-gold-500/60 shadow-xl shadow-gold-900/20' : ''}`}
              >
                {plan.popular && (
                  <div className="absolute -top-3 right-4">
                    <Badge variant="success">MOST POPULAR</Badge>
                  </div>
                )}
                <h3 className="text-3xl mb-2">{plan.name}</h3>
                <div className="mb-6">
                  <span className="text-4xl font-bold text-gold-300">{plan.price}</span>
                  <span className="text-gray-400">{plan.period}</span>
                </div>
                <ul className="space-y-3 mb-8">
                  {plan.features.map((feature) => (
                    <li key={feature} className="flex items-center gap-2 text-gray-200">
                      <Zap size={15} className="text-gold-400" />
                      {feature}
                    </li>
                  ))}
                </ul>
                <Button variant={plan.popular ? 'primary' : 'secondary'} className="w-full" onClick={() => navigate('/register')}>
                  Get Started
                </Button>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 px-4">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-4xl md:text-5xl text-center mb-12">Member Results</h2>
          <div className="grid md:grid-cols-3 gap-6">
            {testimonials.map((testimonial) => (
              <Card key={testimonial.name}>
                <div className="flex items-center gap-1 mb-4">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <Trophy key={star} size={15} className="text-gold-400" fill="currentColor" />
                  ))}
                </div>
                <p className="text-gray-200 mb-4 leading-relaxed">"{testimonial.quote}"</p>
                <p className="font-semibold text-gold-300">{testimonial.name}</p>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20 px-4 border-t border-gold-600/20 bg-black/25">
        <div className="max-w-5xl mx-auto text-center">
          <h2 className="text-4xl mb-4">Ready to Train With Intention?</h2>
          <p className="text-gray-300 mb-8 text-lg">Start your plan today and track every step of your progress inside Elevate Gym.</p>
          <div className="flex flex-wrap justify-center gap-4">
            <Button variant="primary" size="lg" onClick={() => navigate('/register')}>
              Start Your Journey
            </Button>
            <Button variant="secondary" size="lg" onClick={() => navigate('/about')}>
              Learn More
            </Button>
          </div>
        </div>
      </section>
    </div>
  )
}
