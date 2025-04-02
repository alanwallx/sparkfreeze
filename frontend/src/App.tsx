import { useEffect, useState } from "react";
import { Spark, fetchSparks, addSpark, ignoreSpark } from "./api";

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

  const handleIgnoreSpark = (id: string) => {
    ignoreSpark(id).then(() => {
      loadSparks();
    });
  };

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

      <ul style={{ marginTop: "2rem" }}>
        {sparks.map((spark) => (
          <li key={spark.id} style={{ marginBottom: "1rem" }}>
            <span
              style={{
                textDecoration: spark.ignored ? "line-through" : "none",
              }}
            >
              {spark.text}
            </span>
            {!spark.ignored && (
              <>
                <button
                  style={{ marginLeft: "1rem" }}
                  onClick={() => handleIgnoreSpark(spark.id)}
                >
                  Ignore
                </button>
                <a
                  href={`https://www.google.com/search?q=${encodeURIComponent(
                    spark.text
                  )}`}
                  target="_blank"
                  rel="noreferrer"
                  style={{ marginLeft: "1rem" }}
                >
                  Search
                </a>
              </>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
}

export default App;
