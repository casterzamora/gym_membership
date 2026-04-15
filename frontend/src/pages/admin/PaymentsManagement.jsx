import React, { useEffect, useMemo, useState } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';
import { motion } from 'framer-motion';

const initialForm = {
  member_id: '',
  amount_paid: '',
  payment_date: '',
  payment_method_id: '',
  coverage_start: '',
  coverage_end: '',
};

const PaymentsManagement = () => {
  const [payments, setPayments] = useState([]);
  const [members, setMembers] = useState([]);
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingPayment, setEditingPayment] = useState(null);
  const [paymentToDelete, setPaymentToDelete] = useState(null);
  const [formData, setFormData] = useState(initialForm);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchAll();
  }, []);

  const normalizeArray = (payload) => {
    if (Array.isArray(payload)) return payload;
    if (Array.isArray(payload?.data)) return payload.data;
    return [];
  };

  const fetchAll = async () => {
    try {
      setLoading(true);
      const [paymentsRes, membersRes, methodsRes] = await Promise.all([
        api.paymentsAPI.list(),
        api.membersAPI.list(),
        api.paymentMethodsAPI.list(),
      ]);

      setPayments(normalizeArray(paymentsRes?.data?.data ?? paymentsRes?.data));
      setMembers(normalizeArray(membersRes?.data?.data ?? membersRes?.data));
      setPaymentMethods(normalizeArray(methodsRes?.data?.data ?? methodsRes?.data));
    } catch (error) {
      toast.error('Failed to load payments data');
      setPayments([]);
      setMembers([]);
      setPaymentMethods([]);
    } finally {
      setLoading(false);
    }
  };

  const memberOptions = useMemo(
    () => members.map((m) => ({ value: m.id, label: `${m.first_name || ''} ${m.last_name || ''}`.trim() || `Member #${m.id}` })),
    [members]
  );

  const methodOptions = useMemo(
    () => paymentMethods.map((pm) => ({ value: pm.payment_method_id, label: pm.method_name })),
    [paymentMethods]
  );

  const openCreateModal = () => {
    setEditingPayment(null);
    setFormData(initialForm);
    setErrors({});
    setIsModalOpen(true);
  };

  const openEditModal = (payment) => {
    setEditingPayment(payment);
    setFormData({
      member_id: payment.member_id || '',
      amount_paid: payment.amount_paid || '',
      payment_date: payment.payment_date || '',
      payment_method_id: payment.payment_method_id || '',
      coverage_start: payment.coverage_start || '',
      coverage_end: payment.coverage_end || '',
    });
    setErrors({});
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setEditingPayment(null);
    setFormData(initialForm);
    setErrors({});
  };

  const validateForm = () => {
    const next = {};
    if (!formData.member_id) next.member_id = 'Member is required';
    if (!formData.amount_paid || Number(formData.amount_paid) <= 0) next.amount_paid = 'Amount must be greater than 0';
    if (!formData.payment_date) next.payment_date = 'Payment date is required';
    if (!formData.payment_method_id) next.payment_method_id = 'Payment method is required';
    if (!formData.coverage_start) next.coverage_start = 'Coverage start is required';
    if (!formData.coverage_end) next.coverage_end = 'Coverage end is required';
    if (formData.coverage_start && formData.coverage_end && formData.coverage_end <= formData.coverage_start) {
      next.coverage_end = 'Coverage end must be after coverage start';
    }
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    const payload = {
      member_id: Number(formData.member_id),
      amount_paid: Number(formData.amount_paid),
      payment_date: formData.payment_date,
      payment_method_id: Number(formData.payment_method_id),
      coverage_start: formData.coverage_start,
      coverage_end: formData.coverage_end,
    };

    try {
      setLoading(true);
      if (editingPayment) {
        await api.paymentsAPI.update(editingPayment.id, payload);
        toast.success('Payment updated successfully');
      } else {
        await api.paymentsAPI.create(payload);
        toast.success('Payment created successfully');
      }
      closeModal();
      await fetchAll();
    } catch (error) {
      toast.error(error.response?.data?.message || 'Payment operation failed');
    } finally {
      setLoading(false);
    }
  };

  const confirmDelete = async () => {
    if (!paymentToDelete) return;

    try {
      setLoading(true);
      await api.paymentsAPI.delete(paymentToDelete.id);
      toast.success('Payment deleted successfully');
      setIsConfirmOpen(false);
      setPaymentToDelete(null);
      await fetchAll();
    } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to delete payment');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    {
      key: 'member_id',
      label: 'Member',
      render: (value, row) => {
        const firstName = row?.member?.first_name;
        const lastName = row?.member?.last_name;
        if (firstName || lastName) return `${firstName || ''} ${lastName || ''}`.trim();
        return `Member #${value}`;
      },
    },
    { key: 'amount_paid', label: 'Amount', render: (value) => `PHP ${Number(value || 0).toFixed(2)}` },
    {
      key: 'payment_method_id',
      label: 'Method',
      render: (_, row) => row?.paymentMethod?.method_name || row?.payment_method?.method_name || `Method #${row?.payment_method_id || '-'}`,
    },
    {
      key: 'coverage_end',
      label: 'Coverage Status',
      render: (value) => {
        const isActive = value ? new Date(value) >= new Date() : false;
        return (
          <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
            isActive
              ? 'bg-green-500/20 text-green-300 border border-green-500/30'
              : 'bg-gray-600/20 text-gray-300 border border-gray-600/30'
          }`}>
            {isActive ? 'Active' : 'Expired'}
          </span>
        );
      },
    },
    { key: 'payment_date', label: 'Date', render: (value) => new Date(value).toLocaleDateString() },
    { key: 'coverage_start', label: 'Coverage Start', render: (value) => new Date(value).toLocaleDateString() },
    { key: 'coverage_end', label: 'Coverage End', render: (value) => new Date(value).toLocaleDateString() },
  ];

  return (
    <div className="space-y-8">
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Payments & Billing</h1>
          <Button onClick={openCreateModal} className="flex items-center gap-2">
            <Plus size={20} />
            Add Payment
          </Button>
        </div>
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <DataTable
          columns={columns}
          data={payments}
          title="All Payments"
          loading={loading}
          searchFields={['member_id', 'payment_method_id', 'payment_date']}
          onEdit={openEditModal}
          onDelete={(payment) => {
            setPaymentToDelete(payment);
            setIsConfirmOpen(true);
          }}
        />
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-dark-card border border-gold-600/20 rounded-lg shadow p-6">
            <h3 className="text-sm font-medium text-gray-400 mb-2">Total Revenue</h3>
            <p className="text-3xl font-bold text-gold-300">
              PHP {payments.reduce((sum, p) => sum + (parseFloat(p.amount_paid) || 0), 0).toFixed(2)}
            </p>
          </div>
          <div className="bg-dark-card border border-gold-600/20 rounded-lg shadow p-6">
            <h3 className="text-sm font-medium text-gray-400 mb-2">Active Coverages</h3>
            <p className="text-3xl font-bold text-green-300">
              {payments.filter((p) => p.coverage_end && new Date(p.coverage_end) >= new Date()).length}
            </p>
          </div>
          <div className="bg-dark-card border border-gold-600/20 rounded-lg shadow p-6">
            <h3 className="text-sm font-medium text-gray-400 mb-2">Total Transactions</h3>
            <p className="text-3xl font-bold text-blue-300">{payments.length}</p>
          </div>
        </div>
      </motion.div>

      <FormModal
        isOpen={isModalOpen}
        title={editingPayment ? 'Edit Payment' : 'Add Payment'}
        onClose={closeModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingPayment ? 'Update' : 'Create'}
      >
        <FormInput
          label="Member"
          type="select"
          value={formData.member_id}
          onChange={(e) => setFormData({ ...formData, member_id: e.target.value })}
          options={memberOptions}
          error={errors.member_id}
          required
        />
        <FormInput
          label="Amount Paid (PHP)"
          type="number"
          value={formData.amount_paid}
          onChange={(e) => setFormData({ ...formData, amount_paid: e.target.value })}
          error={errors.amount_paid}
          required
        />
        <FormInput
          label="Payment Date"
          type="date"
          value={formData.payment_date}
          onChange={(e) => setFormData({ ...formData, payment_date: e.target.value })}
          error={errors.payment_date}
          required
        />
        <FormInput
          label="Payment Method"
          type="select"
          value={formData.payment_method_id}
          onChange={(e) => setFormData({ ...formData, payment_method_id: e.target.value })}
          options={methodOptions}
          error={errors.payment_method_id}
          required
        />
        <FormInput
          label="Coverage Start"
          type="date"
          value={formData.coverage_start}
          onChange={(e) => setFormData({ ...formData, coverage_start: e.target.value })}
          error={errors.coverage_start}
          required
        />
        <FormInput
          label="Coverage End"
          type="date"
          value={formData.coverage_end}
          onChange={(e) => setFormData({ ...formData, coverage_end: e.target.value })}
          error={errors.coverage_end}
          required
        />
      </FormModal>

      <ConfirmDialog
        isOpen={isConfirmOpen}
        title="Delete Payment"
        message={`Are you sure you want to delete payment #${paymentToDelete?.id}?`}
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={() => {
          setIsConfirmOpen(false);
          setPaymentToDelete(null);
        }}
        loading={loading}
        isDangerous
      />
    </div>
  );
};

export default PaymentsManagement;
