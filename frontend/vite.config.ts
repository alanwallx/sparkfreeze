import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,        // Vite still runs on 5173 *inside* the container
    host: true         // Ensures Docker uses 0.0.0.0 (not localhost-only)
  }
})
