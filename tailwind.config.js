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
                    50: '#FFFADA', //
                    100: '#F2F0E4', //
                    200: '#e8d490',
                    300: '#b3c5ff',
                    400: '#8C9E5E', //
                    500: '#8C9E5E', //
                    600: '#E8D48F', //
                    700: '#4553b8',
                    800: '#333333', //
                    900: '#2d367a',
                },
                secondary: {
                    500: '#764ba2',
                },
                "dark-gray": {
                    300: "#333333"
                }
            },
            fontFamily: {
                sans: ['Lexend', 'system-ui', 'sans-serif'],
            }
        }
    },
    plugins: []
}