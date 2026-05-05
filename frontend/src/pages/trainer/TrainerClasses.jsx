import React, { useContext, useEffect, useMemo, useState } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { FormModal, FormInput, ConfirmDialog, LoadingSpinner, Button, Card } from '@/components';
import api from '@/services/api';
import { Trash2, Edit2, Plus, BookOpen } from 'lucide-react';
import toast from 'react-hot-toast';

const initialForm = {
  class_name: '',
  description: '',
  max_participants: '',
  difficulty_level: '',
};

const TrainerClasses = () => {
  const { user } = useContext(AuthContext);
  const [classes, setClasses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingClass, setEditingClass] = useState(null);
  const [classToDelete, setClassToDelete] = useState(null);
  const [formData, setFormData] = useState(initialForm);
  const [errors, setErrors] = useState({});
  const [currentTrainerId, setCurrentTrainerId] = useState(null);

  useEffect(() => {
    if (user?.trainer_id) {
      setCurrentTrainerId(user.trainer_id);
      fetchClasses();
    }
  }, [user?.trainer_id]);

  const fetchClasses = async () => {
    try {
      setLoading(true);
      const res = await api.classesAPI.list();
      const allClasses = res?.data?.data || [];
      // If trainer, only show classes assigned to them. Admins see all classes.
      if (user?.role === 'trainer' && currentTrainerId) {
        const trainerClasses = allClasses.filter((c) => Number(c.trainer_id) === Number(currentTrainerId));
        setClasses(trainerClasses);
      } else {
        setClasses(allClasses);
      }
    } catch (err) {
      toast.error('Failed to load classes');
      setClasses([]);
    } finally {
      setLoading(false);
    }
  };

  const difficultyOptions = useMemo(
    () => [
      { label: 'Beginner', value: 'Beginner' },
      { label: 'Intermediate', value: 'Intermediate' },
      { label: 'Advanced', value: 'Advanced' },
    ],
    []
  );

  const openCreateModal = () => {
    setEditingClass(null);
    setFormData(initialForm);
    setErrors({});
    setIsModalOpen(true);
  };

  const openEditModal = (classItem) => {
    setEditingClass(classItem);
    setFormData({
      class_name: classItem.class_name || '',
      description: classItem.description || '',
      max_participants: classItem.max_participants || '',
      difficulty_level: classItem.difficulty_level || '',
    });
    setErrors({});
    setIsModalOpen(true);
  };

  const validate = () => {
    const next = {};
    if (!formData.class_name.trim()) next.class_name = 'Class name is required';
    if (!formData.max_participants || Number(formData.max_participants) <= 0) next.max_participants = 'Capacity must be greater than 0';
    if (!formData.difficulty_level) next.difficulty_level = 'Difficulty level is required';
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const handleSave = async () => {
    if (!validate()) return;
    if (!currentTrainerId) {
      toast.error('Trainer profile not found for this account');
      return;
    }

    const payload = {
      class_name: formData.class_name,
      description: formData.description || null,
      max_participants: Number(formData.max_participants),
      difficulty_level: formData.difficulty_level,
      trainer_id: Number(currentTrainerId),
    };

    try {
      setLoading(true);
      if (editingClass) {
        await api.classesAPI.update(editingClass.id, payload);
        toast.success('Class updated successfully');
      } else {
        await api.classesAPI.create(payload);
        toast.success('Class created successfully');
      }
      setIsModalOpen(false);
      setEditingClass(null);
      setFormData(initialForm);
      await fetchClasses();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to save class');
    } finally {
      setLoading(false);
    }
  };

  const handleConfirmDelete = async () => {
    if (!classToDelete) return;

    try {
      setLoading(true);
      await api.classesAPI.delete(classToDelete.id);
      toast.success('Class deleted successfully');
      setIsConfirmOpen(false);
      setClassToDelete(null);
      await fetchClasses();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to delete class');
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
              <BookOpen size={32} className="text-gold-400" />
              <h1 className="text-4xl font-bold text-white">Classes</h1>
            </div>
            <p className="text-gray-400">View all classes and create your own</p>
          </div>
          {user?.role === 'admin' ? (
            <Button onClick={openCreateModal} className="flex items-center gap-2">
              <Plus size={18} />
              New Class
            </Button>
          ) : (
            <div className="text-sm text-gray-400">Trainers may create schedules only; class management is admin-only.</div>
          )}
        </div>

        {classes.length === 0 ? (
          <Card>
            <div className="p-12 text-center">
              <BookOpen size={48} className="text-gray-600 mx-auto mb-4" />
              <p className="text-gray-400">No classes available yet. Create your first class.</p>
            </div>
          </Card>
        ) : (
          <Card className="overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gold-600/20 bg-dark-secondary">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Class Name</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Difficulty</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Capacity</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Description</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {classes.map((classItem) => (
                    <tr key={classItem.id} className="border-b border-gray-700 hover:bg-dark-secondary transition">
                      <td className="px-6 py-4 text-white font-medium">{classItem.class_name}</td>
                      <td className="px-6 py-4 text-gray-300">{classItem.difficulty_level || '-'}</td>
                      <td className="px-6 py-4 text-white">{classItem.max_participants}</td>
                      <td className="px-6 py-4 text-gray-400 text-sm max-w-xs truncate">{classItem.description || '-'}</td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2">
                          {user?.role === 'admin' ? (
                            <>
                              <button
                                onClick={() => openEditModal(classItem)}
                                className="p-2 text-blue-300 hover:bg-blue-500/20 rounded transition"
                                title="Edit"
                              >
                                <Edit2 size={16} />
                              </button>
                              <button
                                onClick={() => {
                                  setClassToDelete(classItem);
                                  setIsConfirmOpen(true);
                                }}
                                className="p-2 text-red-300 hover:bg-red-500/20 rounded transition"
                                title="Delete"
                              >
                                <Trash2 size={16} />
                              </button>
                            </>
                          ) : (
                            <div className="text-sm text-gray-400">Schedule management only</div>
                          )}
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
          title={editingClass ? 'Edit Class' : 'Create New Class'}
          onClose={() => {
            setIsModalOpen(false);
            setEditingClass(null);
            setFormData(initialForm);
            setErrors({});
          }}
          onSubmit={(e) => {
            e.preventDefault();
            handleSave();
          }}
        >
          <FormInput
            label="Class Name"
            value={formData.class_name}
            onChange={(e) => setFormData({ ...formData, class_name: e.target.value })}
            error={errors.class_name}
            required
          />
          <FormInput
            label="Difficulty Level"
            type="select"
            options={difficultyOptions}
            value={formData.difficulty_level}
            onChange={(e) => setFormData({ ...formData, difficulty_level: e.target.value })}
            error={errors.difficulty_level}
            required
          />
          <FormInput
            label="Max Participants"
            type="number"
            value={formData.max_participants}
            onChange={(e) => setFormData({ ...formData, max_participants: e.target.value })}
            error={errors.max_participants}
            required
          />
          <FormInput
            label="Description"
            type="textarea"
            value={formData.description}
            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          />
        </FormModal>

        <ConfirmDialog
          isOpen={isConfirmOpen}
          title="Delete Class"
          message={`Are you sure you want to delete ${classToDelete?.class_name || 'this class'}?`}
          onConfirm={handleConfirmDelete}
          onCancel={() => {
            setIsConfirmOpen(false);
            setClassToDelete(null);
          }}
          isDangerous
        />
      </div>
    </div>
  );
};

export default TrainerClasses;
