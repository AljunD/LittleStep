import React, { useState } from "react";
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
  const student = route?.params?.student;

  const [selectedLanguage, setSelectedLanguage] =
    useState("English");

  const languages = ["English", "Filipino"];

  const goTo = (screen: string) => {
    navigation.navigate(
      screen,
      student ? { student } : undefined
    );
  };

  return (
    <View style={styles.container}>
      <StatusBar
        barStyle="dark-content"
        backgroundColor={COLORS.background}
      />

      {/* Header */}
      <View
        style={[
          styles.header,
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
            color={COLORS.navy}
          />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>Language</Text>

        <View style={styles.headerSpacer} />
      </View>

      {/* Language options */}
      <ScrollView
        style={styles.content}
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>
            Language Preferences
          </Text>
          <Text style={styles.sectionDescription}>
            Choose your preferred app language.
          </Text>
        </View>

        <View style={styles.languageCard}>
          {languages.map((language, index) => {
            const selected = selectedLanguage === language;

            return (
              <React.Fragment key={language}>
                <TouchableOpacity
                  style={styles.languageRow}
                  onPress={() => setSelectedLanguage(language)}
                  activeOpacity={0.7}
                >
                  <View style={styles.languageInfo}>
                    <View
                      style={[
                        styles.languageIcon,
                        selected && styles.selectedLanguageIcon,
                      ]}
                    >
                      <Ionicons
                        name="globe-outline"
                        size={20}
                        color={
                          selected
                            ? COLORS.green
                            : COLORS.gray
                        }
                      />
                    </View>

                    <View style={styles.languageTextContainer}>
                      <Text style={styles.languageText}>
                        {language}
                      </Text>
                    </View>
                  </View>

                  <View
                    style={[
                      styles.radioOuter,
                      selected && styles.radioOuterSelected,
                    ]}
                  >
                    {selected && (
                      <View style={styles.radioInner} />
                    )}
                  </View>
                </TouchableOpacity>

                {index < languages.length - 1 && (
                  <View style={styles.divider} />
                )}
              </React.Fragment>
            );
          })}
        </View>
      </ScrollView>

      {/* Bottom navigation */}
      <View
        style={[
          styles.bottomNav,
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
            color={COLORS.inactive}
          />
          <Text style={styles.navText}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => goTo("ECCDChecklist")}
        >
          <Ionicons
            name="clipboard-outline"
            size={23}
            color={COLORS.inactive}
          />
          <Text style={styles.navText}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => goTo("Profile")}
        >
          <Ionicons
            name="person"
            size={23}
            color="#337D73"
          />
          <Text style={[styles.navText, styles.activeNavText]}>
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