"use client"
import { Link, useLocation } from "react-router-dom"
import { useAuthStore } from "../store/authStore"
import { ChevronRight, Home, History, User, LogOut } from "lucide-react"

export default function Sidebar() {
  const location = useLocation()
  const { logout } = useAuthStore()

  const menuItems = [
    { name: "Beranda", path: "/", icon: Home },
    { name: "Riwayat Presensi", path: "/riwayat", icon: History },
    { name: "Profil", path: "/profil", icon: User },
  ]

  const handleLogout = async () => {
    await logout()
  }

  return (
    <div className="w-full h-full bg-white border-r">
      <div className="p-4 border-b">
        <h2 className="text-sm font-medium">
          Sistem Informasi Presensi
          <br />
          SMPN 4 Sungai Pinyuh
        </h2>
      </div>

      <nav className="flex flex-col">
        {menuItems.map((item) => (
          <Link
            key={item.path}
            to={item.path}
            className={`flex items-center justify-between p-4 border-b ${
              location.pathname === item.path ? "text-[#6b8e23] font-medium" : "text-gray-700"
            }`}
          >
            <div className="flex items-center">
              <item.icon className="w-5 h-5 mr-2" />
              <span>{item.name}</span>
            </div>
            <ChevronRight className="w-5 h-5" />
          </Link>
        ))}

        <button onClick={handleLogout} className="flex items-center justify-between p-4 text-gray-700 border-b">
          <div className="flex items-center">
            <LogOut className="w-5 h-5 mr-2" />
            <span>Keluar</span>
          </div>
          <ChevronRight className="w-5 h-5" />
        </button>
      </nav>
    </div>
  )
}

