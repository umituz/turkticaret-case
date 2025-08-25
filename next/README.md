# 🛒 TurkTicaret E-Commerce Platform

A modern, responsive e-commerce platform built with Next.js 15, TypeScript, and Tailwind CSS. Features a beautiful product showcase with sliding image carousels, optimized image handling, and a professional newsletter subscription system.

## ✨ Features

### 🎨 Modern UI/UX
- **Responsive Design**: Mobile-first approach with seamless desktop experience
- **Smooth Animations**: Hover effects, transitions, and micro-interactions
- **Glass Morphism**: Modern backdrop-blur effects and gradient overlays

### 🖼️ Advanced Image Optimization
- **Next.js Image Component**: Optimized image loading with proper sizing
- **Object-Cover Optimization**: Images perfectly fit containers without distortion
- **Responsive Images**: Multiple breakpoint optimizations
- **Unsplash Integration**: High-quality product images from reliable sources

### 🛍️ E-Commerce Features
- **Product Grid**: Responsive product display with hover effects
- **Product Detail Pages**: Comprehensive product information with image galleries
- **Category System**: Organized product browsing by categories
- **Shopping Cart**: Add to cart functionality with toast notifications
- **Favorites System**: Wishlist functionality for users

### 🎯 Marketing Components
- **Hero Section**: Dynamic sliding carousel with featured products
- **Newsletter Subscription**: Full-width newsletter section with form validation
- **Trust Indicators**: Customer reviews, ratings, and trust badges
- **CTA Optimization**: Strategic call-to-action placements

### 🔧 Technical Excellence
- **TypeScript**: Full type safety across the application
- **Tailwind CSS**: Utility-first styling approach
- **Component Architecture**: Reusable, modular components
- **Performance Optimized**: Fast loading times and smooth interactions

## 🚀 Quick Start

### Prerequisites
- Node.js 18+ 
- npm, yarn, pnpm, or bun

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd turkticaret-case/next
   ```

2. **Install dependencies**
   ```bash
   npm install
   # or
   yarn install
   # or
   pnpm install
   ```

3. **Start development server**
   ```bash
   npm run dev
   # or
   yarn dev
   # or
   pnpm dev
   ```

4. **Open in browser**
   ```
   http://localhost:3000
   ```

## 📁 Project Structure

```
src/
├── app/                    # App Router pages
│   ├── page.tsx           # Homepage with hero and products
│   ├── products/[slug]/   # Product detail pages
│   └── categories/        # Category listing pages
├── components/
│   ├── common/            # Shared components
│   │   └── NewsletterSection.tsx
│   ├── home/              # Homepage components
│   │   └── HeroSection.tsx
│   ├── product/           # Product-related components
│   │   ├── ProductCard.tsx
│   │   ├── ProductGrid.tsx
│   │   └── FilterSidebar.tsx
│   ├── product-detail/    # Product detail components
│   │   ├── ProductImages.tsx
│   │   └── ProductInfo.tsx
│   ├── layout/            # Layout components
│   │   ├── Header.tsx
│   │   ├── Footer.tsx
│   │   └── Layout.tsx
│   └── ui/                # UI primitives (shadcn/ui)
├── data/
│   └── mockData.ts        # Product and category data
├── types/
│   └── api.ts             # TypeScript type definitions
└── hooks/
    └── use-toast.ts       # Toast notification hook
```

## 🎨 Design System

### Brand Colors (Tailwind Config)
```typescript
turkticaret: {
  50: '#fef7f0',   // Lightest - subtle backgrounds
  100: '#feede1',  // Very light - hover states
  200: '#fdd7be',  // Light - borders
  300: '#fbb890',  // Medium light - text accents
  400: '#f89060',  // Medium - highlights
  500: '#f5703a',  // Main brand color
  600: '#e55a2b',  // Dark - primary buttons
  700: '#c04426',  // Darker - hover states
  800: '#9b3826',  // Very dark - gradients
  900: '#7c3023',  // Darkest - deep shadows
}
```

### Component Hierarchy
- **Layout**: Main application wrapper with header, footer, and newsletter
- **Pages**: Route-based page components
- **Sections**: Large content blocks (Hero, Newsletter)
- **Components**: Reusable UI elements (ProductCard, Button)
- **Primitives**: Basic UI components (shadcn/ui)

## 🛠️ Key Technologies

| Technology | Purpose | Version |
|------------|---------|---------|
| **Next.js** | React framework with App Router | 15.5.0 |
| **TypeScript** | Type safety and developer experience | Latest |
| **Tailwind CSS** | Utility-first CSS framework | Latest |
| **shadcn/ui** | High-quality UI components | Latest |
| **Lucide React** | Icon library | Latest |
| **Next.js Image** | Optimized image handling | Built-in |

## 🔧 Configuration

### Image Domains (next.config.ts)
The application is configured to load images from trusted sources:
- `images.unsplash.com` - High-quality product images
- Various e-commerce domains for fallback images

### Tailwind Extensions
Custom brand colors and component styles are defined in `tailwind.config.ts` with full TypeScript support.

## 📱 Responsive Breakpoints

- **Mobile**: `< 640px` - Single column, touch-optimized
- **Tablet**: `640px - 1024px` - Two columns, hybrid navigation
- **Desktop**: `> 1024px` - Full grid layout, hover states

## 🎯 Performance Features

- **Image Optimization**: Automatic WebP conversion and responsive sizing
- **Code Splitting**: Route-based code splitting with Next.js
- **Font Optimization**: Automatic font loading optimization
- **Lazy Loading**: Images and components load on demand
- **Static Generation**: Pre-rendered pages for better performance

## 🚀 Deployment

### Vercel (Recommended)
```bash
vercel --prod
```

### Docker
```bash
docker build -t turkticaret .
docker run -p 3000:3000 turkticaret
```

### Manual Build
```bash
npm run build
npm start
```

## 🧪 Development

### Available Scripts
- `npm run dev` - Start development server
- `npm run build` - Create production build
- `npm run start` - Start production server
- `npm run lint` - Run ESLint

### Code Style
- **TypeScript**: Strict mode enabled
- **ESLint**: Next.js recommended rules
- **Prettier**: Automatic code formatting
- **Component Structure**: Functional components with hooks

## 🎨 Design Philosophy

### User Experience
- **Mobile First**: Optimized for mobile devices
- **Progressive Enhancement**: Enhanced features for larger screens
- **Accessibility**: Semantic HTML and ARIA labels
- **Performance**: Fast loading and smooth interactions

### Visual Design
- **Modern Aesthetics**: Clean lines and contemporary styling
- **Brand Consistency**: Unified color palette and typography
- **Visual Hierarchy**: Clear content organization
- **Interactive Elements**: Engaging hover states and animations

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Next.js Team** - For the amazing framework
- **Vercel** - For hosting and deployment platform
- **Tailwind CSS** - For the utility-first CSS framework
- **shadcn** - For beautiful UI components
- **Unsplash** - For high-quality product images

---

**Built with ❤️ by TurkTicaret Development Team**