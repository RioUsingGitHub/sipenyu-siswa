"use client"

import { useState, useEffect } from "react"
import { Link } from "react-router-dom"
import { Menu, User } from "lucide-react"
import type { Subject } from "../types"

export default function AttendanceHistory() {
  const [showSidebar, setShowSidebar] = useState(false)
  const [subjects, setSubjects] = useState<Subject[]>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchSubjects = async () => {
      setIsLoading(true)
      try {
        const response = await fetch("/api/subjects", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        })

        if (!response.ok) {
          throw new Error("Failed to fetch subjects")
        }

        const data = await response.json()
        setSubjects(data.subjects)
      } catch (error) {
        console.error("Error fetching subjects:", error)
      } finally {
        setIsLoading(false)
      }
    }

    fetchSubjects()
  }, [])

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
        <h1 className="mb-6 text-2xl font-bold text-center">Riwayat Presensi</h1>

        {isLoading ? (
          <div className="flex items-center justify-center h-40">
            <div className="w-8 h-8 border-4 border-t-[#6b8e23] border-r-[#6b8e23] border-b-[#6b8e23] border-l-transparent rounded-full animate-spin"></div>
          </div>
        ) : (
          <div className="space-y-4">
            {subjects.map((subject) => (
              <div key={subject.id} className="flex items-center justify-between p-4 bg-gray-100 rounded-lg">
                <h2 className="font-medium">{subject.name}</h2>
                <Link to={`/riwayat/${subject.id}`} className="px-3 py-1 text-sm text-white bg-[#6b8e23] rounded-md">
                  Lihat Detail
                </Link>
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  )
}

