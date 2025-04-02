export interface Spark {
  id: string;
  text: string;
  ignored: boolean;
}

const API_URL = "http://localhost:8080";

export async function fetchSparks(): Promise<Spark[]> {
  const res = await fetch(API_URL);
  return res.json();
}

export async function addSpark(text: string): Promise<void> {
  await fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ text }),
  });
}

export async function ignoreSpark(id: string): Promise<void> {
  await fetch(`${API_URL}?id=${id}`, {
    method: "DELETE",
  });
}
