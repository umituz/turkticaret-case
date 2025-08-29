'use client';

import { useRef } from 'react';
import { Provider } from 'react-redux';
import { PersistGate } from 'redux-persist/lib/integration/react';
import { makeStore, AppStore } from '@/store/store';
import { Persistor } from 'redux-persist';

export default function StoreProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  const storeRef = useRef<AppStore | null>(null);
  const persistorRef = useRef<Persistor | null>(null);
  
  if (!storeRef.current) {
    const { store, persistor } = makeStore();
    storeRef.current = store;
    persistorRef.current = persistor;
  }

  return (
    <Provider store={storeRef.current}>
      <PersistGate loading={null} persistor={persistorRef.current!}>
        {children}
      </PersistGate>
    </Provider>
  );
}