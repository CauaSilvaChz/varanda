// Tempo curto: "agora", "12 min", "3 h", "5 d" ou "12 de jun."
export function timeAgo(isoDate) {
  const seconds = (Date.now() - new Date(isoDate)) / 1000;

  if (seconds < 60) return 'agora';
  if (seconds < 3600) return `${Math.floor(seconds / 60)} min`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} h`;
  if (seconds < 7 * 86400) return `${Math.floor(seconds / 86400)} d`;

  return new Date(isoDate).toLocaleDateString('pt-BR', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Data completa, usada no texto que aparece ao passar o mouse: "3 de junho de 2026 às 08:20"
export function fullDate(isoDate) {
  return new Date(isoDate).toLocaleString('pt-BR', { dateStyle: 'long', timeStyle: 'short' });
}

export function plural(count, singular, pluralWord) {
  return `${count} ${count === 1 ? singular : pluralWord}`;
}

// Reduz fotos grandes no navegador antes do envio. Uma foto de celular de 5 MB
// vira um JPG de poucas centenas de KB e passa no limite de 2 MB da API.
export async function resizeImage(file, maxSize = 1600) {
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    throw new Error('Escolha uma imagem JPG, PNG ou WEBP.');
  }

  let image;
  try {
    image = await createImageBitmap(file);
  } catch {
    throw new Error('Não foi possível ler esta imagem. Tente outro arquivo.');
  }

  const scale = Math.min(1, maxSize / Math.max(image.width, image.height));

  // Imagem pequena: envia o arquivo original.
  if (scale === 1 && file.size <= 2 * 1024 * 1024) {
    return file;
  }

  const canvas = document.createElement('canvas');
  canvas.width = Math.round(image.width * scale);
  canvas.height = Math.round(image.height * scale);

  const context = canvas.getContext('2d');
  context.fillStyle = '#fff'; // fundo branco para PNG transparente
  context.fillRect(0, 0, canvas.width, canvas.height);
  context.drawImage(image, 0, 0, canvas.width, canvas.height);

  const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
  return new File([blob], 'imagem.jpg', { type: 'image/jpeg' });
}
