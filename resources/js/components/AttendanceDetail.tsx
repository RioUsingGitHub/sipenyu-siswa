"use client"

import { useState, useEffect } from "react"
import { Link, useParams } from "react-router-dom"
import { ArrowLeft, Calendar, Clock } from "lucide-react"
import type { AttendanceDetail } from "../types"

export default function AttendanceDetailPage() {
  const { subjectId } = useParams<{ subjectId: string }>()
  const [attendanceDetails, setAttendanceDetails] = useState<AttendanceDetail[]>([])
  const [subjectName, setSubjectName] = useState("")
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchAttendanceDetails = async () => {
      setIsLoading(true)
      try {
        const response = await fetch(`/api/attendance/subject/${subjectId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
        })

        if (!response.ok) {
          throw new Error("Failed to fetch attendance details")
        }

        const data = await response.json()
        setAttendanceDetails(data.attendances)
        setSubjectName(data.subject_name)
      } catch (error) {
        console.error("Error fetching attendance details:", error)
      } finally {
        setIsLoading(false)
      }
    }

    if (subjectId) {
      fetchAttendanceDetails()
    }
  }, [subjectId])

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "hadir":
        return <span className="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full">Hadir</span>
      case "izin":
        return <span className="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full">Izin</span>
      case "alpa":
        return <span className="px-2 py-1 text-xs text-red-800 bg-red-100 rounded-full">Alpa</span>
      case "sakit":
        return <span className="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded-full">Sakit</span>
      default:
        return <span className="px-2 py-1 text-xs text-gray-800 bg-gray-100 rounded-full">{status}</span>
    }
  }

  return (
    <div className="flex flex-col min-h-screen bg-white">
      <header className="flex items-center p-4 border-b">
        <Link to="/riwayat" className="mr-4">
          <ArrowLeft className="w-6 h-6" />
        </Link>
        <h1 className="text-xl font-bold">Detail Presensi</h1>
      </header>

      <main className="flex-1 p-4">
        {isLoading ? (
          <div className="flex items-center justify-center h-40">
            <div className="w-8 h-8 border-4 border-t-[#6b8e23] border-r-[#6b8e23] border-b-[#6b8e23] border-l-transparent rounded-full animate-spin"></div>
          </div>
        ) : (
          <>
            <h2 className="mb-6 text-xl font-bold">{subjectName}</h2>

            <div className="space-y-4">
              {attendanceDetails.map((attendance, index) => (
                <div key={index} className="p-4 border-b">
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-sm text-gray-500">Pertemuan ke {attendance.meeting_number}</span>
                    <span className="font-medium">{attendance.status_text}</span>
                  </div>

                  <div className="flex items-center mb-2">
                    <Calendar className="w-4 h-4 mr-2 text-gray-500" />
                    <span>{attendance.date}</span>
                  </div>

                  <div className="flex items-center">
                    <Clock className="w-4 h-4 mr-2 text-gray-500" />
                    <span>
                      {attendance.time_start} - {attendance.time_end}
                    </span>
                  </div>

                  <div className="mt-2 text-right">{getStatusBadge(attendance.status)}</div>
                </div>
              ))}
            </div>
          </>
        )}
      </main>
    </div>
  )
}

