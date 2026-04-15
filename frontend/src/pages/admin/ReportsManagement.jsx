import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import api from '@/services/api';

const ReportsManagement = () => {
  const [loading, setLoading] = useState(true);
  const [revenue, setRevenue] = useState([]);
  const [classPopularity, setClassPopularity] = useState([]);
  const [lowAttendanceMembers, setLowAttendanceMembers] = useState([]);
  const [filters, setFilters] = useState({
    from: '',
    to: '',
    group_by: 'month',
    limit: 10,
  });

  useEffect(() => {
    fetchReports();
  }, [filters.from, filters.to, filters.group_by, filters.limit]);

  const fetchReports = async () => {
    try {
      setLoading(true);
      const dateParams = {
        ...(filters.from ? { from: filters.from } : {}),
        ...(filters.to ? { to: filters.to } : {}),
      };
      const [revenueRes, classRes, lowAttendanceRes] = await Promise.all([
        api.reportsAPI.revenue({ ...dateParams, group_by: filters.group_by }).catch(() => ({ data: { data: { series: [] } } })),
        api.reportsAPI.classPopularity({ limit: filters.limit }).catch(() => ({ data: { data: [] } })),
        api.reportsAPI.lowAttendanceMembers({ ...dateParams, limit: filters.limit }).catch(() => ({ data: { data: { members: [] } } })),
      ]);

      setRevenue(revenueRes.data?.data?.series || []);
      setClassPopularity(classRes.data?.data || []);
      setLowAttendanceMembers(lowAttendanceRes.data?.data?.members || []);
    } catch (error) {
      console.error('Failed to load reports', error);
    } finally {
      setLoading(false);
    }
  };

  const toCsvValue = (value) => {
    const text = value === null || value === undefined ? '' : String(value);
    return `"${text.replace(/"/g, '""')}"`;
  };

  const downloadCsv = (filename, headers, rows) => {
    const csvLines = [
      headers.map(toCsvValue).join(','),
      ...rows.map((row) => row.map(toCsvValue).join(',')),
    ];

    const blob = new Blob([csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
  };

  const exportRevenueCsv = () => {
    const rows = revenue.map((row) => [
      row.period,
      Number(row.total_revenue || 0).toFixed(2),
      row.payment_count,
    ]);

    downloadCsv('revenue_report.csv', ['Period', 'Revenue', 'Payments'], rows);
  };

  const exportClassPopularityCsv = () => {
    const rows = classPopularity.map((row) => [
      row.class_name,
      row.total_schedules,
      row.total_attendance_records,
      Number(row.average_attendance_per_schedule || 0).toFixed(2),
      `${Number(row.capacity_utilization_percent || 0).toFixed(2)}%`,
    ]);

    downloadCsv(
      'class_popularity_report.csv',
      ['Class', 'Schedules', 'Attendance', 'Avg per Schedule', 'Capacity Utilization'],
      rows
    );
  };

  const exportLowAttendanceCsv = () => {
    const rows = lowAttendanceMembers.map((row) => [
      `${row.first_name} ${row.last_name}`,
      row.email,
      row.present_count,
    ]);

    downloadCsv('low_attendance_members_report.csv', ['Member', 'Email', 'Present Count'], rows);
  };

  return (
    <div className="space-y-8">
      <motion.div initial={{ opacity: 0, y: -16 }} animate={{ opacity: 1, y: 0 }}>
        <h1 className="text-3xl font-bold text-white">Reports Center</h1>
        <p className="text-gray-400 mt-1">Revenue, class demand, and member engagement insights.</p>
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <h2 className="text-lg font-bold text-white mb-4">Filters</h2>
          <div className="grid md:grid-cols-4 gap-3">
            <input
              type="date"
              value={filters.from}
              onChange={(e) => setFilters({ ...filters, from: e.target.value })}
              className="w-full px-3 py-2 rounded bg-dark-secondary border border-gray-700 text-gray-100"
            />
            <input
              type="date"
              value={filters.to}
              onChange={(e) => setFilters({ ...filters, to: e.target.value })}
              className="w-full px-3 py-2 rounded bg-dark-secondary border border-gray-700 text-gray-100"
            />
            <select
              value={filters.group_by}
              onChange={(e) => setFilters({ ...filters, group_by: e.target.value })}
              className="w-full px-3 py-2 rounded bg-dark-secondary border border-gray-700 text-gray-100"
            >
              <option value="month">Group by Month</option>
              <option value="day">Group by Day</option>
            </select>
            <input
              type="number"
              min="1"
              max="50"
              value={filters.limit}
              onChange={(e) => setFilters({ ...filters, limit: Number(e.target.value || 10) })}
              className="w-full px-3 py-2 rounded bg-dark-secondary border border-gray-700 text-gray-100"
            />
          </div>
        </div>
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-bold text-white">Revenue by Month</h2>
            {!loading && revenue.length > 0 && (
              <button
                onClick={exportRevenueCsv}
                className="px-4 py-2 text-sm font-semibold rounded bg-gold-600 text-black hover:bg-gold-500 transition"
              >
                Export CSV
              </button>
            )}
          </div>
          {loading ? (
            <div className="text-gray-400 py-8 text-center">Loading report data...</div>
          ) : revenue.length === 0 ? (
            <div className="text-gray-400 py-8 text-center">No revenue data available.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b border-gray-700 text-gray-300 text-sm">
                    <th className="py-3 pr-4">Period</th>
                    <th className="py-3 pr-4">Revenue</th>
                    <th className="py-3 pr-4">Payments</th>
                  </tr>
                </thead>
                <tbody>
                  {revenue.map((row) => (
                    <tr key={row.period} className="border-b border-gray-800 text-sm text-gray-100">
                      <td className="py-3 pr-4">{row.period}</td>
                      <td className="py-3 pr-4">${Number(row.total_revenue || 0).toFixed(2)}</td>
                      <td className="py-3 pr-4">{row.payment_count}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-bold text-white">Class Popularity</h2>
            {!loading && classPopularity.length > 0 && (
              <button
                onClick={exportClassPopularityCsv}
                className="px-4 py-2 text-sm font-semibold rounded bg-gold-600 text-black hover:bg-gold-500 transition"
              >
                Export CSV
              </button>
            )}
          </div>
          {loading ? (
            <div className="text-gray-400 py-8 text-center">Loading report data...</div>
          ) : classPopularity.length === 0 ? (
            <div className="text-gray-400 py-8 text-center">No class popularity data available.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b border-gray-700 text-gray-300 text-sm">
                    <th className="py-3 pr-4">Class</th>
                    <th className="py-3 pr-4">Schedules</th>
                    <th className="py-3 pr-4">Attendance</th>
                    <th className="py-3 pr-4">Avg / Schedule</th>
                    <th className="py-3 pr-4">Capacity Utilization</th>
                  </tr>
                </thead>
                <tbody>
                  {classPopularity.map((row) => (
                    <tr key={row.class_id} className="border-b border-gray-800 text-sm text-gray-100">
                      <td className="py-3 pr-4 font-medium">{row.class_name}</td>
                      <td className="py-3 pr-4">{row.total_schedules}</td>
                      <td className="py-3 pr-4">{row.total_attendance_records}</td>
                      <td className="py-3 pr-4">{Number(row.average_attendance_per_schedule || 0).toFixed(2)}</td>
                      <td className="py-3 pr-4">{Number(row.capacity_utilization_percent || 0).toFixed(2)}%</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </motion.div>

      <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
        <div className="bg-gray-900/50 border border-gray-700 rounded-xl shadow-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-bold text-white">Low Attendance Members</h2>
            {!loading && lowAttendanceMembers.length > 0 && (
              <button
                onClick={exportLowAttendanceCsv}
                className="px-4 py-2 text-sm font-semibold rounded bg-gold-600 text-black hover:bg-gold-500 transition"
              >
                Export CSV
              </button>
            )}
          </div>
          {loading ? (
            <div className="text-gray-400 py-8 text-center">Loading report data...</div>
          ) : lowAttendanceMembers.length === 0 ? (
            <div className="text-gray-400 py-8 text-center">No low-attendance members found.</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b border-gray-700 text-gray-300 text-sm">
                    <th className="py-3 pr-4">Member</th>
                    <th className="py-3 pr-4">Email</th>
                    <th className="py-3 pr-4">Present Count</th>
                  </tr>
                </thead>
                <tbody>
                  {lowAttendanceMembers.map((row) => (
                    <tr key={row.member_id} className="border-b border-gray-800 text-sm text-gray-100">
                      <td className="py-3 pr-4 font-medium">{row.first_name} {row.last_name}</td>
                      <td className="py-3 pr-4">{row.email}</td>
                      <td className="py-3 pr-4">{row.present_count}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </motion.div>
    </div>
  );
};

export default ReportsManagement;
