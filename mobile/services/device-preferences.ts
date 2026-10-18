import { Platform } from "react-native";
import * as SecureStore from "expo-secure-store";

/**
 * Small, non-sensitive settings kept on this device only (e.g. language).
 * SecureStore on iOS/Android, localStorage on web. Failures fall back to defaults.
 */
export async function readDevicePreference(key: string): Promise<string | null> {
  try {
    return Platform.OS === "web"
      ? globalThis.localStorage?.getItem(key) ?? null
      : await SecureStore.getItemAsync(key);
  } catch {
    return null;
  }
}

export async function writeDevicePreference(key: string, value: string): Promise<boolean> {
  try {
    if (Platform.OS === "web") {
      globalThis.localStorage?.setItem(key, value);
    } else {
      await SecureStore.setItemAsync(key, value);
    }
    return true;
  } catch {
    return false;
  }
}
