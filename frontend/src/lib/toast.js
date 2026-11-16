'use client';

import { createContext, useContext, useState } from 'react';

const ToastContext = createContext(null);

// Mostra avisos rápidos de sucesso ou erro no rodapé da tela.
// Uso: const showToast = useToast(); showToast('Publicado.'); showToast('Deu erro', 'error');
export function ToastProvider({ children }) {
  const [toast, setToast] = useState(null);

  function showToast(message, type = 'success') {
    const id = Date.now();
    setToast({ id, message, type });

    // Some depois de 4 segundos (se nenhum outro aviso tiver aparecido no lugar).
    setTimeout(() => {
      setToast((current) => (current?.id === id ? null : current));
    }, 4000);
  }

  return (
    <ToastContext.Provider value={showToast}>
      {children}
      {toast && (
        <div className={`toast toast-${toast.type}`} role="status">
          {toast.message}
        </div>
      )}
    </ToastContext.Provider>
  );
}

export function useToast() {
  return useContext(ToastContext);
}
