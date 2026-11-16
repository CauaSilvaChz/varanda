'use client';

import Link from 'next/link';
import { useAuth } from '@/lib/auth';
import Avatar from './Avatar';
import FollowButton from './FollowButton';

// Uma pessoa numa lista (busca, seguidores, sugestões), com o botão de seguir.
export default function UserCard({ user, compact = false }) {
  const { user: me } = useAuth();
  const profileUrl = `/u/${user.username}`;

  return (
    <li className={compact ? 'user-card user-card-compact' : 'user-card'}>
      <Link href={profileUrl}>
        <Avatar src={user.profile_photo_url} size={compact ? 40 : 48} />
      </Link>

      <div className="user-card-info">
        <Link href={profileUrl} className="user-card-name">
          {user.name}
        </Link>
        <span className="muted">@{user.username}</span>
        {!compact && user.bio && <p className="user-card-bio">{user.bio}</p>}
      </div>

      {me.id === user.id ? <span className="muted">Você</span> : <FollowButton user={user} />}
    </li>
  );
}
