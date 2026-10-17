
import React, { useEffect, useState } from "react";
import { ActivityIndicator, StyleSheet, View } from "react-native";
import { NavigationContainer } from "@react-navigation/native";
import { createStackNavigator } from "@react-navigation/stack";

import {
  getGuardianApiErrorMessage,
  GuardianProfile,
  restoreGuardianSession,
} from "./services/guardianApi";
import WelcomeScreen from "./screens/WelcomeScreen";
import LoginScreen from "./screens/LoginScreen";
import SelectStudentScreen from "./screens/SelectStudentScreen";
import DashboardScreen from "./screens/DashboardScreen";
import ECCDChecklistScreen from "./screens/ECCDChecklistScreen";
import ProfileScreen from "./screens/ProfileScreen";
import PersonalInformationScreen from "./screens/PersonalInformationScreen";
import ChildDetailsScreen from "./screens/ChildDetailsScreen";
import GuardianDetailsScreen from "./screens/GuardianDetailsScreen";
import DisplayScreen from "./screens/DisplayScreen";
import LanguageScreen from "./screens/LanguageScreen";

const Stack = createStackNavigator();

export default function App() {
  const [isRestoringSession, setIsRestoringSession] = useState(true);
  const [initialRoute, setInitialRoute] = useState("Welcome");
  const [guardian, setGuardian] = useState<GuardianProfile | null>(null);
  const [initialError, setInitialError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;

    const restoreSession = async () => {
      try {
        const restoredGuardian = await restoreGuardianSession();
        if (isMounted && restoredGuardian) {
          setGuardian(restoredGuardian);
          setInitialRoute("SelectStudent");
        }
      } catch (error) {
        if (isMounted) {
          setInitialRoute("Login");
          setInitialError(getGuardianApiErrorMessage(error));
        }
      } finally {
        if (isMounted) {
          setIsRestoringSession(false);
        }
      }
    };

    void restoreSession();

    return () => {
      isMounted = false;
    };
  }, []);

  if (isRestoringSession) {
    return (
      <View style={styles.loadingScreen}>
        <ActivityIndicator color="#0BA8BD" size="large" />
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator
        initialRouteName={initialRoute}
        screenOptions={{ headerShown: false }}
      >
        <Stack.Screen
          name="Welcome"
          component={WelcomeScreen}
        />

        <Stack.Screen
          name="Login"
          component={LoginScreen}
          initialParams={{ initialError }}
        />

        <Stack.Screen
          name="SelectStudent"
          component={SelectStudentScreen}
          initialParams={{ guardian }}
        />

        <Stack.Screen
          name="Dashboard"
          component={DashboardScreen}
        />

        <Stack.Screen
          name="ECCDChecklist"
          component={ECCDChecklistScreen}
        />

        <Stack.Screen
          name="Profile"
          component={ProfileScreen}
        />
        <Stack.Screen
          name="PersonalInformation"
          component={PersonalInformationScreen}
          options={{ headerShown: false }}
        />

        <Stack.Screen
          name="ChildDetails"
          component={ChildDetailsScreen}
          options={{ headerShown: false }}
        />

        <Stack.Screen
          name="GuardianDetails"
          component={GuardianDetailsScreen}
          options={{ headerShown: false }}
        />
        <Stack.Screen
          name="Display"
          component={DisplayScreen}
          options={{ headerShown: false }}
        />
        <Stack.Screen
          name="Language"
          component={LanguageScreen}
          options={{ headerShown: false }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  loadingScreen: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "#FFFFFF",
  },
});
