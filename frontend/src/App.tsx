import { useEffect, useState } from "react";
import { addSpark, fetchSparks, Spark, updateSparkState, deleteSpark } from "./api";
import SparkForm from "./components/SparkForm.tsx";
import SparkList from "./components/SparkList";

function App() {
  const [sparks, setSparks] = useState<Spark[]>([]);

  const loadSparks = () => {
    fetchSparks().then(setSparks);
  };

  useEffect(() => {
    loadSparks();
  }, []);

  return (
    <div className="main-section">
      <h1>Sparks</h1>
      <SparkForm onSubmit={(text) => {
        addSpark(text).then(() => {
          loadSparks();
        });
      }} />
      <SparkList
        sparks={sparks}
        updateSparkState={updateSparkState}
        loadSparks={loadSparks}
        deleteSpark={deleteSpark}
      />
    </div>
  );
}

export default App;
