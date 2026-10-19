
import React from "react";
import { NavigationContainer } from "@react-navigation/native";
import { createStackNavigator } from "@react-navigation/stack";

import WelcomeScreen from "./screens/WelcomeScreen";
import LoginScreen from "./screens/LoginScreen";
import SelectStudentScreen from "./screens/SelectStudentScreen";
import DashboardScreen from "./screens/DashboardScreen";
import ECCDChecklistScreen from "./screens/ECCDChecklistScreen";
import ProfileScreen from "./screens/profile/ProfileScreen";
import PersonalInformationScreen from "./screens/profile/PersonalInformationScreen";
import ChildDetailsScreen from "./screens/ChildDetailsScreen";
import GuardianDetailsScreen from "./screens/GuardianDetailsScreen";
import DisplayScreen from "./screens/DisplayScreen";
import LanguageScreen from "./screens/profile/LanguageScreen";
import GrossMotorScreen from "./screens/GrossMotorScreen";

const Stack = createStackNavigator();

export default function App() {
  return (
    <NavigationContainer>
      <Stack.Navigator
        initialRouteName="Welcome"
        screenOptions={{ headerShown: false }}
      >
        <Stack.Screen
          name="Welcome"
          component={WelcomeScreen}
        />

        <Stack.Screen
          name="Login"
          component={LoginScreen}
        />

        <Stack.Screen
          name="SelectStudent"
          component={SelectStudentScreen}
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
        <Stack.Screen
          name="GrossMotor"
          component={GrossMotorScreen}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
