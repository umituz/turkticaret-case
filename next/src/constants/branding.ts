export const BRAND = {
  name: 'Ecommerce',
  logo: {
    letter: 'E',
    classes: 'h-8 w-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center',
    textClasses: 'text-white font-bold text-lg'
  },
  colors: {
    gradient: 'bg-gradient-to-r from-blue-600 to-blue-700',
    primary: 'blue-600',
    primaryDark: 'blue-700',
    hover: 'hover:text-blue-600'
  },
  contact: {
    email: 'info@ecommerce.com',
    phone: '+1 (555) 123-4567',
    address: {
      line1: 'Commerce Center, Floor 5',
      line2: 'New York, NY 10001'
    }
  },
  social: {
    facebook: 'https://facebook.com/ecommerce',
    twitter: 'https://twitter.com/ecommerce',
    instagram: 'https://instagram.com/ecommerce'
  },
  description: 'Your trusted e-commerce platform. We bring you quality products at the best prices.'
} as const;