const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

// Faz uma requisição para a API Laravel e devolve o JSON da resposta.
// Se der erro, lança um Error com a mensagem e, em erros de validação,
// os erros de cada campo em error.errors (ex.: { email: ['E-mail inválido.'] }).
export async function api(path, { method = 'GET', body } = {}) {
  const token = localStorage.getItem('token');
  const headers = { Accept: 'application/json' };

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  // Arquivos (FormData) são enviados como estão; o resto vai como JSON.
  if (body && !(body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify(body);
  }

  let response;
  try {
    response = await fetch(API_URL + path, { method, headers, body });
  } catch {
    throw new Error('Não foi possível conectar à API. Verifique se o Laravel está rodando.');
  }

  // Token expirado ou inválido: encerra a sessão e volta para o login.
  if (response.status === 401 && token) {
    localStorage.removeItem('token');
    window.location.href = '/entrar';
  }

  const data = response.status === 204 ? null : await response.json().catch(() => null);

  if (!response.ok) {
    const error = new Error(errorMessage(response.status, data));
    error.status = response.status;
    error.errors = data?.errors || {};
    throw error;
  }

  return data;
}

function errorMessage(status, data) {
  if (status === 404) return 'Não encontramos o que você procurava.';
  if (status === 413) return 'O arquivo enviado é grande demais.';
  if (status === 429) return 'Muitas tentativas. Aguarde um minuto e tente novamente.';
  if (status >= 500) return 'Algo deu errado no servidor. Tente novamente em instantes.';
  return data?.message || 'Não foi possível concluir a ação.';
}
