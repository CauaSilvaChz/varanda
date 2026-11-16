// Foto de perfil redonda. Sem foto (ou se ela não carregar), mostra a imagem padrão.
export default function Avatar({ src, size = 44, alt = '' }) {
  return (
    <img
      className="avatar"
      src={src || '/default-avatar.svg'}
      alt={alt}
      width={size}
      height={size}
      style={{ width: size, height: size }}
      onError={(event) => (event.currentTarget.src = '/default-avatar.svg')}
    />
  );
}
