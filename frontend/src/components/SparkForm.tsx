import { useState } from "react";

interface SparkFormProps {
  onSubmit: (text: string) => void;
  isListVisible: boolean;
}

export default function SparkForm({ onSubmit, isListVisible }: SparkFormProps) {
  const [text, setText] = useState("");

  const animateSparkAdded = (text: string) => {
    if (isListVisible) return; // Don't animate if the list is not visible
    const sparkAdded = document.getElementById("spark-added");
    if (sparkAdded) {
      sparkAdded.innerHTML = '💥 ' + text;
      sparkAdded.classList.add("spark-added-animation");
      setTimeout(() => {
        sparkAdded.classList.remove("spark-added-animation");
        sparkAdded.innerHTML = ''; // clear the text after animation
      }, 2000);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!text.trim()) return;
    onSubmit(text);
    setText(""); // clear the input after submission
    animateSparkAdded(text);
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


