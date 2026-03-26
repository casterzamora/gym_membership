import { motion } from 'framer-motion'

export default function Card({ children, className = '', hover = true, ...props }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      whileHover={hover ? { y: -4, boxShadow: '0 20px 40px rgba(255, 215, 0, 0.1)' } : {}}
      transition={{ duration: 0.3 }}
      className={`bg-dark-card border border-gold-bright/10 rounded-xl p-6 backdrop-blur-xs transition-all duration-300 ${className}`}
      {...props}
    >
      {children}
    </motion.div>
  )
}
