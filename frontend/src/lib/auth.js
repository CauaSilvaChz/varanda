'use client';

import { createContext, useContext, useEffect, useState } from 'react';
import { api } from './api';

const AuthContext = createContext(null);

// Guarda o usuário logado e deixa ele disponível em qualquer página com useAuth().
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  // Ao abrir o site, se houver um token salvo, busca os dados de quem está logado.
  useEffect(() => {
    if (!localStorage.getItem('token')) {
      setLoading(false);
      return;
    }

    api('/me')
      .then(setUser)
      .catch(() => localStorage.removeItem('token'))
      .finally(() => setLoading(false));
  }, []);

  function login(token, user) {
    localStorage.setItem('token', token);
    setUser(user);
  }

  async function logout() {
    try {
      await api('/logout', { method: 'POST' });
    } catch {
      // Mesmo se a API falhar, a sessão é encerrada no navegador.
    }
    localStorage.removeItem('token');
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, setUser, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
