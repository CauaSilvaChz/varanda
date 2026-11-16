'use client';

import Link from 'next/link';
import { notFound, useParams } from 'next/navigation';
import Icon from '@/components/Icon';
import UserCard from '@/components/UserCard';
import { useList } from '@/lib/useList';

// Atende /u/[username]/seguidores e /u/[username]/seguindo.
export default function FollowListPage() {
  const { username, lista } = useParams();
  const isFollowers = lista === 'seguidores';
  const list = useList(`/users/${username}/${isFollowers ? 'followers' : 'following'}`);

  if (lista !== 'seguidores' && lista !== 'seguindo') {
    notFound();
  }

  return (
    <>
      <header className="page-header">
        <Link href={`/u/${username}`} className="icon-button" aria-label="Voltar">
          <Icon name="back" size={20} />
        </Link>
        <div>
          <h1>{isFollowers ? 'Seguidores' : 'Seguindo'}</h1>
          <p className="muted">@{username}</p>
        </div>
      </header>

      {list.loading && <p className="loading">Carregando pessoas…</p>}
      {list.error && <p className="alert">{list.error}</p>}
      {!list.loading && !list.error && list.items.length === 0 && (
        <div className="empty">
          <h2>{isFollowers ? 'Ainda sem seguidores' : 'Ainda não segue ninguém'}</h2>
        </div>
      )}

      <ul className="user-list">
        {list.items.map((user) => (
          <UserCard key={user.id} user={user} />
        ))}
      </ul>

      {list.hasMore && (
        <button className="btn btn-secondary load-more" onClick={list.loadMore} disabled={list.loadingMore}>
          {list.loadingMore ? 'Carregando…' : 'Carregar mais'}
        </button>
      )}
    </>
  );
}
