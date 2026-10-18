
import React, { useEffect, useState } from "react";
import { ActivityIndicator, Platform, StyleSheet, View } from "react-native";
import {
  DarkTheme,
  DefaultTheme,
  getStateFromPath,
  LinkingOptions,
  NavigationContainer,
  ParamListBase,
} from "@react-navigation/native";
import { createStackNavigator } from "@react-navigation/stack";
import * as Linking from "expo-linking";

import { DarkColors } from "./constants/theme";
import { AppThemeProvider, useAppTheme } from "./hooks/use-app-theme";
import {
  getGuardianApiErrorMessage,
  GuardianProfile,
  hasGuardianSession,
  restoreGuardianSession,
} from "./services/guardianApi";
import WelcomeScreen from "./screens/WelcomeScreen";
import LoginScreen from "./screens/LoginScreen";
import SelectStudentScreen from "./screens/SelectStudentScreen";
import DashboardScreen from "./screens/DashboardScreen";
import ECCDChecklistScreen from "./screens/ECCDChecklistScreen";
import DomainScreen from "./screens/DomainScreen";
import InsightsScreen from "./screens/InsightsScreen";
import ProfileScreen from "./screens/profile/ProfileScreen";
import PersonalInformationScreen from "./screens/profile/PersonalInformationScreen";
import ChildDetailsScreen from "./screens/ChildDetailsScreen";
import GuardianDetailsScreen from "./screens/GuardianDetailsScreen";
import DisplayScreen from "./screens/DisplayScreen";
import LanguageScreen from "./screens/profile/LanguageScreen";
import ContactUsScreen from "./screens/profile/ContactUsScreen";
import NotificationScreen from "./screens/profile/NotificationScreen";
import ChangePasswordScreen from "./screens/profile/ChangePasswordScreen";

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

/**
 * Deep links. Only the Login link from the email-verification page is accepted
 * (littlestep://login, or exp://<host>/--/login in Expo Go). Any other path is ignored, so links can't
 * open protected screens, and only `verified=1` is read from the query: other parameters (e.g. a
 * crafted `initialError` message) never reach the screen. A guardian who is already signed in stays where they are.
 */
const linking: LinkingOptions<ParamListBase> = {
  prefixes: [Linking.createURL("/"), "littlestep://"],
  config: {
    screens: {
      Login: "login",
    },
  },
  getStateFromPath(path, options) {
    if (hasGuardianSession()) {
      return undefined;
    }

    const state = getStateFromPath(path, options);
    const route = state?.routes.length === 1 ? state.routes[0] : undefined;
    if (route?.name !== "Login") {
      return undefined;
    }

    const verified = (route.params as Record<string, unknown> | undefined)?.verified === "1";
    return { routes: [{ name: "Login", params: verified ? { verified: true } : {} }] };
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
    <NavigationContainer
      theme={isDark ? navigationDarkTheme : DefaultTheme}
      // Native only: on web, linking would rewrite the browser address for every screen.
      linking={Platform.OS === "web" ? undefined : linking}
    >
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
          name="Insights"
          component={InsightsScreen}
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
        <Stack.Screen
          name="ChangePassword"
          component={ChangePasswordScreen}
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
