export default function ElevateGymLogo({ size = 48, className = "" }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 100 100"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={className}
    >
      {/* Background circle */}
      <circle cx="50" cy="50" r="48" fill="#1a1a2e" stroke="#f59e0b" strokeWidth="2" />
      
      {/* Barbell */}
      <g>
        {/* Left plate */}
        <rect x="10" y="35" width="12" height="30" fill="#f59e0b" rx="2" />
        {/* Center bar */}
        <rect x="25" y="40" width="50" height="20" fill="#d97706" rx="3" />
        {/* Right plate */}
        <rect x="78" y="35" width="12" height="30" fill="#f59e0b" rx="2" />
      </g>
      
      {/* Upward arrow indicating growth */}
      <g>
        {/* Arrow shaft */}
        <line x1="50" y1="75" x2="50" y2="20" stroke="#fbbf24" strokeWidth="3" strokeLinecap="round" />
        {/* Arrow head top */}
        <line x1="50" y1="20" x2="42" y2="30" stroke="#fbbf24" strokeWidth="3" strokeLinecap="round" />
        <line x1="50" y1="20" x2="58" y2="30" stroke="#fbbf24" strokeWidth="3" strokeLinecap="round" />
      </g>
      
      {/* Accent stars */}
      <circle cx="25" cy="15" r="2.5" fill="#fbbf24" />
      <circle cx="75" cy="15" r="2.5" fill="#fbbf24" />
    </svg>
  )
}
