import React, { useState, useMemo } from 'react';
import { ChevronLeft, ChevronRight, Search, Edit2, Trash2, Eye } from 'lucide-react';
import Button from './Button';

const DataTable = ({ 
  columns, 
  data, 
  title, 
  onEdit, 
  onDelete, 
  onView,
  loading,
  searchFields = []
}) => {
  const [currentPage, setCurrentPage] = useState(1);
  const [searchTerm, setSearchTerm] = useState('');
  const itemsPerPage = 10;
  const safeData = Array.isArray(data) ? data : [];

  const filteredData = useMemo(() => {
    if (!searchTerm || searchFields.length === 0) return safeData;
    
    return safeData.filter(item => 
      searchFields.some(field => 
        String(item[field]).toLowerCase().includes(searchTerm.toLowerCase())
      )
    );
  }, [safeData, searchTerm, searchFields]);

  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const paginatedData = filteredData.slice(startIndex, startIndex + itemsPerPage);

  const handlePrevPage = () => setCurrentPage(prev => Math.max(prev - 1, 1));
  const handleNextPage = () => setCurrentPage(prev => Math.min(prev + 1, totalPages));

  if (loading) {
    return <div className="text-center py-8 text-gray-400">Loading...</div>;
  }

  return (
    <div className="bg-dark-card border border-gold-600/20 rounded-lg shadow-xl shadow-black/20 overflow-hidden">
      {/* Header */}
      <div className="p-6 border-b border-gray-800">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-bold text-white">{title}</h2>
        </div>
        
        {searchFields.length > 0 && (
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500" size={20} />
            <input
              type="text"
              placeholder={`Search ${searchFields.join(', ')}...`}
              value={searchTerm}
              onChange={(e) => {
                setSearchTerm(e.target.value);
                setCurrentPage(1);
              }}
              className="w-full pl-10 pr-4 py-2.5 bg-dark-secondary border border-gray-700 text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold-600/60 focus:border-gold-600/60"
            />
          </div>
        )}
      </div>

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-black/25 border-b border-gray-800">
            <tr>
              {columns.map(col => (
                <th 
                  key={col.key}
                  className="px-6 py-3 text-left text-sm font-semibold text-gray-300"
                >
                  {col.label}
                </th>
              ))}
              <th className="px-6 py-3 text-left text-sm font-semibold text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            {paginatedData.length === 0 ? (
              <tr>
                <td colSpan={columns.length + 1} className="px-6 py-8 text-center text-gray-500">
                  No records found
                </td>
              </tr>
            ) : (
              paginatedData.map((item, idx) => (
                <tr key={idx} className="border-b border-gray-800 hover:bg-black/20 transition">
                  {columns.map(col => (
                    <td key={col.key} className="px-6 py-4 text-sm text-gray-100">
                      {col.render ? col.render(item[col.key], item) : item[col.key]}
                    </td>
                  ))}
                  <td className="px-6 py-4">
                    <div className="flex gap-2">
                      {onView && (
                        <button
                          onClick={() => onView(item)}
                          className="p-2 hover:bg-blue-500/20 rounded-lg text-blue-300 transition"
                          title="View"
                        >
                          <Eye size={18} />
                        </button>
                      )}
                      {onEdit && (
                        <button
                          onClick={() => onEdit(item)}
                          className="p-2 hover:bg-amber-500/20 rounded-lg text-amber-300 transition"
                          title="Edit"
                        >
                          <Edit2 size={18} />
                        </button>
                      )}
                      {onDelete && (
                        <button
                          onClick={() => onDelete(item)}
                          className="p-2 hover:bg-red-500/20 rounded-lg text-red-300 transition"
                          title="Delete"
                        >
                          <Trash2 size={18} />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="p-6 border-t border-gray-800 flex justify-between items-center">
          <div className="text-sm text-gray-400">
            Showing {startIndex + 1} to {Math.min(startIndex + itemsPerPage, filteredData.length)} of {filteredData.length} results
          </div>
          <div className="flex gap-2">
            <button
              onClick={handlePrevPage}
              disabled={currentPage === 1}
              className="p-2 hover:bg-black/30 rounded-lg text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronLeft size={20} />
            </button>
            <div className="flex items-center gap-2">
              {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                <button
                  key={page}
                  onClick={() => setCurrentPage(page)}
                  className={`px-3 py-1 rounded-lg text-sm transition ${
                    currentPage === page
                      ? 'bg-gold-600 text-black'
                      : 'bg-dark-secondary text-gray-200 hover:bg-black/30'
                  }`}
                >
                  {page}
                </button>
              ))}
            </div>
            <button
              onClick={handleNextPage}
              disabled={currentPage === totalPages}
              className="p-2 hover:bg-black/30 rounded-lg text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronRight size={20} />
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default DataTable;
