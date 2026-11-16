'use client';

import Link from 'next/link';
import { useParams } from 'next/navigation';
import { useEffect, useState } from 'react';
import Avatar from '@/components/Avatar';
import FollowButton from '@/components/FollowButton';
import Icon from '@/components/Icon';
import { CobogoPattern } from '@/components/Logo';
import PostCard from '@/components/PostCard';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { useList } from '@/lib/useList';
import { plural } from '@/lib/utils';

// Cores da capa do perfil: cada pessoa recebe uma combinação, de acordo com o id.
const COVER_COLORS = [
  ['#1d6b5e', '#e2b13c'],
  ['#2c4f8f', '#f1e6cf'],
  ['#3d5f3b', '#d9e6c3'],
  ['#5e3a57', '#f2c9a5'],
  ['#1f4a5a', '#9fd0c7'],
];

export default function ProfilePage() {
  const { username } = useParams();
  const { user: me } = useAuth();
  const [profile, setProfile] = useState(null);
  const [error, setError] = useState('');
  const posts = useList(`/users/${username}/posts`);

  useEffect(() => {
    setProfile(null);
    setError('');

    api(`/users/${username}`)
      .then(setProfile)
      .catch((err) => setError(err.status === 404 ? `Não há ninguém com o usuário @${username}.` : err.message));
  }, [username]);

  if (error) {
    return (
      <>
        <header className="page-header">
          <Link href="/" className="icon-button" aria-label="Voltar">
            <Icon name="back" size={20} />
          </Link>
          <h1>Perfil</h1>
        </header>
        <p className="alert">{error}</p>
      </>
    );
  }

  if (!profile) {
    return <p className="loading">Carregando perfil…</p>;
  }

  const isMe = profile.id === me.id;
  const [color, background] = COVER_COLORS[profile.id % COVER_COLORS.length];

  function handlePostDeleted(id) {
    posts.setItems((items) => items.filter((post) => post.id !== id));
    setProfile((current) => ({ ...current, posts_count: current.posts_count - 1 }));
  }

  return (
    <>
      <header className="page-header">
        <Link href="/" className="icon-button" aria-label="Voltar">
          <Icon name="back" size={20} />
        </Link>
        <div>
          <h1>{profile.name}</h1>
          <p className="muted">{plural(profile.posts_count, 'publicação', 'publicações')}</p>
        </div>
      </header>

      <section className="profile">
        <div className="profile-cover">
          <CobogoPattern color={color} background={background} size={36} />
        </div>

        <div className="profile-info">
          <div className="profile-top">
            <Avatar src={profile.profile_photo_url} size={104} alt={`Foto de ${profile.name}`} />
            {isMe ? (
              <Link href="/perfil/editar" className="btn btn-secondary btn-small">
                Editar perfil
              </Link>
            ) : (
              <FollowButton
                key={profile.id}
                user={profile}
                onChange={(result) => setProfile((current) => ({ ...current, followers_count: result.followers_count }))}
              />
            )}
          </div>

          <h2 className="profile-name">{profile.name}</h2>
          <p className="muted">@{profile.username}</p>
          {profile.bio && <p className="profile-bio">{profile.bio}</p>}

          <div className="profile-stats">
            <span>{plural(profile.posts_count, 'publicação', 'publicações')}</span>
            <Link href={`/u/${profile.username}/seguidores`}>
              {plural(profile.followers_count, 'seguidor', 'seguidores')}
            </Link>
            <Link href={`/u/${profile.username}/seguindo`}>{profile.following_count} seguindo</Link>
          </div>
        </div>
      </section>

      <h2 className="section-title">Publicações</h2>

      {posts.loading && <p className="loading">Carregando publicações…</p>}
      {posts.error && <p className="alert">{posts.error}</p>}
      {!posts.loading && !posts.error && posts.items.length === 0 && (
        <div className="empty">
          <h2>{isMe ? 'Você ainda não publicou nada' : 'Nenhuma publicação ainda'}</h2>
          <p>{isMe ? 'Sua primeira publicação aparece aqui e no feed de quem segue você.' : `Quando @${profile.username} publicar algo, vai aparecer aqui.`}</p>
        </div>
      )}

      {posts.items.map((post) => (
        <PostCard key={post.id} post={post} onDelete={handlePostDeleted} />
      ))}

      {posts.hasMore && (
        <button className="btn btn-secondary load-more" onClick={posts.loadMore} disabled={posts.loadingMore}>
          {posts.loadingMore ? 'Carregando…' : 'Carregar mais publicações'}
        </button>
      )}
    </>
  );
}
