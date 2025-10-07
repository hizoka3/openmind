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
                    200: '#F2F0E4',
                    300: '#F2F0E4',
                    400: '#8C9E5E', //
                    500: '#8C9E5E', //
                    600: '#E8D48F', //
                    700: '#E8D48F',
                    800: '#333333', //
                    900: '#333333',
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