'use client';

import { useEffect, useState } from 'react';
import { api } from './api';

// Carrega uma lista paginada da API (formato do Laravel: { data, current_page, last_page }).
// A lista começa do zero sempre que a URL muda (por exemplo, numa nova busca).
export function useList(url) {
  const [items, setItems] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    let ignore = false; // impede que a resposta de uma URL antiga apareça na tela

    setLoading(true);
    setError('');

    api(withPage(url, 1))
      .then((data) => {
        if (ignore) return;
        setItems(data.data);
        setPage(1);
        setHasMore(data.current_page < data.last_page);
      })
      .catch((err) => {
        if (!ignore) setError(err.message);
      })
      .finally(() => {
        if (!ignore) setLoading(false);
      });

    return () => {
      ignore = true;
    };
  }, [url]);

  async function loadMore() {
    setLoadingMore(true);

    try {
      const data = await api(withPage(url, page + 1));
      // Ignora itens repetidos (podem aparecer se algo novo foi publicado no meio do caminho).
      setItems((current) => [...current, ...data.data.filter((item) => !current.some((c) => c.id === item.id))]);
      setPage(page + 1);
      setHasMore(data.current_page < data.last_page);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoadingMore(false);
    }
  }

  return { items, setItems, loading, loadingMore, error, hasMore, loadMore };
}

function withPage(url, page) {
  return url + (url.includes('?') ? '&' : '?') + 'page=' + page;
}
