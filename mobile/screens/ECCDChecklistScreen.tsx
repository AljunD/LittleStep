import React from "react";
import {
  View,
  Text,
  StyleSheet,
  ImageBackground,
  TouchableOpacity,
  StatusBar,
  ScrollView,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";

const categories = [
  {
    title: "Physical Movement",
    subtitle: "Gross Motor",
    icon: "body-outline" as const,
    color: "#F9C96B",
  },
  {
    title: "Hand & Finger Skills",
    subtitle: "Fine Motor",
    icon: "hand-left-outline" as const,
    color: "#A8D8B9",
  },
  {
    title: "Understanding & Listening",
    subtitle: "Receptive Language",
    icon: "ear-outline" as const,
    color: "#B9C9F8",
  },
  {
    title: "Speaking & Communication",
    subtitle: "Expressive Language",
    icon: "chatbubble-ellipses-outline" as const,
    color: "#F3B9C7",
  },
  {
    title: "Social & Emotional Skills",
    subtitle: "Social Emotional",
    icon: "people-outline" as const,
    color: "#DCEBFF",
  },
  {
    title: "Thinking & Learning",
    subtitle: "Cognitive",
    icon: "bulb-outline" as const,
    color: "#FFC09F",
  },
];

export default function ECCDChecklistScreen({
  navigation,
  route,
}: any) {
  const { width, height } = useWindowDimensions();
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();

  const compact = height < 760;
  const small = width < 360;
  const student = route?.params?.student;

  return (
    <View style={[styles.screen, isDark && darkStyles.container]}>
      <StatusBar
        backgroundColor={isDark ? DarkColors.surface : "#FFFFFF"}
        barStyle={isDark ? "light-content" : "dark-content"}
      />

      {/* FIXED HEADER */}
      <View
        style={[
          styles.header,
          isDark && darkStyles.header,
          {
            paddingTop: Math.max(insets.top, 8),
            paddingHorizontal: small ? 16 : 22,
          },
        ]}
      >
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.75}
        >
          <Ionicons
            name="chevron-back"
            size={25}
            color={isDark ? DarkColors.text : "#284F47"}
          />
        </TouchableOpacity>

        <Text
          style={[
            styles.headerTitle,
            compact && styles.compactHeaderTitle,
          isDark && darkStyles.text,
          ]}
        >
          ECCD Checklist
        </Text>

        <View style={styles.headerSpacer} />
      </View>

      {/* CATEGORY LIST */}
      <ImageBackground
        source={require("../assets/images/back.jpg")}
        style={styles.background}
        imageStyle={[styles.backgroundImage, isDark && darkStyles.backgroundImage]}
        resizeMode="cover"
      >
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={[
            styles.listContent,
            {
              paddingHorizontal: small ? 16 : 22,
              paddingTop: compact ? 10 : 16,
              paddingBottom: 16,
            },
          ]}
          showsVerticalScrollIndicator={false}
        >
          <Text
            style={[
              styles.sectionTitle,
              compact && styles.compactSectionTitle,
            isDark && darkStyles.text,
            ]}
          >
            Development Areas
          </Text>

          <Text style={[styles.sectionSubtitle, isDark && darkStyles.mutedText]}>
            Choose an area to view developmental skills.
          </Text>

          {categories.map((category) => (
            <TouchableOpacity
              key={category.title}
              style={[
                styles.categoryCard,
                {
                  backgroundColor: category.color,
                },
                compact && styles.compactCategoryCard,
              ]}
              activeOpacity={0.8}
              onPress={() => {
                // Connect category detail screens here later.
                console.log("Selected category:", category.title);
              }}
            >
              <View
                style={[
                  styles.iconCircle,
                  compact && styles.compactIconCircle,
                ]}
              >
                <Ionicons
                  name={category.icon}
                  size={compact ? 21 : 24}
                  color="#365C55"
                />
              </View>

              <View style={styles.categoryInfo}>
                <Text
                  style={[
                    styles.categoryTitle,
                    compact && styles.compactCategoryTitle,
                  ]}
                  numberOfLines={2}
                  adjustsFontSizeToFit
                >
                  {category.title}
                </Text>

                <Text style={styles.categorySubtitle}>
                  {category.subtitle}
                </Text>
              </View>

              <Ionicons
                name="chevron-forward"
                size={20}
                color="#496E65"
              />
            </TouchableOpacity>
          ))}
        </ScrollView>
      </ImageBackground>

      {/* FIXED BOTTOM NAVIGATION */}
      <View
        style={[
          styles.bottomNav,
          isDark && darkStyles.bottomNav,
          {
            paddingBottom: Math.max(insets.bottom, 8),
          },
        ]}
      >
        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() =>
            navigation.navigate("Dashboard", { student })
          }
        >
          <Ionicons
            name="home-outline"
            size={23}
            color={isDark ? DarkColors.textMuted : "#8A9692"}
          />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
        >
          <Ionicons
            name="clipboard"
            size={23}
            color={isDark ? DarkColors.accent : "#337D73"}
          />
          <Text style={[styles.navText, styles.activeNavText, isDark && darkStyles.activeNavText]}>
            ECCD Checklist
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() =>
            navigation.navigate("Profile", { student })
          }
        >
          <Ionicons
            name="person-outline"
            size={23}
            color={isDark ? DarkColors.textMuted : "#8A9692"}
          />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>Profile</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  header: {
    minHeight: 86,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: "#F0F2F0",
  },

  backButton: {
    width: 40,
    height: 43,
    justifyContent: "center",
    alignItems: "flex-start",
  },

  headerTitle: {
    flex: 1,
    textAlign: "center",
    fontSize: 20,
    fontWeight: "800",
    color: "#284F47",
  },

  compactHeaderTitle: {
    fontSize: 21,
  },

  headerSpacer: {
    width: 40,
  },

  background: {
    flex: 1,
  },

  backgroundImage: {
    opacity: 0.35,
  },

  scrollView: {
    flex: 1,
  },

  listContent: {
    flexGrow: 1,
  },

  sectionTitle: {
    fontSize: 20,
    fontWeight: "800",
    color: "#284F47",
    marginBottom: 4,
  },

  compactSectionTitle: {
    fontSize: 18,
  },

  sectionSubtitle: {
    fontSize: 12,
    color: "#788781",
    marginBottom: 15,
  },

  categoryCard: {
    width: "100%",
    minHeight: 82,
    borderRadius: 18,
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 13,
    paddingVertical: 11,
    marginBottom: 11,
    shadowColor: "#31574F",
    shadowOpacity: 0.08,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },

  compactCategoryCard: {
    minHeight: 70,
    paddingVertical: 8,
    marginBottom: 8,
  },

  iconCircle: {
    width: 44,
    height: 44,
    borderRadius: 14,
    backgroundColor: "rgba(255,255,255,0.65)",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 13,
  },

  compactIconCircle: {
    width: 38,
    height: 38,
    borderRadius: 12,
    marginRight: 11,
  },

  categoryInfo: {
    flex: 1,
    marginRight: 8,
  },

  categoryTitle: {
    fontSize: 14,
    fontWeight: "800",
    color: "#294E46",
  },

  compactCategoryTitle: {
    fontSize: 12,
  },

  categorySubtitle: {
    fontSize: 11,
    color: "#58736B",
    marginTop: 4,
  },

  bottomNav: {
    minHeight: 72,
    backgroundColor: "#FFFFFF",
    borderTopWidth: 1,
    borderTopColor: "#E9EEEB",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-around",
    paddingTop: 9,
  },

  navItem: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 3,
  },

  navText: {
    fontSize: 10,
    color: "#8A9692",
    marginTop: 4,
    fontWeight: "600",
    textAlign: "center",
  },

  activeNavText: {
    color: "#337D73",
    fontWeight: "800",
  },
});

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: DarkColors.background,
  },

  header: {
    backgroundColor: DarkColors.surface,
    borderBottomColor: DarkColors.border,
  },

  text: {
    color: DarkColors.text,
  },

  mutedText: {
    color: DarkColors.textMuted,
  },

  backgroundImage: {
    opacity: 0.08,
  },

  bottomNav: {
    backgroundColor: DarkColors.surface,
    borderTopColor: DarkColors.border,
  },

  activeNavText: {
    color: DarkColors.accent,
  },
});
