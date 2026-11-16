'use client';

import Link from 'next/link';
import { useState } from 'react';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { useToast } from '@/lib/toast';
import { useList } from '@/lib/useList';
import { fullDate, timeAgo } from '@/lib/utils';
import Avatar from './Avatar';
import Icon from './Icon';

// Comentários de uma publicação, em ordem cronológica, com o campo para comentar.
export default function Comments({ postId, onCountChange }) {
  const { user } = useAuth();
  const showToast = useToast();
  const list = useList(`/posts/${postId}/comments`);
  const [text, setText] = useState('');
  const [error, setError] = useState('');
  const [sending, setSending] = useState(false);

  // Garante a ordem certa mesmo com comentários novos e páginas carregadas depois.
  const comments = [...list.items].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

  async function handleSubmit(event) {
    event.preventDefault();
    if (!text.trim()) return;

    setSending(true);
    setError('');

    try {
      const result = await api(`/posts/${postId}/comments`, { method: 'POST', body: { content: text.trim() } });
      list.setItems((items) => [...items, result.comment]);
      onCountChange(result.comments_count);
      setText('');
    } catch (err) {
      setError(err.errors?.content?.[0] || err.message);
    } finally {
      setSending(false);
    }
  }

  async function deleteComment(id) {
    try {
      const result = await api(`/comments/${id}`, { method: 'DELETE' });
      list.setItems((items) => items.filter((comment) => comment.id !== id));
      onCountChange(result.comments_count);
      showToast('Comentário excluído.');
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // Enter envia o comentário; Shift + Enter quebra a linha.
  function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      event.currentTarget.form.requestSubmit();
    }
  }

  return (
    <section className="comments">
      {list.loading && <p className="muted">Carregando comentários…</p>}
      {list.error && <p className="error-text">{list.error}</p>}
      {!list.loading && !list.error && comments.length === 0 && (
        <p className="muted">Ninguém comentou ainda. Comece a conversa.</p>
      )}

      {comments.map((comment) => (
        <div key={comment.id} className="comment">
          <Avatar src={comment.user.profile_photo_url} size={32} />
          <div className="comment-bubble">
            <Link href={`/u/${comment.user.username}`} className="comment-author">
              {comment.user.name}
            </Link>
            <span className="comment-time" title={fullDate(comment.created_at)}>
              {timeAgo(comment.created_at)}
            </span>
            <p className="comment-text">{comment.content}</p>
          </div>
          {comment.user.id === user.id && (
            <button className="icon-button icon-button-danger" onClick={() => deleteComment(comment.id)} title="Excluir comentário">
              <Icon name="trash" size={16} />
            </button>
          )}
        </div>
      ))}

      {list.hasMore && (
        <button className="btn btn-ghost btn-small" onClick={list.loadMore} disabled={list.loadingMore}>
          {list.loadingMore ? 'Carregando…' : 'Ver mais comentários'}
        </button>
      )}

      <form className="comment-form" onSubmit={handleSubmit}>
        <Avatar src={user.profile_photo_url} size={32} />
        <textarea
          value={text}
          onChange={(e) => setText(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder="Escreva um comentário…"
          aria-label="Escreva um comentário"
          maxLength={500}
          rows={1}
        />
        <button type="submit" className="comment-send" disabled={sending || !text.trim()} aria-label="Enviar comentário">
          <Icon name="send" size={18} />
        </button>
      </form>
      {error && <p className="error-text">{error}</p>}
    </section>
  );
}
