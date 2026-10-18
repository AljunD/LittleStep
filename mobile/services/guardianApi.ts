import { Platform } from "react-native";
import Constants from "expo-constants";
import * as SecureStore from "expo-secure-store";

const TOKEN_KEY = "littlestep.guardian.access-token";
const REQUEST_TIMEOUT_MS = 15000;
const PHOTO_MAX_BYTES = 5 * 1024 * 1024;
const PHOTO_MIME_TYPES = ["image/jpeg", "image/png", "image/gif"];

const PASSWORD_CHANGE_KEY = "littlestep.guardian.must-change-password";

let sessionToken: string | null = null;
let mustChangePassword = false;
let passwordReminderDismissed = false;

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
  photo_url: string | null;
};

export type GuardianDashboard = {
  child: GuardianChild;
  progress: {
    status: string;
    evaluation_number: number | null;
    latest_round: number | null;
    rounds: Record<number, {
      date: string | null;
      completed_domains: number;
      standard_score: number | null;
      interpretation: string | null;
      domains: Record<string, {
        label: string;
        raw_score: number | null;
        scaled_score: number | null;
      }>;
    }>;
    recent_activity?: GuardianRecentActivity[];
  };
};

export type DisplayValue = string | number | null;

export type GuardianAccount = GuardianProfile & {
  email: string;
  sex: string | null;
  contact_number: string | null;
  relationship_to_child: string | null;
  address: string | null;
};

export type GuardianChildDetails = {
  id: number;
  first_name: string;
  middle_name: string | null;
  last_name: string;
  sex: string;
  date_of_birth: string;
  address: DisplayValue;
  handedness: DisplayValue;
  is_studying: boolean;
  school_name: DisplayValue;
  fathers_name: DisplayValue;
  fathers_age: DisplayValue;
  fathers_occupation: DisplayValue;
  fathers_education: DisplayValue;
  mothers_name: DisplayValue;
  mothers_age: DisplayValue;
  mothers_occupation: DisplayValue;
  mothers_education: DisplayValue;
  number_of_siblings: DisplayValue;
  birth_order: DisplayValue;
  photo_url: string | null;
};

export type GuardianContact = {
  child: Pick<GuardianChild, "id" | "first_name" | "middle_name" | "last_name">;
  teacher: {
    first_name: string;
    middle_name: string | null;
    last_name: string;
    contact_number: string | null;
  } | null;
};

export type GuardianProgressUpdate = {
  child: GuardianChild;
  domain: string;
  label: string;
  updatedAt: string;
};

export type GuardianRecentActivity = {
  domain: string;
  label: string;
  last_updated_at: string | null;
};

export type GuardianItemResult = {
  present: "check" | "hyphen" | null;
  comments: string | null;
} | null;

export type GuardianDomainItem = {
  id: number;
  item_number: number | null;
  activity: string;
  results: Record<number, GuardianItemResult>;
};

export type GuardianDomainRound = {
  date: string | null;
  raw_score: number | null;
  scaled_score: number | null;
  interpretation?: string | null;
  assessed_items: number;
};

export type GuardianSkillItem = {
  id: number;
  item_number: number | null;
  activity: string;
};

export type GuardianSkillsGroup = {
  label: string;
  round: number;
  items: GuardianSkillItem[];
};

export type GuardianExplanationDomain = {
  label: string;
  items_total: number;
  items_answered: number;
  items_present: number;
  raw_score: number | null;
  scaled_score: number | null;
  interpretation: string | null;
};

export type GuardianExplanation = {
  round: number;
  evaluation_date: string | null;
  age: {
    label: string;
    years: number;
    months: number;
    days: number;
    band: string | null;
    band_range: string | null;
  } | null;
  domains: Record<string, GuardianExplanationDomain>;
  scored_domains: number;
  sum_scaled_score: number | null;
  standard_score: number | null;
  overall: string | null;
  interpretation_code: string | null;
  steps: string[];
  warnings: string[];
};

export type GuardianInsights = {
  child: {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
  };
  latest_round: number | null;
  rounds: GuardianExplanation[];
  skills_to_practice: Record<string, GuardianSkillsGroup>;
};

