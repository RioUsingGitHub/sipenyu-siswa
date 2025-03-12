"use client"

import { useState } from "react"
import { QrReader } from "react-qr-reader"
import { ArrowLeft, CheckCircle, User, BookOpen } from "lucide-react"
import { Link } from "react-router-dom"
import type { QRCodeData } from "../types"

export default function QRScanner() {
  const [scanning, setScanning] = useState(true)
  const [success, setSuccess] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [attendanceData, setAttendanceData] = useState<{
    student_name: string
    class_name: string
    subject_name: string
    time: string
    teacher_name: string
  } | null>(null)

  const handleScan = async (result: string | null) => {
    if (!result || !scanning) return

    setScanning(false)

    try {
      // Parse the QR code data
      const qrData: QRCodeData = JSON.parse(result)

      // Send the attendance data to the server
      const response = await fetch("/api/attendance/scan", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify({ qrToken: qrData.token }),
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || "QR Code tidak valid atau sudah kadaluarsa")
      }

      setSuccess(true)
      setAttendanceData(data.attendance)
    } catch (error) {
      setError(error instanceof Error ? error.message : "Terjadi kesalahan")
      setTimeout(() => {
        setError(null)
        setScanning(true)
      }, 3000)
    }
  }

  return (
    <div className="flex flex-col min-h-screen bg-white">
      <header className="flex items-center p-4 border-b">
        <Link to="/" className="mr-4">
          <ArrowLeft className="w-6 h-6" />
        </Link>
        <h1 className="text-xl font-bold">Pindai Kode Presensi</h1>
      </header>

      <main className="flex-1 p-4">
        {scanning && (
          <div className="overflow-hidden border border-gray-300 rounded-lg">
            <QrReader
              constraints={{ facingMode: "environment" }}
              onResult={(result) => result && handleScan(result.getText())}
              containerStyle={{ width: "100%", height: "100%" }}
              videoStyle={{ width: "100%", height: "100%" }}
            />
          </div>
        )}

        {error && (
          <div className="p-4 mt-4 text-center text-red-500 bg-red-50 rounded-lg">
            <p>{error}</p>
          </div>
        )}

        {success && attendanceData && (
          <div className="flex flex-col items-center p-6 mt-4 border border-gray-200 rounded-lg">
            <div className="flex items-center justify-center w-16 h-16 mb-4 text-green-500 border-2 border-green-500 rounded-full">
              <CheckCircle className="w-8 h-8" />
            </div>

            <h2 className="mb-2 text-xl font-bold text-green-500">Scan QR Code Presensi Berhasil!</h2>
            <p className="mb-6 text-center text-gray-600">
              Terima kasih sudah menghadiri kelas. Presensimu sedang divalidasi.
            </p>

            <div className="w-full space-y-4">
              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 mr-3 bg-gray-100 rounded-full">
                  <User className="w-5 h-5 text-gray-600" />
                </div>
                <div>
                  <p className="font-medium">{attendanceData.student_name}</p>
                  <p className="text-sm text-gray-500">Kelas {attendanceData.class_name}</p>
                </div>
              </div>

              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 mr-3 bg-gray-100 rounded-full">
                  <BookOpen className="w-5 h-5 text-gray-600" />
                </div>
                <div>
                  <p className="font-medium">{attendanceData.subject_name}</p>
                  <p className="text-sm text-gray-500">{attendanceData.time}</p>
                </div>
              </div>

              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 mr-3 bg-gray-100 rounded-full">
                  <User className="w-5 h-5 text-gray-600" />
                </div>
                <div>
                  <p className="font-medium">Guru</p>
                  <p className="text-sm text-gray-500">{attendanceData.teacher_name}</p>
                </div>
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}

