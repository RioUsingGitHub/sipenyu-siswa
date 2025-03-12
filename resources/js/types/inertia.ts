import { Page, PageProps as InertiaPageProps } from '@inertiajs/react';

export interface User {
    id: number;
    name: string;
    email: string;
    nisn?: string;
    class?: string;
    role: "student" | "admin";
  }
  
  export interface PageProps extends InertiaPageProps {
    auth: {
      user: User | null;
    };
  }