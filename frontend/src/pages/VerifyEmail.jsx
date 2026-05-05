import { useEffect, useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { authAPI } from '@/services/api';
import toast from 'react-hot-toast';
import { Mail, CheckCircle2, AlertCircle } from 'lucide-react';

export default function VerifyEmail() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [verifying, setVerifying] = useState(true);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    const verifyToken = async () => {
      const token = searchParams.get('token');

      if (!token) {
        setError('No verification token provided');
        setVerifying(false);
        return;
      }

      try {
        const response = await authAPI.verifyEmail({ token });
        if (response.data.success) {
          setSuccess(true);
          toast.success('Email verified successfully!');
          
          // Redirect to checkout page after 2 seconds
          setTimeout(() => {
            navigate('/register/confirmation');
          }, 2000);
        }
      } catch (err) {
        const message = err.response?.data?.message || 'Failed to verify email';
        setError(message);
        toast.error(message);
      } finally {
        setVerifying(false);
      }
    };

    verifyToken();
  }, [searchParams, navigate]);

  return (
    <div className="min-h-screen bg-dark-bg flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="bg-dark-secondary rounded-lg shadow-lg p-8 text-center">
          {verifying ? (
            <>
              <div className="mb-6 flex justify-center">
                <Mail className="text-gold-400 animate-pulse" size={64} />
              </div>
              <h1 className="text-2xl font-bold text-white mb-2">Verifying Email</h1>
              <p className="text-gray-400">Please wait while we verify your email address...</p>
            </>
          ) : success ? (
            <>
              <div className="mb-6 flex justify-center">
                <CheckCircle2 className="text-green-400" size={64} />
              </div>
              <h1 className="text-2xl font-bold text-white mb-2">Email Verified!</h1>
              <p className="text-gray-400">Your email has been successfully verified. Redirecting to checkout...</p>
            </>
          ) : (
            <>
              <div className="mb-6 flex justify-center">
                <AlertCircle className="text-red-400" size={64} />
              </div>
              <h1 className="text-2xl font-bold text-white mb-2">Verification Failed</h1>
              <p className="text-gray-400 mb-6">{error || 'Unable to verify your email'}</p>
              <div className="space-y-3">
                <button
                  onClick={() => navigate('/register')}
                  className="w-full px-4 py-2 bg-gold-500 hover:bg-gold-600 text-white rounded-lg transition font-medium"
                >
                  Back to Registration
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
