export const BASE = import.meta.env.VITE_API_URL;

// Llama SIEMPRE antes de login/logout para preparar la cookie CSRF
export async function getCsrf() {
  await fetch(`${BASE}/sanctum/csrf-cookie`, {
    method: 'GET',
    credentials: 'include'
  });
}

// Login (crea cookie de sesión en el dominio del backend)
export async function login(email, password) {
  await getCsrf();
  const res = await fetch(`${BASE}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ email, password })
  });
  if (!res.ok) throw new Error('Login fallido');
  return res.json();
}

// Ejemplo de llamada autenticada
export async function getLinks() {
  const res = await fetch(`${BASE}/api/v1/links`, {
    method: 'GET',
    credentials: 'include'
  });
  if (!res.ok) throw new Error('No autorizado');
  return res.json();
}