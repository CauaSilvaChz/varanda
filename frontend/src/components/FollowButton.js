'use client';

import { useState } from 'react';
import { api } from '@/lib/api';
import { useToast } from '@/lib/toast';

// Botão "Seguir" / "Seguindo". onChange recebe { following, followers_count } da API.
export default function FollowButton({ user, onChange }) {
  const showToast = useToast();
  const [following, setFollowing] = useState(user.is_following);
  const [loading, setLoading] = useState(false);

  async function handleClick() {
    setLoading(true);

    try {
      const result = await api(`/users/${user.username}/follow`, { method: following ? 'DELETE' : 'POST' });
      setFollowing(result.following);
      onChange?.(result);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  }

  if (following) {
    return (
      <button className="btn btn-secondary btn-small btn-following" onClick={handleClick} disabled={loading}>
        <span className="text-idle">Seguindo</span>
        <span className="text-hover">Deixar de seguir</span>
      </button>
    );
  }

  return (
    <button className="btn btn-small" onClick={handleClick} disabled={loading}>
      Seguir
    </button>
  );
}
