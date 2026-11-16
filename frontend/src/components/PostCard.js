'use client';

import Link from 'next/link';
import { useState } from 'react';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { useToast } from '@/lib/toast';
import { fullDate, timeAgo } from '@/lib/utils';
import Avatar from './Avatar';
import Comments from './Comments';
import Icon from './Icon';

// Uma publicação: autor, texto, imagem, curtir, comentários e (para o dono) editar e excluir.
export default function PostCard({ post: initialPost, onDelete, showComments = false }) {
  const { user } = useAuth();
  const showToast = useToast();

  const [post, setPost] = useState(initialPost);
  const [commentsOpen, setCommentsOpen] = useState(showComments);
  const [liking, setLiking] = useState(false);
  const [editing, setEditing] = useState(false);
  const [draft, setDraft] = useState('');
  const [editError, setEditError] = useState('');
  const [saving, setSaving] = useState(false);

  const author = post.user;
  const profileUrl = `/u/${author.username}`;
  const isMine = author.id === user.id;

  async function toggleLike() {
    setLiking(true);

    try {
      const result = await api(`/posts/${post.id}/like`, { method: post.liked_by_me ? 'DELETE' : 'POST' });
      setPost((current) => ({ ...current, liked_by_me: result.liked, likes_count: result.likes_count }));
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLiking(false);
    }
  }

  function startEditing() {
    setDraft(post.content || '');
    setEditError('');
    setEditing(true);
  }

  async function saveEdit(event) {
    event.preventDefault();

    if (!draft.trim() && !post.image_url) {
      setEditError('Uma publicação sem imagem precisa ter texto.');
      return;
    }

    setSaving(true);

    try {
      const updated = await api(`/posts/${post.id}`, { method: 'PUT', body: { content: draft.trim() } });
      setPost(updated);
      setEditing(false);
      showToast('Publicação atualizada.');
    } catch (err) {
      setEditError(err.errors?.content?.[0] || err.message);
    } finally {
      setSaving(false);
    }
  }

  async function deletePost() {
    if (!confirm('Excluir esta publicação? As curtidas e os comentários também serão apagados.')) {
      return;
    }

    try {
      await api(`/posts/${post.id}`, { method: 'DELETE' });
      showToast('Publicação excluída.');
      onDelete(post.id);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  return (
    <article className="post">
      <Link href={profileUrl}>
        <Avatar src={author.profile_photo_url} size={44} />
      </Link>

      <div className="post-body">
        <header className="post-header">
          <Link href={profileUrl} className="post-author">
            {author.name}
          </Link>
          <span className="post-username">@{author.username}</span>
          <Link href={`/p/${post.id}`} className="post-time" title={fullDate(post.created_at)}>
            {timeAgo(post.created_at)}
          </Link>
          {post.updated_at !== post.created_at && <span className="post-edited">editado</span>}

          {isMine && (
            <div className="post-owner-actions">
              <button className="icon-button" onClick={startEditing} title="Editar texto">
                <Icon name="edit" size={18} />
              </button>
              <button className="icon-button icon-button-danger" onClick={deletePost} title="Excluir publicação">
                <Icon name="trash" size={18} />
              </button>
            </div>
          )}
        </header>

        {editing ? (
          <form className="post-edit" onSubmit={saveEdit}>
            <textarea value={draft} onChange={(e) => setDraft(e.target.value)} maxLength={1000} rows={4} autoFocus />
            {editError && <p className="error-text">{editError}</p>}
            <div className="post-edit-buttons">
              <button type="button" className="btn btn-ghost btn-small" onClick={() => setEditing(false)}>
                Cancelar
              </button>
              <button type="submit" className="btn btn-small" disabled={saving}>
                {saving ? 'Salvando…' : 'Salvar'}
              </button>
            </div>
          </form>
        ) : (
          post.content && <p className="post-text">{post.content}</p>
        )}

        {post.image_url && (
          <img className="post-image" src={post.image_url} alt={`Imagem publicada por ${author.name}`} loading="lazy" />
        )}

        <div className="post-actions">
          <button
            className={post.liked_by_me ? 'post-action post-like liked' : 'post-action post-like'}
            onClick={toggleLike}
            disabled={liking}
            aria-label={post.liked_by_me ? 'Remover curtida' : 'Curtir'}
          >
            <Icon name="heart" size={20} filled={post.liked_by_me} />
            {post.likes_count}
          </button>
          <button
            className={commentsOpen ? 'post-action active' : 'post-action'}
            onClick={() => setCommentsOpen(!commentsOpen)}
            aria-label="Comentários"
          >
            <Icon name="comment" size={20} />
            {post.comments_count}
          </button>
        </div>

        {commentsOpen && (
          <Comments postId={post.id} onCountChange={(count) => setPost((current) => ({ ...current, comments_count: count }))} />
        )}
      </div>
    </article>
  );
}
