'use client';

import Link from 'next/link';
import { useParams, useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import PostCard from '@/components/PostCard';
import { api } from '@/lib/api';

// Página de uma publicação, já com os comentários abertos.
export default function PostPage() {
  const { id } = useParams();
  const router = useRouter();
  const [post, setPost] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    api(`/posts/${id}`)
      .then(setPost)
      .catch((err) => setError(err.status === 404 ? 'Esta publicação não existe. Ela pode ter sido excluída.' : err.message));
  }, [id]);

  return (
    <>
      <header className="page-header">
        <Link href="/" className="icon-button" aria-label="Voltar">
          <Icon name="back" size={20} />
        </Link>
        <h1>Publicação</h1>
      </header>

      {!post && !error && <p className="loading">Carregando publicação…</p>}
      {error && <p className="alert">{error}</p>}
      {post && <PostCard post={post} showComments onDelete={() => router.replace('/')} />}
    </>
  );
}
