'use client';

import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { Suspense, useState } from 'react';
import { api } from '@/lib/api';
import { useToast } from '@/lib/toast';

// O link do e-mail traz o token e o e-mail na URL: /redefinir-senha?token=...&email=...
function ResetPasswordForm() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const showToast = useToast();

  const token = searchParams.get('token');
  const [form, setForm] = useState({
    email: searchParams.get('email') || '',
    password: '',
    password_confirmation: '',
  });
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
      await api('/reset-password', { method: 'POST', body: { ...form, token } });
      showToast('Senha redefinida. Entre com a nova senha.');
      router.replace('/entrar');
    } catch (err) {
      setErrors(err.errors || {});
      if (Object.keys(err.errors || {}).length === 0) setMessage(err.message);
      setSending(false);
    }
  }

  if (!token) {
    return (
      <>
        <h1>Link inválido</h1>
        <p className="muted">Este endereço não tem o código de redefinição. Peça um novo link.</p>
        <p className="auth-footer">
          <Link href="/esqueci-senha" className="link">Pedir novo link</Link>
        </p>
      </>
    );
  }

  return (
    <>
      <h1>Criar nova senha</h1>
      <p className="muted">Escolha uma senha com pelo menos 8 caracteres, com letras e números.</p>

      <form className="form" onSubmit={handleSubmit}>
        {message && <p className="alert">{message}</p>}

        <label className="field">
          E-mail
          <input name="email" type="email" value={form.email} onChange={handleChange} autoComplete="email" required />
          {errors.email && <span className="error-text">{errors.email[0]}</span>}
        </label>

        <label className="field">
          Nova senha
          <input name="password" type="password" value={form.password} onChange={handleChange} minLength={8} autoComplete="new-password" required />
          {errors.password && <span className="error-text">{errors.password[0]}</span>}
        </label>

        <label className="field">
          Confirme a nova senha
          <input name="password_confirmation" type="password" value={form.password_confirmation} onChange={handleChange} autoComplete="new-password" required />
        </label>

        <button type="submit" className="btn" disabled={sending}>
          {sending ? 'Salvando…' : 'Salvar nova senha'}
        </button>
      </form>
    </>
  );
}

// useSearchParams precisa estar dentro de um <Suspense> no Next.js.
export default function ResetPasswordPage() {
  return (
    <Suspense>
      <ResetPasswordForm />
    </Suspense>
  );
}
