'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useRef, useState } from 'react';
import Avatar from '@/components/Avatar';
import Icon from '@/components/Icon';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { useToast } from '@/lib/toast';
import { resizeImage } from '@/lib/utils';

export default function EditProfilePage() {
  const { user, setUser } = useAuth();
  const showToast = useToast();
  const router = useRouter();
  const fileInput = useRef(null);

  const [name, setName] = useState(user.name);
  const [bio, setBio] = useState(user.bio || '');
  const [photo, setPhoto] = useState(null); // nova foto escolhida
  const [preview, setPreview] = useState(user.profile_photo_url);
  const [removePhoto, setRemovePhoto] = useState(false);
  const [errors, setErrors] = useState({});
  const [message, setMessage] = useState('');
  const [saving, setSaving] = useState(false);

  async function handlePhotoChange(event) {
    const file = event.target.files[0];
    event.target.value = '';
    if (!file) return;

    try {
      const resized = await resizeImage(file, 600);
      setPhoto(resized);
      setPreview(URL.createObjectURL(resized));
      setRemovePhoto(false);
      setErrors({});
    } catch (err) {
      setErrors({ photo: [err.message] });
    }
  }

  function handleRemovePhoto() {
    setPhoto(null);
    setPreview(null);
    setRemovePhoto(true);
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSaving(true);
    setErrors({});
    setMessage('');

    // Upload de arquivo só funciona com POST no PHP, então enviamos _method=PUT
    // e o Laravel trata a requisição como PUT.
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', name.trim());
    formData.append('bio', bio.trim());
    if (photo) formData.append('photo', photo);
    if (removePhoto) formData.append('remove_photo', '1');

    try {
      const updated = await api('/profile', { method: 'POST', body: formData });
      setUser(updated);
      showToast('Perfil atualizado.');
      router.push(`/u/${updated.username}`);
    } catch (err) {
      setErrors(err.errors || {});
      if (Object.keys(err.errors || {}).length === 0) setMessage(err.message);
      setSaving(false);
    }
  }

  return (
    <>
      <header className="page-header">
        <Link href={`/u/${user.username}`} className="icon-button" aria-label="Voltar">
          <Icon name="back" size={20} />
        </Link>
        <h1>Editar perfil</h1>
      </header>

      <form className="form edit-profile" onSubmit={handleSubmit}>
        {message && <p className="alert">{message}</p>}

        <div className="edit-photo">
          <Avatar src={preview} size={96} />
          <div>
            <button type="button" className="btn btn-secondary btn-small" onClick={() => fileInput.current.click()}>
              <Icon name="camera" size={18} />
              Trocar foto
            </button>
            {preview && (
              <button type="button" className="btn btn-ghost btn-small" onClick={handleRemovePhoto}>
                Remover foto
              </button>
            )}
            <input ref={fileInput} type="file" accept="image/jpeg,image/png,image/webp" onChange={handlePhotoChange} hidden />
            {errors.photo ? (
              <p className="error-text">{errors.photo[0]}</p>
            ) : (
              <p className="hint">JPG, PNG ou WEBP. Fotos grandes são reduzidas antes do envio.</p>
            )}
          </div>
        </div>

        <label className="field">
          Nome
          <input value={name} onChange={(e) => setName(e.target.value)} maxLength={60} autoComplete="name" required />
          {errors.name && <span className="error-text">{errors.name[0]}</span>}
        </label>

        <label className="field">
          Nome de usuário
          <input value={`@${user.username}`} disabled />
          <span className="hint">O nome de usuário não pode ser alterado.</span>
        </label>

        <label className="field">
          Biografia
          <textarea value={bio} onChange={(e) => setBio(e.target.value)} maxLength={160} rows={3} placeholder="Conte um pouco sobre você" />
          {errors.bio && <span className="error-text">{errors.bio[0]}</span>}
        </label>

        <div className="form-buttons">
          <Link href={`/u/${user.username}`} className="btn btn-ghost">
            Cancelar
          </Link>
          <button type="submit" className="btn" disabled={saving}>
            {saving ? 'Salvando…' : 'Salvar alterações'}
          </button>
        </div>
      </form>
    </>
  );
}
