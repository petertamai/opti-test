/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./public/*.{html,js,php}",
      "./public/**/*.{html,js,php}",
      "./templates/**/*.{html,php}",
      "./src/**/*.php"
    ],
    theme: {
      extend: {
        colors: {
          background: "#4D4D4D",
          button: "#397BFB",
          accent: "#cecaba",
          accent2: "#b0fbc1"
        },
        fontFamily: {
          sans: ['Nunito', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
        },
        screens: {
          'xs': '475px',
        },
        boxShadow: {
          'input': '0 2px 4px rgba(0, 0, 0, 0.05)',
          'card': '0 4px 6px rgba(0, 0, 0, 0.1)',
          'elevated': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        },
        borderRadius: {
          'xl': '1rem',
          '2xl': '1.5rem',
        }
      },
    },
    plugins: [],
    safelist: [
      'bg-red-500',
      'bg-green-500',
      'bg-yellow-500',
      'text-white',
      'p-2',
      'rounded'
    ]
  }