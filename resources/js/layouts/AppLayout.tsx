import React, { ReactNode } from 'react';
import { useAuth } from '../Contexts/AuthContext';
import AdminDashboard from './AdminDashboard';
import StudentDashboard from './StudentDashboard';

interface AppLayoutProps {
  children: ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
  const { user, isLoading } = useAuth();
  
  if (isLoading) {
    return <div className="flex items-center justify-center min-h-screen">Loading...</div>;
  }
  
  if (!user) {
    return <div className="guest-layout">{children}</div>;
  }
  
  // Route based on user role
  if (user.role === 'admin') {
    return <AdminDashboard>{children}</AdminDashboard>;
  }
  
  return <StudentDashboard>{children}</StudentDashboard>;
}