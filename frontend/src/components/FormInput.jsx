import React from 'react';

const FormInput = ({ 
  label, 
  type = 'text', 
  placeholder, 
  value, 
  onChange, 
  error,
  required = false,
  disabled = false,
  options = []
}) => {
  return (
    <div>
      <label className="block text-sm font-medium text-gray-300 mb-2">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </label>
      
      {type === 'select' ? (
        <select
          value={value || ''}
          onChange={onChange}
          disabled={disabled}
          className={`w-full px-4 py-2.5 border rounded-lg bg-dark-secondary text-gray-100 focus:outline-none focus:ring-2 focus:ring-gold-600/60 transition ${
            error ? 'border-red-500' : 'border-gray-700'
          } disabled:opacity-50 disabled:cursor-not-allowed`}
        >
          <option value="">-- Select {label} --</option>
          {options.map(opt => (
            <option key={opt.value} value={opt.value}>
              {opt.label}
            </option>
          ))}
        </select>
      ) : type === 'textarea' ? (
        <textarea
          value={value || ''}
          onChange={onChange}
          placeholder={placeholder}
          disabled={disabled}
          className={`w-full px-4 py-2.5 border rounded-lg bg-dark-secondary text-gray-100 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gold-600/60 transition resize-none h-24 ${
            error ? 'border-red-500' : 'border-gray-700'
          } disabled:opacity-50 disabled:cursor-not-allowed`}
        />
      ) : (
        <input
          type={type}
          value={value || ''}
          onChange={onChange}
          placeholder={placeholder}
          disabled={disabled}
          className={`w-full px-4 py-2.5 border rounded-lg bg-dark-secondary text-gray-100 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gold-600/60 transition ${
            error ? 'border-red-500' : 'border-gray-700'
          } disabled:opacity-50 disabled:cursor-not-allowed`}
        />
      )}
      
      {error && <p className="text-red-500 text-sm mt-1">{error}</p>}
    </div>
  );
};

export default FormInput;
