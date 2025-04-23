module.exports = {
    darkMode: 'class', // Mengaktifkan dark mode dengan kelas
    theme: {
      extend: {
        // Kamu bisa menambahkan custom theme di sini
        colors: {
          'dark-bg': '#1a202c',  // Misalnya, warna latar belakang gelap
          'dark-text': '#f7fafc', // Warna teks terang untuk mode gelap
        },
      },
    },
    variants: {
      extend: {
        // Jika perlu, kamu bisa menambahkan variabel yang ingin bereaksi terhadap dark mode
        backgroundColor: ['dark'],
        textColor: ['dark'],
      },
    },
    plugins: [],
  }
  