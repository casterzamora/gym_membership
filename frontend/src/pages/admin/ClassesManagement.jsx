import React, { useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';

const ClassesManagement = () => {
  const [classes, setClasses] = useState([]);
  const [trainers, setTrainers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingClass, setEditingClass] = useState(null);
  const [classToDelete, setClassToDelete] = useState(null);
  const [formData, setFormData] = useState({
    class_name: '',
    description: '',
    trainer_id: '',
    max_participants: '',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchClasses();
    fetchTrainers();
  }, []);

  const fetchClasses = async () => {
    try {
      setLoading(true);
      const response = await api.classesAPI.list();
      setClasses(response.data.data || response.data || []);
    } catch (err) {
      toast.error('Failed to load classes');
    } finally {
      setLoading(false);
    }
  };

  const fetchTrainers = async () => {
    try {
      const response = await api.trainersAPI.list();
      setTrainers(response.data.data || response.data || []);
    } catch (err) {
      console.error('Failed to load trainers');
    }
  };

  const handleOpenModal = (fitnessClass = null) => {
    if (fitnessClass) {
      setEditingClass(fitnessClass);
      setFormData({
        class_name: fitnessClass.class_name || '',
        description: fitnessClass.description || '',
        trainer_id: fitnessClass.trainer_id || '',
        max_participants: fitnessClass.max_participants || '',
      });
    } else {
      setEditingClass(null);
      setFormData({
        class_name: '',
        description: '',
        trainer_id: '',
        max_participants: '',
      });
    }
    setErrors({});
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingClass(null);
    setFormData({
      class_name: '',
      description: '',
      trainer_id: '',
      max_participants: '',
    });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.class_name.trim()) newErrors.class_name = 'Class name is required';
    if (!formData.description.trim()) newErrors.description = 'Description is required';
    if (!formData.trainer_id) newErrors.trainer_id = 'Trainer assignment is required';
    if (!formData.max_participants || formData.max_participants <= 0) newErrors.max_participants = 'Valid max participants is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      if (editingClass) {
        await api.classesAPI.update(editingClass.id, formData);
        toast.success('Class updated successfully');
      } else {
        await api.classesAPI.create(formData);
        toast.success('Class created successfully');
      }
      handleCloseModal();
      fetchClasses();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = (fitnessClass) => {
    setClassToDelete(fitnessClass);
    setIsConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      setLoading(true);
      await api.classesAPI.delete(classToDelete.id);
      toast.success('Class deleted successfully');
      setIsConfirmOpen(false);
      setClassToDelete(null);
      fetchClasses();
    } catch (err) {
      toast.error('Failed to delete class');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'class_name', label: 'Class Name' },
    { key: 'description', label: 'Description' },
    { 
      key: 'trainer_id', 
      label: 'Trainer',
      render: (value) => trainers.find(t => t.id === value)?.first_name + ' ' + (trainers.find(t => t.id === value)?.last_name || '') || 'N/A'
    },
    { key: 'max_participants', label: 'Max Participants' },
  ];

  const trainerOptions = trainers.map(t => ({ 
    value: t.id, 
    label: `${t.first_name} ${t.last_name}` 
  }));

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold text-gray-900">Classes Management</h1>
        <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
          <Plus size={20} />
          Add Class
        </Button>
      </div>

      <DataTable
        columns={columns}
        data={classes}
        title="All Classes"
        loading={loading}
        searchFields={['class_name', 'description']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />

      <FormModal
        isOpen={isModalOpen}
        title={editingClass ? 'Edit Class' : 'Add New Class'}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingClass ? 'Update' : 'Create'}
      >
        <FormInput
          label="Class Name"
          value={formData.class_name}
          onChange={(e) => setFormData({ ...formData, class_name: e.target.value })}
          error={errors.class_name}
          placeholder="e.g., Yoga, HIIT, Pilates"
          required
        />
        <FormInput
          label="Description"
          type="textarea"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          error={errors.description}
          placeholder="Class description"
          required
        />
        <FormInput
          label="Assign Trainer"
          type="select"
          value={formData.trainer_id}
          onChange={(e) => setFormData({ ...formData, trainer_id: e.target.value })}
          error={errors.trainer_id}
          options={trainerOptions}
          required
        />
        <FormInput
          label="Max Participants"
          type="number"
          value={formData.max_participants}
          onChange={(e) => setFormData({ ...formData, max_participants: e.target.value })}
          error={errors.max_participants}
          placeholder="e.g., 20"
          required
        />
      </FormModal>

      <ConfirmDialog
        isOpen={isConfirmOpen}
        title="Delete Class"
        message={`Are you sure you want to delete "${classToDelete?.class_name}"? This action cannot be undone.`}
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={() => setIsConfirmOpen(false)}
        loading={loading}
        isDangerous
      />
    </div>
  );
};

export default ClassesManagement;
