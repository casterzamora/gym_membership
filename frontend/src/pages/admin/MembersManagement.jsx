import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { DataTable, FormModal, FormInput, ConfirmDialog, Button } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { AlertCircle } from 'lucide-react';

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
      console.log('Fetching membership plans...');
      const response = await api.plansAPI.list();
      console.log('Plans response:', response);
      const plansData = response.data?.data || response.data || [];
      console.log('Plans loaded:', plansData);
      setPlans(plansData);
      if (plansData.length === 0) {
        console.warn('No membership plans found in database');
      }
    } catch (err) {
      console.error('Failed to load plans:', err);
      toast.error('Failed to load membership plans');
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
    
    // Required fields
    if (!formData.first_name || !formData.first_name.trim()) {
      newErrors.first_name = 'First name is required';
    }
    if (!formData.last_name || !formData.last_name.trim()) {
      newErrors.last_name = 'Last name is required';
    }
    if (!formData.plan_id) {
      newErrors.plan_id = 'Membership plan is required';
    }
    
    // Optional fields - only validate if they have values
    if (formData.phone && formData.phone.trim() && formData.phone.trim().length < 7) {
      newErrors.phone = 'Phone must be at least 7 characters';
    }
    if (formData.date_of_birth) {
      const dob = new Date(formData.date_of_birth);
      const today = new Date();
      if (dob >= today) {
        newErrors.date_of_birth = 'Date of birth must be in the past';
      }
    }
    
    // Date validation if both are provided
    if (formData.membership_start && formData.membership_end) {
      const start = new Date(formData.membership_start);
      const end = new Date(formData.membership_end);
      if (end <= start) {
        newErrors.membership_end = 'Membership end date must be after start date';
      }
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) {
      console.warn('Form validation failed', errors);
      return;
    }

    try {
      setLoading(true);
      if (editingMember) {
        // Prepare update data
        const updateData = {
          first_name: formData.first_name?.trim(),
          last_name: formData.last_name?.trim(),
          phone: formData.phone?.trim() || null,
          date_of_birth: formData.date_of_birth || null,
          plan_id: formData.plan_id || null,
          fitness_goal: formData.fitness_goal?.trim() || null,
          health_notes: formData.health_notes?.trim() || null,
          membership_status: formData.membership_status || null,
          membership_start: formData.membership_start || null,
          membership_end: formData.membership_end || null,
        };

        console.log('Sending update for member ID:', editingMember.id);
        console.log('Update data:', updateData);

        // Validate date logic if both dates exist
        if (updateData.membership_start && updateData.membership_end) {
          const startDate = new Date(updateData.membership_start);
          const endDate = new Date(updateData.membership_end);
          
          if (endDate <= startDate) {
            toast.error('Membership end date must be after start date');
            setLoading(false);
            return;
          }
        }

        const response = await api.membersAPI.update(editingMember.id, updateData);
        
        console.log('Update response:', response);
        
        if (response.success || response.data) {
          toast.success('Member updated successfully');
        } else {
          toast.error(response.message || 'Update completed but no confirmation');
        }
      }
      handleCloseModal();
      fetchMembers();
    } catch (err) {
      console.error('Update error:', err);
      
      // Handle validation errors
      if (err.response?.data?.errors) {
        const validationErrors = err.response.data.errors;
        const errorMessages = Object.entries(validationErrors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n');
        console.error('Validation errors:', validationErrors);
        toast.error(`Validation failed:\n${errorMessages}`);
      } else if (err.response?.data?.message) {
        console.error('API error message:', err.response.data.message);
        toast.error(err.response.data.message);
      } else if (err.message) {
        console.error('Error message:', err.message);
        toast.error(`Error: ${err.message}`);
      } else {
        console.error('Unknown error');
        toast.error('Failed to update member');
      }
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
    // Validate member data
    if (!member || !member.id) {
      console.error('Invalid member data:', member);
      toast.error('Invalid member data');
      return;
    }
    
    console.log('Opening upgrade modal for member:', member.id, 'Plans available:', plans.length);
    
    // Allow modal to open even if plans haven't loaded yet
    // We'll show a loading state or error in the modal if needed
    setUpgradeMember(member);
    setUpgradePlanId('');
    setIsUpgradeModalOpen(true);
    
    // If plans aren't loaded, try to fetch them again
    if (plans.length === 0) {
      console.log('Plans not loaded, fetching...');
      fetchPlans();
    }
  };

  const handleCloseUpgradeModal = () => {
    setUpgradeMember(null);
    setUpgradePlanId('');
    setIsUpgradeModalOpen(false);
  };

  const getUpgradeEligibilityDate = (member) => {
    if (!member?.membership_end && !member?.membership_start) return null;

    if (member?.membership_end) {
      const endDate = new Date(member.membership_end);
      if (Number.isNaN(endDate.getTime())) return null;

      const eligibleDate = new Date(endDate);
      eligibleDate.setDate(eligibleDate.getDate() + 1);
      return eligibleDate;
    }

    const startDate = new Date(member.membership_start);
    if (Number.isNaN(startDate.getTime())) return null;

    const eligibleDate = new Date(startDate);
    eligibleDate.setMonth(eligibleDate.getMonth() + 1);
    return eligibleDate;
  };

  const isUpgradeEligible = (member) => {
    const eligibleDate = getUpgradeEligibilityDate(member);
    if (!eligibleDate) return false;
    return new Date() >= eligibleDate;
  };

  const handleUpgradeMember = async (e) => {
    e.preventDefault();

    console.log('Upgrade button clicked - Processing upgrade...');
    
    if (!upgradeMember || !upgradeMember.id) {
      console.error('Invalid member data:', upgradeMember);
      toast.error('Invalid member data');
      return;
    }

    if (!upgradePlanId || upgradePlanId === '') {
      console.error('No plan selected. upgradePlanId:', upgradePlanId);
      toast.error('Please select a new membership plan');
      return;
    }

    // Ensure the selected plan exists
    const selectedPlan = plans.find(p => p.id === Number(upgradePlanId));
    if (!selectedPlan) {
      console.error('Selected plan not found. upgradePlanId:', upgradePlanId, 'Available plans:', plans);
      toast.error('Selected plan is not available');
      return;
    }

    if (Number(upgradeMember.plan_id) === Number(upgradePlanId)) {
      console.warn('Attempting to upgrade to same plan:', upgradePlanId);
      toast.error('Selected plan is the same as current plan');
      return;
    }

    try {
      setActionLoading(true);
      console.log('Sending upgrade request for member:', upgradeMember.id, 'New plan:', upgradePlanId);
      
      const response = await api.membersAPI.upgrade(upgradeMember.id, { 
        new_plan_id: Number(upgradePlanId) 
      });
      
      console.log('Upgrade response:', response);
      
      if (response?.data?.success !== false && response?.status !== 'error') {
        console.log('Upgrade successful! Plan:', selectedPlan.plan_name);
        toast.success(`Membership upgraded to ${selectedPlan.plan_name} successfully!`);
        handleCloseUpgradeModal();
        await fetchMembers();
      } else {
        const errorMsg = response?.data?.message || response?.message || 'Upgrade completed but response unclear';
        console.error('Upgrade failed:', errorMsg);
        toast.error(errorMsg);
      }
    } catch (err) {
      console.error('Upgrade error caught:', err);
      console.error('Error response:', err.response);
      
      // Provide detailed error message
      const errorMsg = err.response?.data?.message 
        || err.response?.data?.errors 
        || err.message 
        || 'Failed to upgrade membership';
      
      // If it's a validation error, show detailed info
      if (err.response?.data?.errors) {
        const validationErrors = Object.entries(err.response.data.errors)
          .map(([field, msgs]) => `${field}: ${msgs.join(', ')}`)
          .join('\n');
        console.error('Validation errors:', validationErrors);
        toast.error(`Validation Error:\n${validationErrors}`);
      } else {
        toast.error(errorMsg);
      }
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
      label: 'Current Plan',
      render: (value) => plans.find(p => p.id === value)?.plan_name || 'N/A'
    },
    {
      key: 'membership_end',
      label: 'Membership End',
      render: (value) => value ? new Date(value).toLocaleDateString() : 'N/A'
    },
    {
      key: 'membership_status',
      label: 'Status',
      render: (value) => {
        const statusColors = {
          'active': 'bg-green-600 text-white',
          'suspended': 'bg-yellow-600 text-white',
          'cancelled': 'bg-red-600 text-white',
        };
        return (
          <span className={`px-3 py-1 rounded text-sm font-medium ${statusColors[value] || 'bg-gray-600 text-white'}`}>
            {value?.charAt(0).toUpperCase() + value?.slice(1) || 'N/A'}
          </span>
        );
      }
    },
    {
      key: 'membership_actions',
      label: 'Actions',
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
          />
          <FormInput
            label="Date of Birth"
            type="date"
            value={formData.date_of_birth}
            onChange={(e) => setFormData({ ...formData, date_of_birth: e.target.value })}
            error={errors.date_of_birth}
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
              { value: '', label: '-- Select Status (Optional) --' },
              { value: 'active', label: 'Active' },
              { value: 'suspended', label: 'Suspended' },
              { value: 'cancelled', label: 'Cancelled' },
            ]}
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
          isValid={isUpgradeEligible(upgradeMember) && upgradePlanId !== '' && Number(upgradePlanId) > 0}
          submitLabel={!isUpgradeEligible(upgradeMember)
            ? 'Upgrade Available After 1 Month'
            : !upgradePlanId
              ? 'Select a Plan First'
              : 'Confirm Upgrade'}
        >
          {!isUpgradeEligible(upgradeMember) && (
            <div className="mb-5 p-4 bg-amber-500/10 border border-amber-400/30 rounded-lg flex gap-3">
              <AlertCircle size={18} className="text-amber-300 flex-shrink-0 mt-0.5" />
              <div className="text-sm text-amber-100">
                <p className="font-semibold">Upgrade not available yet</p>
                <p>
                  You can only upgrade once your membership expires.
                  {getUpgradeEligibilityDate(upgradeMember) && (
                    <> Eligible on {getUpgradeEligibilityDate(upgradeMember).toLocaleDateString()}.</>
                  )}
                </p>
              </div>
            </div>
          )}

          {/* Current Plan Info */}
          <div className="bg-gray-800 p-4 rounded-lg mb-6">
            <h4 className="text-gold-500 font-semibold mb-3">📋 Current Membership</h4>
            <FormInput
              label="Current Plan"
              value={plans.find(p => p.id === upgradeMember.plan_id)?.plan_name || 'N/A'}
              disabled
            />
            <FormInput
              label="Membership Ends"
              value={upgradeMember.membership_end ? new Date(upgradeMember.membership_end).toLocaleDateString() : 'N/A'}
              disabled
            />
            <FormInput
              label="Status"
              value={upgradeMember.membership_status?.charAt(0).toUpperCase() + upgradeMember.membership_status?.slice(1) || 'N/A'}
              disabled
            />
          </div>

          {/* New Plan Selection */}
          <div className="bg-gray-800 p-4 rounded-lg">
            <h4 className="text-gold-500 font-semibold mb-3">⬆️ Select New Plan</h4>
            
            {plans.length === 0 ? (
              <div className="p-4 bg-yellow-900/30 border border-yellow-600 rounded text-yellow-300 mb-4">
                ⏳ Loading membership plans...
              </div>
            ) : (
              <div>
                <FormInput
                  label="New Membership Plan"
                  type="select"
                  value={upgradePlanId}
                  onChange={(e) => setUpgradePlanId(e.target.value)}
                  options={plans
                    .filter(p => Number(p.id) !== Number(upgradeMember.plan_id))
                    .map(p => ({ value: p.id, label: `${p.plan_name} - PHP ${p.price}/month` }))}
                  required
                />
                
                {/* Check if there are alternative plans available */}
                {plans.filter(p => Number(p.id) !== Number(upgradeMember.plan_id)).length === 0 && (
                  <div className="mt-3 p-3 bg-blue-900/30 border border-blue-600 rounded text-blue-300 text-sm">
                    ℹ️ No other plans available for upgrade
                  </div>
                )}
              </div>
            )}
            
            {/* Show new plan details if selected */}
            {upgradePlanId && plans.length > 0 && (
              <div className="mt-6 p-4 bg-gold-600/10 border border-gold-600/30 rounded-lg">
                <h5 className="text-gold-300 font-semibold mb-3">📊 New Plan Details</h5>
                {(() => {
                  const newPlan = plans.find(p => p.id === Number(upgradePlanId));
                  if (!newPlan) return <p className="text-gray-400">Plan not found</p>;
                  
                  return (
                    <div className="space-y-2 text-sm">
                      <p className="text-white">
                        <strong>Plan Name:</strong> {newPlan.plan_name}
                      </p>
                      <p className="text-white">
                        <strong>Price:</strong> PHP {newPlan.price} per month
                      </p>
                      <p className="text-white">
                        <strong>Duration:</strong> {newPlan.duration_months} month(s)
                      </p>
                      <p className="text-white">
                        <strong>Classes/Week:</strong> {newPlan.max_classes_per_week === 999 ? '♾️ Unlimited' : newPlan.max_classes_per_week}
                      </p>
                      <p className="text-gray-300 mt-3">
                        <strong>Description:</strong> {newPlan.description}
                      </p>
                    </div>
                  );
                })()}
              </div>
            )}
            
            {upgradePlanId && !plans.find(p => p.id === Number(upgradePlanId)) && (
              <div className="mt-4 p-3 bg-red-900/30 border border-red-600 rounded text-red-300 text-sm">
                ⚠️ Selected plan is invalid
              </div>
            )}
          </div>
        </FormModal>
      )}
    </div>
  );
};

export default MembersManagement;
