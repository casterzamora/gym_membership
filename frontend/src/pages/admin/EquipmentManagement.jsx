import React, { useState, useEffect } from 'react';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';
import { motion } from 'framer-motion';

const EquipmentManagement = () => {
  const [equipment, setEquipment] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [itemToDelete, setItemToDelete] = useState(null);
  const [formData, setFormData] = useState({
    equipment_name: '',
    status: 'Available',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    fetchEquipment();
  }, []);

  const fetchEquipment = async () => {
    try {
      setLoading(true);
      const response = await api.equipmentAPI.list();
      console.log('Equipment API response:', response);
      console.log('Response data:', response.data);
      const equipmentData = response.data?.data || response.data || [];
      console.log('Parsed equipment:', equipmentData);
      setEquipment(equipmentData);
    } catch (err) {
      console.error('Failed to load equipment:', err);
      toast.error('Failed to load equipment');
      setEquipment([]);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (item = null) => {
    if (item) {
      setEditingItem(item);
      setFormData({
        equipment_name: item.equipment_name || '',
        status: item.status || 'Available',
      });
    } else {
      setEditingItem(null);
      setFormData({
        equipment_name: '',
        status: 'Available',
      });
    }
    setErrors({});
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingItem(null);
    setFormData({ equipment_name: '', status: 'Available' });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.equipment_name.trim()) newErrors.equipment_name = 'Equipment name is required';
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      console.log('Submitting equipment data:', formData);
      if (editingItem) {
        console.log('Updating equipment:', editingItem.id);
        await api.equipmentAPI.update(editingItem.id, formData);
        toast.success('Equipment updated successfully');
      } else {
        console.log('Creating new equipment');
        const response = await api.equipmentAPI.create(formData);
        console.log('Equipment create response:', response);
        toast.success('Equipment added successfully');
      }
      handleCloseModal();
      await fetchEquipment();
    } catch (err) {
      console.error('Submit error:', err);
      console.error('Error response:', err.response?.data);
      const errorMsg = err.response?.data?.message || err.message || 'Operation failed';
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = (item) => {
    setItemToDelete(item);
    setIsConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      setLoading(true);
      await api.equipmentAPI?.delete?.(itemToDelete.id);
      toast.success('Equipment deleted successfully');
      setIsConfirmOpen(false);
      setItemToDelete(null);
      fetchEquipment();
    } catch (err) {
      toast.error('Failed to delete equipment');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'equipment_name', label: 'Equipment Name' },
    { 
      key: 'status', 
      label: 'Status',
      render: (value) => (
        <span className={`px-3 py-1 rounded-full text-sm font-medium ${
          value === 'Available' ? 'bg-green-100 text-green-800' :
          value === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' :
          'bg-red-100 text-red-800'
        }`}>
          {value}
        </span>
      )
    },
  ];

  return (
    <div className="space-y-8">
      {/* Header with Action Button */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold text-white">Equipment Management</h1>
          <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
            <Plus size={20} />
            Add Equipment
          </Button>
        </div>
      </motion.div>

      {/* Data Table */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <DataTable
        columns={columns}
        data={equipment}
        title="Gym Equipment"
        loading={loading}
        searchFields={['equipment_name']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />
      </motion.div>

      <FormModal
        isOpen={isModalOpen}
        title={editingItem ? 'Edit Equipment' : 'Add New Equipment'}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingItem ? 'Update' : 'Add'}
      >
        <FormInput
          label="Equipment Name"
          value={formData.equipment_name}
          onChange={(e) => setFormData({ ...formData, equipment_name: e.target.value })}
          error={errors.equipment_name}
          placeholder="e.g., Dumbbells, Treadmill"
          required
        />
        <FormInput
          label="Status"
          type="select"
          value={formData.status}
          onChange={(e) => setFormData({ ...formData, status: e.target.value })}
          options={[
            { value: 'Available', label: 'Available' },
            { value: 'Maintenance', label: 'Maintenance' },
            { value: 'Out of Service', label: 'Out of Service' },
          ]}
        />
      </FormModal>

      <ConfirmDialog
        isOpen={isConfirmOpen}
        title="Delete Equipment"
        message={`Are you sure you want to delete "${itemToDelete?.equipment_name}"? This action cannot be undone.`}
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={() => setIsConfirmOpen(false)}
        loading={loading}
        isDangerous
      />
    </div>
  );
};

export default EquipmentManagement;
