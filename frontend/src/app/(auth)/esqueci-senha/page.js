'use client';

import Link from 'next/link';
import { useState } from 'react';
import { api } from '@/lib/api';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [sending, setSending] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();
    setSending(true);
    setError('');
    setSuccess('');

    try {
      const data = await api('/forgot-password', { method: 'POST', body: { email } });
      setSuccess(data.message);
    } catch (err) {
      setError(err.errors?.email?.[0] || err.message);
    } finally {
      setSending(false);
    }
  }

  return (
    <>
      <h1>Recuperar senha</h1>
      <p className="muted">Informe o e-mail da sua conta e enviaremos um link para criar uma nova senha.</p>

      <form className="form" onSubmit={handleSubmit}>
        {success && <p className="alert alert-success">{success}</p>}

        <label className="field">
          E-mail
          <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} autoComplete="email" required />
          {error && <span className="error-text">{error}</span>}
        </label>

        <button type="submit" className="btn" disabled={sending}>
          {sending ? 'Enviando…' : 'Enviar link'}
        </button>
      </form>

      <p className="auth-footer">
        Lembrou a senha? <Link href="/entrar" className="link">Entrar</Link>
      </p>
    </>
  );
}
