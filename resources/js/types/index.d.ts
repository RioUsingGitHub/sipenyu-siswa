import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface User {
    id: number
    name: string
    email: string
    nisn?: string
    class?: string
    role: "student" | "admin"
  }
  
  export interface AuthState {
    user: User | null
    token: string | null
    isAuthenticated: boolean
    isLoading: boolean
    error: string | null
  }
  
  export interface Attendance {
    id: number
    student_id: number
    subject_id: number
    class_id: number
    teacher_id: number
    meeting_number: number
    status: "hadir" | "izin" | "alpa" | "sakit"
    date: string
    time_start: string
    time_end: string
  }
  
  export interface Subject {
    id: number
    name: string
    teacher_name: string
    day?: string
    time_start?: string
    time_end?: string
  }
  
  export interface AttendanceDetail {
    meeting_number: number
    date: string
    time_start: string
    time_end: string
    status: "hadir" | "izin" | "alpa" | "sakit"
    status_text: string
  }
  
  export interface QRCodeData {
    token: string
    subject_id: number
    class_id: number
    teacher_id: number
    meeting_number: number
    expires_at: string
  }
  
  