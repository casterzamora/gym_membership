import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';

import { motion } from 'framer-motion';

const MembersManagement = () => {
  const { user } = useContext(AuthContext);
  const [members, setMembers] = useState([]);
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingMember, setEditingMember] = useState(null);
  const [memberToDelete, setMemberToDelete] = useState(null);
  const [upgradeMember, setUpgradeMember] = useState(null);
  const [upgradePlanId, setUpgradePlanId] = useState('');
  const [isUpgradeModalOpen, setIsUpgradeModalOpen] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    plan_id: '',
    fitness_goal: '',
    health_notes: '',
    membership_status: '',
    membership_start: '',
    membership_end: '',
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
  const handleOpenModal = (member) => {
    if (member) {
      setEditingMember(member);
      setFormData({
        first_name: member.first_name || '',
        last_name: member.last_name || '',
        email: member.email || '',
        phone: member.phone || '',
        date_of_birth: member.date_of_birth || '',
        plan_id: member.plan_id || '',
        fitness_goal: member.fitness_goal || '',
        health_notes: member.health_notes || '',
        membership_status: member.membership_status || 'active',
        membership_start: member.membership_start || '',
        membership_end: member.membership_end || '',
      });
      setErrors({});
      setIsModalOpen(true);
    }
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingMember(null);
    setFormData({
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      date_of_birth: '',
      plan_id: '',
      fitness_goal: '',
      health_notes: '',
      membership_status: '',
      membership_start: '',
      membership_end: '',
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
    if (!formData.membership_status) newErrors.membership_status = 'Membership status is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      setLoading(true);
      if (editingMember) {
        const updateData = {
          first_name: formData.first_name,
          last_name: formData.last_name,
          phone: formData.phone,
          date_of_birth: formData.date_of_birth,
          plan_id: formData.plan_id,
          fitness_goal: formData.fitness_goal || null,
          health_notes: formData.health_notes || null,
          membership_status: formData.membership_status,
          membership_start: formData.membership_start || null,
          membership_end: formData.membership_end || null,
        };
        await api.membersAPI.update(editingMember.id, updateData);
        toast.success('Member updated successfully');
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

  const handleRenewMember = async (member) => {
    try {
      setActionLoading(true);
      await api.membersAPI.renew(member.id);
      toast.success(`Membership renewed for ${member.first_name} ${member.last_name}`);
      fetchMembers();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to renew membership');
    } finally {
      setActionLoading(false);
    }
  };

  const handleOpenUpgradeModal = (member) => {
    setUpgradeMember(member);
    setUpgradePlanId('');
    setIsUpgradeModalOpen(true);
  };

  const handleCloseUpgradeModal = () => {
    setUpgradeMember(null);
    setUpgradePlanId('');
    setIsUpgradeModalOpen(false);
  };

  const handleUpgradeMember = async (e) => {
    e.preventDefault();

    if (!upgradeMember || !upgradePlanId) {
      toast.error('Please select a new membership plan');
      return;
    }

    if (String(upgradeMember.plan_id) === String(upgradePlanId)) {
      toast.error('Selected plan is the same as current plan');
      return;
    }

    try {
      setActionLoading(true);
      await api.membersAPI.upgrade(upgradeMember.id, { new_plan_id: Number(upgradePlanId) });
      toast.success(`Membership upgraded for ${upgradeMember.first_name} ${upgradeMember.last_name}`);
      handleCloseUpgradeModal();
      fetchMembers();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to upgrade membership');
    } finally {
      setActionLoading(false);
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
    {
      key: 'membership_actions',
      label: 'Membership',
      render: (_, row) => (
        <div className="flex gap-2">
          <Button
            size="sm"
            variant="secondary"
            onClick={() => handleRenewMember(row)}
            disabled={actionLoading}
          >
            Renew
          </Button>
          <Button
            size="sm"
            onClick={() => handleOpenUpgradeModal(row)}
            disabled={actionLoading}
          >
            Upgrade
          </Button>
        </div>
      )
    },
  ];

  const planOptions = plans.map(p => ({ value: p.id, label: p.plan_name }));

  return (
    <div className="space-y-8">
      {/* Header */}
      <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }}>
        <h1 className="text-3xl font-bold text-white">Members Management</h1>
      </motion.div>

      {/* Data Table */}
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
      <DataTable
        columns={columns}
        data={members}
        title="All Members"
        loading={loading}
        searchFields={['first_name', 'last_name', 'phone']}
        onEdit={handleOpenModal}
        onDelete={handleDelete}
      />
      </motion.div>

      {/* Edit Modal */}
      {editingMember && (
        <FormModal
          isOpen={isModalOpen}
          title="Edit Member"
          onClose={handleCloseModal}
          onSubmit={handleSubmit}
          loading={loading}
          submitLabel="Update"
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
          <FormInput
            label="Membership Status"
            type="select"
            value={formData.membership_status}
            onChange={(e) => setFormData({ ...formData, membership_status: e.target.value })}
            error={errors.membership_status}
            options={[
              { value: 'active', label: 'Active' },
              { value: 'suspended', label: 'Suspended' },
              { value: 'cancelled', label: 'Cancelled' },
            ]}
            required
          />
          <FormInput
            label="Membership Start Date"
            type="date"
            value={formData.membership_start}
            onChange={(e) => setFormData({ ...formData, membership_start: e.target.value })}
          />
          <FormInput
            label="Membership End Date"
            type="date"
            value={formData.membership_end}
            onChange={(e) => setFormData({ ...formData, membership_end: e.target.value })}
          />
          <FormInput
            label="Fitness Goal"
            value={formData.fitness_goal}
            onChange={(e) => setFormData({ ...formData, fitness_goal: e.target.value })}
            placeholder="e.g., Weight loss, Muscle gain"
          />
          <FormInput
            label="Health Notes"
            type="textarea"
            value={formData.health_notes}
            onChange={(e) => setFormData({ ...formData, health_notes: e.target.value })}
            placeholder="Any health-related information or restrictions"
          />
        </FormModal>
      )}

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

      {/* Upgrade Membership Modal */}
      {upgradeMember && (
        <FormModal
          isOpen={isUpgradeModalOpen}
          title={`Upgrade Membership: ${upgradeMember.first_name} ${upgradeMember.last_name}`}
          onClose={handleCloseUpgradeModal}
          onSubmit={handleUpgradeMember}
          loading={actionLoading}
          submitLabel="Upgrade"
        >
          <FormInput
            label="Current Plan"
            value={plans.find(p => p.id === upgradeMember.plan_id)?.plan_name || 'Unknown'}
            disabled
          />
          <FormInput
            label="New Membership Plan"
            type="select"
            value={upgradePlanId}
            onChange={(e) => setUpgradePlanId(e.target.value)}
            options={planOptions.filter(p => String(p.value) !== String(upgradeMember.plan_id))}
            required
          />
        </FormModal>
      )}
    </div>
  );
};

export default MembersManagement;
