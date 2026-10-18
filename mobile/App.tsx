
import React, { useEffect, useState } from "react";
import { ActivityIndicator, StyleSheet, View } from "react-native";
import { DarkTheme, DefaultTheme, NavigationContainer } from "@react-navigation/native";
import { createStackNavigator } from "@react-navigation/stack";

import { DarkColors } from "./constants/theme";
import { AppThemeProvider, useAppTheme } from "./hooks/use-app-theme";
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
import DomainScreen from "./screens/DomainScreen";
import ProfileScreen from "./screens/profile/ProfileScreen";
import PersonalInformationScreen from "./screens/profile/PersonalInformationScreen";
import ChildDetailsScreen from "./screens/ChildDetailsScreen";
import GuardianDetailsScreen from "./screens/GuardianDetailsScreen";
import DisplayScreen from "./screens/DisplayScreen";
import LanguageScreen from "./screens/profile/LanguageScreen";
import ContactUsScreen from "./screens/profile/ContactUsScreen";
import NotificationScreen from "./screens/profile/NotificationScreen";

const Stack = createStackNavigator();

const navigationDarkTheme = {
  ...DarkTheme,
  colors: {
    ...DarkTheme.colors,
    primary: DarkColors.accent,
    background: DarkColors.background,
    card: DarkColors.surface,
    text: DarkColors.text,
    border: DarkColors.border,
  },
};

export default function App() {
  return (
    <AppThemeProvider>
      <AppNavigator />
    </AppThemeProvider>
  );
}

function AppNavigator() {
  const { isDark } = useAppTheme();
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
      <View style={[styles.loadingScreen, isDark && styles.loadingScreenDark]}>
        <ActivityIndicator color="#0BA8BD" size="large" />
      </View>
    );
  }

  return (
    <NavigationContainer theme={isDark ? navigationDarkTheme : DefaultTheme}>
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
          name="Domain"
          component={DomainScreen}
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
        <Stack.Screen
          name="ContactUs"
          component={ContactUsScreen}
        />
        <Stack.Screen
          name="Notifications"
          component={NotificationScreen}
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

  loadingScreenDark: {
    backgroundColor: DarkColors.background,
  },
});
