'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { useToast } from '@/lib/toast';

export default function RegisterPage() {
  const { login } = useAuth();
  const showToast = useToast();
  const router = useRouter();

  const [form, setForm] = useState({ name: '', username: '', email: '', password: '', password_confirmation: '' });
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState('');
  const [sending, setSending] = useState(false);

  function handleChange(event) {
    let { name, value } = event.target;

    // O nome de usuário só aceita letras minúsculas, números e _.
    if (name === 'username') {
      value = value.toLowerCase().replace(/[^a-z0-9_]/g, '');
    }

    setForm({ ...form, [name]: value });
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSending(true);
    setErrors({});
    setMessage('');

    try {
      const data = await api('/register', { method: 'POST', body: form });
      login(data.token, data.user);
      showToast('Conta criada. Boas-vindas à Varanda!');
      router.replace('/');
    } catch (err) {
      setErrors(err.errors || {});
      if (Object.keys(err.errors || {}).length === 0) setMessage(err.message);
      setSending(false);
    }
  }

  return (
    <>
      <h1>Criar conta</h1>
      <p className="muted">Leva menos de um minuto.</p>

      <form className="form" onSubmit={handleSubmit}>
        {message && <p className="alert">{message}</p>}

        <label className="field">
          Nome
          <input name="name" value={form.name} onChange={handleChange} maxLength={60} autoComplete="name" required />
          {errors.name && <span className="error-text">{errors.name[0]}</span>}
        </label>

        <label className="field">
          Nome de usuário
          <input name="username" value={form.username} onChange={handleChange} minLength={3} maxLength={20} autoComplete="username" required />
          {errors.username ? (
            <span className="error-text">{errors.username[0]}</span>
          ) : (
            <span className="hint">Letras minúsculas, números e _. Não dá para trocar depois.</span>
          )}
        </label>

        <label className="field">
          E-mail
          <input name="email" type="email" value={form.email} onChange={handleChange} autoComplete="email" required />
          {errors.email && <span className="error-text">{errors.email[0]}</span>}
        </label>

        <label className="field">
          Senha
          <input name="password" type="password" value={form.password} onChange={handleChange} minLength={8} autoComplete="new-password" required />
          {errors.password ? (
            <span className="error-text">{errors.password[0]}</span>
          ) : (
            <span className="hint">Pelo menos 8 caracteres, com letras e números.</span>
          )}
        </label>

        <label className="field">
          Confirme a senha
          <input name="password_confirmation" type="password" value={form.password_confirmation} onChange={handleChange} autoComplete="new-password" required />
        </label>

        <button type="submit" className="btn" disabled={sending}>
          {sending ? 'Criando conta…' : 'Criar conta'}
        </button>
      </form>

      <p className="auth-footer">
        Já tem conta? <Link href="/entrar" className="link">Entrar</Link>
      </p>
    </>
  );
}
