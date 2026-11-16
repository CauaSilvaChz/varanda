'use client';

import { useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import UserCard from '@/components/UserCard';
import { useList } from '@/lib/useList';

export default function SearchPage() {
  const [query, setQuery] = useState('');
  const [term, setTerm] = useState('');

  // Espera a pessoa parar de digitar (350 ms) antes de buscar, para não chamar a API a cada letra.
  useEffect(() => {
    const timer = setTimeout(() => setTerm(query.trim().replace(/^@/, '')), 350);
    return () => clearTimeout(timer);
  }, [query]);

  const results = useList(`/users?q=${encodeURIComponent(term)}`);

  return (
    <>
      <header className="page-header">
        <h1>Buscar pessoas</h1>
      </header>

      <div className="search-bar">
        <Icon name="search" size={20} />
        <input
          type="search"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Nome ou @usuário"
          aria-label="Buscar por nome ou nome de usuário"
          autoComplete="off"
          autoFocus
        />
      </div>

      <h2 className="section-title">{term ? `Resultados para “${term}”` : 'Quem chegou por último'}</h2>

      {results.loading && <p className="loading">Buscando…</p>}
      {results.error && <p className="alert">{results.error}</p>}
      {!results.loading && !results.error && results.items.length === 0 && (
        <div className="empty">
          <h2>Ninguém encontrado</h2>
          <p>Confira a grafia ou tente buscar por outra parte do nome.</p>
        </div>
      )}

      <ul className="user-list">
        {results.items.map((user) => (
          <UserCard key={user.id} user={user} />
        ))}
      </ul>

      {results.hasMore && (
        <button className="btn btn-secondary load-more" onClick={results.loadMore} disabled={results.loadingMore}>
          {results.loadingMore ? 'Carregando…' : 'Carregar mais'}
        </button>
      )}
    </>
  );
}
