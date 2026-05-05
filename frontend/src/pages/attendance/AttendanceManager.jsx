import { useContext, useEffect, useMemo, useState } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { Card, LoadingSpinner, Badge, Button } from '@/components';
import { attendanceAPI, classesAPI } from '@/services/api';
import toast from 'react-hot-toast';
import { CalendarCheck2, Filter, Users, CheckCircle2, XCircle, CircleDashed } from 'lucide-react';
import { motion } from 'framer-motion';

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '-';
  return date.toLocaleDateString();
}

function formatTime(value) {
  if (!value) return '-';
  return String(value).slice(0, 5);
}

function getScheduleDate(schedule) {
  return schedule?.class_date || schedule?.date || '';
}

function getScheduleStart(schedule) {
  return schedule?.start_time || schedule?.class_time || '';
}

function getScheduleEnd(schedule) {
  return schedule?.end_time || schedule?.class_end_time || '';
}

function statusVariant(status) {
  if (status === 'Present') return 'success';
  if (status === 'Absent') return 'danger';
  if (status === 'Late') return 'warning';
  return 'default';
}

export default function AttendanceManager() {
  const { user } = useContext(AuthContext);
  const [loading, setLoading] = useState(true);
  const [rosterLoading, setRosterLoading] = useState(false);
  const [savingKey, setSavingKey] = useState('');
  const [classes, setClasses] = useState([]);
  const [selectedClassId, setSelectedClassId] = useState('');
  const [selectedDate, setSelectedDate] = useState('');
  const [selectedScheduleId, setSelectedScheduleId] = useState('');
  const [scheduleMeta, setScheduleMeta] = useState(null);
  const [summary, setSummary] = useState({ present: 0, absent: 0, late: 0, not_marked: 0, total: 0 });
  const [roster, setRoster] = useState([]);

  useEffect(() => {
    if (user?.role) {
      fetchClasses();
    }
  }, [user?.role, user?.trainer_id]);

  const fetchClasses = async () => {
    try {
      setLoading(true);
      const res = await classesAPI.list();
      const allClasses = Array.isArray(res?.data?.data) ? res.data.data : [];
      const scoped = user?.role === 'trainer'
        ? allClasses.filter((fitnessClass) => Number(fitnessClass.trainer_id) === Number(user?.trainer_id))
        : allClasses;

      setClasses(scoped);
      const firstClass = scoped[0];
      if (firstClass) {
        setSelectedClassId(String(firstClass.id));
      }
    } catch (error) {
      console.error('Failed to load classes', error);
      toast.error('Failed to load classes');
      setClasses([]);
    } finally {
      setLoading(false);
    }
  };

  const schedules = useMemo(() => {
    const scopedClasses = selectedClassId
      ? classes.filter((fitnessClass) => String(fitnessClass.id) === String(selectedClassId))
      : classes;

    return scopedClasses
      .flatMap((fitnessClass) => (fitnessClass.schedules || []).map((schedule) => ({
        ...schedule,
        class_name: fitnessClass.class_name,
        trainer_name: fitnessClass.trainer?.name || fitnessClass.trainer_name || 'TBD',
      })))
      .filter((schedule) => {
        if (!selectedDate) return true;
        const scheduleDate = String(getScheduleDate(schedule) || '').slice(0, 10);
        return scheduleDate === selectedDate;
      })
      .sort((left, right) => {
        const leftDate = new Date(left.class_date || 0).getTime();
        const rightDate = new Date(right.class_date || 0).getTime();
        if (leftDate !== rightDate) return rightDate - leftDate;
        return String(left.start_time || '').localeCompare(String(right.start_time || ''));
      });
  }, [classes, selectedClassId, selectedDate]);

  useEffect(() => {
    if (!schedules.length) {
      setSelectedScheduleId('');
      setScheduleMeta(null);
      setRoster([]);
      setSummary({ present: 0, absent: 0, late: 0, not_marked: 0, total: 0 });
      return;
    }

    const stillValid = schedules.some((schedule) => String(schedule.id) === String(selectedScheduleId));
    if (!stillValid) {
      setSelectedScheduleId(String(schedules[0].id));
    }
  }, [schedules, selectedScheduleId]);

  useEffect(() => {
    if (selectedScheduleId) {
      fetchRoster(selectedScheduleId);
    }
  }, [selectedScheduleId]);

  const fetchRoster = async (scheduleId) => {
    try {
      setRosterLoading(true);
      const res = await attendanceAPI.list({ schedule_id: scheduleId });
      const payload = res?.data?.data || {};
      setScheduleMeta(payload.schedule || null);
      setRoster(Array.isArray(payload.members) ? payload.members : []);
      setSummary(payload.summary || { present: 0, absent: 0, late: 0, not_marked: 0, total: 0 });
    } catch (error) {
      console.error('Failed to load attendance roster', error);
      toast.error(error.response?.data?.message || 'Failed to load attendance roster');
      setScheduleMeta(null);
      setRoster([]);
      setSummary({ present: 0, absent: 0, late: 0, not_marked: 0, total: 0 });
    } finally {
      setRosterLoading(false);
    }
  };

  const handleMark = async (member, status) => {
    if (!selectedScheduleId) {
      toast.error('Select a schedule first');
      return;
    }

    try {
      setSavingKey(`${member.member_id}-${status}`);
      await attendanceAPI.mark({
        member_id: member.member_id,
        schedule_id: Number(selectedScheduleId),
        attendance_status: status,
        recorded_at: new Date().toISOString(),
      });
      toast.success(`Marked ${member.name} as ${status}`);
      await fetchRoster(selectedScheduleId);
    } catch (error) {
      console.error('Failed to mark attendance', error);
      toast.error(error.response?.data?.message || 'Failed to mark attendance');
    } finally {
      setSavingKey('');
    }
  };

  const statusGroups = useMemo(() => ({
    present: roster.filter((row) => row.attendance_status === 'Present'),
    absent: roster.filter((row) => row.attendance_status === 'Absent'),
    late: roster.filter((row) => row.attendance_status === 'Late'),
    notMarked: roster.filter((row) => row.attendance_status === 'Not Marked'),
  }), [roster]);

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
        <motion.div initial={{ opacity: 0, y: -18 }} animate={{ opacity: 1, y: 0 }} className="mb-8">
          <div className="flex items-center gap-3 mb-2">
            <CalendarCheck2 className="text-gold-400" size={34} />
            <h1 className="text-4xl font-bold text-white">Attendance Manager</h1>
          </div>
          <p className="text-gray-400">Mark members as present or absent for a specific class schedule.</p>
        </motion.div>

        <Card className="mb-6">
          <div className="grid lg:grid-cols-4 gap-4 items-end">
            <div>
              <label className="block text-sm font-medium text-gray-300 mb-2">Class</label>
              <select
                value={selectedClassId}
                onChange={(e) => {
                  setSelectedClassId(e.target.value);
                  setSelectedScheduleId('');
                  setScheduleMeta(null);
                  setRoster([]);
                }}
                className="w-full rounded-lg bg-dark-secondary border border-gold-600/20 px-3 py-3 text-white"
              >
                <option value="">All classes</option>
                {classes.map((fitnessClass) => (
                  <option key={fitnessClass.id} value={fitnessClass.id}>{fitnessClass.class_name}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-300 mb-2">Date</label>
              <input
                type="date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                className="w-full rounded-lg bg-dark-secondary border border-gold-600/20 px-3 py-3 text-white"
              />
            </div>

            <div className="lg:col-span-2">
              <label className="block text-sm font-medium text-gray-300 mb-2">Schedule</label>
              <select
                value={selectedScheduleId}
                onChange={(e) => setSelectedScheduleId(e.target.value)}
                className="w-full rounded-lg bg-dark-secondary border border-gold-600/20 px-3 py-3 text-white"
              >
                <option value="">Select a schedule</option>
                {schedules.map((schedule) => (
                  <option key={schedule.id} value={schedule.id}>
                    {schedule.class_name} - {formatDate(getScheduleDate(schedule))} {formatTime(getScheduleStart(schedule))} to {formatTime(getScheduleEnd(schedule))}
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div className="mt-4 flex items-center gap-2 text-sm text-gray-400">
            <Filter size={16} />
            Use class and date filters to narrow the schedule list.
          </div>
        </Card>

        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
          <Card>
            <div className="text-gray-400 text-sm mb-1">Total</div>
            <div className="text-3xl font-bold text-white">{summary.total}</div>
          </Card>
          <Card>
            <div className="text-gray-400 text-sm mb-1">Present</div>
            <div className="text-3xl font-bold text-green-300">{summary.present}</div>
          </Card>
          <Card>
            <div className="text-gray-400 text-sm mb-1">Absent</div>
            <div className="text-3xl font-bold text-red-300">{summary.absent}</div>
          </Card>
          
        </div>

        {scheduleMeta ? (
          <Card className="mb-6 border border-gold-600/20">
            <div className="flex flex-wrap items-center gap-4 justify-between">
              <div>
                <div className="text-xs uppercase tracking-wide text-gold-300 mb-1">Selected Schedule</div>
                <h2 className="text-2xl font-bold text-white">
                  {scheduleMeta.class_name || 'Class'}
                </h2>
                <p className="text-gray-400 mt-1">
                  {formatDate(getScheduleDate(scheduleMeta))} · {formatTime(getScheduleStart(scheduleMeta))} - {formatTime(getScheduleEnd(scheduleMeta))}
                </p>
              </div>
              <div className="flex items-center gap-3 text-gray-300">
                <Users size={18} />
                <span>{summary.total} members</span>
              </div>
            </div>
          </Card>
        ) : (
          <Card className="mb-6 border border-dashed border-gold-600/20">
            <div className="text-gray-400">Choose a class schedule to load the attendance roster.</div>
          </Card>
        )}

        <Card className="overflow-hidden">
          {rosterLoading ? (
            <div className="py-16 flex items-center justify-center">
              <LoadingSpinner />
            </div>
          ) : roster.length === 0 ? (
            <div className="py-16 text-center text-gray-400">
              No roster found for this schedule yet.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gold-600/20 bg-dark-secondary">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Member</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Email</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Plan</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Status</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Recorded</th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {roster.map((member) => {
                    const isPresent = member.attendance_status === 'Present';
                    const isAbsent = member.attendance_status === 'Absent';
                    const isLate = member.attendance_status === 'Late';
                    const rowClass = isAbsent ? 'bg-red-500/5' : isPresent ? 'bg-green-500/5' : isLate ? 'bg-yellow-500/5' : '';

                    return (
                      <tr key={member.member_id} className={`border-b border-gray-700 hover:bg-dark-secondary transition ${rowClass}`}>
                        <td className="px-6 py-4 text-white font-medium">{member.name}</td>
                        <td className="px-6 py-4 text-gray-300 text-sm">{member.email || '-'}</td>
                        <td className="px-6 py-4 text-gray-300 text-sm">{member.plan_name || '-'}</td>
                        <td className="px-6 py-4">
                          <Badge variant={statusVariant(member.attendance_status)}>{member.attendance_status}</Badge>
                        </td>
                        <td className="px-6 py-4 text-gray-300 text-sm">{formatDate(member.recorded_at)}</td>
                        <td className="px-6 py-4">
                          <div className="flex flex-wrap items-center gap-2">
                            <Button
                              type="button"
                              size="sm"
                              variant="success"
                              isLoading={savingKey === `${member.member_id}-Present`}
                              onClick={() => handleMark(member, 'Present')}
                            >
                              <CheckCircle2 size={16} />
                              Present
                            </Button>
                            <Button
                              type="button"
                              size="sm"
                              variant="danger"
                              isLoading={savingKey === `${member.member_id}-Absent`}
                              onClick={() => handleMark(member, 'Absent')}
                            >
                              <XCircle size={16} />
                              Absent
                            </Button>
                            
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </Card>
      </div>
    </div>
  );
}
