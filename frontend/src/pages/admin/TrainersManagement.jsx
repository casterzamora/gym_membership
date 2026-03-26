import React, { useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';

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
    specialization: '',
    phone: '',
    hourly_rate: '',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchTrainers();
  }, []);

  const fetchTrainers = async () => {
    try {
      setLoading(true);
      const response = await api.trainersAPI.list();
      setTrainers(response.data.data || response.data || []);
    } catch (err) {
      toast.error('Failed to load trainers');
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
        specialization: trainer.specialization || '',
        phone: trainer.phone || '',
        hourly_rate: trainer.hourly_rate || '',
      });
    } else {
      setEditingTrainer(null);
      setFormData({
        first_name: '',
        last_name: '',
        specialization: '',
        phone: '',
        hourly_rate: '',
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
      specialization: '',
      phone: '',
      hourly_rate: '',
    });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.first_name.trim()) newErrors.first_name = 'First name is required';
    if (!formData.last_name.trim()) newErrors.last_name = 'Last name is required';
    if (!formData.specialization.trim()) newErrors.specialization = 'Specialization is required';
    if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
    if (!formData.hourly_rate || formData.hourly_rate <= 0) newErrors.hourly_rate = 'Valid hourly rate is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      if (editingTrainer) {
        await api.trainersAPI.update(editingTrainer.id, formData);
        toast.success('Trainer updated successfully');
      } else {
        await api.trainersAPI.create(formData);
        toast.success('Trainer created successfully');
      }
      handleCloseModal();
      fetchTrainers();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Operation failed');
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
      await api.trainersAPI.delete(trainerToDelete.id);
      toast.success('Trainer deleted successfully');
      setIsConfirmOpen(false);
      setTrainerToDelete(null);
      fetchTrainers();
    } catch (err) {
      toast.error('Failed to delete trainer');
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
    { key: 'hourly_rate', label: 'Hourly Rate', render: (value) => `$${value}` },
  ];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold text-gray-900">Trainers Management</h1>
        <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
          <Plus size={20} />
          Add Trainer
        </Button>
      </div>

      <DataTable
        columns={columns}
        data={trainers}
        title="All Trainers"
        loading={loading}
        searchFields={['first_name', 'last_name', 'specialization']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />

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
