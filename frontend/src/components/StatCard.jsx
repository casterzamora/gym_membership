import { motion } from 'framer-motion'

export default function StatCard({ icon: Icon, label, value, trend, gradient = 'from-gold-500 to-gold-600' }) {
  const accent = gradient.includes('blue')
    ? 'bg-blue-500'
    : gradient.includes('purple')
      ? 'bg-violet-500'
      : gradient.includes('orange') || gradient.includes('red')
        ? 'bg-orange-500'
        : 'bg-gold-500'

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      whileHover={{ y: -4 }}
      className="rounded-xl p-5 bg-dark-card border border-gold-600/20 text-white shadow-lg shadow-black/20 transition-all duration-300"
    >
      <div className={`h-1.5 w-12 rounded-full mb-4 ${accent}`} />
      <div className="flex items-start justify-between">
        <div>
          <p className="text-xs font-semibold tracking-wide text-gray-400 mb-2">{label}</p>
          <p className="text-3xl font-bold mb-2 leading-none">{value}</p>
          {trend && (
            <p className={`text-xs font-semibold ${trend > 0 ? 'text-green-300' : 'text-red-300'}`}>
              {trend > 0 ? '↑' : '↓'} {Math.abs(trend)}% this week
            </p>
          )}
        </div>
        {Icon && (
          <div className="bg-black/30 rounded-lg p-3 border border-gold-600/20">
            <Icon size={22} className="text-gold-400" />
          </div>
        )}
      </div>
    </motion.div>
  )
}
