// tailwind.config.js
module.exports = {
    theme: {
        fontFamily: {
            'sans': ['Roboto', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', "Segoe UI", "Helvetica Neue", 'Arial', "Noto Sans", 'sans-serif', "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"],
        },
        extend: {
            colors: {
                orange: {
                    light: '#F99664',
                    DEFAULT: '#F96C26',
                    dark: '#f15609',
                },
            }
        }
    }
}