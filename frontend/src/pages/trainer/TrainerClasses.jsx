import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { FormModal, FormInput, ConfirmDialog, LoadingSpinner, Button, Card } from '@/components';
import api from '@/services/api';
import { Trash2, Edit2, Plus, BookOpen } from 'lucide-react';
import toast from 'react-hot-toast';

const TrainerClasses = () => {
  const { user } = useContext(AuthContext);
  const [classes, setClasses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [editingClass, setEditingClass] = useState(null);
  const [classToDelete, setClassToDelete] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    category: '',
    capacity: '',
    description: '',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (user?.id) {
      fetchClasses();
    }
  }, [user?.id]);

  const fetchClasses = async () => {
    try {
      setLoading(true);
      const res = await api.classesAPI.list();
      const allClasses = res.data.data || [];
      const myClasses = allClasses.filter(c => c.trainer_id === user?.id);
      setClasses(myClasses);
    } catch (err) {
      toast.error('Failed to load classes');
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (classItem) => {
    setEditingClass(classItem);
    setFormData({
      name: classItem.name,
      category: classItem.category,
      capacity: classItem.capacity,
      description: classItem.description,
    });
    setErrors({});
    setIsModalOpen(true);
  };

  const handleDelete = (classItem) => {
    setClassToDelete(classItem);
    setIsConfirmOpen(true);
  };

  const handleSave = async () => {
    if (!formData.name || !formData.category || !formData.capacity) {
      setErrors({ general: 'Please fill in all required fields' });
      return;
    }

    try {
      if (editingClass) {
        await api.classesAPI.update(editingClass.id, {
          ...formData,
          trainer_id: user?.id,
        });
        toast.success('Class updated successfully');
        setClasses(classes.map(c => c.id === editingClass.id ? { ...c, ...formData } : c));
      } else {
        const res = await api.classesAPI.create({
          ...formData,
          trainer_id: user?.id,
        });
        toast.success('Class created successfully');
        setClasses([...classes, res.data.data]);
      }
      setIsModalOpen(false);
      setEditingClass(null);
      setFormData({ name: '', category: '', capacity: '', description: '' });
    } catch (err) {
      setErrors({ general: err.response?.data?.message || 'Failed to save class' });
      toast.error('Failed to save class');
    }
  };

  const handleConfirmDelete = async () => {
    try {
      await api.classesAPI.delete(classToDelete.id);
      toast.success('Class deleted successfully');
      setClasses(classes.filter(c => c.id !== classToDelete.id));
      setIsConfirmOpen(false);
      setClassToDelete(null);
    } catch (err) {
      toast.error('Failed to delete class');
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
        {/* Header */}
        <div className="flex justify-between items-center mb-8">
          <div>
            <div className="flex items-center gap-3 mb-2">
              <BookOpen size={32} className="text-gold-bright" />
              <h1 className="text-4xl font-bold text-white">My Classes</h1>
            </div>
            <p className="text-gray-400">Manage and organize your training programs</p>
          </div>
          <Button 
            onClick={() => {
              setEditingClass(null);
              setFormData({ name: '', category: '', capacity: '', description: '' });
              setErrors({});
              setIsModalOpen(true);
            }}
            className="flex items-center gap-2"
          >
            <Plus size={18} />
            New Class
          </Button>
        </div>

        {/* Classes Table */}
        {classes.length === 0 ? (
          <Card>
            <div className="p-12 text-center">
              <BookOpen size={48} className="text-gray-600 mx-auto mb-4" />
              <p className="text-gray-400">No classes created yet. Create your first class!</p>
            </div>
          </Card>
        ) : (
          <Card className="overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gold-bright/20 bg-dark-secondary">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Class Name</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Category</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Capacity</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Description</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {classes.map((classItem) => (
                    <tr key={classItem.id} className="border-b border-gray-700 hover:bg-dark-secondary transition">
                      <td className="px-6 py-4 text-white font-medium">{classItem.name}</td>
                      <td className="px-6 py-4">
                        <span className="capitalize text-gray-300 text-sm">{classItem.category}</span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="text-white font-medium">{classItem.capacity}</span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="text-gray-400 text-sm truncate max-w-xs inline-block">
                          {classItem.description || '-'}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2">
                          <button
                            onClick={() => handleEdit(classItem)}
                            className="p-2 text-blue-400 hover:bg-blue-500/20 rounded transition"
                            title="Edit"
                          >
                            <Edit2 size={16} />
                          </button>
                          <button
                            onClick={() => handleDelete(classItem)}
                            className="p-2 text-red-400 hover:bg-red-500/20 rounded transition"
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

        {/* Modal */}
        <FormModal
          isOpen={isModalOpen}
          title={editingClass ? 'Edit Class' : 'Create New Class'}
          onClose={() => {
            setIsModalOpen(false);
            setEditingClass(null);
            setFormData({ name: '', category: '', capacity: '', description: '' });
            setErrors({});
          }}
          onSubmit={handleSave}
        >
          {errors.general && (
            <div className="mb-4 p-3 bg-red-500/20 border border-red-500/30 text-red-300 rounded">
              {errors.general}
            </div>
          )}
          <FormInput
            name="name"
            label="Class Name"
            placeholder="e.g., Yoga 101"
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
            required
          />
          <FormInput
            name="category"
            label="Category"
            type="select"
            options={[
              { label: 'Yoga', value: 'yoga' },
              { label: 'Pilates', value: 'pilates' },
              { label: 'Cardio', value: 'cardio' },
              { label: 'Strength', value: 'strength' },
              { label: 'HIIT', value: 'hiit' },
              { label: 'Zumba', value: 'zumba' },
            ]}
            value={formData.category}
            onChange={(e) => setFormData({ ...formData, category: e.target.value })}
            required
          />
          <FormInput
            name="capacity"
            label="Capacity"
            type="number"
            placeholder="20"
            value={formData.capacity}
            onChange={(e) => setFormData({ ...formData, capacity: e.target.value })}
            required
          />
          <FormInput
            name="description"
            label="Description"
            type="textarea"
            placeholder="Describe your class..."
            value={formData.description}
            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          />
        </FormModal>

        {/* Confirm Delete */}
        <ConfirmDialog
          isOpen={isConfirmOpen}
          title="Delete Class?"
          message="Are you sure you want to delete this class? This action cannot be undone."
          onConfirm={handleConfirmDelete}
          onCancel={() => {
            setIsConfirmOpen(false);
            setClassToDelete(null);
          }}
          dangerous
        />
      </div>
    </div>
  );
};

export default TrainerClasses;
