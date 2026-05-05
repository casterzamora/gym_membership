import React, { useState, useEffect } from 'react';
import { Button, ConfirmDialog } from '@/components';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Trash2, CheckCircle, Clock } from 'lucide-react';
import { motion } from 'framer-motion';

const EquipmentTrackingModal = ({ classId, className, isOpen, onClose, onUpdate }) => {
  const [equipment, setEquipment] = useState([]);
  const [allEquipment, setAllEquipment] = useState([]);
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [isAdding, setIsAdding] = useState(false);
  const [addFormData, setAddFormData] = useState({
    equipment_id: '',
    user_id: '',
    quantity: '1',
    status: 'required',
  });
  const [errors, setErrors] = useState({});
  const [toDelete, setToDelete] = useState(null);
  const [showConfirm, setShowConfirm] = useState(false);

  useEffect(() => {
    if (isOpen) {
      fetchData();
    }
  }, [isOpen, classId]);

  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Fetch class equipment
      const equipmentRes = await api.equipmentTrackingAPI.getClassEquipment(classId);
      setEquipment(equipmentRes.data.data || []);

      // Fetch all available equipment
      const allEqRes = await api.equipmentAPI.list();
      setAllEquipment(allEqRes.data.data || []);

      // Fetch members for user selection
      const membersRes = await api.membersAPI.list();
      setMembers(membersRes.data.data || []);
    } catch (err) {
      console.error('Failed to load data:', err);
      toast.error('Failed to load equipment data');
    } finally {
      setLoading(false);
    }
  };

  const handleAddEquipment = async (e) => {
    e.preventDefault();
    const newErrors = {};

    if (!addFormData.equipment_id) newErrors.equipment_id = 'Equipment is required';
    if (addFormData.status === 'in_use' && !addFormData.user_id) {
      newErrors.user_id = 'User is required when marking as in use';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    try {
      setIsAdding(true);
      await api.equipmentTrackingAPI.create({
        class_id: classId,
        equipment_id: addFormData.equipment_id,
        user_id: addFormData.user_id || null,
        quantity: parseInt(addFormData.quantity),
        status: addFormData.status,
      });

      toast.success('Equipment added successfully');
      setAddFormData({
        equipment_id: '',
        user_id: '',
        quantity: '1',
        status: 'required',
      });
      setErrors({});
      fetchData();
      onUpdate?.();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to add equipment');
    } finally {
      setIsAdding(false);
    }
  };

  const handleMarkInUse = async (id) => {
    const record = equipment.find(e => e.id === id);
    if (!record?.user_id) {
      toast.error('Cannot mark as in use without a user');
      return;
    }

    try {
      await api.equipmentTrackingAPI.markAsInUse(id);
      toast.success('Equipment marked as in use');
      fetchData();
      onUpdate?.();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to update status');
    }
  };

  const handleMarkReturned = async (id) => {
    try {
      await api.equipmentTrackingAPI.markAsReturned(id);
      toast.success('Equipment marked as returned');
      fetchData();
      onUpdate?.();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to mark as returned');
    }
  };

  const handleDelete = (id) => {
    setToDelete(id);
    setShowConfirm(true);
  };

  const confirmDelete = async () => {
    try {
      await api.equipmentTrackingAPI.delete(toDelete);
      toast.success('Equipment record deleted');
      fetchData();
      onUpdate?.();
      setShowConfirm(false);
      setToDelete(null);
    } catch (err) {
      toast.error('Failed to delete equipment record');
    }
  };

  const getStatusBadgeColor = (status) => {
    switch (status) {
      case 'required': return 'bg-blue-500/20 text-blue-400';
      case 'in_use': return 'bg-green-500/20 text-green-400';
      case 'returned': return 'bg-gray-500/20 text-gray-400';
      default: return 'bg-gray-500/20 text-gray-400';
    }
  };

  const getEquipmentName = (eqId) => {
    return allEquipment.find(e => e.id === eqId)?.equipment_name || `Equipment ${eqId}`;
  };

  const getMemberName = (userId) => {
    return members.find(m => m.user_id === userId)?.name || `User ${userId}`;
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <motion.div
        initial={{ scale: 0.9, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        className="bg-gray-900 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
      >
        {/* Header */}
        <div className="sticky top-0 bg-gray-800 border-b border-gray-700 p-6 flex justify-between items-center">
          <div>
            <h2 className="text-2xl font-bold text-white">Equipment Tracking</h2>
            <p className="text-gray-400 text-sm">{className}</p>
          </div>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-white transition-colors"
          >
            ✕
          </button>
        </div>

        <div className="p-6 space-y-6">
          {/* Add Equipment Form */}
          <div className="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h3 className="text-lg font-semibold text-white mb-4">Add Equipment</h3>
            <form onSubmit={handleAddEquipment} className="grid grid-cols-1 md:grid-cols-5 gap-4">
              <div className="flex flex-col">
                <label className="text-sm font-semibold text-white mb-2">Equipment *</label>
                <select
                  value={addFormData.equipment_id}
                  onChange={(e) => setAddFormData({ ...addFormData, equipment_id: e.target.value })}
                  className="bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                >
                  <option value="">Select Equipment</option>
                  {allEquipment.map(eq => (
                    <option key={eq.id} value={eq.id}>{eq.equipment_name}</option>
                  ))}
                </select>
                {errors.equipment_id && <p className="text-red-400 text-xs mt-1">{errors.equipment_id}</p>}
              </div>
              
              <div className="flex flex-col">
                <label className="text-sm font-semibold text-white mb-2">User</label>
                <select
                  value={addFormData.user_id}
                  onChange={(e) => setAddFormData({ ...addFormData, user_id: e.target.value })}
                  className="bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                >
                  <option value="">Select User (optional)</option>
                  {members.map(m => (
                    <option key={m.user_id} value={m.user_id}>{m.name}</option>
                  ))}
                </select>
              </div>
              
              <div className="flex flex-col">
                <label className="text-sm font-semibold text-white mb-2">Quantity</label>
                <input
                  type="number"
                  min="1"
                  value={addFormData.quantity}
                  onChange={(e) => setAddFormData({ ...addFormData, quantity: e.target.value })}
                  className="bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                />
              </div>
              
              <div className="flex flex-col">
                <label className="text-sm font-semibold text-white mb-2">Status</label>
                <select
                  value={addFormData.status}
                  onChange={(e) => setAddFormData({ ...addFormData, status: e.target.value })}
                  className="bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-blue-500"
                >
                  <option value="required">Required</option>
                  <option value="in_use">In Use</option>
                  <option value="returned">Returned</option>
                </select>
              </div>
              
              <div className="flex items-end">
                <button
                  type="submit"
                  disabled={isAdding}
                  className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 text-white font-semibold py-2 px-4 rounded transition-colors"
                >
                  {isAdding ? 'Adding...' : 'Add'}
                </button>
              </div>
            </form>
          </div>

          {/* Equipment List */}
          <div>
            <h3 className="text-lg font-semibold text-white mb-4">Equipment Records</h3>
            {loading ? (
              <div className="text-center py-8 text-gray-400">Loading...</div>
            ) : equipment.length === 0 ? (
              <div className="text-center py-8 text-gray-400">No equipment records</div>
            ) : (
              <div className="space-y-2">
                {equipment.map((record) => (
                  <motion.div
                    key={record.id}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-gray-800 rounded-lg p-4 border border-gray-700 hover:border-gray-600 transition-colors"
                  >
                    <div className="flex items-center justify-between gap-4">
                      <div className="flex-1">
                        <div className="flex items-center gap-3">
                          <span className="font-semibold text-white">
                            {getEquipmentName(record.equipment_id)}
                          </span>
                          <span className={`px-2 py-1 rounded-full text-xs font-semibold ${getStatusBadgeColor(record.status)}`}>
                            {record.status}
                          </span>
                          {record.quantity > 1 && (
                            <span className="text-sm text-gray-400">Qty: {record.quantity}</span>
                          )}
                        </div>
                        {record.user && (
                          <div className="text-sm text-gray-400 mt-1">
                            Using: {record.user.name}
                          </div>
                        )}
                        {record.assigned_by && (
                          <div className="text-xs text-gray-500 mt-1">
                            Assigned by: {record.assigned_by.name || 'Unknown'}
                          </div>
                        )}
                      </div>
                      
                      {/* Status Buttons */}
                      <div className="flex gap-2">
                        {record.status === 'required' && (
                          <button
                            onClick={() => handleMarkInUse(record.id)}
                            title="Mark as In Use"
                            className="p-2 bg-green-500/20 hover:bg-green-500/30 text-green-400 rounded transition-colors"
                          >
                            <Clock size={18} />
                          </button>
                        )}
                        {record.status === 'in_use' && (
                          <button
                            onClick={() => handleMarkReturned(record.id)}
                            title="Mark as Returned"
                            className="p-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 rounded transition-colors"
                          >
                            <CheckCircle size={18} />
                          </button>
                        )}
                        <button
                          onClick={() => handleDelete(record.id)}
                          title="Delete"
                          className="p-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded transition-colors"
                        >
                          <Trash2 size={18} />
                        </button>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Close Button */}
        <div className="sticky bottom-0 bg-gray-800 border-t border-gray-700 p-4">
          <Button onClick={onClose} className="w-full">Close</Button>
        </div>
      </motion.div>

      {/* Delete Confirmation */}
      <ConfirmDialog
        isOpen={showConfirm}
        title="Delete Equipment Record"
        message="Are you sure you want to delete this equipment record?"
        onConfirm={confirmDelete}
        onCancel={() => setShowConfirm(false)}
      />
    </div>
  );
};

export default EquipmentTrackingModal;
