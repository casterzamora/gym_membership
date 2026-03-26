import { motion } from 'framer-motion'

export default function LoadingSpinner() {
  return (
    <div className="flex items-center justify-center gap-2">
      <motion.div
        animate={{ rotate: 360 }}
        transition={{ duration: 2, repeat: Infinity, ease: 'linear' }}
        className="w-8 h-8 border-3 border-gold-bright border-t-transparent rounded-full"
      />
      <span className="text-gold-bright font-semibold">Loading...</span>
    </div>
  )
}
