
import React from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  ScrollView,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../constants/theme";
import { DisplayMode, useAppTheme } from "../hooks/use-app-theme";

const displayOptions: {
  mode: DisplayMode;
  title: string;
  description: string;
  icon: "sunny-outline" | "moon-outline" | "phone-portrait-outline";
}[] = [
  {
    mode: "light",
    title: "Light",
    description: "Use a light appearance",
    icon: "sunny-outline",
  },
  {
    mode: "dark",
    title: "Dark",
    description: "Use a dark appearance",
    icon: "moon-outline",
  },
  {
    mode: "system",
    title: "System",
    description: "Follow your device settings",
    icon: "phone-portrait-outline",
  },
];

export default function DisplayScreen({ navigation, route }: any) {
  const insets = useSafeAreaInsets();
  const { mode, setMode, isDark } = useAppTheme();

  const student = route?.params?.student;
  const navIconColor = isDark ? DarkColors.textMuted : "#89918F";

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
        backgroundColor={isDark ? DarkColors.background : "#FFFFFF"}
      />

      {/* Header */}
      <View style={[styles.header, isDark && darkStyles.container, { paddingTop: insets.top + 10 }]}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
        >
          <Ionicons
            name="chevron-back"
            size={26}
            color={isDark ? DarkColors.text : "#263447"}
          />
        </TouchableOpacity>

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Display</Text>

        <View style={styles.headerRight} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        <Text style={[styles.pageTitle, isDark && darkStyles.text]}>Appearance</Text>

        <Text style={[styles.pageDescription, isDark && darkStyles.mutedText]}>
          Choose how LittleStep looks on your device.
        </Text>

        {/* Display Options */}
        <View style={[styles.optionsCard, isDark && darkStyles.optionsCard]}>
          {displayOptions.map((option, index) => {
            const selected = mode === option.mode;

            return (
              <React.Fragment key={option.mode}>
                <TouchableOpacity
                  style={styles.optionRow}
                  activeOpacity={0.7}
                  accessibilityRole="radio"
                  accessibilityState={{ checked: selected }}
                  onPress={() => setMode(option.mode)}
                >
                  <View style={[styles.optionIcon, isDark && darkStyles.optionIcon]}>
                    <Ionicons
                      name={option.icon}
                      size={22}
                      color={isDark ? DarkColors.icon : "#365C55"}
                    />
                  </View>

                  <View style={styles.optionTextContainer}>
                    <Text style={[styles.optionTitle, isDark && darkStyles.text]}>
                      {option.title}
                    </Text>

                    <Text style={[styles.optionDescription, isDark && darkStyles.mutedText]}>
                      {option.description}
                    </Text>
                  </View>

                  {/* Radio Circle */}
                  <View
                    style={[
                      styles.radioOuter,
                      isDark && darkStyles.radioOuter,
                      selected && styles.radioOuterSelected,
                      selected && isDark && darkStyles.radioOuterSelected,
                    ]}
                  >
                    {selected && (
                      <View style={[styles.radioInner, isDark && darkStyles.radioInner]} />
                    )}
                  </View>
                </TouchableOpacity>

                {index < displayOptions.length - 1 && (
                  <View style={[styles.divider, isDark && darkStyles.divider]} />
                )}
              </React.Fragment>
            );
          })}
        </View>

      </ScrollView>

      {/* Bottom Navigation */}
      <View
        style={[
          styles.bottomNav,
          isDark && darkStyles.bottomNav,
          { paddingBottom: Math.max(insets.bottom, 10) },
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
            color={navIconColor}
          />
          <Text style={[styles.navText, isDark && darkStyles.navText]}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() =>
            navigation.navigate("ECCDChecklist", { student })
          }
        >
          <Ionicons
            name="clipboard-outline"
            size={23}
            color={navIconColor}
          />
          <Text style={[styles.navText, isDark && darkStyles.navText]}>ECCD Checklist</Text>
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
            color={navIconColor}
          />
          <Text style={[styles.navText, isDark && darkStyles.navText]}>Profile</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: DarkColors.background,
  },

  text: {
    color: DarkColors.text,
  },

  mutedText: {
    color: DarkColors.textMuted,
  },

  optionsCard: {
    backgroundColor: DarkColors.surface,
    borderColor: DarkColors.border,
  },

  optionIcon: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  radioOuter: {
    borderColor: DarkColors.border,
  },

  radioOuterSelected: {
    borderColor: DarkColors.accent,
  },

  radioInner: {
    backgroundColor: DarkColors.accent,
  },

  divider: {
    backgroundColor: DarkColors.border,
  },

  bottomNav: {
    backgroundColor: DarkColors.surface,
    borderTopColor: DarkColors.border,
  },

  navText: {
    color: DarkColors.accent,
  },
});

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  header: {
    minHeight: 95,
    paddingHorizontal: 20,
    paddingBottom: 15,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: "#FFFFFF",
  },

  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: "center",
    justifyContent: "center",
  },

  headerTitle: {
    flex: 1,
    textAlign: "center",
    fontSize: 20,
    fontWeight: "700",
    color: "#263447",
    marginRight: 40,
  },

  headerRight: {
    width: 0,
  },

  scrollView: {
    flex: 1,
  },

  scrollContent: {
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
    paddingHorizontal: 22,
    paddingTop: 20,
    paddingBottom: 30,
  },

  pageTitle: {
    fontSize: 25,
    fontWeight: "700",
    color: "#263447",
    marginBottom: 8,
  },

  pageDescription: {
    fontSize: 14,
    lineHeight: 21,
    color: "#858D91",
    marginBottom: 25,
  },

  optionsCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    paddingHorizontal: 16,
    marginBottom: 22,
  },

  optionRow: {
    minHeight: 88,
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: 14,
  },

  optionIcon: {
    width: 46,
    height: 46,
    borderRadius: 14,
    backgroundColor: "#EEF5F1",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 13,
  },

  optionTextContainer: {
    flex: 1,
    paddingRight: 8,
  },

  optionTitle: {
    fontSize: 16,
    fontWeight: "600",
    color: "#263447",
    marginBottom: 5,
  },

  optionDescription: {
    fontSize: 12,
    lineHeight: 18,
    color: "#858D91",
  },

  radioOuter: {
    width: 22,
    height: 22,
    borderRadius: 11,
    borderWidth: 2,
    borderColor: "#C9D1CE",
    alignItems: "center",
    justifyContent: "center",
    marginLeft: 5,
  },

  radioOuterSelected: {
    borderColor: "#365C55",
  },

  radioInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: "#365C55",
  },

  divider: {
    height: 1,
    backgroundColor: "#EDF0EF",
  },

  infoIcon: {
    marginRight: 12,
    marginTop: 1,
  },

  infoTitle: {
    fontSize: 14,
    fontWeight: "700",
    color: "#365C55",
    marginBottom: 6,
  },

  infoDescription: {
    fontSize: 13,
    lineHeight: 20,
    color: "#667773",
  },

  bottomNav: {
    minHeight: 68,
    backgroundColor: "#FFFFFF",
    borderTopWidth: 1,
    borderTopColor: "#EDF0EF",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-around",
    paddingTop: 10,
    paddingHorizontal: 8,
  },

  navItem: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    gap: 4,
  },

  navText: {
    fontSize: 11,
    color: "#337D73",
    fontWeight: "500",
  },
});
