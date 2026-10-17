import { Platform } from "react-native";
import Constants from "expo-constants";
import * as SecureStore from "expo-secure-store";

const TOKEN_KEY = "littlestep.guardian.access-token";
const REQUEST_TIMEOUT_MS = 15000;

let sessionToken: string | null = null;

export type GuardianProfile = {
  id: number;
  first_name: string;
  middle_name: string | null;
  last_name: string;
};

export type GuardianChild = {
  id: number;
  first_name: string;
  middle_name: string | null;
  last_name: string;
  sex: string;
  date_of_birth: string;
};

type ApiErrorPayload = {
  error?: unknown;
  message?: unknown;
};

export class GuardianApiError extends Error {
  constructor(
    message: string,
    public readonly status?: number,
  ) {
    super(message);
    this.name = "GuardianApiError";
  }
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}

function isGuardianProfile(value: unknown): value is GuardianProfile {
  return isRecord(value)
    && typeof value.id === "number"
    && typeof value.first_name === "string"
    && (typeof value.middle_name === "string" || value.middle_name === null)
    && typeof value.last_name === "string";
}

function isGuardianChild(value: unknown): value is GuardianChild {
  return isRecord(value)
    && typeof value.id === "number"
    && typeof value.first_name === "string"
    && (typeof value.middle_name === "string" || value.middle_name === null)
    && typeof value.last_name === "string"
    && typeof value.sex === "string"
    && typeof value.date_of_birth === "string";
}

function getApiBaseUrl(): URL {
  let configuredUrl = process.env.EXPO_PUBLIC_API_URL?.trim();

  if (!configuredUrl && __DEV__) {
    const expoHost = Constants.expoConfig?.hostUri;
    const host = Platform.OS === "web"
      ? "localhost"
      : expoHost
        ? new URL(`http://${expoHost}`).hostname
        : null;

    if (host) {
      configuredUrl = `http://${host}/LittleStep/backend/public`;
    }
  }

  if (!configuredUrl) {
    throw new GuardianApiError(
      "The app is not connected to the server. Set EXPO_PUBLIC_API_URL to your Laravel backend address and restart Expo.",
    );
  }

  let baseUrl: URL;
  try {
    baseUrl = new URL(configuredUrl);
  } catch {
    throw new GuardianApiError("The configured server address is invalid.");
  }

  if (
    baseUrl.protocol !== "https:" &&
    !(__DEV__ && baseUrl.protocol === "http:")
  ) {
    throw new GuardianApiError("A secure HTTPS server address is required.");
  }

  baseUrl.pathname = `${baseUrl.pathname.replace(/\/+$/, "")}/`;
  baseUrl.search = "";
  baseUrl.hash = "";

  return baseUrl;
}

async function readAccessToken(): Promise<string | null> {
  if (sessionToken) {
    return sessionToken;
  }

  if (Platform.OS === "web") {
    return null;
  }

  if (! await SecureStore.isAvailableAsync()) {
    return null;
  }

  sessionToken = await SecureStore.getItemAsync(TOKEN_KEY);

  return sessionToken;
}

async function saveAccessToken(token: string, rememberMe: boolean): Promise<void> {
  if (Platform.OS !== "web") {
    const secureStorageAvailable = await SecureStore.isAvailableAsync();

    if (rememberMe && !secureStorageAvailable) {
      throw new GuardianApiError(
        "Secure storage is unavailable, so this device cannot safely remember your sign-in.",
      );
    }

    if (secureStorageAvailable) {
      if (rememberMe) {
        await SecureStore.setItemAsync(TOKEN_KEY, token, {
          keychainAccessible: SecureStore.WHEN_UNLOCKED_THIS_DEVICE_ONLY,
        });
      } else {
        await SecureStore.deleteItemAsync(TOKEN_KEY);
      }
    }
  } else if (rememberMe) {
    throw new GuardianApiError(
      "Remember me is available only in the iOS and Android apps.",
    );
  }

  sessionToken = token;
}

export async function clearAccessToken(): Promise<void> {
  sessionToken = null;

  if (Platform.OS !== "web" && await SecureStore.isAvailableAsync()) {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
  }
}

