import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Plus } from 'lucide-react';

const MembersManagement = () => {
  const { user } = useContext(AuthContext);
  const [members, setMembers] = useState([]);
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingMember, setEditingMember] = useState(null);
  const [memberToDelete, setMemberToDelete] = useState(null);
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    phone: '',
    date_of_birth: '',
    plan_id: '',
  });
  const [errors, setErrors] = useState({});

  // Fetch data
  useEffect(() => {
    fetchMembers();
    fetchPlans();
  }, []);

  const fetchMembers = async () => {
    try {
      setLoading(true);
      const response = await api.membersAPI.list();
      setMembers(response.data.data || response.data || []);
    } catch (err) {
      toast.error('Failed to load members');
    } finally {
      setLoading(false);
    }
  };

  const fetchPlans = async () => {
    try {
      const response = await api.plansAPI.list();
      setPlans(response.data.data || response.data || []);
    } catch (err) {
      console.error('Failed to load plans');
    }
  };

  // Handle add/edit modal
  const handleOpenModal = (member = null) => {
    if (member) {
      setEditingMember(member);
      setFormData({
        first_name: member.first_name || '',
        last_name: member.last_name || '',
        phone: member.phone || '',
        date_of_birth: member.date_of_birth || '',
        plan_id: member.plan_id || '',
      });
    } else {
      setEditingMember(null);
      setFormData({
        first_name: '',
        last_name: '',
        phone: '',
        date_of_birth: '',
        plan_id: '',
      });
    }
    setErrors({});
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingMember(null);
    setFormData({
      first_name: '',
      last_name: '',
      phone: '',
      date_of_birth: '',
      plan_id: '',
    });
    setErrors({});
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.first_name.trim()) newErrors.first_name = 'First name is required';
    if (!formData.last_name.trim()) newErrors.last_name = 'Last name is required';
    if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
    if (!formData.date_of_birth) newErrors.date_of_birth = 'Date of birth is required';
    if (!formData.plan_id) newErrors.plan_id = 'Membership plan is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      if (editingMember) {
        await api.membersAPI.update(editingMember.id, formData);
        toast.success('Member updated successfully');
      } else {
        await api.membersAPI.create(formData);
        toast.success('Member created successfully');
      }
      handleCloseModal();
      fetchMembers();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = (member) => {
    setMemberToDelete(member);
    setIsConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      setLoading(true);
      await api.membersAPI.delete(memberToDelete.id);
      toast.success('Member deleted successfully');
      setIsConfirmOpen(false);
      setMemberToDelete(null);
      fetchMembers();
    } catch (err) {
      toast.error('Failed to delete member');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'phone', label: 'Phone' },
    { 
      key: 'plan_id', 
      label: 'Plan',
      render: (value) => plans.find(p => p.id === value)?.plan_name || 'N/A'
    },
    { 
      key: 'date_of_birth', 
      label: 'DOB',
      render: (value) => new Date(value).toLocaleDateString()
    },
  ];

  const planOptions = plans.map(p => ({ value: p.id, label: p.plan_name }));

  return (
    <div className="space-y-6">
      {/* Header with Action Button */}
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold text-gray-900">Members Management</h1>
        <Button onClick={() => handleOpenModal()} className="flex items-center gap-2">
          <Plus size={20} />
          Add Member
        </Button>
      </div>

      {/* Data Table */}
      <DataTable
        columns={columns}
        data={members}
        title="All Members"
        loading={loading}
        searchFields={['first_name', 'last_name', 'phone']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />

      {/* Add/Edit Modal */}
      <FormModal
        isOpen={isModalOpen}
        title={editingMember ? 'Edit Member' : 'Add New Member'}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={loading}
        submitLabel={editingMember ? 'Update' : 'Create'}
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
          label="Phone"
          type="tel"
          value={formData.phone}
          onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
          error={errors.phone}
          required
        />
        <FormInput
          label="Date of Birth"
          type="date"
          value={formData.date_of_birth}
          onChange={(e) => setFormData({ ...formData, date_of_birth: e.target.value })}
          error={errors.date_of_birth}
          required
        />
        <FormInput
          label="Membership Plan"
          type="select"
          value={formData.plan_id}
          onChange={(e) => setFormData({ ...formData, plan_id: e.target.value })}
          error={errors.plan_id}
          options={planOptions}
          required
        />
      </FormModal>

      {/* Delete Confirmation */}
      <ConfirmDialog
        isOpen={isConfirmOpen}
        title="Delete Member"
        message={`Are you sure you want to delete ${memberToDelete?.first_name} ${memberToDelete?.last_name}? This action cannot be undone.`}
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={() => setIsConfirmOpen(false)}
        loading={loading}
        isDangerous
      />
    </div>
  );
};

export default MembersManagement;
