/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#eef6fd',
          100: '#d6e9f9',
          500: '#0a72c4',
          600: '#0259a0', // ← bleu exact du logo MITSINJO
          700: '#024780',
        },
      },
    },
  },
  plugins: [],
}
