export const BRAND = {
  name: 'TurkTicaret',
  logo: {
    letter: 'T',
    classes: 'h-8 w-8 rounded-lg bg-gradient-to-br from-red-600 to-red-700 flex items-center justify-center',
    textClasses: 'text-white font-bold text-lg'
  },
  colors: {
    gradient: 'bg-gradient-to-r from-red-600 to-red-700',
    primary: 'red-600',
    primaryDark: 'red-700',
    hover: 'hover:text-red-600'
  },
  contact: {
    email: 'info@turkticaret.com',
    phone: '+90 (212) 123-4567',
    address: {
      line1: 'Ticaret Merkezi, Kat 5',
      line2: 'İstanbul, Türkiye 34000'
    }
  },
  social: {
    facebook: 'https://facebook.com/turkticaret',
    twitter: 'https://twitter.com/turkticaret',
    instagram: 'https://instagram.com/turkticaret'
  },
  description: 'Türkiye\'nin en güvenilir e-ticaret platformu. Size en kaliteli ürünleri en iyi fiyatlarla sunuyoruz.'
} as const;