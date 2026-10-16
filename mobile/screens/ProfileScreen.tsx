import React from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  StatusBar,
  Image,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

const COLORS = {
  background: "#FFFFFF",
  navy: "#263447",
  green: "#365C55",
  paleGreen: "#EEF5F1",
  gray: "#858D91",
  border: "#EDF0EF",
  infoBackground: "#F4F8F6",
  teal: "#00A9C7",
  inactive: "#C5CBCD",
};

export default function ProfileScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();

  const student = route?.params?.student ?? {
    name: "Juan Dela Cruz",
    firstName: "Juan",
    email: "juan.delacruz@gmail.com",
    image: require("../assets/images/juan.jpg"),
  };

  const profileItems = [
    {
      title: "Personal Information",
      subtitle: "View personal details",
      icon: "person-outline",
    },
    {
      title: "Notification",
      subtitle: "Manage notifications",
      icon: "notifications-outline",
    },
    {
      title: "Display",
      subtitle: "Light, dark, or system theme",
      icon: "moon-outline",
    },
    {
      title: "Language",
      subtitle: "Choose your preferred language",
      icon: "globe-outline",
    },
    {
      title: "Contact Us",
      subtitle: "Get help and support",
      icon: "call-outline",
    },
  ];

  const openScreen = (screen: string) => {
    navigation.navigate(
      screen,
      student ? { student } : undefined
    );
  };

  const handleMenuPress = (title: string) => {
    if (title === "Personal Information") {
      openScreen("PersonalInformation");
    } else if (title === "Display") {
      openScreen("Display");
    } else if (title === "Language") {
      openScreen("Language");
      } else if (title === "Contact Us") {
      openScreen("ContactUs");
    } else {
      console.log("Selected:", title);
    }
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
        <Text style={styles.headerTitle}>Profile</Text>
        <Text style={styles.headerSubtitle}>
          Manage your account
        </Text>
      </View>

      <ScrollView
        style={styles.content}
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}
      >
        {/* Student profile card */}
        <View style={styles.profileCard}>
          <View style={styles.avatarContainer}>
            <Image
              source={
                typeof student.image === "string"
                  ? { uri: student.image }
                  : student.image ??
                    require("../assets/images/juan.jpg")
              }
              style={styles.avatar}
            />
          </View>

          <View style={styles.profileDetails}>
            <Text style={styles.studentName}>
              {student.name ?? "Juan Dela Cruz"}
            </Text>
            <Text style={styles.studentEmail}>
              {student.email ?? "juan.delacruz@gmail.com"}
            </Text>

          </View>

          <Ionicons
            name="person-circle-outline"
            size={24}
            color={COLORS.green}
          />
        </View>

        {/* Section heading */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>
            Account Settings
          </Text>
        </View>

        {/* Settings menu */}
        <View style={styles.menuCard}>
          {profileItems.map((item, index) => (
            <React.Fragment key={item.title}>
              <TouchableOpacity
                style={styles.menuItem}
                activeOpacity={0.7}
                onPress={() => handleMenuPress(item.title)}
              >
                <View style={styles.menuItemLeft}>
                  <View style={styles.menuIconContainer}>
                    <Ionicons
                      name={item.icon as any}
                      size={21}
                      color={COLORS.green}
                    />
                  </View>

                  <View style={styles.menuTextContainer}>
                    <Text style={styles.menuTitle}>
                      {item.title}
                    </Text>
                    <Text style={styles.menuSubtitle}>
                      {item.subtitle}
                    </Text>
                  </View>
                </View>

                <Ionicons
                  name="chevron-forward"
                  size={19}
                  color={COLORS.gray}
                />
              </TouchableOpacity>

              {index < profileItems.length - 1 && (
                <View style={styles.divider} />
              )}
            </React.Fragment>
          ))}
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
          onPress={() => openScreen("Dashboard")}
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
          onPress={() => openScreen("ECCDChecklist")}
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
          onPress={() => openScreen("Profile")}
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
    backgroundColor: COLORS.background,
    paddingHorizontal: 22,
    paddingBottom: 18,
  },

  headerTitle: {
    fontSize: 23,
    fontWeight: "700",
    color: COLORS.navy,
  },

  headerSubtitle: {
    fontSize: 13,
    color: COLORS.gray,
    marginTop: 5,
  },

  content: {
    flex: 1,
    backgroundColor: COLORS.background,
  },

  contentContainer: {
    paddingHorizontal: 20,
    paddingTop: 8,
    paddingBottom: 24,
  },

  profileCard: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: COLORS.paleGreen,
    borderRadius: 16,
    padding: 16,
    marginBottom: 28,
  },

  avatarContainer: {
    width: 62,
    height: 62,
    borderRadius: 31,
    overflow: "hidden",
    borderWidth: 2,
    borderColor: COLORS.background,
    marginRight: 13,
  },

  avatar: {
    width: "100%",
    height: "100%",
  },

  profileDetails: {
    flex: 1,
  },

  studentName: {
    fontSize: 16,
    fontWeight: "700",
    color: COLORS.navy,
  },

  studentEmail: {
    fontSize: 12,
    color: COLORS.gray,
    marginTop: 4,
  },

  accountBadgeText: {
    fontSize: 10,
    fontWeight: "600",
    color: COLORS.green,
  },

  sectionHeader: {
    marginBottom: 14,
  },

  sectionTitle: {
    fontSize: 17,
    fontWeight: "700",
    color: COLORS.navy,
  },

  menuCard: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 14,
    paddingHorizontal: 14,
  },

  menuItem: {
    minHeight: 72,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
  },

  menuItemLeft: {
    flexDirection: "row",
    alignItems: "center",
    flex: 1,
  },

  menuIconContainer: {
    width: 42,
    height: 42,
    borderRadius: 12,
    backgroundColor: COLORS.infoBackground,
    alignItems: "center",
    justifyContent: "center",
    marginRight: 12,
  },

  menuTextContainer: {
    flex: 1,
  },

  menuTitle: {
    fontSize: 13,
    fontWeight: "600",
    color: COLORS.navy,
  },

  menuSubtitle: {
    fontSize: 11,
    color: COLORS.gray,
    marginTop: 4,
  },

  divider: {
    height: 1,
    backgroundColor: COLORS.border,
  },

  infoCard: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: COLORS.infoBackground,
    borderRadius: 14,
    padding: 15,
    marginTop: 20,
  },

  infoTextContainer: {
    flex: 1,
  },

  infoTitle: {
    fontSize: 14,
    fontWeight: "700",
    color: COLORS.green,
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