import React, { useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';
import { motion } from 'framer-motion';

const TrainersManagement = () => {
  const [trainers, setTrainers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingTrainer, setEditingTrainer] = useState(null);
  const [trainerToDelete, setTrainerToDelete] = useState(null);
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    specialization: '',
    phone: '',
    hourly_rate: '',
    password: '',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchTrainers();
  }, []);

  const fetchTrainers = async () => {
    try {
      setLoading(true);
      const response = await api.trainersAPI.list();
      console.log('Trainers fetched:', response.data);
      setTrainers(response.data.data || response.data || []);
    } catch (err) {
      console.error('Failed to load trainers:', err);
      toast.error('Failed to load trainers');
      setTrainers([]);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (trainer = null) => {
    if (trainer) {
      setEditingTrainer(trainer);
      setFormData({
        first_name: trainer.first_name || '',
        last_name: trainer.last_name || '',
        email: trainer.email || '',
        specialization: trainer.specialization || '',
        phone: trainer.phone || '',
        hourly_rate: trainer.hourly_rate || '',
      });
    } else {
      setEditingTrainer(null);
      setFormData({
        first_name: '',
        last_name: '',
        email: '',
        specialization: '',
        phone: '',
        hourly_rate: '',
        password: '',
      });
    }
    setErrors({});
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingTrainer(null);
    setFormData({
      first_name: '',
      last_name: '',
      email: '',
      specialization: '',
      phone: '',
      hourly_rate: '',
      password: '',
    });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.first_name.trim()) newErrors.first_name = 'First name is required';
    if (!formData.last_name.trim()) newErrors.last_name = 'Last name is required';
    if (!formData.email.trim()) newErrors.email = 'Email is required';
    if (formData.email.trim() && !formData.email.includes('@')) newErrors.email = 'Valid email is required';
    if (!formData.specialization.trim()) newErrors.specialization = 'Specialization is required';
    if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
    if (!formData.hourly_rate || formData.hourly_rate <= 0) newErrors.hourly_rate = 'Valid hourly rate is required';
    
    // Password only required on create (not edit)
    if (!editingTrainer) {
      if (!formData.password) newErrors.password = 'Password is required';
      if (formData.password && formData.password.length < 8) newErrors.password = 'Password must be at least 8 characters';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      console.log('Submitting trainer data:', formData);
      if (editingTrainer) {
        console.log('Updating trainer:', editingTrainer.id);
        await api.trainersAPI.update(editingTrainer.id, formData);
        toast.success('Trainer updated successfully');
      } else {
        console.log('Creating new trainer');
        await api.trainersAPI.create(formData);
        toast.success('Trainer created successfully');
      }
      handleCloseModal();
      fetchTrainers();
    } catch (err) {
      console.error('Submit error:', err);
      console.error('Error response:', err.response?.data);
      console.error('Validation errors:', err.response?.data?.errors);
      
      let errorMessage = err.response?.data?.message || err.message || 'Operation failed';
      
      // If there are validation errors, show them
      if (err.response?.data?.errors) {
        const validationErrors = Object.entries(err.response.data.errors)
          .map(([field, msgs]) => `${field}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`)
          .join('\n');
        errorMessage = `Validation Error:\n${validationErrors}`;
        setErrors(err.response.data.errors);
      }
      
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = (trainer) => {
    setTrainerToDelete(trainer);
    setIsConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      setLoading(true);
      console.log('Deleting trainer:', trainerToDelete.id);
      const response = await api.trainersAPI.delete(trainerToDelete.id);
      console.log('Delete response:', response);
      toast.success('Trainer deleted successfully');
      setIsConfirmOpen(false);
      setTrainerToDelete(null);
      fetchTrainers();
    } catch (err) {
      console.error('Delete error:', err);
      const errorMessage = err.response?.data?.message || err.message || 'Failed to delete trainer';
      toast.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'specialization', label: 'Specialization' },
    { key: 'phone', label: 'Phone' },
    { key: 'hourly_rate', label: 'Hourly Rate', render: (value) => value ? `$${parseFloat(value).toFixed(2)}/hr` : '$0.00/hr' },
  ];

  return (
    <div className="space-y-8">
      {/* Header with Action Button */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Trainers Management</h1>
          <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
            <Plus size={20} />
            Add Trainer
          </Button>
        </div>
      </motion.div>

      {/* Data Table */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <DataTable
        columns={columns}
        data={trainers}
        title="All Trainers"
        loading={loading}
        searchFields={['first_name', 'last_name', 'specialization']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />
      </motion.div>

      <FormModal
        isOpen={isModalOpen}
        title={editingTrainer ? 'Edit Trainer' : 'Add New Trainer'}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingTrainer ? 'Update' : 'Create'}
      >
        <FormInput
          label="First Name"
          value={formData.first_name}
          onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
          error={errors.first_name}
          required
        />
        <FormInput
          label="Last Name"
          value={formData.last_name}
          onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
          error={errors.last_name}
          required
        />
        <FormInput
          label="Email"
          type="email"
          value={formData.email}
          onChange={(e) => setFormData({ ...formData, email: e.target.value })}
          error={errors.email}
          placeholder="e.g., trainer@gym.com"
          required
        />
        <FormInput
          label="Specialization"
          value={formData.specialization}
          onChange={(e) => setFormData({ ...formData, specialization: e.target.value })}
          error={errors.specialization}
          placeholder="e.g., Fitness, CrossFit, Yoga"
          required
        />
        <FormInput
          label="Phone"
          type="tel"
          value={formData.phone}
          onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
          error={errors.phone}
          required
        />
        <FormInput
          label="Hourly Rate ($)"
          type="number"
          value={formData.hourly_rate}
          onChange={(e) => setFormData({ ...formData, hourly_rate: e.target.value })}
          error={errors.hourly_rate}
          placeholder="e.g., 50"
          required
        />
        {!editingTrainer && (
          <FormInput
            label="Password"
            type="password"
            value={formData.password}
            onChange={(e) => setFormData({ ...formData, password: e.target.value })}
            error={errors.password}
            placeholder="Minimum 8 characters"
            required
          />
        )}
      </FormModal>

      <ConfirmDialog
        isOpen={isConfirmOpen}
        title="Delete Trainer"
        message={`Are you sure you want to delete ${trainerToDelete?.first_name} ${trainerToDelete?.last_name}? This action cannot be undone.`}
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={() => setIsConfirmOpen(false)}
        loading={loading}
        isDangerous
      />
    </div>
  );
};

export default TrainersManagement;
