import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../layouts/AppLayout';
import { useAuth } from '../Contexts/AuthContext';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';

export default function Home() {
  const { user } = useAuth();
  
  return (
    <AppLayout>
      <Head title="Dashboard" />
      
      <div className="space-y-4">
        <h1 className="text-2xl font-bold">Selamat Datang, {user?.name}</h1>
        
        {user?.role === 'siswa' && (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Jadwal Hari Ini</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">5 Mata Pelajaran</p>
              </CardContent>
            </Card>
            
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Tugas Mendatang</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">3 Tugas</p>
              </CardContent>
            </Card>
            
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Pengumuman</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">2 Pengumuman Baru</p>
              </CardContent>
            </Card>
          </div>
        )}
        
        {user?.role === 'admin' && (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Total Siswa</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">256</p>
              </CardContent>
            </Card>
            
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Mata Pelajaran</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">12</p>
              </CardContent>
            </Card>
            
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Kehadiran Hari Ini</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">94%</p>
              </CardContent>
            </Card>
            
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">Pengumuman Aktif</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-2xl font-bold">5</p>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </AppLayout>
  );
}