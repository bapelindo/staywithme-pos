/** @type {import('tailwindcss').Config} */
module.exports = {
  // 1. Konfigurasi Konten (PENTING untuk Purging)
  //    Tailwind akan memindai file-file ini untuk mencari kelas yang digunakan.
  content: [
    "./app/Views/**/*.php",   // Mencakup semua file view PHP Anda
    "./public/js/**/*.js",    // Mencakup semua file JavaScript Anda
  ],

  // 2. Konfigurasi Tema
  theme: {
    extend: {
      // Tambahkan kustomisasi tema Anda di sini
      // Berdasarkan style.css Anda, Anda bisa mendefinisikan warna dan font:
      colors: {
        'bg-dark-primary': '#0f172a',
        'bg-dark-secondary': '#1e293b',
        'bg-dark-tertiary': '#334155',
        'bg-dark': '#111827', // Anda juga mendefinisikan bg-dark
        'text-dark-primary': '#f1f5f9',
        'text-dark-secondary': '#94a3b8',
        'text-dark-muted': '#64748b',
        'border-dark': '#334155',
        'accent-primary': '#fbbf24',   // Kuning/Amber
        'accent-primary-hover': '#f59e0b', // Oranye
        'accent-secondary': '#38bdf8', // Biru Langit
        'accent-dark': '#fcd34d',      // Kuning lebih terang
      },
      fontFamily: {
        'sans': ['Inter', 'sans-serif'],       // Sesuai dengan <link> di layout
        'heading': ['Playfair Display', 'serif'], // Sesuai dengan <link> di layout
        'body': ['Inter', 'sans-serif']        // Sesuai dengan style.css
      },
      // Anda bisa menambahkan extend lainnya seperti spacing, borderRadius, dll.
    },
  },

  // 3. Konfigurasi Plugin
  //    Berdasarkan link CDN Anda, Anda menggunakan 'forms' dan 'typography'.
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'), // Plugin ini juga ada di public_layout.php
    // Tambahkan plugin lain jika Anda menggunakannya
  ],

  // 4. Mode Gelap (Dark Mode) - Opsional
  // Jika Anda ingin kontrol dark mode via class (seperti di public_layout.php Anda)
  darkMode: 'class', // atau 'media' jika ingin berdasarkan preferensi OS
}