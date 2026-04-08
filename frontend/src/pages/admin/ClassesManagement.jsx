import React, { useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';
import { motion } from 'framer-motion';

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
      console.log('Classes fetched:', response.data);
      setClasses(response.data.data || response.data || []);
    } catch (err) {
      console.error('Failed to load classes:', err);
      toast.error('Failed to load classes');
      setClasses([]);
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
      console.log('Opening modal with class data:', fitnessClass);
      setEditingClass(fitnessClass);
      const trainerIdValue = fitnessClass.trainer_id ? String(fitnessClass.trainer_id) : '';
      console.log('Setting trainer_id to:', trainerIdValue, 'Type:', typeof trainerIdValue);
      setFormData({
        class_name: fitnessClass.class_name || '',
        description: fitnessClass.description || '',
        trainer_id: trainerIdValue,
        max_participants: fitnessClass.max_participants ? String(fitnessClass.max_participants) : '',
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
    console.log('Form submitted. Current formData:', formData);
    if (!validateForm()) {
      console.log('Form validation failed. Errors:', errors);
      return;
    }

    try {
      setLoading(true);
      const dataToSubmit = {
        class_name: formData.class_name,
        description: formData.description,
        trainer_id: parseInt(formData.trainer_id, 10),
        max_participants: parseInt(formData.max_participants, 10),
      };
      console.log('Submitting class data:', dataToSubmit);
      console.log('Trainers available:', trainers);
      console.log('Selected trainer_id:', dataToSubmit.trainer_id, 'Trainer exists?', trainers.some(t => t.id === dataToSubmit.trainer_id));
      
      if (editingClass) {
        console.log('Updating class ID:', editingClass.id);
        console.log('Current form values before update:', formData);
        const response = await api.classesAPI.update(editingClass.id, dataToSubmit);
        console.log('Update response:', response);
        toast.success('Class updated successfully');
      } else {
        console.log('Creating new class');
        const response = await api.classesAPI.create(dataToSubmit);
        console.log('Create response:', response);
        toast.success('Class created successfully');
      }
      handleCloseModal();
      await fetchClasses();
    } catch (err) {
      console.error('Submit error:', err);
      console.error('Error response data:', err.response?.data);
      console.error('Error status:', err.response?.status);
      if (err.response?.data?.errors) {
        console.error('Validation errors:', err.response.data.errors);
        setErrors(err.response.data.errors);
      }
      const errorMessage = err.response?.data?.message || err.message || 'Operation failed';
      toast.error(errorMessage);
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
      console.log('Deleting class:', classToDelete.id);
      await api.classesAPI.delete(classToDelete.id);
      toast.success('Class deleted successfully');
      setIsConfirmOpen(false);
      setClassToDelete(null);
      fetchClasses();
    } catch (err) {
      console.error('Delete error:', err);
      const errorMessage = err.response?.data?.message || err.message || 'Failed to delete class';
      toast.error(errorMessage);
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
    <div className="space-y-8">
      {/* Header with Action Button */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Classes Management</h1>
          <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
            <Plus size={20} />
            Add Class
          </Button>
        </div>
      </motion.div>

      {/* Data Table */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <DataTable
        columns={columns}
        data={classes}
        title="All Classes"
        loading={loading}
        searchFields={['class_name', 'description']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />
      </motion.div>

      <FormModal
        isOpen={isModalOpen}
        title={editingClass ? 'Edit Class' : 'Add New Class'}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingClass ? 'Update' : 'Create'}
      >
        {editingClass && (
          <div className="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-sm">
            <p className="text-blue-900">Editing ID: {editingClass.id}</p>
            <p className="text-blue-700 text-xs mt-1">Debug - Current trainer_id: {formData.trainer_id}</p>
          </div>
        )}
        <FormInput
          label="Class Name"
          value={formData.class_name}
          onChange={(e) => {
            console.log('Class name changed from:', formData.class_name, 'to:', e.target.value);
            setFormData({ ...formData, class_name: e.target.value });
          }}
          error={errors.class_name}
          placeholder="e.g., Yoga, HIIT, Pilates"
          required
        />
        <FormInput
          label="Description"
          type="textarea"
          value={formData.description}
          onChange={(e) => {
            console.log('Description changed from:', formData.description, 'to:', e.target.value);
            setFormData({ ...formData, description: e.target.value });
          }}
          error={errors.description}
          placeholder="Class description"
          required
        />
        <FormInput
          label="Assign Trainer"
          type="select"
          value={formData.trainer_id}
          onChange={(e) => {
            console.log('Trainer changed from:', formData.trainer_id, 'to:', e.target.value);
            setFormData({ ...formData, trainer_id: e.target.value });
          }}
          error={errors.trainer_id}
          options={trainerOptions}
          required
        />
        <FormInput
          label="Max Participants"
          type="number"
          value={formData.max_participants}
          onChange={(e) => {
            console.log('Max participants changed from:', formData.max_participants, 'to:', e.target.value);
            setFormData({ ...formData, max_participants: e.target.value });
          }}
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
