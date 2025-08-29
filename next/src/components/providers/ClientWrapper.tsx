'use client';

import { SessionProvider } from "next-auth/react";
import StoreProvider from "@/providers/StoreProvider";

interface ClientWrapperProps {
  children: React.ReactNode;
}

export function ClientWrapper({ children }: ClientWrapperProps) {
  return (
    <SessionProvider>
      <StoreProvider>
        {children}
      </StoreProvider>
    </SessionProvider>
  );
}