import React from 'react';
import { AlertTriangle } from 'lucide-react';
import Button from './Button';

const ConfirmDialog = ({ 
  isOpen, 
  title, 
  message, 
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  onConfirm, 
  onCancel,
  loading = false,
  isDangerous = false
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div className="bg-dark-card border border-gold-600/20 rounded-lg shadow-xl max-w-md w-full">
        <div className="p-6">
          <div className="flex items-center gap-4 mb-4">
            <div className={`p-3 rounded-full ${isDangerous ? 'bg-red-500/20' : 'bg-gold-500/20'}`}>
              <AlertTriangle 
                size={24} 
                className={isDangerous ? 'text-red-400' : 'text-gold-300'}
              />
            </div>
            <h2 className="text-lg font-bold text-white">{title}</h2>
          </div>
          
          <p className="text-gray-300 mb-6">{message}</p>
          
          <div className="flex gap-3 justify-end">
            <Button
              type="button"
              variant="secondary"
              onClick={onCancel}
              disabled={loading}
            >
              {cancelLabel}
            </Button>
            <Button
              type="button"
              variant={isDangerous ? 'danger' : 'primary'}
              onClick={onConfirm}
              disabled={loading}
            >
              {loading ? 'Processing...' : confirmLabel}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ConfirmDialog;
