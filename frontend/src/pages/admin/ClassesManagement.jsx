import React, { useMemo, useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import EquipmentTrackingModal from '@/components/admin/EquipmentTrackingModal';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus, Package, CheckCircle2 } from 'lucide-react';
import { motion } from 'framer-motion';

const ClassesManagement = () => {
  const [classes, setClasses] = useState([]);
  const [trainers, setTrainers] = useState([]);
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [plansLoading, setPlansLoading] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [isEquipmentModalOpen, setIsEquipmentModalOpen] = useState(false);
  const [selectedClassForEquipment, setSelectedClassForEquipment] = useState(null);
  const [editingClass, setEditingClass] = useState(null);
  const [classToDelete, setClassToDelete] = useState(null);
  const [formData, setFormData] = useState({
    class_name: '',
    description: '',
    trainer_id: '',
    max_participants: '',
    is_special: false,
    membership_plan_ids: [],
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchClasses();
    fetchTrainers();
    fetchPlans();
  }, []);

  useEffect(() => {
    if (isModalOpen && !editingClass && plans.length > 0 && formData.membership_plan_ids.length === 0) {
      setFormData((current) => ({
        ...current,
        membership_plan_ids: plans.map((plan) => String(plan.id)),
      }));
    }
  }, [isModalOpen, editingClass, plans, formData.membership_plan_ids.length]);

  const fetchPlans = async () => {
    try {
      setPlansLoading(true);
      const response = await api.plansAPI.list();
      setPlans(response.data.data || response.data || []);
    } catch (err) {
      console.error('Failed to load membership plans:', err);
      toast.error('Failed to load membership plans');
      setPlans([]);
    } finally {
      setPlansLoading(false);
    }
  };

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
      console.error('Failed to load trainers:', err);
    }
  };

  const handleOpenModal = (fitnessClass = null) => {
    console.log('Opening modal - fitnessClass:', fitnessClass);
    
    if (fitnessClass) {
      console.log('Edit mode - Class data:', fitnessClass);
      setEditingClass(fitnessClass);
      const trainerIdValue = fitnessClass.trainer_id ? String(fitnessClass.trainer_id) : '';
      const selectedPlanIds = Array.isArray(fitnessClass.membership_plan_ids)
        ? fitnessClass.membership_plan_ids.map((id) => String(id))
        : Array.isArray(fitnessClass.membership_plans)
          ? fitnessClass.membership_plans.map((plan) => String(plan.id))
          : [];
      console.log('Setting trainer_id to:', trainerIdValue, 'Type:', typeof trainerIdValue);
      setFormData({
        class_name: fitnessClass.class_name || '',
        description: fitnessClass.description || '',
        trainer_id: trainerIdValue,
        max_participants: fitnessClass.max_participants ? String(fitnessClass.max_participants) : '',
        is_special: Boolean(fitnessClass.is_special),
        membership_plan_ids: selectedPlanIds,
      });
      console.log('Form data set for editing:', {
        class_name: fitnessClass.class_name || '',
        description: fitnessClass.description || '',
        trainer_id: trainerIdValue,
        max_participants: fitnessClass.max_participants ? String(fitnessClass.max_participants) : '',
      });
    } else {
      console.log('Create mode - Fresh form');
      setEditingClass(null);
      const defaultPlanIds = plans.map((plan) => String(plan.id));
      setFormData({
        class_name: '',
        description: '',
        trainer_id: '',
        max_participants: '',
        is_special: false,
        membership_plan_ids: defaultPlanIds,
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
      is_special: false,
      membership_plan_ids: [],
    });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    
    // Required fields
    if (!formData.class_name || !formData.class_name.trim()) {
      newErrors.class_name = 'Class name is required';
    }
    if (!formData.description || !formData.description.trim()) {
      newErrors.description = 'Description is required';
    }
    if (!formData.trainer_id || formData.trainer_id === '') {
      newErrors.trainer_id = 'Trainer assignment is required';
    }
    if (!formData.max_participants || parseInt(formData.max_participants, 10) <= 0) {
      newErrors.max_participants = 'Valid max participants is required (must be > 0)';
    }
    if (!Array.isArray(formData.membership_plan_ids) || formData.membership_plan_ids.length === 0) {
      newErrors.membership_plan_ids = 'Select at least one membership plan';
    }
    if (formData.is_special && plans.find((plan) => String(plan.id) === String(formData.membership_plan_ids?.[0]))?.plan_name !== 'Gold') {
      newErrors.membership_plan_ids = 'Special classes can only be assigned to Gold';
    }
    
    console.log('Validation result - Has errors?', Object.keys(newErrors).length > 0, 'Errors:', newErrors);
    
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
        is_special: Boolean(formData.is_special),
        membership_plan_ids: formData.is_special
          ? [Number(plans.find((plan) => plan.plan_name === 'Gold')?.id)]
          : formData.membership_plan_ids.map((id) => Number(id)),
      };
      console.log('Submitting class data:', dataToSubmit);
      console.log('Trainers available:', trainers);
      console.log('Selected trainer_id:', dataToSubmit.trainer_id, 'Trainer exists?', trainers.some(t => t.id === dataToSubmit.trainer_id));
      console.log('Selected membership plans:', dataToSubmit.membership_plan_ids);
      
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
      
      let errorMessage = err.response?.data?.message || err.message || 'Operation failed';
      
      // Log detailed validation errors
      if (err.response?.data?.errors) {
        console.error('Validation errors:', err.response.data.errors);
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

  const handleDelete = (fitnessClass) => {
    console.log('Delete button clicked for class:', fitnessClass);
    setClassToDelete(fitnessClass);
    setIsConfirmOpen(true);
  };

  const handleOpenEquipmentModal = (fitnessClass) => {
    setSelectedClassForEquipment(fitnessClass);
    setIsEquipmentModalOpen(true);
  };

  const confirmDelete = async () => {
    try {
      setLoading(true);
      console.log('Deleting class:', classToDelete.id);
      console.log('Class details:', classToDelete);
      
      const response = await api.classesAPI.delete(classToDelete.id);
      console.log('Delete response:', response);
      
      if (response?.status === 200 || response?.data?.success !== false) {
        toast.success(`Class "${classToDelete.class_name}" deleted successfully`);
        setIsConfirmOpen(false);
        setClassToDelete(null);
        await fetchClasses();
      } else {
        const errorMsg = response?.data?.message || 'Delete may have failed';
        console.error('Delete error:', errorMsg);
        toast.error(errorMsg);
      }
    } catch (err) {
      console.error('Delete error:', err);
      console.error('Error response data:', err.response?.data);
      console.error('Error status:', err.response?.status);
      
      const errorMessage = err.response?.data?.message || err.message || 'Failed to delete class';
      
      if (err.response?.data?.errors) {
        console.error('Validation errors:', err.response.data.errors);
      }
      
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
    {
      key: 'membership_plans',
      label: 'Allowed Memberships',
      render: (_, row) => (row.membership_plans?.length
        ? row.membership_plans.map((plan) => plan.plan_name || plan.name).join(', ')
        : 'All plans'),
    },
    {
      key: 'is_special',
      label: 'Special',
      render: (value) => value ? <span className="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-300"><CheckCircle2 size={12} />Gold only</span> : <span className="text-xs text-gray-400">No</span>,
    },
    { key: 'max_participants', label: 'Max Participants' },
  ];

  const goldPlan = useMemo(() => plans.find((plan) => plan.plan_name === 'Gold' || plan.name === 'Gold'), [plans]);

  const togglePlan = (planId) => {
    if (formData.is_special) return;

    setFormData((current) => {
      const planIdString = String(planId);
      const exists = current.membership_plan_ids.includes(planIdString);
      const membership_plan_ids = exists
        ? current.membership_plan_ids.filter((id) => id !== planIdString)
        : [...current.membership_plan_ids, planIdString];

      return { ...current, membership_plan_ids };
    });
  };

  const handleSpecialToggle = (checked) => {
    setFormData((current) => ({
      ...current,
      is_special: checked,
      membership_plan_ids: checked && goldPlan ? [String(goldPlan.id)] : current.membership_plan_ids.length > 0 ? current.membership_plan_ids : plans.map((plan) => String(plan.id)),
    }));
  };

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

      {/* Equipment Management Quick Access */}
      {classes.length > 0 && (
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <div className="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div className="bg-gray-700 px-6 py-4 border-b border-gray-600">
              <h2 className="text-xl font-bold text-white flex items-center gap-2">
                <Package size={20} />
                Equipment Management
              </h2>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {classes.map((fitnessClass) => (
                  <div
                    key={fitnessClass.id}
                    className="bg-gray-700 rounded-lg p-4 border border-gray-600 hover:border-gray-500 transition-colors"
                  >
                    <h3 className="font-semibold text-white mb-2">{fitnessClass.class_name}</h3>
                    <p className="text-sm text-gray-400 mb-4 line-clamp-2">{fitnessClass.description}</p>
                    <button
                      onClick={() => handleOpenEquipmentModal(fitnessClass)}
                      className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors flex items-center justify-center gap-2"
                    >
                      <Package size={18} />
                      Manage Equipment
                    </button>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </motion.div>
      )}

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

        <div className="rounded-lg border border-gray-700 bg-dark-secondary p-4">
          <div className="flex items-center justify-between gap-4 mb-4">
            <div>
              <h3 className="text-sm font-semibold text-white">Allowed Membership Plans</h3>
              <p className="text-xs text-gray-400">Choose which memberships can enroll in this class.</p>
            </div>
            <label className="flex items-center gap-2 text-sm text-gray-300">
              <input
                type="checkbox"
                checked={Boolean(formData.is_special)}
                onChange={(e) => handleSpecialToggle(e.target.checked)}
                className="h-4 w-4 rounded border-gray-600 text-gold-500 focus:ring-gold-500"
              />
              Gold-only special class
            </label>
          </div>

          {plansLoading ? (
            <div className="text-sm text-gray-400">Loading membership plans...</div>
          ) : (
            <div className="grid gap-3 sm:grid-cols-3">
              {plans.map((plan) => {
                const checked = formData.is_special
                  ? String(plan.id) === String(goldPlan?.id)
                  : formData.membership_plan_ids.includes(String(plan.id));

                return (
                  <label
                    key={plan.id}
                    className={`flex items-center gap-3 rounded-lg border px-3 py-2 text-sm transition ${checked ? 'border-gold-500/60 bg-gold-500/10 text-white' : 'border-gray-700 text-gray-300'}`}
                  >
                    <input
                      type="checkbox"
                      checked={checked}
                      disabled={formData.is_special}
                      onChange={() => togglePlan(plan.id)}
                      className="h-4 w-4 rounded border-gray-600 text-gold-500 focus:ring-gold-500 disabled:opacity-60"
                    />
                    <span>{plan.plan_name || plan.name}</span>
                  </label>
                );
              })}
            </div>
          )}
          {errors.membership_plan_ids && <p className="mt-2 text-sm text-red-400">{errors.membership_plan_ids}</p>}
          {formData.is_special && <p className="mt-2 text-xs text-yellow-300">Special classes are restricted to Gold members only.</p>}
        </div>
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

      <EquipmentTrackingModal
        isOpen={isEquipmentModalOpen}
        onClose={() => setIsEquipmentModalOpen(false)}
        classId={selectedClassForEquipment?.id}
        className={selectedClassForEquipment?.class_name}
        onUpdate={fetchClasses}
      />
    </div>
  );
};

export default ClassesManagement;
