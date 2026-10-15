
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

export default function PersonalInformationScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();
  const student = route?.params?.student;

  const openDetails = (type: "GuardianDetails" | "ChildDetails") => {
    navigation.navigate(type, { student });
  };

  return (
    <View style={styles.container}>
      <StatusBar
        barStyle="dark-content"
        backgroundColor="#FFFFFF"
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
            size={26}
            color="#263447"
          />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>
          Personal Information
        </Text>

        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
      >
        {/* Page Heading */}
        <Text style={styles.pageTitle}>
          Personal Information
        </Text>

        <Text style={styles.pageDescription}>
          View your child's and guardian's information.
        </Text>

        {/* Information Options */}
        <View style={styles.optionsCard}>
          <TouchableOpacity
            style={styles.optionRow}
            activeOpacity={0.7}
            onPress={() => openDetails("GuardianDetails")}
          >
            <View style={styles.optionIcon}>
              <Ionicons
                name="people-outline"
                size={22}
                color="#365C55"
              />
            </View>

            <View style={styles.optionTextContainer}>
              <Text style={styles.optionTitle}>
                Guardian Details
              </Text>

              <Text style={styles.optionDescription}>
                View guardian information and contact details
              </Text>
            </View>

            <Ionicons
              name="chevron-forward"
              size={21}
              color="#89918F"
            />
          </TouchableOpacity>

          <View style={styles.divider} />

          <TouchableOpacity
            style={styles.optionRow}
            activeOpacity={0.7}
            onPress={() => openDetails("ChildDetails")}
          >
            <View style={styles.optionIcon}>
              <Ionicons
                name="person-outline"
                size={22}
                color="#365C55"
              />
            </View>

            <View style={styles.optionTextContainer}>
              <Text style={styles.optionTitle}>
                Child Details
              </Text>

              <Text style={styles.optionDescription}>
                View your child's personal and school information
              </Text>
            </View>

            <Ionicons
              name="chevron-forward"
              size={21}
              color="#89918F"
            />
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Bottom Navigation */}
      <View
        style={[
          styles.bottomNav,
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
            color="#89918F"
          />
          <Text style={styles.navText}>Home</Text>
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
            color="#89918F"
          />
          <Text style={styles.navText}>
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
            name="person"
            size={23}
            color="#365C55"
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
  },

  headerSpacer: {
    width: 40,
  },

  scrollView: {
    flex: 1,
  },

  content: {
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

  divider: {
    height: 1,
    backgroundColor: "#EDF0EF",
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
    color: "#89918F",
    fontWeight: "500",
  },

  activeNavText: {
    color: "#365C55",
    fontWeight: "600",
  },
});
