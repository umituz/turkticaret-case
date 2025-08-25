import Link from 'next/link';
import { Facebook, Twitter, Instagram } from 'lucide-react';
import { BRAND } from '@/constants/branding';

export function Footer() {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-muted/50 border-t">
      <div className="container mx-auto px-4 py-12">
        <div className="flex justify-center items-center">
          {}
          <div className="text-center space-y-4">
            <div className="flex items-center justify-center space-x-2">
              <div className={BRAND.logo.classes}>
                <span className={BRAND.logo.textClasses}>{BRAND.logo.letter}</span>
              </div>
              <span className={`text-xl font-bold ${BRAND.colors.gradient} bg-clip-text text-transparent`}>
                {BRAND.name}
              </span>
            </div>
            <p className="text-sm text-muted-foreground">
              {BRAND.description}
            </p>
            <div className="flex justify-center space-x-4">
              <Link 
                href={BRAND.social.facebook} 
                target="_blank"
                className={`text-muted-foreground ${BRAND.colors.hover} transition-colors`}
              >
                <Facebook className="w-5 h-5" />
              </Link>
              <Link 
                href={BRAND.social.twitter} 
                target="_blank"
                className={`text-muted-foreground ${BRAND.colors.hover} transition-colors`}
              >
                <Twitter className="w-5 h-5" />
              </Link>
              <Link 
                href={BRAND.social.instagram} 
                target="_blank"
                className={`text-muted-foreground ${BRAND.colors.hover} transition-colors`}
              >
                <Instagram className="w-5 h-5" />
              </Link>
            </div>
          </div>
        </div>

        {}
        <div className="border-t mt-8 pt-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <p className="text-sm text-muted-foreground">
            © {currentYear} {BRAND.name}. All rights reserved.
          </p>
          <div className="flex items-center space-x-4 text-sm text-muted-foreground">
          </div>
        </div>
      </div>
    </footer>
  );
}