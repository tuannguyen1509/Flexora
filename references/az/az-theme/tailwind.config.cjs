module.exports = {
  content: ['./**/*.php', './js/**/*.js', '../az-theme-child/**/*.php', '../az-theme-child/js/**/*.js'],
  theme: { extend: {
    colors: {
      brand: { dark: '#111111', primary: '#292023', cyan: 'var(--primary-color)', magenta: '#fede0c' },
      dark: { 900: '#111111', 800: '#181818', 700: '#252525' },
      neon: { blue: 'var(--primary-color)', purple: '#fede0c', green: '#627D47' }
    },
    fontFamily: { sans: ['var(--az-font-main)', 'Arial', 'sans-serif'] },
    boxShadow: {
      'glow-blue': '0 0 20px -5px rgba(41,32,35,0.45), inset 0 0 10px -5px rgba(41,32,35,0.25)',
      'glow-purple': '0 0 20px -5px rgba(254,222,12,0.4), inset 0 0 10px -5px rgba(254,222,12,0.22)',
      'glow-green': '0 0 20px -5px rgba(98,125,71,0.3), inset 0 0 10px -5px rgba(98,125,71,0.18)',
      'glow-text-blue': '0 0 10px rgba(41,32,35,0.35)'
    }
  } },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/container-queries')]
};
