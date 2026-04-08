import React, { useState, useEffect } from 'react';
import { DataTable, Badge } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { motion } from 'framer-motion';

const PaymentsManagement = () => {
  const [payments, setPayments] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPayments();
  }, []);

  const fetchPayments = async () => {
    try {
      setLoading(true);
      const response = await api.paymentsAPI?.list?.() || { data: { data: [] } };
      setPayments(response.data?.data || response.data || []);
    } catch (err) {
      toast.error('Failed to load payments');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { 
      key: 'member_id', 
      label: 'Member',
      render: (value) => `Member #${value}`
    },
    { key: 'amount_paid', label: 'Amount', render: (value) => `$${parseFloat(value).toFixed(2)}` },
    { key: 'payment_method', label: 'Method' },
    { 
      key: 'status', 
      label: 'Status',
      render: (value) => (
        <span className={`px-3 py-1 rounded-full text-sm font-medium ${
          value === 'Completed' ? 'bg-green-100 text-green-800' :
          value === 'Pending' ? 'bg-yellow-100 text-yellow-800' :
          'bg-red-100 text-red-800'
        }`}>
          {value}
        </span>
      )
    },
    { 
      key: 'payment_date', 
      label: 'Date',
      render: (value) => new Date(value).toLocaleDateString()
    },
    { 
      key: 'coverage_start', 
      label: 'Coverage Start',
      render: (value) => new Date(value).toLocaleDateString()
    },
    { 
      key: 'coverage_end', 
      label: 'Coverage End',
      render: (value) => new Date(value).toLocaleDateString()
    },
  ];

  return (
    <div className="space-y-8">
      {/* Header */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <h1 className="text-3xl font-bold text-white">Payments & Billing</h1>
      </motion.div>

      {/* Data Table */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <DataTable
        columns={columns}
        data={payments}
        title="All Payments"
        loading={loading}
        searchFields={['member_id', 'payment_method']}
      />
      </motion.div>

      {/* Summary Stats */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-sm font-medium text-gray-600 mb-2">Total Revenue</h3>
          <p className="text-3xl font-bold text-white">
            ${payments.reduce((sum, p) => sum + (parseFloat(p.amount_paid) || 0), 0).toFixed(2)}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-sm font-medium text-gray-600 mb-2">Completed Payments</h3>
          <p className="text-3xl font-bold text-blue-600">
            {payments.filter(p => p.status === 'Completed').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-sm font-medium text-gray-600 mb-2">Pending Payments</h3>
          <p className="text-3xl font-bold text-yellow-600">
            {payments.filter(p => p.status === 'Pending').length}
          </p>
        </div>
      </div>
      </motion.div>
    </div>
  );
};

export default PaymentsManagement;
