import { useEffect, useState } from "react";
import { Spark, fetchSparks, addSpark, updateSparkState } from "./api";

function App() {
  const [sparks, setSparks] = useState<Spark[]>([]);
  const [newSpark, setNewSpark] = useState("");

  const loadSparks = () => {
    fetchSparks().then(setSparks);
  };

  useEffect(() => {
    loadSparks();
  }, []);

  const handleAddSpark = () => {
    if (!newSpark.trim()) return;
    addSpark(newSpark).then(() => {
      setNewSpark("");
      loadSparks();
    });
  };

  const emojiMap = {
    open: "💥",
    ignored: "❄️",
    searched: "🔥",
    finished: "🧨",
  };

  const [activeSparkId, setActiveSparkId] = useState<string | null>(null);


  return (
    <div style={{ padding: "2rem", maxWidth: "600px", margin: "auto" }}>
      <h1>Sparks</h1>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          handleAddSpark();
        }}
      >
        <input
          type="text"
          value={newSpark}
          onChange={(e) => setNewSpark(e.target.value)}
          placeholder="Add a new spark..."
          style={{ width: "70%", marginRight: "1rem" }}
        />
        <button type="submit">Save Spark</button>
      </form>

      <ul className={"sparks-list"} style={{ marginTop: "2rem" }}>
        {sparks.map((spark) => (
          <li
            key={spark.id}
            className="spark-item"
            onClick={() => setActiveSparkId(spark.id === activeSparkId ? null : spark.id)} // toggle on mobile
          >
            {emojiMap[spark.state]}&nbsp;

            <span
              className="spark-text"
              style={{
                textDecoration: spark.state === "ignored" ? "line-through" : "none",
              }}
            >
    {spark.text}
  </span>

            <div
              className="spark-controls"
              style={{
                display:
                  activeSparkId === spark.id ? "inline" : undefined, // fallback for mobile
              }}
            >
              <button
                style={{ marginLeft: "1rem" }}
                onClick={(e) => {
                  e.stopPropagation(); // prevent bubbling on mobile
                  updateSparkState(
                    spark.id,
                    spark.state === "ignored" ? "open" : "ignored"
                  ).then(() => loadSparks());
                }}
              >
                {spark.state === "ignored" ? "Un-ignore" : "Ignore"}
              </button>

              <a
                href={`https://www.google.com/search?q=${encodeURIComponent(spark.text)}`}
                target="_blank"
                rel="noreferrer"
                style={{ marginLeft: "1rem" }}
                onClick={(e) => e.stopPropagation()} // prevent mobile tap clash
              >
                Search
              </a>
            </div>
          </li>

        ))}
      </ul>
    </div>
  );
}

export default App;
