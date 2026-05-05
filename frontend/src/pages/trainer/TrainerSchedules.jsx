import React, { useContext, useEffect, useMemo, useState } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { FormModal, FormInput, ConfirmDialog, LoadingSpinner, Button, Card } from '@/components';
import api from '@/services/api';
import { CalendarRange, Trash2, Edit2, Plus } from 'lucide-react';
import toast from 'react-hot-toast';

const initialForm = {
  class_id: '',
  class_date: '',
  start_time: '',
  end_time: '',
  recurrence_type: '',
  recurrence_end_date: '',
};

const TrainerSchedules = () => {
  const { user } = useContext(AuthContext);
  const [loading, setLoading] = useState(true);
  const [currentTrainerId, setCurrentTrainerId] = useState(null);
  const [trainerClasses, setTrainerClasses] = useState([]);
  const [schedules, setSchedules] = useState([]);

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingSchedule, setEditingSchedule] = useState(null);
  const [formData, setFormData] = useState(initialForm);
  const [errors, setErrors] = useState({});

  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [scheduleToDelete, setScheduleToDelete] = useState(null);

  useEffect(() => {
    if (user?.trainer_id) {
      setCurrentTrainerId(user.trainer_id);
      fetchAllData(user.trainer_id);
    }
  }, [user?.trainer_id]);

  const recurrenceOptions = useMemo(
    () => [
      { label: 'One-time', value: '' },
      { label: 'Daily', value: 'daily' },
      { label: 'Weekly', value: 'weekly' },
      { label: 'Monthly', value: 'monthly' },
    ],
    []
  );

  const classOptions = useMemo(
    () => trainerClasses.map((c) => ({ label: c.class_name, value: String(c.id) })),
    [trainerClasses]
  );

  const normalizeListPayload = (res) => {
    const payload = res?.data?.data;
    if (Array.isArray(payload)) {
      return payload;
    }
    if (payload && Array.isArray(payload.data)) {
      return payload.data;
    }
    return [];
  };

  const formatTime = (value) => {
    if (!value) return '';
    // Extract HH:MM from either "HH:MM:SS" or "HH:MM" format
    const timeStr = String(value).trim();
    if (timeStr.length >= 5) {
      return timeStr.slice(0, 5); // Returns "HH:MM"
    }
    return timeStr;
  };

  const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    try {
      // Handle ISO format dates like "2026-04-23T00:00:00.000000Z"
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        // If it's already in YYYY-MM-DD format, return as-is
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
          return dateString;
        }
        return '';
      }
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    } catch (e) {
      console.error('Error formatting date:', dateString, e);
      return '';
    }
  };

  const formatDateForDisplay = (dateString) => {
    if (!dateString) return '-';
    const formatted = formatDateForInput(dateString);
    return formatted || '-';
  };

  const getScheduleClassId = (schedule) => {
    return Number(
      schedule.class_id ||
      schedule.fitness_class_id ||
      schedule.fitnessClass?.id ||
      schedule.fitness_class?.id ||
      0
    );
  };

  const getScheduleClassName = (schedule) => {
    const fromRelation = schedule.fitnessClass?.class_name || schedule.fitness_class?.class_name;
    if (fromRelation) return fromRelation;

    const classId = getScheduleClassId(schedule);
    const classItem = trainerClasses.find((c) => Number(c.id) === Number(classId));
    return classItem?.class_name || `Class #${classId}`;
  };

  const fetchAllData = async (trainerId = null) => {
    try {
      setLoading(true);
      console.log('📡 Fetching data for trainer ID:', trainerId || currentTrainerId);

      const id = trainerId || currentTrainerId;
      if (!id) {
        console.warn('⚠️ No trainer ID available');
        setTrainerClasses([]);
        setSchedules([]);
        return;
      }

      const [classesRes, schedulesRes] = await Promise.all([
        api.classesAPI.list(),
        api.schedulesAPI.list(),
      ]);

      const allClasses = normalizeListPayload(classesRes);
      console.log(`📦 Loaded ${allClasses.length} classes`);
      
      const trainerOwnedClasses = allClasses.filter((c) => Number(c.trainer_id) === Number(id));
      console.log(`✅ Found ${trainerOwnedClasses.length} classes for trainer ${id}`);
      setTrainerClasses(trainerOwnedClasses);

      const trainerClassIds = new Set(trainerOwnedClasses.map((c) => Number(c.id)));
      const allSchedules = normalizeListPayload(schedulesRes);
      console.log(`📦 Loaded ${allSchedules.length} total schedules`);
      
      const filteredSchedules = allSchedules.filter((s) => trainerClassIds.has(getScheduleClassId(s)));
      console.log(`✅ Found ${filteredSchedules.length} schedules for trainer's classes`);
      console.log('Filtered schedules:', filteredSchedules);
      
      setSchedules(filteredSchedules);
      console.log('✅ State updated with new schedules');
    } catch (err) {
      console.error('❌ Failed to load data:', err);
      toast.error('Failed to load schedules');
      setTrainerClasses([]);
      setSchedules([]);
    } finally {
      setLoading(false);
    }
  };

  const openCreateModal = () => {
    setEditingSchedule(null);
    setFormData(initialForm);
    setErrors({});
    setIsModalOpen(true);
  };

  const openEditModal = (schedule) => {
    console.log('=== OPENING EDIT MODAL ===');
    console.log('Schedule object:', schedule);
    console.log('Raw start_time:', schedule.start_time, '| Raw end_time:', schedule.end_time);
    console.log('Raw class_date:', schedule.class_date);
    
    const classId = getScheduleClassId(schedule);
    const formattedDate = formatDateForInput(schedule.class_date);
    const formattedStart = formatTime(schedule.start_time);
    const formattedEnd = formatTime(schedule.end_time);
    
    console.log('Formatted class_id:', classId);
    console.log('Formatted class_date:', formattedDate);
    console.log('Formatted start_time:', formattedStart);
    console.log('Formatted end_time:', formattedEnd);
    
    setEditingSchedule(schedule);
    const formattedData = {
      class_id: String(classId || ''),
      class_date: formattedDate,
      start_time: formattedStart,
      end_time: formattedEnd,
      recurrence_type: schedule.recurrence_type || '',
      recurrence_end_date: formatDateForInput(schedule.recurrence_end_date),
    };
    
    console.log('Final form data to set:', formattedData);
    setFormData(formattedData);
    setErrors({});
    setIsModalOpen(true);
  };

  const validateForm = () => {
    const nextErrors = {};
    if (!formData.class_id) nextErrors.class_id = 'Class is required';
    if (!formData.class_date) nextErrors.class_date = 'Date is required';
    if (!formData.start_time) nextErrors.start_time = 'Start time is required';
    if (!formData.end_time) nextErrors.end_time = 'End time is required';

    if (formData.start_time && formData.end_time) {
      if (formData.end_time <= formData.start_time) {
        nextErrors.end_time = 'End time must be after start time';
      }
    }

    if (formData.recurrence_type && formData.recurrence_end_date) {
      if (formData.recurrence_end_date <= formData.class_date) {
        nextErrors.recurrence_end_date = 'Recurrence end date must be after class date';
      }
    }

    setErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const handleSave = async () => {
    if (!validateForm()) {
      console.error('Form validation failed, errors:', errors);
      toast.error('Please fix the form errors');
      return;
    }

    console.log('=== SAVING SCHEDULE ===');
    console.log('Editing?', !!editingSchedule, 'ID:', editingSchedule?.id);
    console.log('FormData before sending:', formData);
    
    const payload = {
      class_id: Number(formData.class_id),
      class_date: formData.class_date,
      start_time: formData.start_time,
      end_time: formData.end_time,
      recurrence_type: formData.recurrence_type || null,
      recurrence_end_date: formData.recurrence_end_date || null,
    };

    console.log('Payload being sent:', payload);

    try {
      setLoading(true);
      let response;
      
      if (editingSchedule) {
        console.log('🔄 Updating schedule ID:', editingSchedule.id);
        response = await api.schedulesAPI.update(editingSchedule.id, payload);
        console.log('✅ Update response:', response.data);
        console.log('Response data content:', response.data.data);
        
        // Verify we got schedule data back
        if (response.data.data && response.data.data.start_time) {
          console.log('✅ Response contains updated schedule data');
          console.log('   Updated start_time:', response.data.data.start_time);
          console.log('   Updated end_time:', response.data.data.end_time);
        } else {
          console.warn('⚠️ Response missing schedule data fields');
        }
        
        toast.success('Schedule updated successfully');
      } else {
        console.log('➕ Creating new schedule');
        response = await api.schedulesAPI.create(payload);
        console.log('✅ Create response:', response.data);
        toast.success('Schedule created successfully');
      }
      
      // Close modal and reset form
      setIsModalOpen(false);
      setEditingSchedule(null);
      setFormData(initialForm);
      setErrors({});
      
      // Refresh the list
      console.log('🔄 Refreshing schedule list...');
      await fetchAllData(user?.trainer_id || currentTrainerId);
      console.log('✅ Schedule list refreshed');
    } catch (err) {
      console.error('❌ Save error:', err.message);
      console.error('Error response:', err.response?.data);
      if (err.response?.data?.errors) {
        console.error('Validation errors:', err.response.data.errors);
        setErrors(err.response.data.errors);
      }
      toast.error(err.response?.data?.message || 'Failed to save schedule');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteConfirm = async () => {
    if (!scheduleToDelete) return;
    try {
      setLoading(true);
      console.log('🗑️ Deleting schedule ID:', scheduleToDelete.id);
      const deleteRes = await api.schedulesAPI.delete(scheduleToDelete.id);
      console.log('✅ Delete response:', deleteRes.data);
      toast.success('Schedule deleted successfully');
      setIsConfirmOpen(false);
      setScheduleToDelete(null);
      console.log('🔄 Refreshing schedule list after delete...');
      await fetchAllData(user?.trainer_id || currentTrainerId);
      console.log('✅ Schedule list refreshed after delete');
    } catch (err) {
      console.error('❌ Delete error:', err);
      console.error('Error response:', err.response?.data);
      toast.error(err.response?.data?.message || 'Failed to delete schedule');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center">
        <LoadingSpinner />
      </div>
    );
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="flex justify-between items-center mb-8">
          <div>
            <div className="flex items-center gap-3 mb-2">
              <CalendarRange size={32} className="text-gold-400" />
              <h1 className="text-4xl font-bold text-white">My Schedules</h1>
            </div>
            <p className="text-gray-400">Create and manage your class sessions</p>
          </div>
          <Button onClick={openCreateModal} className="flex items-center gap-2" disabled={!user?.trainer_id || trainerClasses.length === 0}>
            <Plus size={18} />
            New Schedule
          </Button>
        </div>

        {trainerClasses.length === 0 ? (
          <Card>
            <div className="p-12 text-center">
              <CalendarRange size={48} className="text-gray-600 mx-auto mb-4" />
              <p className="text-gray-400">Create at least one class first to start adding schedules.</p>
            </div>
          </Card>
        ) : schedules.length === 0 ? (
          <Card>
            <div className="p-12 text-center">
              <CalendarRange size={48} className="text-gray-600 mx-auto mb-4" />
              <p className="text-gray-400">No schedules yet. Add your first schedule.</p>
            </div>
          </Card>
        ) : (
          <Card className="overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gold-600/20 bg-dark-secondary">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Class</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Date</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Start</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">End</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Recurrence</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {schedules.map((schedule) => (
                    <tr key={schedule.id} className="border-b border-gray-700 hover:bg-dark-secondary transition">
                      <td className="px-6 py-4 text-white font-medium">{getScheduleClassName(schedule)}</td>
                      <td className="px-6 py-4 text-gray-300">{formatDateForDisplay(schedule.class_date)}</td>
                      <td className="px-6 py-4 text-white">{formatTime(schedule.start_time)}</td>
                      <td className="px-6 py-4 text-white">{formatTime(schedule.end_time)}</td>
                      <td className="px-6 py-4 text-gray-300">{schedule.recurrence_type || 'One-time'}</td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2">
                          <button
                            onClick={() => openEditModal(schedule)}
                            className="p-2 text-blue-300 hover:bg-blue-500/20 rounded transition"
                            title="Edit"
                          >
                            <Edit2 size={16} />
                          </button>
                          <button
                            onClick={() => {
                              setScheduleToDelete(schedule);
                              setIsConfirmOpen(true);
                            }}
                            className="p-2 text-red-300 hover:bg-red-500/20 rounded transition"
                            title="Delete"
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </Card>
        )}

        <FormModal
          isOpen={isModalOpen}
          title={editingSchedule ? 'Edit Schedule' : 'Create New Schedule'}
          onClose={() => {
            setIsModalOpen(false);
            setEditingSchedule(null);
            setFormData(initialForm);
            setErrors({});
          }}
          onSubmit={(e) => {
            e.preventDefault();
            handleSave();
          }}
        >
          <FormInput
            label="Class"
            type="select"
            options={classOptions}
            value={formData.class_id}
            onChange={(e) => setFormData({ ...formData, class_id: e.target.value })}
            error={errors.class_id}
            required
          />
          <FormInput
            label="Class Date"
            type="date"
            value={formData.class_date}
            onChange={(e) => setFormData({ ...formData, class_date: e.target.value })}
            error={errors.class_date}
            required
          />
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput
              label="Start Time"
              type="time"
              value={formData.start_time}
              onChange={(e) => setFormData({ ...formData, start_time: e.target.value })}
              error={errors.start_time}
              required
            />
            <FormInput
              label="End Time"
              type="time"
              value={formData.end_time}
              onChange={(e) => setFormData({ ...formData, end_time: e.target.value })}
              error={errors.end_time}
              required
            />
          </div>
          <FormInput
            label="Recurrence"
            type="select"
            options={recurrenceOptions}
            value={formData.recurrence_type}
            onChange={(e) => setFormData({ ...formData, recurrence_type: e.target.value })}
          />
          <FormInput
            label="Recurrence End Date"
            type="date"
            value={formData.recurrence_end_date}
            onChange={(e) => setFormData({ ...formData, recurrence_end_date: e.target.value })}
            error={errors.recurrence_end_date}
          />
        </FormModal>

        <ConfirmDialog
          isOpen={isConfirmOpen}
          title="Delete Schedule"
          message="Are you sure you want to delete this schedule?"
          onConfirm={handleDeleteConfirm}
          onCancel={() => {
            setIsConfirmOpen(false);
            setScheduleToDelete(null);
          }}
          isDangerous
        />
      </div>
    </div>
  );
};

export default TrainerSchedules;