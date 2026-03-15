import { useEffect, useState } from "react";
import { addSpark, fetchSparks, Spark, updateSparkState, deleteSpark } from "./api";
import SparkForm from "./components/SparkForm.tsx";
import SparkList from "./components/SparkList";
import ToggleSwitch from "./components/ToggleSwitch.tsx";

import { GoogleSignInButton } from "./components/GoogleSignInButton";
import { fetchSession, logout } from "./api/auth";

type SessionUser = {
  id: number;
  email: string;
  name: string | null;
  created_at: string;
};

function App() {
  const [sparks, setSparks] = useState<Spark[]>([]);
  const [sparksVisible, setSparksVisible] = useState(false);
  const [user, setUser] = useState<SessionUser | null>(null);
  const [authLoading, setAuthLoading] = useState(true);

  const refreshSession = async () => {
    const data = await fetchSession();
    setUser(data.user ?? null);
  };

  const loadSparks = () => {
    fetchSparks().then((data) => {
      const sorted = data.sort(
        (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      );
      setSparks(sorted);
    });
  };

  useEffect(() => {
    (async () => {
      try {
        await refreshSession();
      } finally {
        setAuthLoading(false);
      }
    })();
  }, []);

  useEffect(() => {
    if (user) {
      loadSparks();
    } else {
      setSparks([]);
    }
  }, [user]);

  if (authLoading) {
    return (
      <div className="main-section">
        <div className={"spark-header"}>
          <h1>Sparks!</h1>
        </div>
        <div>Loading...</div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="main-section">
        <div className={"spark-header"}>
          <h1>SparkFreeze</h1>
        </div>

        <GoogleSignInButton
          onLoggedIn={async () => {
            await refreshSession();
          }}
        />
      </div>
    );
  }

  return (
    <div className="main-section">
      <div className={"spark-header"}>
        <h1>Sparks</h1>

        <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
          <div style={{ fontSize: 14 }}>
            Signed in as <strong>{user.email}</strong>
          </div>

          <button
            type="button"
            onClick={async () => {
              await logout();
              await refreshSession();
              setSparksVisible(false);
            }}
          >
            Logout
          </button>

          <ToggleSwitch checked={sparksVisible} onChange={setSparksVisible} label={"Show sparks"} />
        </div>
      </div>

      <SparkForm
        onSubmit={(text) => {
          addSpark(text).then(() => {
            loadSparks();
          });
        }}
        isListVisible={sparksVisible}
      />

      <div id={"spark-added"}></div>

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
