import React, { useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';
import { motion } from 'framer-motion';

const PlansManagement = () => {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingPlan, setEditingPlan] = useState(null);
  const [planToDelete, setPlanToDelete] = useState(null);
  const [formData, setFormData] = useState({
    plan_name: '',
    price: '',
    duration_months: '',
    description: '',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchPlans();
  }, []);

  const fetchPlans = async () => {
    try {
      setLoading(true);
      const response = await api.plansAPI.list();
      setPlans(response.data.data || response.data || []);
    } catch (err) {
      toast.error('Failed to load plans');
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (plan = null) => {
    if (plan) {
      setEditingPlan(plan);
      setFormData({
        plan_name: plan.plan_name || '',
        price: plan.price || '',
        duration_months: plan.duration_months || '',
        description: plan.description || '',
      });
    } else {
      setEditingPlan(null);
      setFormData({
        plan_name: '',
        price: '',
        duration_months: '',
        description: '',
      });
    }
    setErrors({});
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingPlan(null);
    setFormData({ plan_name: '', price: '', duration_months: '', description: '' });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.plan_name.trim()) newErrors.plan_name = 'Plan name is required';
    if (!formData.price || formData.price <= 0) newErrors.price = 'Valid price is required';
    if (!formData.duration_months || formData.duration_months <= 0) newErrors.duration_months = 'Valid duration is required';
    if (!formData.description.trim()) newErrors.description = 'Description is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      if (editingPlan) {
        await api.plansAPI.update(editingPlan.id, formData);
        toast.success('Plan updated successfully');
      } else {
        await api.plansAPI.create(formData);
        toast.success('Plan created successfully');
      }
      handleCloseModal();
      fetchPlans();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = (plan) => {
    setPlanToDelete(plan);
    setIsConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      setLoading(true);
      await api.plansAPI.delete(planToDelete.id);
      toast.success('Plan deleted successfully');
      setIsConfirmOpen(false);
      setPlanToDelete(null);
      fetchPlans();
    } catch (err) {
      toast.error('Failed to delete plan');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'plan_name', label: 'Plan Name' },
    { key: 'price', label: 'Price', render: (value) => `PHP ${value}` },
    { key: 'duration_months', label: 'Duration (Months)' },
    { key: 'description', label: 'Description' },
  ];

  return (
    <div className="space-y-8">
      {/* Header with Action Button */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Membership Plans</h1>
          <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
            <Plus size={20} />
            Add Plan
          </Button>
        </div>
      </motion.div>

      {/* Data Table */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <DataTable
        columns={columns}
        data={plans}
        title="All Membership Plans"
        loading={loading}
        searchFields={['plan_name']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />
      </motion.div>

      <FormModal
        isOpen={isModalOpen}
        title={editingPlan ? 'Edit Plan' : 'Add New Plan'}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingPlan ? 'Update' : 'Create'}
      >
        <FormInput
          label="Plan Name"
          value={formData.plan_name}
          onChange={(e) => setFormData({ ...formData, plan_name: e.target.value })}
          error={errors.plan_name}
          placeholder="e.g., Basic Monthly, Premium Annual"
          required
        />
        <FormInput
          label="Price (PHP)"
          type="number"
          value={formData.price}
          onChange={(e) => setFormData({ ...formData, price: e.target.value })}
          error={errors.price}
          placeholder="e.g., 49.99"
          step="0.01"
          required
        />
        <FormInput
          label="Duration (Months)"
          type="number"
          value={formData.duration_months}
          onChange={(e) => setFormData({ ...formData, duration_months: e.target.value })}
          error={errors.duration_months}
          placeholder="e.g., 1 for monthly, 12 for annual"
          required
        />
        <FormInput
          label="Description"
          type="textarea"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          error={errors.description}
          placeholder="Plan benefits and features"
          required
        />
      </FormModal>

      <ConfirmDialog
        isOpen={isConfirmOpen}
        title="Delete Plan"
        message={`Are you sure you want to delete "${planToDelete?.plan_name}"? This action cannot be undone.`}
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={() => setIsConfirmOpen(false)}
        loading={loading}
        isDangerous
      />
    </div>
  );
};

export default PlansManagement;