export type GuardianDomainDetail = {
  child: {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
  };
  domain: {
    key: string;
    label: string;
  };
  progress: {
    status: string;
    latest_round: number | null;
    last_updated_at: string | null;
    rounds: Record<number, GuardianDomainRound>;
  };
  items: GuardianDomainItem[];
  skills_to_practice?: GuardianSkillsGroup | null;
};

export type GuardianPhotoSource = {
  uri: string;
};

type ApiErrorPayload = {
  error?: unknown;
  message?: unknown;
  errors?: unknown;
};

export class GuardianApiError extends Error {
  constructor(
    message: string,
    public readonly status?: number,
    public readonly fieldErrors: Record<string, string> = {},
  ) {
    super(message);
    this.name = "GuardianApiError";
  }
}

export type ChangePasswordInput = {
  currentPassword: string;
  password: string;
  passwordConfirmation: string;
};

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
    && typeof value.date_of_birth === "string"
    && (typeof value.photo_url === "string" || value.photo_url === null);
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

  try {
    sessionToken = await SecureStore.getItemAsync(TOKEN_KEY);
  } catch {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    sessionToken = null;
  }

  return sessionToken;
}

export async function isRememberMeSupported(): Promise<boolean> {
  if (Platform.OS === "web") {
    return false;
  }

  try {
    return await SecureStore.isAvailableAsync();
  } catch {
    return false;
  }
}

function writeStoredToken(token: string): Promise<void> {
  return SecureStore.setItemAsync(TOKEN_KEY, token, {
    keychainAccessible: SecureStore.WHEN_UNLOCKED_THIS_DEVICE_ONLY,
  });
}

async function saveAccessToken(token: string, rememberMe: boolean): Promise<void> {
  if (rememberMe) {
    if (! await isRememberMeSupported()) {
      throw new GuardianApiError(
        Platform.OS === "web"
          ? "Remember me is available only in the iOS and Android apps."
          : "Secure storage is unavailable, so this device cannot safely remember your sign-in.",
      );
    }

    try {
      await writeStoredToken(token);
    } catch {
      await SecureStore.deleteItemAsync(TOKEN_KEY);
      await writeStoredToken(token);
    }
  } else if (Platform.OS !== "web" && await SecureStore.isAvailableAsync()) {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
  }

  sessionToken = token;
}

export async function clearAccessToken(): Promise<void> {
  sessionToken = null;
  mustChangePassword = false;
  passwordReminderDismissed = false;

  if (Platform.OS !== "web" && await SecureStore.isAvailableAsync()) {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
    await SecureStore.deleteItemAsync(PASSWORD_CHANGE_KEY);
  }
}

async function setMustChangePassword(value: boolean, persist: boolean): Promise<void> {
  mustChangePassword = value;
  passwordReminderDismissed = false;

  if (Platform.OS === "web" || ! await SecureStore.isAvailableAsync()) {
    return;
  }

  try {
    if (value && persist) {
      await SecureStore.setItemAsync(PASSWORD_CHANGE_KEY, "1", {
        keychainAccessible: SecureStore.WHEN_UNLOCKED_THIS_DEVICE_ONLY,
      });
    } else {
      await SecureStore.deleteItemAsync(PASSWORD_CHANGE_KEY);
    }
  } catch {
  }
}

export function hasGuardianSession(): boolean {
  return sessionToken !== null;
}

export function isUsingDefaultPassword(): boolean {
  return mustChangePassword;
}

export function shouldShowPasswordReminder(): boolean {
  return mustChangePassword && !passwordReminderDismissed;
}

export function dismissPasswordReminder(): void {
  passwordReminderDismissed = true;
}

