export interface Spark {
  id: string;
  text: string;
  state: SparkState;
  created_at: string;
}

const API_URL = "http://localhost:8080";

export enum SparkState {
  Open = "open",
  Ignored = "ignored",
  Searched = "searched",
  Finished = "finished",
}

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

export async function deleteSpark(id: string): Promise<void> {
  await fetch(`http://localhost:8080?id=${id}`, {
    method: "DELETE",
  });
}

export async function updateSparkState(
  id: string,
  state: SparkState
): Promise<void> {
  await fetch(`http://localhost:8080?id=${id}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ state }),
  });
}

