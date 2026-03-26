import { motion } from 'framer-motion'

export default function StatCard({ icon: Icon, label, value, trend, gradient = 'from-gold-500 to-gold-600' }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      whileHover={{ y: -5 }}
      className={`bg-gradient-to-br ${gradient} rounded-xl p-6 text-white shadow-lg hover:shadow-2xl transition-all duration-300`}
    >
      <div className="flex items-start justify-between">
        <div>
          <p className="text-sm font-medium text-white/80 mb-2">{label}</p>
          <p className="text-4xl font-bold mb-2">{value}</p>
          {trend && (
            <p className={`text-xs font-semibold ${trend > 0 ? 'text-green-300' : 'text-red-300'}`}>
              {trend > 0 ? '↑' : '↓'} {Math.abs(trend)}% this week
            </p>
          )}
        </div>
        {Icon && (
          <div className="bg-white/20 rounded-lg p-3">
            <Icon size={24} className="text-white" />
          </div>
        )}
      </div>
    </motion.div>
  )
}
