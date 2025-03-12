import React, { ReactNode } from 'react';
import { useAuth } from '../Contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/button';
import {
  LayoutDashboard,
  Users,
  BookOpen,
  Calendar,
  Settings,
  LogOut,
} from 'lucide-react';

interface AdminDashboardProps {
  children: ReactNode;
}

export default function AdminDashboard({ children }: AdminDashboardProps) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="flex h-screen bg-gray-100 dark:bg-gray-900">
      {/* Sidebar */}
      <aside className="w-64 bg-white dark:bg-gray-800 border-r dark:border-gray-700">
        <div className="p-4 border-b dark:border-gray-700">
          <h2 className="text-xl font-semibold text-gray-800 dark:text-white">Sipenyu Admin</h2>
        </div>
        <nav className="p-2">
          {/* Navigation Links */}
          <div className="space-y-1">
            <Button variant="ghost" className="w-full justify-start" onClick={() => navigate('/admin')}>
              <LayoutDashboard className="mr-2 h-4 w-4" />
              Dashboard
            </Button>
            <Button variant="ghost" className="w-full justify-start" onClick={() => navigate('/admin/siswa')}>
              <Users className="mr-2 h-4 w-4" />
              Siswa
            </Button>
            <Button variant="ghost" className="w-full justify-start" onClick={() => navigate('/admin/mata-pelajaran')}>
              <BookOpen className="mr-2 h-4 w-4" />
              Mata Pelajaran
            </Button>
            <Button variant="ghost" className="w-full justify-start" onClick={() => navigate('/admin/jadwal')}>
              <Calendar className="mr-2 h-4 w-4" />
              Jadwal
            </Button>
            <Button variant="ghost" className="w-full justify-start" onClick={() => navigate('/admin/settings')}>
              <Settings className="mr-2 h-4 w-4" />
              Pengaturan
            </Button>
          </div>
        </nav>
        <div className="absolute bottom-0 w-64 p-4 border-t dark:border-gray-700">
          <div className="flex items-center mb-4">
            <div className="ml-3">
              <p className="text-sm font-medium">{user?.name}</p>
              <p className="text-xs text-gray-500 dark:text-gray-400">{user?.email}</p>
            </div>
          </div>
          <Button variant="outline" className="w-full justify-start" onClick={handleLogout}>
            <LogOut className="mr-2 h-4 w-4" />
            Logout
          </Button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 overflow-auto">
        <div className="p-6">{children}</div>
      </main>
    </div>
  );
}