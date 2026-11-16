import Link from 'next/link';
import Logo from '@/components/Logo';

export default function NotFound() {
  return (
    <main className="not-found">
      <Logo />
      <h1>Página não encontrada</h1>
      <p className="muted">O endereço pode estar errado ou a página foi removida.</p>
      <Link href="/" className="link">
        Voltar para o início
      </Link>
    </main>
  );
}
