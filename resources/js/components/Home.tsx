"use client"

import { useState, useEffect } from "react"
import { Link } from "react-router-dom"
import { useAuthStore } from "../store/authStore"
import { Menu, User, Clock, Calendar, QrCode } from "lucide-react"
import type { Subject } from "../types"
import { format } from "date-fns"
import { id } from "date-fns/locale"

export default function Home() {
  const { user } = useAuthStore()
  const [showSidebar, setShowSidebar] = useState(false)
  const [todaySchedule, setTodaySchedule] = useState<Subject[]>([])
  const [currentDate, setCurrentDate] = useState(new Date())

  useEffect(() => {
    const fetchTodaySchedule = async () => {
      try {
        const response = await fetch("/api/schedule/today", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        })

        if (!response.ok) {
          throw new Error("Failed to fetch schedule")
        }

        const data = await response.json()
        setTodaySchedule(data.schedule)
      } catch (error) {
        console.error("Error fetching schedule:", error)
      }
    }

    fetchTodaySchedule()

    // Update current date every minute
    const interval = setInterval(() => {
      setCurrentDate(new Date())
    }, 60000)

    return () => clearInterval(interval)
  }, [])

  const formattedDay = format(currentDate, "EEEE, d MMM", { locale: id })
  const capitalizedDay = formattedDay.charAt(0).toUpperCase() + formattedDay.slice(1)

  return (
    <div className="flex flex-col min-h-screen bg-white">
      <header className="flex items-center justify-between p-4 border-b">
        <button onClick={() => setShowSidebar(!showSidebar)} className="flex items-center">
          <Menu className="w-6 h-6 text-[#6b8e23]" />
        </button>
        <h1 className="text-sm font-medium">
          Sistem Informasi Presensi
          <br />
          SMPN 4 Sungai Pinyuh
        </h1>
        <div className="w-8 h-8 overflow-hidden bg-gray-200 rounded-full">
          <User className="w-full h-full p-1 text-gray-600" />
        </div>
      </header>

      {showSidebar && (
        <div className="fixed inset-0 z-50 flex">
          <div className="absolute inset-0 bg-black bg-opacity-50" onClick={() => setShowSidebar(false)}></div>
          <div className="relative z-10 w-64 h-full">
            <div className="h-full">
              {/* Sidebar component will be rendered here */}
              <div className="sidebar-placeholder"></div>
            </div>
          </div>
        </div>
      )}

      <main className="flex-1 p-4">
        <div className="p-4 mb-4 bg-[#6b8e23] rounded-lg text-white">
          <div className="flex items-center justify-between mb-2">
            <h2 className="text-lg font-medium">Jadwal Hari Ini</h2>
            <Link to="/jadwal" className="flex items-center px-3 py-1 text-sm bg-white rounded-full text-[#6b8e23]">
              <Calendar className="w-4 h-4 mr-1" />
              Lihat Jadwal
            </Link>
          </div>
          <p className="text-xl font-bold">{capitalizedDay}</p>
        </div>

        <div className="mb-6 space-y-4">
          {todaySchedule.length > 0 ? (
            todaySchedule.map((subject) => (
              <div key={subject.id} className="flex items-start">
                <div className="flex items-center mr-3">
                  <Clock className="w-5 h-5 text-gray-500" />
                </div>
                <div>
                  <h3 className="font-medium">{subject.name}</h3>
                  <p className="text-sm text-gray-500">
                    {subject.time_start} - {subject.time_end}
                  </p>
                </div>
              </div>
            ))
          ) : (
            <p className="text-center text-gray-500">Tidak ada jadwal hari ini</p>
          )}
        </div>

        <Link
          to="/scan"
          className="flex items-center justify-center w-full p-4 text-[#6b8e23] border border-[#6b8e23] rounded-lg"
        >
          <QrCode className="w-5 h-5 mr-2" />
          <span className="font-medium">Presensi QR Code</span>
        </Link>
      </main>
    </div>
  )
}

