const API_URL = import.meta.env.VITE_API_URL ?? "http://localhost:8080";

export async function fetchNonce(): Promise<string> {
  const res = await fetch(`${API_URL}/auth/nonce`, {
    method: "GET",
    credentials: "include",
  });

  if (!res.ok) {
    throw new Error(`Failed to fetch nonce (${res.status})`);
  }

  const data = (await res.json()) as { nonce: string };
  if (!data?.nonce) {
    throw new Error("Nonce missing in response");
  }
  return data.nonce;
}

export async function loginWithGoogleCredential(credential: string): Promise<void> {
  const res = await fetch(`${API_URL}/auth/google`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    body: JSON.stringify({ credential }),
  });

  if (!res.ok) {
    const text = await res.text();
    throw new Error(text || `Login failed (${res.status})`);
  }
}

export async function fetchSession(): Promise<any> {
  const res = await fetch(`${API_URL}/auth/session`, {
    method: "GET",
    credentials: "include",
  });

  if (!res.ok) {
    throw new Error(`Failed to fetch session (${res.status})`);
  }

  return res.json();
}

export async function logout(): Promise<void> {
  const res = await fetch(`${API_URL}/auth/logout`, {
    method: "POST",
    credentials: "include",
  });

  if (!res.ok) {
    throw new Error(`Logout failed (${res.status})`);
  }
}
