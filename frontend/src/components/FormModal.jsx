import React from 'react';
import { X } from 'lucide-react';
import Button from './Button';

const FormModal = ({ 
  isOpen, 
  title, 
  onClose, 
  onSubmit, 
  children, 
  loading = false,
  submitLabel = 'Save',
  cancelLabel = 'Cancel',
  isValid = true
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div className="bg-dark-card border border-gold-600/20 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="flex justify-between items-center p-6 border-b border-gray-800 sticky top-0 bg-dark-card">
          <h2 className="text-xl font-bold text-white">{title}</h2>
          <button
            onClick={onClose}
            disabled={loading}
            className="p-1 text-gray-300 hover:bg-black/30 rounded-lg transition disabled:opacity-50"
          >
            <X size={24} />
          </button>
        </div>

        {/* Content */}
        <form onSubmit={onSubmit} className="p-6">
          <div className="space-y-4">
            {children}
          </div>

          {/* Actions */}
          <div className="flex gap-3 justify-end mt-8 pt-6 border-t border-gray-800">
            <Button
              type="button"
              variant="secondary"
              onClick={onClose}
              disabled={loading}
            >
              {cancelLabel}
            </Button>
            <Button
              type="submit"
              disabled={loading || !isValid}
              title={!isValid ? 'Please fill in all required fields' : 'Submit form'}
            >
              {loading ? 'Saving...' : submitLabel}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default FormModal;
