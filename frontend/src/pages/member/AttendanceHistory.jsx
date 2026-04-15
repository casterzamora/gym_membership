import { useContext, useEffect, useMemo, useState } from 'react';
import { AuthContext } from '@/context/AuthContext';
import { attendanceAPI, classesAPI } from '@/services/api';
import { Card, LoadingSpinner, Badge } from '@/components';
import { CalendarCheck2 } from 'lucide-react';

export default function AttendanceHistory() {
  const { user } = useContext(AuthContext);
  const [loading, setLoading] = useState(true);
  const [records, setRecords] = useState([]);
  const [classNameBySchedule, setClassNameBySchedule] = useState({});

  useEffect(() => {
    if (user?.id) {
      fetchHistory();
    }
  }, [user?.id]);

  const fetchHistory = async () => {
    try {
      setLoading(true);

      const [attendanceRes, classesRes] = await Promise.all([
        attendanceAPI.list(),
        classesAPI.list(),
      ]);

      const allAttendance = attendanceRes?.data?.data || [];
      const allClasses = classesRes?.data?.data || [];

      const scheduleToClass = {};
      allClasses.forEach((fitnessClass) => {
        (fitnessClass.schedules || []).forEach((schedule) => {
          scheduleToClass[schedule.id] = fitnessClass.class_name;
        });
      });

      const mine = allAttendance
        .filter((row) => Number(row.member_id) === Number(user.id))
        .sort((a, b) => {
          const left = new Date(a.recorded_at || a.created_at || 0).getTime();
          const right = new Date(b.recorded_at || b.created_at || 0).getTime();
          return right - left;
        });

      setClassNameBySchedule(scheduleToClass);
      setRecords(mine);
    } catch (error) {
      setRecords([]);
      setClassNameBySchedule({});
    } finally {
      setLoading(false);
    }
  };

  const stats = useMemo(() => {
    const present = records.filter((r) => r.attendance_status === 'Present').length;
    const late = records.filter((r) => r.attendance_status === 'Late').length;
    const absent = records.filter((r) => r.attendance_status === 'Absent').length;
    return { present, late, absent };
  }, [records]);

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
          <h1 className="text-4xl font-bold text-white mb-2 flex items-center gap-3">
            <CalendarCheck2 className="text-gold-400" />
            Attendance History
          </h1>
          <p className="text-gray-400">Your class attendance records and status history.</p>
        </div>

        <div className="grid md:grid-cols-3 gap-4 mb-6">
          <Card>
            <div className="text-gray-400 text-sm">Present</div>
            <div className="text-3xl font-bold text-green-300">{stats.present}</div>
          </Card>
          <Card>
            <div className="text-gray-400 text-sm">Late</div>
            <div className="text-3xl font-bold text-yellow-300">{stats.late}</div>
          </Card>
          <Card>
            <div className="text-gray-400 text-sm">Absent</div>
            <div className="text-3xl font-bold text-red-300">{stats.absent}</div>
          </Card>
        </div>

        <Card className="overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-gold-600/20 bg-dark-secondary">
                  <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Date</th>
                  <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Class</th>
                  <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Schedule ID</th>
                  <th className="px-6 py-4 text-left text-sm font-semibold text-gold-300">Status</th>
                </tr>
              </thead>
              <tbody>
                {records.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="px-6 py-8 text-center text-gray-500">No attendance records yet.</td>
                  </tr>
                ) : (
                  records.map((row, index) => (
                    <tr key={`${row.member_id}-${row.schedule_id}-${index}`} className="border-b border-gray-700 hover:bg-dark-secondary transition">
                      <td className="px-6 py-4 text-gray-100">
                        {new Date(row.recorded_at || row.created_at || Date.now()).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 text-gray-200">{classNameBySchedule[row.schedule_id] || 'Class'}</td>
                      <td className="px-6 py-4 text-gray-400">{row.schedule_id}</td>
                      <td className="px-6 py-4">
                        <Badge
                          variant={
                            row.attendance_status === 'Present'
                              ? 'success'
                              : row.attendance_status === 'Late'
                                ? 'warning'
                                : 'danger'
                          }
                        >
                          {row.attendance_status || 'Unknown'}
                        </Badge>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </Card>
      </div>
    </div>
  );
}
