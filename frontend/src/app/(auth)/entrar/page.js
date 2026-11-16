'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';

export default function LoginPage() {
  const { login } = useAuth();
  const router = useRouter();

  const [form, setForm] = useState({ email: '', password: '' });
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState('');
  const [sending, setSending] = useState(false);

  function handleChange(event) {
    setForm({ ...form, [event.target.name]: event.target.value });
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSending(true);
    setErrors({});
    setMessage('');

    try {
      const data = await api('/login', { method: 'POST', body: form });
      login(data.token, data.user);
      router.replace('/');
    } catch (err) {
      setErrors(err.errors || {});
      if (!err.errors?.email && !err.errors?.password) setMessage(err.message);
      setSending(false);
    }
  }

  return (
    <>
      <h1>Entrar na Varanda</h1>
      <p className="muted">Que bom ver você de novo.</p>

      <form className="form" onSubmit={handleSubmit}>
        {message && <p className="alert">{message}</p>}

        <label className="field">
          E-mail
          <input name="email" type="email" value={form.email} onChange={handleChange} autoComplete="email" required />
          {errors.email && <span className="error-text">{errors.email[0]}</span>}
        </label>

        <label className="field">
          Senha
          <input name="password" type="password" value={form.password} onChange={handleChange} autoComplete="current-password" required />
          {errors.password && <span className="error-text">{errors.password[0]}</span>}
        </label>

        <Link href="/esqueci-senha" className="link forgot-link">
          Esqueci minha senha
        </Link>

        <button type="submit" className="btn" disabled={sending}>
          {sending ? 'Entrando…' : 'Entrar'}
        </button>
      </form>

      <button className="demo-button" onClick={() => setForm({ email: 'demo@varanda.test', password: 'varanda123' })}>
        Quer só conhecer? <strong>Preencher com a conta de demonstração</strong>
      </button>

      <p className="auth-footer">
        Ainda não tem conta? <Link href="/cadastro" className="link">Criar conta</Link>
      </p>
    </>
  );
}
