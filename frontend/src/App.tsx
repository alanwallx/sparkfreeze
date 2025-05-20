import { useEffect, useState } from "react";
import { addSpark, fetchSparks, Spark, updateSparkState, deleteSpark } from "./api";
import SparkForm from "./components/SparkForm.tsx";
import SparkList from "./components/SparkList";

function App() {
  const [sparks, setSparks] = useState<Spark[]>([]);
  const [sparksVisible, setSparksVisible] = useState(false);

  const loadSparks = () => {
    fetchSparks().then((data) => {
      const sorted = data.sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      );
      setSparks(sorted);
    });
  };

  useEffect(() => {
    loadSparks();
  }, []);

  return (
    <div className="main-section">
      <div className={"spark-header"}>
        <h1>Sparks</h1>
        <button
          className="toggle-sparks-button"
          onClick={() => setSparksVisible(!sparksVisible)}
        >{sparksVisible ? 'Hide' : 'Show'} Sparks</button>
      </div>
      <SparkForm onSubmit={(text) => {
        addSpark(text).then(() => {
          loadSparks();
        });
      }} />
      <div id = {"spark-added"}></div>
      {sparksVisible && (
        <SparkList
          sparks={sparks}
          updateSparkState={updateSparkState}
          loadSparks={loadSparks}
          deleteSpark={deleteSpark}
        />
      )}
    </div>
  );
}

export default App;