async function requestJson<T>(
  path: string,
  options: {
    method?: "GET" | "POST" | "PUT";
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
      const fieldErrors: Record<string, string> = {};
      if (isRecord(errorPayload?.errors)) {
        for (const [field, messages] of Object.entries(errorPayload.errors)) {
          if (Array.isArray(messages) && typeof messages[0] === "string") {
            fieldErrors[field] = messages[0];
          }
        }
      }
      let message = "The request could not be completed.";

      if (typeof errorPayload?.error === "string") {
        message = errorPayload.error;
      } else if (typeof errorPayload?.message === "string") {
        message = errorPayload.message;
      } else if (response.status === 429) {
        message = "Too many sign-in attempts. Please wait a moment and try again.";
      }

      throw new GuardianApiError(message, response.status, fieldErrors);
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
  } catch (saveError) {
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

    throw new GuardianApiError(
      saveError instanceof GuardianApiError
        ? saveError.message
        : "This device could not save your sign-in securely. Try again, or sign in without Remember me.",
    );
  }

  await setMustChangePassword(response.must_change_password === true, rememberMe);

  return response.guardian;
}

export async function changeGuardianPassword(input: ChangePasswordInput): Promise<string> {
  const response = await requestJson<unknown>("/api/v1/guardian/password", {
    method: "PUT",
    body: {
      current_password: input.currentPassword,
      password: input.password,
      password_confirmation: input.passwordConfirmation,
    },
  });

  if (!isRecord(response) || typeof response.message !== "string") {
    throw new GuardianApiError("The server returned an invalid response.");
  }

  await setMustChangePassword(false, false);

  return response.message;
}

export async function getGuardianProfile(): Promise<GuardianProfile> {
  const profile = await requestJson<unknown>("/api/v1/guardian/profile");
  if (!isGuardianProfile(profile)) {
    throw new GuardianApiError("The server returned an invalid guardian profile.");
  }

  return profile;
}

function isOptionalString(value: unknown): value is string | null {
  return typeof value === "string" || value === null;
}

function isDisplayValue(value: unknown): value is DisplayValue {
  return typeof value === "string" || typeof value === "number" || value === null;
}

const CHILD_DETAIL_FIELDS = [
  "address", "handedness", "school_name",
  "fathers_name", "fathers_age", "fathers_occupation", "fathers_education",
  "mothers_name", "mothers_age", "mothers_occupation", "mothers_education",
  "number_of_siblings", "birth_order",
] as const;

export async function getGuardianAccount(): Promise<GuardianAccount> {
  const account = await requestJson<unknown>("/api/v1/guardian/profile");
  if (
    !isRecord(account)
    || typeof account.email !== "string"
    || !isOptionalString(account.sex)
    || !isOptionalString(account.contact_number)
    || !isOptionalString(account.relationship_to_child)
    || !isOptionalString(account.address)
    || !isGuardianProfile(account)
  ) {
    throw new GuardianApiError("The server returned an invalid guardian profile.");
  }

  return account as GuardianAccount;
}

export async function getGuardianChild(childId: number): Promise<GuardianChildDetails> {
  const child = await requestJson<unknown>(`/api/v1/guardian/children/${childId}`);
  if (
    !isRecord(child)
    || typeof child.is_studying !== "boolean"
    || !CHILD_DETAIL_FIELDS.every((field) => isDisplayValue(child[field]))
    || !isGuardianChild(child)
  ) {
    throw new GuardianApiError("The server returned invalid child details.");
  }

  return child as GuardianChildDetails;
}

function isGuardianContact(value: unknown): value is GuardianContact {
  if (!isRecord(value) || !isRecord(value.child) || typeof value.child.id !== "number") {
    return false;
  }

  const teacher = value.teacher;
  return teacher === null || (
    isRecord(teacher)
    && typeof teacher.first_name === "string"
    && typeof teacher.last_name === "string"
    && isOptionalString(teacher.middle_name)
    && isOptionalString(teacher.contact_number)
  );
}

export async function getGuardianContacts(): Promise<GuardianContact[]> {
  const contacts = await requestJson<unknown>("/api/v1/guardian/contacts");
  if (!Array.isArray(contacts) || !contacts.every(isGuardianContact)) {
    throw new GuardianApiError("The server returned invalid contact details.");
  }

  return contacts;
}

export async function getGuardianProgressUpdates(): Promise<GuardianProgressUpdate[]> {
  const children = await getGuardianChildren();
  const dashboards = await Promise.all(children.map((child) => getGuardianDashboard(child.id)));

  return dashboards
    .flatMap((dashboard) => (dashboard.progress.recent_activity ?? [])
      .filter((activity) => activity.last_updated_at !== null)
      .map((activity) => ({
        child: dashboard.child,
        domain: activity.domain,
        label: activity.label,
        updatedAt: activity.last_updated_at as string,
      })))
    .sort((a, b) => Date.parse(b.updatedAt) - Date.parse(a.updatedAt));
}