async function requestJson<T>(
  path: string,
  options: {
    method?: "GET" | "POST";
    body?: unknown;
    requiresAuth?: boolean;
    accessToken?: string;
  } = {},
): Promise<T> {
  const baseUrl = getApiBaseUrl();
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

  try {
    const headers: Record<string, string> = {
      Accept: "application/json",
    };

    if (options.body !== undefined) {
      headers["Content-Type"] = "application/json";
    }

    if (options.requiresAuth !== false) {
      const token = options.accessToken ?? await readAccessToken();
      if (token) {
        headers.Authorization = `Bearer ${token}`;
      }
    }

    const response = await fetch(new URL(path.replace(/^\/+/, ""), baseUrl), {
      method: options.method ?? "GET",
      headers,
      body: options.body === undefined ? undefined : JSON.stringify(options.body),
      signal: controller.signal,
    });
    const responseText = await response.text();
    let payload: unknown = null;

    if (responseText) {
      try {
        payload = JSON.parse(responseText);
      } catch {
        throw new GuardianApiError(
          "The server returned an unreadable response.",
          response.status,
        );
      }
    }

    if (!response.ok) {
      if (
        options.requiresAuth !== false &&
        (response.status === 401 || response.status === 403)
      ) {
        await clearAccessToken();
      }

      const errorPayload = payload as ApiErrorPayload | null;
      let message = "The request could not be completed.";

      if (typeof errorPayload?.error === "string") {
        message = errorPayload.error;
      } else if (typeof errorPayload?.message === "string") {
        message = errorPayload.message;
      } else if (response.status === 429) {
        message = "Too many sign-in attempts. Please wait a moment and try again.";
      }

      throw new GuardianApiError(message, response.status);
    }

    return payload as T;
  } catch (error) {
    if (error instanceof GuardianApiError) {
      throw error;
    }

    if (controller.signal.aborted) {
      throw new GuardianApiError("The server took too long to respond. Try again.");
    }

    throw new GuardianApiError(
      "Could not connect to LittleStep. Check your connection and server address.",
    );
  } finally {
    clearTimeout(timeout);
  }
}

export async function loginGuardian(
  email: string,
  password: string,
  rememberMe: boolean,
): Promise<GuardianProfile> {
  const response = await requestJson<unknown>("/api/v1/guardian/login", {
    method: "POST",
    body: { email: email.trim().toLowerCase(), password },
    requiresAuth: false,
  });

  if (
    !isRecord(response) ||
    typeof response.token !== "string" ||
    !response.token ||
    !isGuardianProfile(response.guardian)
  ) {
    throw new GuardianApiError("The server returned an invalid sign-in response.");
  }

  try {
    await saveAccessToken(response.token, rememberMe);
  } catch {
    try {
      await requestJson<{ message: string }>("/api/v1/guardian/logout", {
        method: "POST",
        accessToken: response.token,
      });
    } catch {
      throw new GuardianApiError(
        "Sign-in could not be saved securely, and the server could not confirm token revocation. The token will expire within 12 hours.",
      );
    }

    throw new GuardianApiError("Sign-in could not be saved securely. Please try again.");
  }

  return response.guardian;
}

export async function getGuardianProfile(): Promise<GuardianProfile> {
  const profile = await requestJson<unknown>("/api/v1/guardian/profile");
  if (!isGuardianProfile(profile)) {
    throw new GuardianApiError("The server returned an invalid guardian profile.");
  }

  return profile;
}

export async function getGuardianChildren(): Promise<GuardianChild[]> {
  const children = await requestJson<unknown>("/api/v1/guardian/children");
  if (!Array.isArray(children) || !children.every(isGuardianChild)) {
    throw new GuardianApiError("The server returned invalid child records.");
  }

  return children;
}

export async function restoreGuardianSession(): Promise<GuardianProfile | null> {
  const token = await readAccessToken();
  if (!token) {
    return null;
  }

  try {
    return await getGuardianProfile();
  } catch (error) {
    if (
      error instanceof GuardianApiError &&
      (error.status === 401 || error.status === 403)
    ) {
      await clearAccessToken();
      return null;
    }

    throw error;
  }
}

export async function logoutGuardian(): Promise<void> {
  try {
    const token = await readAccessToken();
    if (token) {
      await requestJson<{ message: string }>("/api/v1/guardian/logout", {
        method: "POST",
      });
    }
  } finally {
    await clearAccessToken();
  }
}

export function getGuardianApiErrorMessage(error: unknown): string {
  if (error instanceof GuardianApiError) {
    return error.message;
  }

  return "Something went wrong. Please try again.";
}
