import React, { useEffect, useState } from "react";
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
import { DarkColors, Layout } from "../../constants/theme";
import { useAppTheme } from "../../hooks/use-app-theme";
import { readDevicePreference, writeDevicePreference } from "../../services/device-preferences";

const LANGUAGE_KEY = "littlestep.language";
const LANGUAGES = ["English", "Filipino"];

const COLORS = {
  background: "#FFFFFF",
  navy: "#263447",
  green: "#365C55",
  paleGreen: "#EEF5F1",
  gray: "#858D91",
  inactive: "#C5CBCD",
  border: "#EDF0EF",
  infoBackground: "#F4F8F6",
  teal: "#00A9C7",
};

export default function LanguageScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();
  const student = route?.params?.student;

  const [selectedLanguage, setSelectedLanguage] =
    useState("English");
  const [saveFailed, setSaveFailed] = useState(false);

  useEffect(() => {
    let isMounted = true;

    void readDevicePreference(LANGUAGE_KEY).then((stored) => {
      if (isMounted && stored && LANGUAGES.includes(stored)) {
        setSelectedLanguage(stored);
      }
    });

    return () => {
      isMounted = false;
    };
  }, []);

  const selectLanguage = (language: string) => {
    setSelectedLanguage(language);
    void writeDevicePreference(LANGUAGE_KEY, language).then((saved) => setSaveFailed(!saved));
  };

  const languages = LANGUAGES;

  const goTo = (screen: string) => {
    navigation.navigate(
      screen,
      student ? { student } : undefined
    );
  };

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
        backgroundColor={isDark ? DarkColors.background : COLORS.background}
      />

      <View
        style={[
          styles.header,
          isDark && darkStyles.container,
          { paddingTop: insets.top + 10 },
        ]}
      >
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
        >
          <Ionicons
            name="chevron-back"
            size={25}
            color={isDark ? DarkColors.text : COLORS.navy}
          />
        </TouchableOpacity>

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Language</Text>

        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={[styles.content, isDark && darkStyles.container]}
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.sectionHeader}>
          <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>
            Language Preferences
          </Text>
          <Text style={[styles.sectionDescription, isDark && darkStyles.mutedText]}>
            Choose your preferred app language.
          </Text>
        </View>

        <View style={[styles.languageCard, isDark && darkStyles.card]}>
          {languages.map((language, index) => {
            const selected = selectedLanguage === language;

            return (
              <React.Fragment key={language}>
                <TouchableOpacity
                  style={styles.languageRow}
                  onPress={() => selectLanguage(language)}
                  activeOpacity={0.7}
                  accessibilityRole="radio"
                  accessibilityState={{ checked: selected }}
                >
                  <View style={styles.languageInfo}>
                    <View
                      style={[
                        styles.languageIcon,
                        selected && styles.selectedLanguageIcon,
                        isDark && darkStyles.surfaceMuted,
                        selected && isDark && darkStyles.selectedIcon,
                      ]}
                    >
                      <Ionicons
                        name="globe-outline"
                        size={20}
                        color={
                          selected
                            ? (isDark ? DarkColors.accent : COLORS.green)
                            : (isDark ? DarkColors.textMuted : COLORS.gray)
                        }
                      />
                    </View>

                    <View style={styles.languageTextContainer}>
                      <Text style={[styles.languageText, isDark && darkStyles.text]}>
                        {language}
                      </Text>
                    </View>
                  </View>

                  <View
                    style={[
                      styles.radioOuter,
                      selected && styles.radioOuterSelected,
                      isDark && darkStyles.radioOuter,
                      selected && isDark && darkStyles.radioOuterSelected,
                    ]}
                  >
                    {selected && (
                      <View style={[styles.radioInner, isDark && darkStyles.radioInner]} />
                    )}
                  </View>
                </TouchableOpacity>

                {index < languages.length - 1 && (
                  <View style={[styles.divider, isDark && darkStyles.divider]} />
                )}
              </React.Fragment>
            );
          })}
        </View>

        <Text style={[styles.note, isDark && darkStyles.mutedText]}>
          {saveFailed
            ? "Your choice applies for now but could not be saved on this device."
            : "Your choice is saved on this device. LittleStep is currently shown in English; other languages will apply once translations are available."}
        </Text>
      </ScrollView>

      <View
        style={[
          styles.bottomNav,
          isDark && darkStyles.bottomNav,
          { paddingBottom: Math.max(insets.bottom, 8) },
        ]}
      >
        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => goTo("Dashboard")}
        >
          <Ionicons
            name="home-outline"
            size={23}
            color={isDark ? DarkColors.textMuted : COLORS.inactive}
          />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => goTo("ECCDChecklist")}
        >
          <Ionicons
            name="clipboard-outline"
            size={23}
            color={isDark ? DarkColors.textMuted : COLORS.inactive}
          />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => goTo("Profile")}
        >
          <Ionicons
            name="person"
            size={23}
            color={isDark ? DarkColors.accent : "#337D73"}
          />
          <Text style={[styles.navText, styles.activeNavText, isDark && darkStyles.activeNavText]}>
            Profile
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },

  header: {
    minHeight: 86,
    backgroundColor: COLORS.background,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 18,
    paddingBottom: 12,
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
    fontSize: 16,
    fontWeight: "700",
    color: COLORS.navy,
  },

  headerSpacer: {
    width: 40,
  },

  content: {
    flex: 1,
    backgroundColor: COLORS.background,
  },

  contentContainer: {
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
    paddingHorizontal: 20,
    paddingTop: 18,
    paddingBottom: 24,
  },

  sectionHeader: {
    marginBottom: 18,
  },

  sectionTitle: {
    fontSize: 17,
    fontWeight: "700",
    color: COLORS.navy,
    marginBottom: 6,
  },

  sectionDescription: {
    fontSize: 13,
    color: COLORS.gray,
    lineHeight: 19,
  },

  note: {
    fontSize: 12,
    lineHeight: 18,
    color: COLORS.gray,
    marginTop: 14,
  },

  languageCard: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 14,
    paddingHorizontal: 14,
  },

  languageRow: {
    minHeight: 76,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
  },

  languageInfo: {
    flexDirection: "row",
    alignItems: "center",
    flex: 1,
  },

  languageIcon: {
    width: 42,
    height: 42,
    borderRadius: 12,
    backgroundColor: COLORS.infoBackground,
    alignItems: "center",
    justifyContent: "center",
    marginRight: 12,
  },

  selectedLanguageIcon: {
    backgroundColor: COLORS.paleGreen,
  },

  languageTextContainer: {
    flex: 1,
  },

  languageText: {
    fontSize: 14,
    fontWeight: "600",
    color: COLORS.navy,
  },

  radioOuter: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: COLORS.gray,
    alignItems: "center",
    justifyContent: "center",
    marginLeft: 12,
  },

  radioOuterSelected: {
    borderColor: COLORS.green,
  },

  radioInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: COLORS.green,
  },

  divider: {
    height: 1,
    backgroundColor: COLORS.border,
  },

  bottomNav: {
    minHeight: 72,
    backgroundColor: COLORS.background,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
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
    color: "#B9BEC0",
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

  text: {
    color: DarkColors.text,
  },

  mutedText: {
    color: DarkColors.textMuted,
  },

  card: {
    backgroundColor: DarkColors.surface,
    borderColor: DarkColors.border,
  },

  surfaceMuted: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  selectedIcon: {
    backgroundColor: DarkColors.selected,
  },

  radioOuter: {
    borderColor: DarkColors.textMuted,
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

  activeNavText: {
    color: DarkColors.accent,
  },
});