export function isSessionEndedError(error: unknown): boolean {
  return error instanceof GuardianApiError && (error.status === 401 || error.status === 403);
}

export async function getGuardianChildren(): Promise<GuardianChild[]> {
  const children = await requestJson<unknown>("/api/v1/guardian/children");
  if (!Array.isArray(children) || !children.every(isGuardianChild)) {
    throw new GuardianApiError("The server returned invalid child records.");
  }

  return children;
}

export async function getGuardianDashboard(childId: number): Promise<GuardianDashboard> {
  const dashboard = await requestJson<unknown>(
    `/api/v1/guardian/children/${childId}/dashboard`,
  );
  if (
    !isRecord(dashboard)
    || !isGuardianChild(dashboard.child)
    || !isRecord(dashboard.progress)
    || typeof dashboard.progress.status !== "string"
  ) {
    throw new GuardianApiError("The server returned invalid dashboard data.");
  }

  const recentActivity = dashboard.progress.recent_activity;
  if (
    recentActivity !== undefined
    && (!Array.isArray(recentActivity) || !recentActivity.every(isGuardianRecentActivity))
  ) {
    throw new GuardianApiError("The server returned invalid dashboard data.");
  }

  return dashboard as GuardianDashboard;
}

function isGuardianRecentActivity(value: unknown): value is GuardianRecentActivity {
  return isRecord(value)
    && typeof value.domain === "string"
    && typeof value.label === "string"
    && (typeof value.last_updated_at === "string" || value.last_updated_at === null);
}

function isGuardianDomainItem(value: unknown): value is GuardianDomainItem {
  return isRecord(value)
    && typeof value.id === "number"
    && typeof value.activity === "string"
    && isRecord(value.results);
}

export async function getGuardianChildDomain(
  childId: number,
  domainKey: string,
): Promise<GuardianDomainDetail> {
  const detail = await requestJson<unknown>(
    `/api/v1/guardian/children/${childId}/domains/${encodeURIComponent(domainKey)}`,
  );
  if (
    !isRecord(detail)
    || !isRecord(detail.domain)
    || typeof detail.domain.key !== "string"
    || typeof detail.domain.label !== "string"
    || !isRecord(detail.progress)
    || typeof detail.progress.status !== "string"
    || !isRecord(detail.progress.rounds)
    || !Array.isArray(detail.items)
    || !detail.items.every(isGuardianDomainItem)
    || (detail.skills_to_practice != null && !isGuardianSkillsGroup(detail.skills_to_practice))
  ) {
    throw new GuardianApiError("The server returned invalid domain data.");
  }

  return detail as GuardianDomainDetail;
}

function isNumberOrNull(value: unknown): value is number | null {
  return value === null || typeof value === "number";
}

function isStringArray(value: unknown): value is string[] {
  return Array.isArray(value) && value.every((entry) => typeof entry === "string");
}

function isGuardianSkillsGroup(value: unknown): value is GuardianSkillsGroup {
  return isRecord(value)
    && typeof value.label === "string"
    && typeof value.round === "number"
    && Array.isArray(value.items)
    && value.items.every((item) => isRecord(item)
      && typeof item.id === "number"
      && isNumberOrNull(item.item_number)
      && typeof item.activity === "string");
}

function isGuardianExplanationDomain(value: unknown): value is GuardianExplanationDomain {
  return isRecord(value)
    && typeof value.label === "string"
    && typeof value.items_total === "number"
    && typeof value.items_answered === "number"
    && typeof value.items_present === "number"
    && isNumberOrNull(value.raw_score)
    && isNumberOrNull(value.scaled_score)
    && isOptionalString(value.interpretation);
}

