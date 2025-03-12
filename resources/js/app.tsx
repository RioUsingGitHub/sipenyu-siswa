import React, { useState, useEffect } from 'react';
import { AuthProvider, User } from './Contexts/AuthContext';
import { createInertiaApp } from '@inertiajs/react';
import { ThemeProvider } from './components/theme-provider';

// Define proper types
interface Auth {
  user: User | null;
}

interface PageProps {
  auth: Auth;
  [key: string]: any;
}

export default function App() {
  const [appContent, setAppContent] = useState<React.ReactNode | null>(null);

  useEffect(() => {
    createInertiaApp({
      resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true });
        return pages[`./Pages/${name}.tsx`];
      },
      setup({ el, App, props }) {
        setAppContent(<App {...props} />);
      },
    });
  }, []);

  if (!appContent) {
    return <div>Loading...</div>; // Bisa diganti dengan spinner atau splash screen
  }

  return (
    <ThemeProvider defaultTheme="light" storageKey="sipenyu-theme">
      <AuthProvider>{appContent}</AuthProvider>
    </ThemeProvider>
  );
}
