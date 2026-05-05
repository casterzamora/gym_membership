import React, { useEffect, useMemo, useState, useContext } from 'react';
import { Card, LoadingSpinner, FormModal } from '@/components';
import { AuthContext } from '@/context/AuthContext';
import api from '@/services/api';
import toast from 'react-hot-toast';
import { Users } from 'lucide-react';

const TrainerMembers = () => {
  const { user } = useContext(AuthContext);
  const [members, setMembers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [trainerClasses, setTrainerClasses] = useState([]);
  const [isEnrollOpen, setIsEnrollOpen] = useState(false);
  const [enrollMember, setEnrollMember] = useState(null);
  const [enrollClassId, setEnrollClassId] = useState('');
  const [scheduleOptions, setScheduleOptions] = useState([]);
  const [selectedSchedule, setSelectedSchedule] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [isSuggesting, setIsSuggesting] = useState(false);
  const [suggestingQuery, setSuggestingQuery] = useState('');
  const [suggestTimer, setSuggestTimer] = useState(null);

  useEffect(() => {
    if (user?.trainer_id) {
      fetchMembers();
      fetchTrainerClasses();
    }
  }, [user?.trainer_id]);


  useEffect(() => {
    let mounted = true;
    const q = suggestingQuery;
    if (!q || q.length < 2) {
      setSuggestions([]);
      setIsSuggesting(false);
      return;
    }

    (async () => {
      try {
        setIsSuggesting(true);
        const res = await api.membersAPI.search(q);
        if (!mounted) return;
        const list = res?.data?.data || [];
        setSuggestions(list);
      } catch (e) {
        setSuggestions([]);
      }
    })();

    return () => { mounted = false; };
  }, [suggestingQuery]);

  // Fetch upcoming schedules for the selected class when enrollClassId changes
  useEffect(() => {
    let mounted = true;
    const classId = enrollClassId;
    if (!classId) {
      setScheduleOptions([]);
      setSelectedSchedule('');
      return;
    }

    (async () => {
      try {
        const res = await api.request.get('/v1/schedules', { params: { class_id: classId } });
        if (!mounted) return;
        const payload = res?.data?.data || [];
        // Filter for upcoming schedules (server expects class_date >= today for check-in)
        const upcoming = payload.filter(s => new Date(s.class_date) >= new Date(new Date().toDateString()));
        setScheduleOptions(upcoming);
        setSelectedSchedule(upcoming.length ? String(upcoming[0].id) : '');
      } catch (e) {
        setScheduleOptions([]);
        setSelectedSchedule('');
      }
    })();

    return () => { mounted = false; };
  }, [enrollClassId]);

  const fetchTrainerClasses = async () => {
    try {
      const res = await api.classesAPI.list();
      const all = res?.data?.data || [];
      const mine = all.filter(c => Number(c.trainer_id) === Number(user.trainer_id));
      setTrainerClasses(mine);
    } catch (e) {
      setTrainerClasses([]);
    }
  }

  const fetchMembers = async () => {
    try {
      setLoading(true);
      const membersRes = await api.membersAPI.list();
      
      const payload = membersRes?.data?.data || [];
      const membersList = Array.isArray(payload) ? payload : [];
      setMembers(membersList);

      // For any members missing plan_name, fetch full member detail and merge plan_name
      const missingPlanIds = membersList.filter(m => !m.plan_name && !m.plan?.plan_name).map(m => m.id);
      if (missingPlanIds.length) {
        try {
          const detailPromises = missingPlanIds.map(id => api.membersAPI.get(id).catch(() => null));
          const results = await Promise.all(detailPromises);
          const updates = {};
          results.forEach((res) => {
            const mem = res?.data?.data;
            if (mem && mem.id) updates[mem.id] = mem.plan_name || mem.plan?.plan_name || null;
          });
          if (Object.keys(updates).length) {
            setMembers(prev => prev.map(m => ({ ...m, plan_name: updates[m.id] ?? m.plan_name })));
          }
        } catch (e) {
          // ignore detail fetch errors
        }
      }
    } catch (err) {
      console.error('Failed to load members', err);
      console.error('Error details:', err.response?.data);
      setMembers([]);
    } finally {
      setLoading(false);
    }
  };

  const handleUnenroll = async (member) => {
    const ok = window.confirm(`Remove ${member.first_name} ${member.last_name} from your students?`);
    if (!ok) return;

    try {
      setLoading(true);
      await api.attendanceAPI.unenrollMember(member.id);
      toast.success('Member unenrolled');
      await fetchMembers();
    } catch (e) {
      console.error('Unenroll failed', e);
      const message = e.response?.data?.message || e.message || 'Failed to unenroll member';
      toast.error(message);
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

        <div className="flex items-center gap-4 mb-6">
          <Card className="flex-1 relative">
            <input
              type="text"
              placeholder="Search members by name or email to add..."
              value={searchTerm}
              onChange={(e) => {
                const v = e.target.value;
                setSearchTerm(v);
                if (suggestTimer) clearTimeout(suggestTimer);
                const t = setTimeout(() => setSuggestingQuery(v.trim()), 300);
                setSuggestTimer(t);
              }}
              className="w-full px-4 py-3 bg-dark-secondary border border-gold-600/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-gold-500"
            />

            {suggestions.length > 0 && isSuggesting && !isEnrollOpen && (
              <div className="absolute left-3 right-3 mt-2 bg-dark-secondary border border-gold-600/20 rounded shadow" style={{ zIndex: 1000 }}>
                {suggestions.map(s => (
                  <div key={s.id} className="px-4 py-3 hover:bg-dark-bg cursor-pointer" onClick={async () => {
                    const ok = window.confirm(`Add ${s.name} as a student?`);
                    if (!ok) return;
                    try {
                      setLoading(true);
                      const payload = { user_id: s.id, first_name: s.first_name || '', last_name: s.last_name || '' };
                      const createRes = await api.membersAPI.create(payload);
                      const created = createRes?.data?.data || null;

                      if (created && created.id) {
                        toast.success('Student added — choose a class to enroll');
                        setSearchTerm('');
                        setSuggestions([]);
                        setIsSuggesting(false);
                        setEnrollMember(created);
                        if (trainerClasses && trainerClasses.length) {
                          setEnrollClassId(String(trainerClasses[0].id));
                        } else {
                          setEnrollClassId('');
                        }
                        setIsEnrollOpen(true);
                        await fetchMembers();
                      } else {
                        toast.error('Student created but could not determine member id');
                      }
                    } catch (e) {
                      console.error('Add student failed', e);
                      const status = e.response?.status;
                      const body = e.response?.data;
                      const message = body?.message || e.message || 'Failed to add student';
                      toast.error(`${status ?? ''} ${message}`);
                      console.error('Add student error body:', body);
                    } finally {
                      setLoading(false);
                    }
                  }}>
                    <div className="text-white font-medium">{s.name}</div>
                    <div className="text-gray-400 text-sm">{s.email}</div>
                  </div>
                ))}
              </div>
            )}
          </Card>
        </div>

        {!isSuggesting && filteredMembers.length === 0 && searchTerm.trim().length === 0 && (
          <Card>
            <div className="p-6 text-center">
              <p className="text-gray-400">No students found for your current class assignments.</p>
            </div>
          </Card>
        )}

        {!isSuggesting && !(filteredMembers.length === 0 && searchTerm.trim().length === 0) && (
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
                      <td className="px-6 py-4 text-gray-300 text-sm">{member.plan?.plan_name || member.plan_name || '-'}</td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2">
                          <span className="inline-block bg-green-500/20 text-green-300 px-3 py-1 rounded font-bold border border-green-500/30">
                            {Array.isArray(member.attendances) ? member.attendances.length : 0}
                          </span>
                          <button
                            onClick={() => handleUnenroll(member)}
                            className="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-500"
                          >
                            Unenroll
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

        {/* Enroll Modal */}
        <FormModal
          isOpen={isEnrollOpen}
          title={enrollMember ? `Enroll ${enrollMember.first_name}` : 'Enroll Member'}
          onClose={() => { setIsEnrollOpen(false); setEnrollMember(null); setEnrollClassId(''); }}
          onSubmit={async (e) => {
            e.preventDefault();
            if (!enrollMember || !enrollClassId) {
              toast.error('Select a class');
              return;
            }
            if (scheduleOptions.length === 0) {
              toast.error('No upcoming schedules to enroll into for this class');
              return;
            }
            try {
              setLoading(true);
              // If a specific schedule was selected, send schedule_id to target it directly.
              if (selectedSchedule) {
                await api.attendanceAPI.checkIn({ member_id: enrollMember.id, schedule_id: Number(selectedSchedule) });
              } else {
                // Fallback to class_id (backend will resolve next upcoming schedule)
                await api.attendanceAPI.checkIn({ member_id: enrollMember.id, class_id: Number(enrollClassId) });
              }
              toast.success('Member enrolled');
              setIsEnrollOpen(false);
              setEnrollMember(null);
              setEnrollClassId('');
              setSelectedSchedule('');
              await fetchMembers();
            } catch (e) {
              console.error('Enroll failed', e);
              const status = e.response?.status;
              const body = e.response?.data;
              const message = body?.message || e.message || 'Failed to enroll member';
              toast.error(`${status ?? ''} ${message}`);
              console.error('Enroll error body:', body);
            } finally {
              setLoading(false);
            }
          }}
          submitLabel="Enroll"
        >
          <div className="mb-4">
            <label className="block text-sm text-gray-300 mb-2">Select Class</label>
            <select value={enrollClassId} onChange={(e) => setEnrollClassId(e.target.value)} className="w-full bg-dark-secondary px-3 py-2 rounded">
              <option value="">-- Select class --</option>
              {trainerClasses.map(c => (
                <option key={c.id} value={c.id}>{c.class_name} ({c.max_participants} cap)</option>
              ))}
            </select>
            {enrollClassId && scheduleOptions.length === 0 && (
              <p className="mt-2 text-sm text-red-400">No upcoming schedules found for this class. Create a schedule first or choose another class.</p>
            )}
            {enrollClassId && scheduleOptions.length > 0 && (
              <div className="mt-3">
                <label className="block text-sm text-gray-300 mb-2">Upcoming Schedule</label>
                <select value={selectedSchedule} onChange={(e) => setSelectedSchedule(e.target.value)} className="w-full bg-dark-secondary px-3 py-2 rounded">
                  {scheduleOptions.map(s => (
                    <option key={s.id} value={s.id}>{s.class_date} {s.start_time} ({s.max_participants || 'cap'})</option>
                  ))}
                </select>
              </div>
            )}
          </div>
        </FormModal>
      </div>
    </div>
  );
};

export default TrainerMembers;