function isGuardianExplanation(value: unknown): value is GuardianExplanation {
  return isRecord(value)
    && typeof value.round === "number"
    && isOptionalString(value.evaluation_date)
    && (value.age === null || (isRecord(value.age) && typeof value.age.label === "string"))
    && isRecord(value.domains)
    && Object.values(value.domains).every(isGuardianExplanationDomain)
    && typeof value.scored_domains === "number"
    && isNumberOrNull(value.sum_scaled_score)
    && isNumberOrNull(value.standard_score)
    && isOptionalString(value.overall)
    && isStringArray(value.steps)
    && isStringArray(value.warnings);
}

export async function getGuardianChildInsights(childId: number): Promise<GuardianInsights> {
  const insights = await requestJson<unknown>(`/api/v1/guardian/children/${childId}/insights`);
  if (
    !isRecord(insights)
    || !isRecord(insights.child)
    || typeof insights.child.id !== "number"
    || !isNumberOrNull(insights.latest_round)
    || !Array.isArray(insights.rounds)
    || !insights.rounds.every(isGuardianExplanation)
    || !isRecord(insights.skills_to_practice)
    || !Object.values(insights.skills_to_practice).every(isGuardianSkillsGroup)
  ) {
    throw new GuardianApiError("The server returned invalid results data.");
  }

  return insights as GuardianInsights;
}

export async function getGuardianChildPhotoSource(
  photoUrl: string,
): Promise<GuardianPhotoSource | null> {
  const token = await readAccessToken();
  if (!token) {
    return null;
  }

  const baseUrl = getApiBaseUrl();
  let imageUrl: URL;
  try {
    imageUrl = new URL(photoUrl, baseUrl);
  } catch {
    throw new GuardianApiError("The server returned an invalid child photo URL.");
  }

  if (
    imageUrl.origin !== baseUrl.origin
    || !imageUrl.pathname.startsWith(`${baseUrl.pathname}api/v1/guardian/children/`)
    || !imageUrl.pathname.endsWith("/photo")
  ) {
    throw new GuardianApiError("The child photo URL is outside the trusted server.");
  }

  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

  try {
    const response = await fetch(imageUrl, {
      headers: {
        Accept: "application/json, image/jpeg, image/png, image/gif",
        Authorization: `Bearer ${token}`,
      },
      signal: controller.signal,
    });

    if (response.status === 401 || response.status === 403) {
      await clearAccessToken();
      throw new GuardianApiError("Your session has ended. Please sign in again.", response.status);
    }

    if (response.status === 404) {
      return null;
    }

    if (!response.ok) {
      throw new GuardianApiError("The child photo could not be loaded.", response.status);
    }

    const mimeType = (response.headers.get("Content-Type") ?? "").split(";")[0].trim().toLowerCase();
    if (!PHOTO_MIME_TYPES.includes(mimeType)) {
      throw new GuardianApiError("The server returned an invalid child photo.");
    }

    const blob = await response.blob();
    if (blob.size === 0 || blob.size > PHOTO_MAX_BYTES) {
      throw new GuardianApiError("The server returned an invalid child photo.");
    }

    const dataUri = await readBlobAsDataUri(blob);

    return {
      uri: `data:${mimeType};base64,${dataUri.slice(dataUri.indexOf(",") + 1)}`,
    };
  } catch (error) {
    if (error instanceof GuardianApiError) {
      throw error;
    }

    if (controller.signal.aborted) {
      throw new GuardianApiError("The server took too long to send the child photo.");
    }

    throw new GuardianApiError("The child photo could not be loaded.");
  } finally {
    clearTimeout(timeout);
  }
}

function readBlobAsDataUri(blob: Blob): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => {
      if (typeof reader.result === "string") {
        resolve(reader.result);
      } else {
        reject(new Error("The child photo could not be read."));
      }
    };
    reader.onerror = () => reject(reader.error ?? new Error("The child photo could not be read."));
    reader.readAsDataURL(blob);
  });
}

export async function restoreGuardianSession(): Promise<GuardianProfile | null> {
  const token = await readAccessToken();
  if (!token) {
    return null;
  }

  try {
    const profile = await getGuardianProfile();
    if (Platform.OS !== "web" && await SecureStore.isAvailableAsync()) {
      mustChangePassword = await SecureStore.getItemAsync(PASSWORD_CHANGE_KEY).catch(() => null) === "1";
    }

    return profile;
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