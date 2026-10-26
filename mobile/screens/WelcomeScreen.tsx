import React, { useRef, useState } from "react";
import {
  View,
  StyleSheet,
  Image,
  TouchableOpacity,
  StatusBar,
  Text,
  ScrollView,
  NativeScrollEvent,
  NativeSyntheticEvent,
  useWindowDimensions,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { Ionicons } from "@expo/vector-icons";
import { StackNavigationProp } from "@react-navigation/stack";
import { DarkColors, Layout } from "../constants/theme";
import { ECCD_DOMAINS } from "../constants/eccd-domains";
import { useAppTheme } from "../hooks/use-app-theme";

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

type Slide = {
  key: string;
  title: string;
  body: string;
  icon?: React.ComponentProps<typeof Ionicons>["name"];
};

const SLIDES: Slide[] = [
  {
    key: "welcome",
    title: "Welcome to LittleStep",
    body: "Follow your child's early learning journey with the teacher who knows them best.",
  },
  {
    key: "progress",
    title: "See every milestone",
    body: "View ECCD checklist results across all seven developmental domains, from each evaluation round.",
    icon: "stats-chart",
  },
  {
    key: "home",
    title: "Support learning at home",
    body: "Get easy-to-understand insights and the skills your child can practice with you every day.",
    icon: "home",
  },
];

// Domain badges that float around each illustration, like the reference design's decorations.
const ORBIT_POSITIONS = [
  { top: "4%", left: "6%" },
  { top: "10%", right: "2%" },
  { bottom: "14%", left: "0%" },
  { bottom: "4%", right: "10%" },
] as const;

export default function WelcomeScreen({ navigation }: Props) {
  const { isDark } = useAppTheme();
  const { width, height } = useWindowDimensions();
  const scrollRef = useRef<React.ComponentRef<typeof ScrollView>>(null);
  const [index, setIndex] = useState(0);

  const isLast = index === SLIDES.length - 1;
  const compact = height < Layout.compactHeight;
  const artSize = Math.min(width * 0.68, height * (compact ? 0.32 : 0.36), 300);

  const goToLogin = () => navigation.navigate("Login");

  const goTo = (next: number) => {
    scrollRef.current?.scrollTo({ x: next * width, animated: true });
    setIndex(next);
  };

  const handleScroll = (event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const next = Math.round(event.nativeEvent.contentOffset.x / Math.max(width, 1));
    if (next !== index && next >= 0 && next < SLIDES.length) {
      setIndex(next);
    }
  };

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar barStyle="light-content" />

      <View style={[styles.ring, styles.ringTop, isDark && darkStyles.ring]} />
      <View style={[styles.ring, styles.ringBottom, isDark && darkStyles.ring]} />

      <SafeAreaView style={styles.safeArea}>
        <View style={styles.topBar}>
          {!isLast ? (
            <TouchableOpacity
              onPress={goToLogin}
              accessibilityRole="button"
              accessibilityLabel="Skip introduction"
              hitSlop={10}
            >
              <Text style={[styles.skipText, isDark && darkStyles.mutedText]}>Skip</Text>
            </TouchableOpacity>
          ) : null}
        </View>

        <ScrollView
          ref={scrollRef}
          horizontal
          pagingEnabled
          showsHorizontalScrollIndicator={false}
          onScroll={handleScroll}
          scrollEventThrottle={16}
          style={styles.pager}
        >
          {SLIDES.map((slide, slideIndex) => (
            <ScrollView
              key={slide.key}
              style={{ width }}
              contentContainerStyle={styles.slide}
              showsVerticalScrollIndicator={false}
              accessibilityLabel={`Page ${slideIndex + 1} of ${SLIDES.length}`}
            >
              <View style={[styles.art, { width: artSize, height: artSize }]}>
                <View style={[styles.artHalo, isDark && darkStyles.artHalo]} />
                <View
                  style={[
                    styles.artCore,
                    isDark && darkStyles.artCore,
                    { width: artSize * 0.62, height: artSize * 0.62, borderRadius: artSize * 0.31 },
                  ]}
                >
                  {slide.icon ? (
                    <Ionicons name={slide.icon} size={artSize * 0.24} color={isDark ? DarkColors.accent : "#0BA8BD"} />
                  ) : (
                    <Image
                      source={require("../assets/images/splash-icon.png")}
                      style={{ width: artSize * 0.46, height: artSize * 0.46 }}
                      resizeMode="contain"
                      accessibilityIgnoresInvertColors
                    />
                  )}
                </View>

                {ORBIT_POSITIONS.map((position, orbitIndex) => {
                  const domain = ECCD_DOMAINS[(slideIndex * 2 + orbitIndex) % ECCD_DOMAINS.length];

                  return (
                    <View
                      key={domain.key}
                      style={[styles.orbitBadge, { backgroundColor: domain.color }, position]}
                      accessibilityElementsHidden
                      importantForAccessibility="no-hide-descendants"
                    >
                      <Ionicons name={domain.icon} size={18} color="#263447" />
                    </View>
                  );
                })}
              </View>

              <View style={styles.textBlock}>
                <Text style={[styles.title, compact && styles.titleCompact, isDark && darkStyles.text]}>
                  {slide.title}
                </Text>
                <Text style={[styles.body, isDark && darkStyles.mutedText]}>{slide.body}</Text>
              </View>
            </ScrollView>
          ))}
        </ScrollView>

        <View style={[styles.footer, { paddingBottom: Math.max(height * 0.04, 16) }]}>
          <View style={styles.dots} accessibilityLabel={`Page ${index + 1} of ${SLIDES.length}`}>
            {SLIDES.map((slide, dotIndex) => (
              <TouchableOpacity
                key={slide.key}
                onPress={() => goTo(dotIndex)}
                accessibilityRole="button"
                accessibilityLabel={`Go to page ${dotIndex + 1}`}
                accessibilityState={{ selected: dotIndex === index }}
                hitSlop={8}
              >
                <View
                  style={[
                    styles.dot,
                    isDark && darkStyles.dot,
                    dotIndex === index && styles.dotActive,
                    dotIndex === index && isDark && darkStyles.dotActive,
                  ]}
                />
              </TouchableOpacity>
            ))}
          </View>

          <TouchableOpacity
            style={[styles.button, isDark && darkStyles.button]}
            activeOpacity={0.85}
            accessibilityRole="button"
            onPress={isLast ? goToLogin : () => goTo(index + 1)}
          >
            <Text style={[styles.buttonText, isDark && darkStyles.buttonText]}>
              {isLast ? "Get Started" : "Next"}
            </Text>
            <Ionicons name="arrow-forward" size={20} color={isDark ? DarkColors.background : "#0BA8BD"} />
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#0BA8BD",
    overflow: "hidden",
  },

  ring: {
    position: "absolute",
    borderRadius: 9999,
    backgroundColor: "rgba(255, 255, 255, 0.07)",
  },

  ringTop: {
    width: 420,
    height: 420,
    top: -190,
    left: -150,
  },

  ringBottom: {
    width: 320,
    height: 320,
    bottom: 80,
    right: -200,
  },

  safeArea: {
    flex: 1,
  },

  topBar: {
    height: 44,
    alignItems: "flex-end",
    justifyContent: "center",
    paddingHorizontal: 24,
  },

  skipText: {
    color: "rgba(255, 255, 255, 0.9)",
    fontSize: 14,
    fontWeight: "700",
  },

  pager: {
    flex: 1,
  },

  slide: {
    flexGrow: 1,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 28,
    paddingVertical: 12,
  },

  art: {
    alignItems: "center",
    justifyContent: "center",
    marginBottom: 32,
  },

  artHalo: {
    position: "absolute",
    top: 0,
    right: 0,
    bottom: 0,
    left: 0,
    borderRadius: 9999,
    backgroundColor: "rgba(255, 255, 255, 0.14)",
    borderWidth: 1,
    borderColor: "rgba(255, 255, 255, 0.22)",
  },

  artCore: {
    backgroundColor: "#FFFFFF",
    alignItems: "center",
    justifyContent: "center",
    shadowColor: "#05495A",
    shadowOpacity: 0.22,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 8 },
    elevation: 6,
  },

  orbitBadge: {
    position: "absolute",
    width: 42,
    height: 42,
    borderRadius: 21,
    alignItems: "center",
    justifyContent: "center",
    borderWidth: 3,
    borderColor: "#FFFFFF",
  },

  textBlock: {
    width: "100%",
    maxWidth: Layout.maxFormWidth,
    alignItems: "center",
  },

  title: {
    fontSize: 25,
    fontWeight: "800",
    color: "#FFFFFF",
    textAlign: "center",
  },

  titleCompact: {
    fontSize: 22,
  },

  body: {
    marginTop: 12,
    fontSize: 15,
    lineHeight: 22,
    color: "rgba(255, 255, 255, 0.88)",
    textAlign: "center",
  },

  footer: {
    alignItems: "center",
    paddingHorizontal: 28,
    paddingTop: 8,
  },

  dots: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    marginBottom: 22,
  },

  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: "rgba(255, 255, 255, 0.45)",
  },

  dotActive: {
    width: 24,
    backgroundColor: "#FFFFFF",
  },

  button: {
    width: "100%",
    maxWidth: Layout.maxFormWidth,
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
    backgroundColor: DarkColors.background,
  },

  ring: {
    backgroundColor: "rgba(108, 199, 184, 0.06)",
  },

  text: {
    color: DarkColors.text,
  },

  mutedText: {
    color: DarkColors.textMuted,
  },

  artHalo: {
    backgroundColor: "rgba(108, 199, 184, 0.1)",
    borderColor: "rgba(108, 199, 184, 0.2)",
  },

  artCore: {
    backgroundColor: DarkColors.surface,
  },

  dot: {
    backgroundColor: DarkColors.border,
  },

  dotActive: {
    backgroundColor: DarkColors.accent,
  },

  button: {
    backgroundColor: DarkColors.accent,
  },

  buttonText: {
    color: DarkColors.background,
  },
});
