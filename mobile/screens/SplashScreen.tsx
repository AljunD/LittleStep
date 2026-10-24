import React, { useEffect, useRef } from "react";
import {
  AccessibilityInfo,
  Animated,
  Image,
  Platform,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";

type Props = {
  // Set when shown as the "Start" route: the logo is already on screen and the button fades in.
  navigation?: { navigate: (screen: "Welcome") => void };
};

/**
 * Branded first screen. App.tsx shows it (without the button) while the saved session is restored,
 * then signed-out guardians land on the same screen as the "Start" route with "Let's Get Started".
 */
export default function SplashScreen({ navigation }: Props) {
  const { isDark } = useAppTheme();
  const { width } = useWindowDimensions();
  const insets = useSafeAreaInsets();
  const isStartRoute = navigation !== undefined;
  const appear = useRef(new Animated.Value(isStartRoute ? 1 : 0)).current;
  const buttonAppear = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    let cancelled = false;

    void AccessibilityInfo.isReduceMotionEnabled().then((reduceMotion) => {
      if (cancelled) {
        return;
      }

      Animated.timing(isStartRoute ? buttonAppear : appear, {
        toValue: 1,
        duration: reduceMotion ? 0 : isStartRoute ? 450 : 650,
        useNativeDriver: Platform.OS !== "web",
      }).start();
    });

    return () => {
      cancelled = true;
    };
  }, [appear, buttonAppear, isStartRoute]);

  const logoWidth = Math.min(width * 0.52, 240);

  return (
    <View
      style={[styles.container, isDark && darkStyles.container]}
      accessibilityLabel={isStartRoute ? undefined : "LittleStep is starting"}
    >
      <StatusBar barStyle="light-content" />

      <View style={[styles.ring, styles.ringLarge, isDark && darkStyles.ring]} />
      <View style={[styles.ring, styles.ringSmall, isDark && darkStyles.ring]} />

      <Animated.View
        style={[
          styles.content,
          {
            opacity: appear,
            transform: [
              {
                scale: appear.interpolate({ inputRange: [0, 1], outputRange: [0.92, 1] }),
              },
            ],
          },
        ]}
      >
        <View style={[styles.logoCard, isDark && darkStyles.logoCard]}>
          <Image
            source={require("../assets/images/logo.png")}
            style={{ width: logoWidth, height: logoWidth * (408 / 612) }}
            resizeMode="contain"
            accessibilityIgnoresInvertColors
          />
        </View>

        <Text style={[styles.tagline, isDark && darkStyles.tagline]}>
          Every little step counts
        </Text>
      </Animated.View>

      <Animated.View
        style={[styles.footer, { opacity: appear, paddingBottom: Math.max(insets.bottom, 16) + 24 }]}
      >
        <Text style={[styles.footerText, isDark && darkStyles.footerText]}>
          ECCD progress for parents and guardians
        </Text>

        {isStartRoute ? (
          <Animated.View
            style={[
              styles.buttonWrap,
              {
                opacity: buttonAppear,
                transform: [
                  {
                    translateY: buttonAppear.interpolate({ inputRange: [0, 1], outputRange: [16, 0] }),
                  },
                ],
              },
            ]}
          >
            <TouchableOpacity
              style={[styles.button, isDark && darkStyles.button]}
              activeOpacity={0.85}
              accessibilityRole="button"
              onPress={() => navigation.navigate("Welcome")}
            >
              <Text style={[styles.buttonText, isDark && darkStyles.buttonText]}>
                Let&apos;s Get Started
              </Text>
              <Ionicons name="arrow-forward" size={20} color={isDark ? DarkColors.background : "#0BA8BD"} />
            </TouchableOpacity>
          </Animated.View>
        ) : null}
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#0BA8BD",
    alignItems: "center",
    justifyContent: "center",
    overflow: "hidden",
  },

  ring: {
    position: "absolute",
    borderRadius: 9999,
    borderWidth: 1,
    borderColor: "rgba(255, 255, 255, 0.16)",
    backgroundColor: "rgba(255, 255, 255, 0.05)",
  },

  ringLarge: {
    width: 560,
    height: 560,
    top: -220,
    right: -240,
  },

  ringSmall: {
    width: 360,
    height: 360,
    bottom: -150,
    left: -170,
  },

  content: {
    alignItems: "center",
    paddingHorizontal: 24,
  },

  logoCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 28,
    paddingHorizontal: 22,
    paddingVertical: 16,
    shadowColor: "#05495A",
    shadowOpacity: 0.25,
    shadowRadius: 18,
    shadowOffset: { width: 0, height: 10 },
    elevation: 8,
  },

  tagline: {
    marginTop: 22,
    fontSize: 17,
    fontWeight: "700",
    color: "#FFFFFF",
    letterSpacing: 0.3,
  },

  footer: {
    position: "absolute",
    bottom: 0,
    left: 0,
    right: 0,
    alignItems: "center",
    paddingHorizontal: 28,
  },

  footerText: {
    fontSize: 12,
    fontWeight: "600",
    color: "rgba(255, 255, 255, 0.8)",
  },

  buttonWrap: {
    width: "100%",
    maxWidth: Layout.maxFormWidth,
    marginTop: 18,
  },

  button: {
    height: 52,
    borderRadius: 26,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
    shadowColor: "#05495A",
    shadowOpacity: 0.2,
    shadowRadius: 10,
    shadowOffset: { width: 0, height: 6 },
    elevation: 4,
  },

  buttonText: {
    color: "#0BA8BD",
    fontSize: 16,
    fontWeight: "800",
  },
});

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: DarkColors.selected,
  },

  ring: {
    borderColor: "rgba(108, 199, 184, 0.18)",
    backgroundColor: "rgba(108, 199, 184, 0.05)",
  },

  logoCard: {
    backgroundColor: "rgba(255, 255, 255, 0.94)",
  },

  tagline: {
    color: DarkColors.text,
  },

  footerText: {
    color: DarkColors.textMuted,
  },

  button: {
    backgroundColor: DarkColors.accent,
  },

  buttonText: {
    color: DarkColors.background,
  },
});
