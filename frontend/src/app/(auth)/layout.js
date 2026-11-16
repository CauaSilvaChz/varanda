'use client';

import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import Logo, { CobogoPattern } from '@/components/Logo';
import { useAuth } from '@/lib/auth';

// Layout das telas de login e cadastro: parede de cobogó de um lado e o formulário do outro.
export default function AuthLayout({ children }) {
  const { user, loading } = useAuth();
  const router = useRouter();

  // Quem já está logado vai direto para o feed.
  useEffect(() => {
    if (!loading && user) router.replace('/');
  }, [loading, user, router]);

  return (
    <div className="auth">
      <aside className="auth-panel">
        <CobogoPattern size={72} />
        <div className="auth-panel-card">
          <Logo size={36} />
          <p>Conversa de vizinho, sem pressa.</p>
        </div>
      </aside>

      <main className="auth-content">
        <div className="auth-box">{loading || user ? <p className="loading">Carregando…</p> : children}</div>
      </main>
    </div>
  );
}
