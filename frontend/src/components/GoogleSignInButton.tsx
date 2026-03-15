import { useCallback, useEffect, useRef, useState } from "react";
import { fetchNonce, loginWithGoogleCredential } from "../api/auth";

type Props = {
  onLoggedIn?: () => void;
};

export function GoogleSignInButton({ onLoggedIn }: Props) {
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const initialisedRef = useRef(false);

  const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID as string | undefined;

  const ensureGisLoaded = useCallback(async () => {
    // GIS script sets window.google
    for (let i = 0; i < 50; i++) {
      if (window.google?.accounts?.id) return;
      await new Promise((r) => setTimeout(r, 50));
    }
    throw new Error("Google Identity Services script not loaded");
  }, []);

  const startLogin = useCallback(async () => {
    setError(null);

    if (!clientId) {
      setError("Missing VITE_GOOGLE_CLIENT_ID");
      return;
    }

    setBusy(true);
    try {
      await ensureGisLoaded();

      // Get one-shot nonce from backend (stored in PHP session)
      const nonce = await fetchNonce();

      window.google.accounts.id.initialize({
        client_id: clientId,
        nonce,
        callback: async (response: { credential?: string }) => {
          try {
            const credential = response.credential;
            if (!credential) throw new Error("Missing Google credential");

            await loginWithGoogleCredential(credential);
            onLoggedIn?.();
          } catch (e: any) {
            setError(e?.message ?? "Login failed");
          } finally {
            setBusy(false);
          }
        },
      });

      // Show the one-tap/prompt UX
      window.google.accounts.id.prompt((notification: any) => {
        // If user closes it, we should stop showing “busy”
        if (notification?.isNotDisplayed?.() || notification?.isSkippedMoment?.()) {
          setBusy(false);
        }
      });
    } catch (e: any) {
      setError(e?.message ?? "Login failed");
      setBusy(false);
    }
  }, [clientId, ensureGisLoaded, onLoggedIn]);

  // Optional: preflight GIS readiness once
  useEffect(() => {
    if (initialisedRef.current) return;
    initialisedRef.current = true;
    // no-op; just ensures we don’t re-run setup accidentally
  }, []);

  return (
    <div>
      <button type="button" onClick={startLogin} disabled={busy}>
        {busy ? "Signing in..." : "Sign in with Google"}
      </button>
      {error ? <div style={{ marginTop: 8, color: "crimson" }}>{error}</div> : null}
    </div>
  );
}
