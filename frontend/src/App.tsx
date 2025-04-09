import { useEffect, useState } from "react";
import { addSpark, fetchSparks, Spark, SparkState, updateSparkState } from "./api";

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
    [SparkState.Open]: "💥",
    [SparkState.Ignored]: "❄️",
    [SparkState.Searched]: "🔥",
    [SparkState.Finished]: "🧨",
  };

  const [activeSparkId, setActiveSparkId] = useState<string | null>(null);


  return (
    <div className="main-section">
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
            className={`spark-item ${activeSparkId === spark.id ? "active" : ""}`}
            onClick={() =>
              setActiveSparkId(activeSparkId === spark.id ? null : spark.id)
            }
            style={{ position: "relative", marginBottom: "3rem" }} // add space for overlay
          >
            <div className="spark-content">
              {emojiMap[spark.state]}&nbsp;
              <span
                className="spark-text"
                style={{
                  textDecoration: spark.state === SparkState.Ignored ? "line-through" : "none",
                }}
              >
      {spark.text}
    </span>
            </div>

            {activeSparkId === spark.id && (
              <div className="spark-buttons">
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    updateSparkState(
                      spark.id,
                      spark.state === SparkState.Ignored ? SparkState.Open : SparkState.Ignored
                    ).then(loadSparks);
                  }}
                >
                  ❄️ {spark.state === SparkState.Ignored ? "Un-ignore" : "Ignore"}
                </button>
                <a
                  href={`https://www.google.com/search?q=${encodeURIComponent(
                    spark.text
                  )}`}
                  target="_blank"
                  rel="noreferrer"
                  onClick={(e) => e.stopPropagation()}
                >
                  🔥 Search
                </a>
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    updateSparkState(spark.id, SparkState.Finished).then(loadSparks);
                  }}
                >
                  🧨 Finished
                </button>
              </div>
            )}
          </li>


        ))}
      </ul>
    </div>
  );
}

export default App;
