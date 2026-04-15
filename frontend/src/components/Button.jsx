import { motion } from 'framer-motion'

export default function Button({ 
  children, 
  variant = 'primary', 
  size = 'md', 
  isLoading = false,
  className = '',
  ...props 
}) {
  const baseStyles = 'font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 border'
  
  const variants = {
    primary: 'bg-gold-600 border-gold-500 text-black hover:bg-gold-500 hover:border-gold-400 shadow-md shadow-black/20',
    secondary: 'bg-dark-card border-gold-600/30 text-gold-300 hover:border-gold-500/70 hover:bg-dark-secondary',
    danger: 'bg-red-600 border-red-500 text-white hover:bg-red-700 hover:border-red-600 shadow-md shadow-black/20',
    success: 'bg-accent-teal border-teal-400 text-black hover:bg-teal-500 hover:border-teal-300 shadow-md shadow-black/20',
  }
  
  const sizes = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-5 py-2.5 text-base',
    lg: 'px-7 py-3 text-lg',
  }

  return (
    <motion.button
      whileHover={{ y: -1 }}
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
