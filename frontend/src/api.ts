export interface Spark {
  id: number;
  text: string;
  state: SparkState;
  created_at: string;
  updated_at: string;
  completed_note: string | null;
}

const API_URL = "http://localhost:8080";

export enum SparkState {
  Open = "open",
  Ignored = "ignored",
  Searched = "searched",
  Finished = "finished",
}

export async function fetchSparks(): Promise<Spark[]> {
  const res = await fetch(`${API_URL}/sparks`);
  const data = await res.json();
  return data.items;
}

export async function addSpark(text: string): Promise<Spark> {
  const res = await fetch(`${API_URL}/sparks`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ text }),
  });
  const data = await res.json();
  return data.item;
}

export async function deleteSpark(id: number): Promise<void> {
  await fetch(`${API_URL}/sparks/${id}`, {
    method: "DELETE",
  });
}

export async function updateSparkState(
  id: number,
  state: SparkState
): Promise<void> {
  await fetch(`${API_URL}/sparks/${id}`, {
    method: "PATCH",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ state }),
  });
}
