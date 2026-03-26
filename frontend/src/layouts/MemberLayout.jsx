import React from 'react';
import Navbar from '@/components/Navbar';

const MemberLayout = ({ children }) => {
  return (
    <div className="min-h-screen bg-dark-bg">
      <Navbar />
      <main className="w-full py-8">
        {children}
      </main>
    </div>
  );
};

export default MemberLayout;
