'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useEffect } from 'react';
import Avatar from '@/components/Avatar';
import Icon from '@/components/Icon';
import Logo from '@/components/Logo';
import UserCard from '@/components/UserCard';
import { useAuth } from '@/lib/auth';
import { useToast } from '@/lib/toast';
import { useList } from '@/lib/useList';

// Layout das páginas que exigem login: menu lateral, conteúdo e sugestões de quem seguir.
// No celular, o menu vira uma barra fixa embaixo da tela.
export default function AppLayout({ children }) {
  const { user, loading, logout } = useAuth();
  const showToast = useToast();
  const router = useRouter();
  const pathname = usePathname();

  // Quem não está logado volta para a tela de login.
  useEffect(() => {
    if (!loading && !user) router.replace('/entrar');
  }, [loading, user, router]);

  if (!user) {
    return <p className="loading">Carregando…</p>;
  }

  async function handleLogout() {
    await logout();
    showToast('Você saiu da sua conta.');
  }

  const profileUrl = `/u/${user.username}`;
  const links = [
    { href: '/', label: 'Início', icon: 'home' },
    { href: '/buscar', label: 'Buscar', icon: 'search' },
    { href: profileUrl, label: 'Perfil', icon: 'user' },
  ];

  return (
    <div className="app">
      <header className="topbar">
        <Link href="/">
          <Logo size={26} />
        </Link>
        <button className="icon-button icon-button-danger" onClick={handleLogout} title="Sair">
          <Icon name="logout" />
        </button>
      </header>

      <aside className="sidebar">
        <Link href="/" className="sidebar-logo">
          <Logo />
        </Link>

        <nav className="nav">
          {links.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              className={pathname === link.href || (link.href !== '/' && pathname.startsWith(link.href)) ? 'nav-link active' : 'nav-link'}
            >
              <Icon name={link.icon} size={23} />
              <span>{link.label}</span>
            </Link>
          ))}
        </nav>

        <div className="sidebar-account">
          <Link href={profileUrl} className="sidebar-user">
            <Avatar src={user.profile_photo_url} size={38} />
            <span>
              <strong>{user.name}</strong>
              <small>@{user.username}</small>
            </span>
          </Link>
          <button className="icon-button icon-button-danger" onClick={handleLogout} title="Sair">
            <Icon name="logout" size={20} />
          </button>
        </div>
      </aside>

      <main className="main">{children}</main>

      <aside className="suggestions">
        <Suggestions />
        <p className="footnote">Varanda é um projeto de portfólio feito com Laravel e Next.js.</p>
      </aside>
    </div>
  );
}

// Caixa "Quem seguir": as 5 pessoas mais recentes que você ainda não segue.
function Suggestions() {
  const list = useList('/users');
  const people = list.items.filter((person) => !person.is_following).slice(0, 5);

  return (
    <section className="box">
      <h2>Quem seguir</h2>
      {list.loading && <p className="muted">Carregando…</p>}
      {list.error && <p className="muted">Não foi possível carregar as sugestões.</p>}
      {!list.loading && !list.error && people.length === 0 && (
        <p className="muted">Você já segue as pessoas que chegaram por último.</p>
      )}
      <ul className="user-list">
        {people.map((person) => (
          <UserCard key={person.id} user={person} compact />
        ))}
      </ul>
      <Link href="/buscar" className="link">
        Ver todas as pessoas
      </Link>
    </section>
  );
}
