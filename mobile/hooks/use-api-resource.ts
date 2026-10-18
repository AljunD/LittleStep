import { useCallback, useState } from "react";
import { useFocusEffect, useNavigation } from "@react-navigation/native";
import {
  getGuardianApiErrorMessage,
  isSessionEndedError,
} from "../services/guardianApi";

export type ApiResource<T> = {
  data: T | null;
  /** First load (or a retry after an error). */
  loading: boolean;
  /** Pull-to-refresh in progress. */
  refreshing: boolean;
  error: string | null;
  retry: () => void;
  refresh: () => void;
};

const SESSION_ENDED_MESSAGE = "Your session has ended. Please sign in again.";

/**
 * Returns a handler that sends the guardian back to Login when `error` is a 401/403 (guardianApi has
 * already cleared the stored token). It returns true when it redirected, so callers can skip their own message.
 */
export function useSessionEndedRedirect(): (error: unknown) => boolean {
  const navigation = useNavigation<any>();

  return useCallback((error: unknown) => {
    if (!isSessionEndedError(error)) {
      return false;
    }

    navigation.reset({
      index: 0,
      routes: [{ name: "Login", params: { initialError: SESSION_ENDED_MESSAGE } }],
    });
    return true;
  }, [navigation]);
}

/**
 * Loads `load()` each time the screen comes into view, with retry and pull-to-refresh.
 * A 401/403 means the session ended, so this returns the guardian to the Login screen.
 *
 * `load` must be stable between renders (wrap it in useCallback).
 */
export function useApiResource<T>(load: () => Promise<T>): ApiResource<T> {
  const redirectIfSessionEnded = useSessionEndedRedirect();
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleError = useCallback((loadError: unknown) => {
    if (!redirectIfSessionEnded(loadError)) {
      setError(getGuardianApiErrorMessage(loadError));
    }
  }, [redirectIfSessionEnded]);

  useFocusEffect(
    useCallback(() => {
      let isActive = true;

      load()
        .then((result) => {
          if (isActive) {
            setData(result);
            setError(null);
          }
        })
        .catch((loadError: unknown) => {
          if (isActive) {
            handleError(loadError);
          }
        })
        .finally(() => {
          if (isActive) {
            setLoading(false);
          }
        });

      return () => {
        isActive = false;
      };
    }, [load, handleError]),
  );

  const reload = useCallback(async (asRefresh: boolean) => {
    if (asRefresh) {
      setRefreshing(true);
    } else {
      setLoading(true);
    }

    try {
      setData(await load());
      setError(null);
    } catch (loadError) {
      handleError(loadError);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [load, handleError]);

  return {
    data,
    loading,
    refreshing,
    error,
    retry: () => void reload(false),
    refresh: () => void reload(true),
  };
}
