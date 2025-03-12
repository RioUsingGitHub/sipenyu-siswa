"use client"

import type React from "react"
import { useState } from "react"
import { useAuthStore } from "../store/authStore"
import { EyeIcon, EyeOffIcon } from "lucide-react"

export default function Login() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [showPassword, setShowPassword] = useState(false)
  const { login, isLoading, error } = useAuthStore()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    await login(email, password)
  }

  return (
    <div className="flex flex-col items-center justify-center min-h-screen p-4 bg-white">
      <div className="w-full max-w-md">
        <div className="flex flex-col items-center mb-8">
          <img src="/images/logo.png" alt="SMPN 4 Sungai Pinyuh" className="w-32 h-32 mb-4" />
          <h1 className="text-3xl font-bold text-[#6b8e23]">SIPENYU</h1>
          <h2 className="text-center text-gray-800">
            Sistem Informasi Presensi
            <br />
            SMPN 4 Sungai Pinyuh
          </h2>
        </div>

        {error && (
          <div className="mb-4 text-center">
            <div className="flex flex-col items-center justify-center mb-2">
              <div className="flex items-center justify-center w-12 h-12 mb-2 border-2 border-red-500 rounded-full">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="w-6 h-6 text-red-500"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <p className="text-red-500">Username atau sandi salah.</p>
              <p className="font-medium">Coba lagi!</p>
            </div>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
              Email/ akun pengguna<span className="text-red-500">*</span>
            </label>
            <input
              id="email"
              type="text"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Masukkan email/NIS/NIP/Username yang terdaftar"
              className="w-full px-3 py-2 mt-1 border-b border-gray-300 focus:outline-none focus:border-[#6b8e23]"
              required
            />
          </div>

          <div className="relative">
            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
              Password<span className="text-red-500">*</span>
            </label>
            <div className="relative">
              <input
                id="password"
                type={showPassword ? "text" : "password"}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Masukkan Password"
                className="w-full px-3 py-2 mt-1 border-b border-gray-300 focus:outline-none focus:border-[#6b8e23]"
                required
              />
              <button
                type="button"
                className="absolute inset-y-0 right-0 flex items-center pr-3 mt-1"
                onClick={() => setShowPassword(!showPassword)}
              >
                {showPassword ? (
                  <EyeOffIcon className="w-5 h-5 text-gray-400" />
                ) : (
                  <EyeIcon className="w-5 h-5 text-gray-400" />
                )}
              </button>
            </div>
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-3 mt-6 font-medium text-white transition-colors bg-[#6b8e23] rounded-md hover:bg-[#556b2f] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6b8e23] disabled:opacity-50"
          >
            {isLoading ? "Memproses..." : "Masuk"}
          </button>
        </form>
      </div>
    </div>
  )
}

