'use client';

import { useRef, useState } from 'react';
import { api } from '@/lib/api';
import { useAuth } from '@/lib/auth';
import { useToast } from '@/lib/toast';
import { resizeImage } from '@/lib/utils';
import Avatar from './Avatar';
import Icon from './Icon';

// Formulário de nova publicação: texto, uma imagem, ou os dois.
export default function PostForm({ onCreated }) {
  const { user } = useAuth();
  const showToast = useToast();
  const fileInput = useRef(null);

  const [content, setContent] = useState('');
  const [image, setImage] = useState(null);
  const [preview, setPreview] = useState('');
  const [error, setError] = useState('');
  const [sending, setSending] = useState(false);

  async function handleImageChange(event) {
    const file = event.target.files[0];
    event.target.value = ''; // permite escolher o mesmo arquivo de novo
    if (!file) return;

    try {
      const resized = await resizeImage(file);
      setImage(resized);
      setPreview(URL.createObjectURL(resized));
      setError('');
    } catch (err) {
      setError(err.message);
    }
  }

  function removeImage() {
    setImage(null);
    setPreview('');
  }

  async function handleSubmit(event) {
    event.preventDefault();

    if (!content.trim() && !image) {
      setError('Escreva algo ou adicione uma imagem.');
      return;
    }

    // Com imagem, os dados vão como FormData (multipart) em vez de JSON.
    const formData = new FormData();
    if (content.trim()) formData.append('content', content.trim());
    if (image) formData.append('image', image);

    setSending(true);
    setError('');

    try {
      const post = await api('/posts', { method: 'POST', body: formData });
      setContent('');
      removeImage();
      onCreated(post);
      showToast('Publicado.');
    } catch (err) {
      setError(err.errors?.content?.[0] || err.errors?.image?.[0] || err.message);
    } finally {
      setSending(false);
    }
  }

  return (
    <form className="post-form" onSubmit={handleSubmit}>
      <Avatar src={user.profile_photo_url} size={44} />

      <div className="post-form-body">
        <textarea
          value={content}
          onChange={(e) => setContent(e.target.value)}
          placeholder="Conte algo para a vizinhança…"
          aria-label="Nova publicação"
          maxLength={1000}
          rows={2}
        />

        {preview && (
          <div className="post-form-preview">
            <img src={preview} alt="Prévia da imagem escolhida" />
            <button type="button" onClick={removeImage} aria-label="Remover imagem">
              <Icon name="close" size={18} />
            </button>
          </div>
        )}

        {error && <p className="error-text">{error}</p>}

        <div className="post-form-footer">
          <button
            type="button"
            className="btn btn-ghost btn-small"
            onClick={() => fileInput.current.click()}
            disabled={Boolean(image)}
            title="Cada publicação aceita uma imagem (JPG, PNG ou WEBP)"
          >
            <Icon name="image" size={20} />
            Imagem
          </button>
          <input ref={fileInput} type="file" accept="image/jpeg,image/png,image/webp" onChange={handleImageChange} hidden />

          {content.length > 900 && <span className="muted">{1000 - content.length}</span>}

          <button type="submit" className="btn btn-small" disabled={sending || (!content.trim() && !image)}>
            {sending ? 'Publicando…' : 'Publicar'}
          </button>
        </div>
      </div>
    </form>
  );
}
