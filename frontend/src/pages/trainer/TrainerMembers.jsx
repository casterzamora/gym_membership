import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { Card, LoadingSpinner } from '@/components';
import api from '@/services/api';
import { Users } from 'lucide-react';

const TrainerMembers = () => {
  const { user } = useContext(AuthContext);
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    if (user?.id) {
      fetchMembers();
    }
  }, [user?.id]);

  const fetchMembers = async () => {
    try {
      setLoading(true);
      
      const classesRes = await api.classesAPI.list();
      const allClasses = classesRes.data.data || [];
      const myClasses = allClasses.filter(c => c.trainer_id === user?.id);
      const myClassIds = myClasses.map(c => c.id);

      const schedulesRes = await api.schedulesAPI.list();
      const allSchedules = schedulesRes.data.data || [];
      const mySchedules = allSchedules.filter(s => myClassIds.includes(s.class_id));

      const attendanceRes = await api.attendanceAPI.list();
      const allAttendance = attendanceRes.data.data || [];
      
      const membersRes = await api.membersAPI.list();
      const allMembers = membersRes.data.data || [];

      const memberMap = new Map();
      
      allAttendance.forEach(attendance => {
        const schedule = allSchedules.find(s => s.id === attendance.schedule_id);
        if (!schedule) return;
        
        const member = allMembers.find(m => m.id === attendance.member_id);
        if (!member) return;

        const classInfo = myClasses.find(c => c.id === schedule.class_id);
        if (!classInfo) return;

        const key = member.id;
        if (!memberMap.has(key)) {
          memberMap.set(key, {
            id: member.id,
            name: member.name,
            email: member.email,
            phone: member.phone,
            classesEnrolled: [],
            attendanceCount: 0,
          });
        }

        const memberData = memberMap.get(key);
        if (!memberData.classesEnrolled.includes(classInfo.name)) {
          memberData.classesEnrolled.push(classInfo.name);
        }
        memberData.attendanceCount++;
      });

      const membersList = Array.from(memberMap.values());
      setMembers(membersList);
    } catch (err) {
      console.error('Failed to load members', err);
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

  const filteredMembers = members.filter(member =>
    member.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    member.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-6xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center gap-3 mb-2">
            <Users size={32} className="text-gold-bright" />
            <h1 className="text-4xl font-bold text-white">My Students</h1>
          </div>
          <p className="text-gray-400">Members enrolled in your classes</p>
        </div>

        {/* Search Bar */}
        <Card className="mb-6">
          <input
            type="text"
            placeholder="Search by name or email..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full px-4 py-3 bg-dark-secondary border border-gold-bright/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-gold-bright"
          />
        </Card>

        {/* Members Table */}
        {filteredMembers.length === 0 ? (
          <Card>
            <div className="p-12 text-center">
              <p className="text-gray-400">No students enrolled in your classes yet</p>
            </div>
          </Card>
        ) : (
          <Card className="overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gold-bright/20 bg-dark-secondary">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Member Name</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Email</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Phone</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Classes Enrolled</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-bright">Attendances</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredMembers.map((member) => (
                    <tr key={member.id} className="border-b border-gray-700 hover:bg-dark-secondary transition">
                      <td className="px-6 py-4 text-white font-medium">{member.name}</td>
                      <td className="px-6 py-4 text-gray-300 text-sm">{member.email}</td>
                      <td className="px-6 py-4 text-gray-300 text-sm">{member.phone || '-'}</td>
                      <td className="px-6 py-4">
                        <div className="flex flex-wrap gap-1">
                          {member.classesEnrolled.map((className) => (
                            <span 
                              key={className}
                              className="text-xs bg-blue-500/20 text-blue-300 px-2 py-1 rounded border border-blue-500/30"
                            >
                              {className}
                            </span>
                          ))}
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-block bg-green-500/20 text-green-300 px-3 py-1 rounded font-bold border border-green-500/30">
                          {member.attendanceCount}
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
