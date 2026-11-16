'use client';

import Link from 'next/link';
import PostCard from '@/components/PostCard';
import PostForm from '@/components/PostForm';
import { useList } from '@/lib/useList';

// Feed: minhas publicações e as de quem eu sigo.
export default function FeedPage() {
  const feed = useList('/feed');

  return (
    <>
      <header className="page-header">
        <h1>Início</h1>
      </header>

      <PostForm onCreated={(post) => feed.setItems((posts) => [post, ...posts])} />

      {feed.loading && <p className="loading">Carregando publicações…</p>}
      {feed.error && <p className="alert">{feed.error}</p>}

      {!feed.loading && !feed.error && feed.items.length === 0 && (
        <div className="empty">
          <h2>Seu feed está vazio por enquanto</h2>
          <p>Siga algumas pessoas para ver aqui o que elas publicam. O que você publicar também aparece aqui.</p>
          <Link href="/buscar" className="btn">
            Encontrar pessoas
          </Link>
        </div>
      )}

      {feed.items.map((post) => (
        <PostCard
          key={post.id}
          post={post}
          onDelete={(id) => feed.setItems((posts) => posts.filter((p) => p.id !== id))}
        />
      ))}

      {feed.hasMore && (
        <button className="btn btn-secondary load-more" onClick={feed.loadMore} disabled={feed.loadingMore}>
          {feed.loadingMore ? 'Carregando…' : 'Carregar mais publicações'}
        </button>
      )}
    </>
  );
}
