import { useState } from "react";

interface SparkFormProps {
  onSubmit: (text: string) => void;
}

export default function SparkForm({ onSubmit }: SparkFormProps) {
  const [text, setText] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!text.trim()) return;
    onSubmit(text);
    setText(""); // clear the input after submission
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        value={text}
        onChange={(e) => setText(e.target.value)}
        placeholder="Add a new spark..."
        className="new-spark-input"
      />
      <button type="submit" style={{ whiteSpace: "nowrap"}}>
        Save 💥
      </button>
    </form>
  );
}


