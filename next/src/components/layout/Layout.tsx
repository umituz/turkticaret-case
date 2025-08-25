'use client';

import { Header } from './Header';
import { Footer } from './Footer';

interface LayoutProps {
  children: React.ReactNode;
  onSearchChange?: (query: string) => void;
}

export function Layout({ children, onSearchChange }: LayoutProps) {
  return (
    <div className="min-h-screen flex flex-col">
      <Header onSearchChange={onSearchChange} />
      <main className="flex-1">
        {children}
      </main>
      <Footer />
    </div>
  );
}