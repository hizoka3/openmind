/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./templates/**/*.php",
        "./assets/js/**/*.js",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f5f7ff',
                    100: '#ebf0ff',
                    200: '#d6e0ff',
                    300: '#b3c5ff',
                    400: '#8099ff',
                    500: '#667eea',
                    600: '#5568d3',
                    700: '#4553b8',
                    800: '#374299',
                    900: '#2d367a',
                },
                secondary: {
                    500: '#764ba2',
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            }
        }
    },
    plugins: []
}