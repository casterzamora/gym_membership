export default function Badge({ children, variant = 'default' }) {
  const variants = {
    default: 'bg-gold-bright/20 text-gold-bright border border-gold-bright/30',
    success: 'bg-accent-teal/20 text-accent-teal border border-accent-teal/30',
    danger: 'bg-red-500/20 text-red-300 border border-red-500/30',
    warning: 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
    'difficulty-beginner': 'bg-green-500/20 text-green-300 border border-green-500/30',
    'difficulty-intermediate': 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
    'difficulty-advanced': 'bg-red-500/20 text-red-300 border border-red-500/30',
  }

  return (
    <span className={`inline-block px-3 py-1 rounded-full text-xs font-semibold ${variants[variant]}`}>
      {children}
    </span>
  )
}
