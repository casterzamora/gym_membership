import { motion } from 'framer-motion'

export default function Button({ 
  children, 
  variant = 'primary', 
  size = 'md', 
  isLoading = false,
  className = '',
  ...props 
}) {
  const baseStyles = 'font-bold rounded-lg transition-all duration-300 flex items-center justify-center gap-2'
  
  const variants = {
    primary: 'bg-gradient-to-r from-gold-bright to-gold-600 hover:from-gold-600 hover:to-gold-700 text-black shadow-lg hover:shadow-xl hover:shadow-gold-500/50',
    secondary: 'bg-dark-card border border-gold-bright/30 text-gold-bright hover:border-gold-bright hover:shadow-lg hover:shadow-gold-bright/20',
    danger: 'bg-red-600 hover:bg-red-700 text-white shadow-lg hover:shadow-red-500/50',
    success: 'bg-accent-teal hover:bg-teal-600 text-black shadow-lg hover:shadow-teal-500/50',
  }
  
  const sizes = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-6 py-2.5 text-base',
    lg: 'px-8 py-3 text-lg',
  }

  return (
    <motion.button
      whileHover={{ scale: 1.02 }}
      whileTap={{ scale: 0.98 }}
      disabled={isLoading}
      className={`${baseStyles} ${variants[variant]} ${sizes[size]} ${isLoading ? 'opacity-75 cursor-not-allowed' : ''} ${className}`}
      {...props}
    >
      {isLoading ? (
        <span className="animate-spin">⚡</span>
      ) : null}
      {children}
    </motion.button>
  )
}
