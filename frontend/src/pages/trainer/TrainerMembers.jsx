import React, { useEffect, useMemo, useState } from 'react';
import { Card, LoadingSpinner } from '@/components';
import api from '@/services/api';
import { Users } from 'lucide-react';

const TrainerMembers = () => {
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchMembers();
  }, []);

  const fetchMembers = async () => {
    try {
      setLoading(true);
      const membersRes = await api.membersAPI.list();
      const payload = membersRes?.data?.data || [];
      setMembers(Array.isArray(payload) ? payload : []);
    } catch (err) {
      console.error('Failed to load members', err);
      setMembers([]);
    } finally {
      setLoading(false);
    }
  };

  const filteredMembers = useMemo(
    () => members.filter((member) => {
      const fullName = `${member.first_name || ''} ${member.last_name || ''}`.toLowerCase();
      const email = (member.email || '').toLowerCase();
      const needle = searchTerm.toLowerCase();
      return fullName.includes(needle) || email.includes(needle);
    }),
    [members, searchTerm]
  );

  if (loading) {
    return (
      <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center">
        <LoadingSpinner />
      </div>
    );
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-6xl mx-auto px-4 py-8">
        <div className="mb-8">
          <div className="flex items-center gap-3 mb-2">
            <Users size={32} className="text-gold-400" />
            <h1 className="text-4xl font-bold text-white">My Students</h1>
          </div>
          <p className="text-gray-400">Members currently linked to your classes</p>
        </div>

        <Card className="mb-6">
          <input
            type="text"
            placeholder="Search by name or email..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full px-4 py-3 bg-dark-secondary border border-gold-600/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-gold-500"
          />
        </Card>

        {filteredMembers.length === 0 ? (
          <Card>
            <div className="p-12 text-center">
              <p className="text-gray-400">No students found for your current class assignments.</p>
            </div>
          </Card>
        ) : (
          <Card className="overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gold-600/20 bg-dark-secondary">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Member Name</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Email</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Phone</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Plan</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Attendances</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredMembers.map((member) => (
                    <tr key={member.id} className="border-b border-gray-700 hover:bg-dark-secondary transition">
                      <td className="px-6 py-4 text-white font-medium">{`${member.first_name || ''} ${member.last_name || ''}`.trim()}</td>
                      <td className="px-6 py-4 text-gray-300 text-sm">{member.email}</td>
                      <td className="px-6 py-4 text-gray-300 text-sm">{member.phone || '-'}</td>
                      <td className="px-6 py-4 text-gray-300 text-sm">{member.plan?.plan_name || '-'}</td>
                      <td className="px-6 py-4">
                        <span className="inline-block bg-green-500/20 text-green-300 px-3 py-1 rounded font-bold border border-green-500/30">
                          {Array.isArray(member.attendances) ? member.attendances.length : 0}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </Card>
        )}
      </div>
    </div>
  );
};

export default TrainerMembers;
