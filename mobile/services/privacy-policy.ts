import { Alert } from "react-native";
import * as WebBrowser from "expo-web-browser";

import { getGuardianApiErrorMessage, getPrivacyPolicyUrl } from "./guardianApi";

export async function openPrivacyPolicy(): Promise<void> {
  try {
    await WebBrowser.openBrowserAsync(getPrivacyPolicyUrl());
  } catch (error) {
    Alert.alert("Privacy Policy", getGuardianApiErrorMessage(error));
  }
}
