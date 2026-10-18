import React from "react";
import {
  View,
  StyleSheet,
  ImageBackground,
  Image,
  TouchableOpacity,
  StatusBar,
  Text,
  useWindowDimensions,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { Ionicons } from "@expo/vector-icons";
import { DarkColors, Layout } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";
import { StackNavigationProp } from "@react-navigation/stack";

const LOGO_MAX_WIDTH = 360;
const LOGO_MAX_HEIGHT = 160;

const BACKGROUND = require("../assets/images/back.jpg");
const BACKGROUND_ASPECT = 687 / 1024;

type RootStackParamList = {
  Welcome: undefined;
  Login: undefined;
};

type WelcomeScreenNavigationProp = StackNavigationProp<
  RootStackParamList,
  "Welcome"
>;

type Props = {
  navigation: WelcomeScreenNavigationProp;
};

export default function WelcomeScreen({ navigation }: Props) {
  const { isDark } = useAppTheme();
  const { width, height } = useWindowDimensions();

  const framedWidth = height * BACKGROUND_ASPECT;
  const frameArtwork = width > framedWidth * 1.15;
  const frameInset = frameArtwork ? (width - framedWidth) / 2 : 0;

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        translucent
        backgroundColor="transparent"
        barStyle={isDark ? "light-content" : "dark-content"}
      />

      {frameArtwork && (
        <Image
          source={BACKGROUND}
          style={[StyleSheet.absoluteFill, isDark && darkStyles.backgroundImage]}
          resizeMode="cover"
          blurRadius={24}
        />
      )}

      <ImageBackground
        source={BACKGROUND}
        style={styles.background}
        imageStyle={[
          frameArtwork && { left: frameInset, right: frameInset },
          isDark && darkStyles.backgroundImage,
        ]}
        resizeMode="cover"
      >
        <SafeAreaView style={styles.safeArea}>
          <Image
            source={require("../assets/images/logo.png")}
            style={[
              {
                width: Math.min(width * 0.55, LOGO_MAX_WIDTH),
                height: Math.min(height * 0.16, LOGO_MAX_HEIGHT),
                marginTop: height * 0.06,
              },
              isDark && darkStyles.logoBadge,
            ]}
            resizeMode="contain"
          />

          <View style={[styles.bottomContent, { paddingBottom: height * 0.045 }]}>
            <TouchableOpacity
              style={styles.button}
              activeOpacity={0.85}
              onPress={() => navigation.navigate("Login")}
            >
              <Text style={styles.buttonText}>Get Started</Text>

              <Ionicons
                name="arrow-forward"
                size={20}
                color="#FFFFFF"
              />
            </TouchableOpacity>
          </View>
        </SafeAreaView>
      </ImageBackground>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  background: {
    flex: 1,
  },

  safeArea: {
    flex: 1,
    alignItems: "center",
    justifyContent: "space-between",
  },

  bottomContent: {
    width: "100%",
    alignItems: "center",
    paddingHorizontal: 28,
  },

  button: {
    width: "88%",
    maxWidth: Layout.maxFormWidth,
    height: 52,
    borderRadius: 26,
    backgroundColor: "#08A9C1",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
  },

  buttonText: {
    color: "#FFFFFF",
    fontSize: 16,
    fontWeight: "700",
  },
});

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: DarkColors.background,
  },

  backgroundImage: {
    opacity: 0.3,
  },

  logoBadge: {
    backgroundColor: "rgba(255, 255, 255, 0.92)",
    borderRadius: 20,
  },
});
