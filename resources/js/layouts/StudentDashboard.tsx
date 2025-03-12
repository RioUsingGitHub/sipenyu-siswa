import React, { ReactNode } from 'react';
import { useAuth } from '../Contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { Button } from '../components/ui/button';
import {
  Home,
  BookOpen,
  Calendar,
  FileText,
  User,
  LogOut,
} from 'lucide-react';

interface StudentDashboardProps {
  children: ReactNode;
}

export default function StudentDashboard({ children }: StudentDashboardProps) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Header */}
      <header className="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b dark:border-gray-700">
        <div className="container mx-auto px-4 py-3 flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <h1 className="text-xl font-bold text-primary">Sipenyu</h1>
          </div>
          
          <nav className="hidden md:flex items-center space-x-4">
            <Button variant="ghost" onClick={() => navigate('/')}>
              <Home className="h-4 w-4 mr-2" />
              Beranda
            </Button>
            <Button variant="ghost" onClick={() => navigate('/pelajaran')}>
              <BookOpen className="h-4 w-4 mr-2" />
              Mata Pelajaran
            </Button>
            <Button variant="ghost" onClick={() => navigate('/jadwal')}>
              <Calendar className="h-4 w-4 mr-2" />
              Jadwal
            </Button>
            <Button variant="ghost" onClick={() => navigate('/tugas')}>
              <FileText className="h-4 w-4 mr-2" />
              Tugas
            </Button>
          </nav>

          <div className="flex items-center space-x-4">
            <Button variant="ghost" onClick={() => navigate('/profile')}>
              <User className="h-4 w-4 mr-2" />
              {user?.name}
            </Button>
            <Button variant="outline" size="sm" onClick={handleLogout}>
              <LogOut className="h-4 w-4 mr-2" />
              Keluar
            </Button>
          </div>
        </div>
      </header>

      {/* Mobile Navigation */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white dark:bg-gray-800 border-t dark:border-gray-700">
        <div className="grid grid-cols-5 gap-1">
          <Button variant="ghost" className="flex flex-col items-center py-2" onClick={() => navigate('/')}>
            <Home className="h-5 w-5" />
            <span className="text-xs mt-1">Beranda</span>
          </Button>
          <Button variant="ghost" className="flex flex-col items-center py-2" onClick={() => navigate('/pelajaran')}>
            <BookOpen className="h-5 w-5" />
            <span className="text-xs mt-1">Pelajaran</span>
          </Button>
          <Button variant="ghost" className="flex flex-col items-center py-2" onClick={() => navigate('/jadwal')}>
            <Calendar className="h-5 w-5" />
            <span className="text-xs mt-1">Jadwal</span>
          </Button>
          <Button variant="ghost" className="flex flex-col items-center py-2" onClick={() => navigate('/tugas')}>
            <FileText className="h-5 w-5" />
            <span className="text-xs mt-1">Tugas</span>
          </Button>
          <Button variant="ghost" className="flex flex-col items-center py-2" onClick={() => navigate('/profile')}>
            <User className="h-5 w-5" />
            <span className="text-xs mt-1">Profil</span>
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <main className="flex-1 container mx-auto px-4 py-6 mb-16 md:mb-0">
        {children}
      </main>
    </div>
  );
}