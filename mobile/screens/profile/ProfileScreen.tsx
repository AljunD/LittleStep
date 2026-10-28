import React, { useCallback, useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  StatusBar,
  RefreshControl,
  Alert,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { useFocusEffect } from "@react-navigation/native";
import { DarkColors, Layout } from "../../constants/theme";
import { useAppTheme } from "../../hooks/use-app-theme";
import { useApiResource } from "../../hooks/use-api-resource";
import {
  getGuardianAccount,
  isUsingDefaultPassword,
  logoutGuardian,
} from "../../services/guardianApi";
import { fullName } from "../../services/format";
import { openPrivacyPolicy } from "../../services/privacy-policy";

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
  danger: "#B42318",
  dangerBackground: "#FEF3F2",
};

export default function ProfileScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();

  const student = route?.params?.student;
  const account = useApiResource(getGuardianAccount);
  const guardianName = fullName(account.data);
  const [usingDefaultPassword, setUsingDefaultPassword] = useState(isUsingDefaultPassword);

  useFocusEffect(
    useCallback(() => {
      setUsingDefaultPassword(isUsingDefaultPassword());
    }, []),
  );
  const initials = account.data
    ? `${account.data.first_name.charAt(0)}${account.data.last_name.charAt(0)}`.toUpperCase()
    : "";

  const profileItems = [
    {
      title: "Personal Information",
      subtitle: "View personal details",
      icon: "person-outline",
    },
    {
      title: "Change Password",
      subtitle: usingDefaultPassword
        ? "Recommended: you are using a temporary password"
        : "Update your account password",
      icon: "key-outline",
      warning: usingDefaultPassword,
    },
    {
      title: "Notification",
      subtitle: "Updates from your child's teacher",
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
    {
      title: "Privacy Policy",
      subtitle: "How your child's information is protected",
      icon: "shield-checkmark-outline",
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
    } else if (title === "Change Password") {
      openScreen("ChangePassword");
    } else if (title === "Display") {
      openScreen("Display");
    } else if (title === "Language") {
      openScreen("Language");
    } else if (title === "Contact Us") {
      openScreen("ContactUs");
    } else if (title === "Notification") {
      openScreen("Notifications");
    } else if (title === "Privacy Policy") {
      void openPrivacyPolicy();
    }
  };

  const handleLogout = async () => {
    try {
      await logoutGuardian();
    } catch {
      Alert.alert(
        "Signed out on this device",
        "The app cleared its saved sign-in, but could not confirm that the server revoked the token. It will expire automatically.",
      );
    } finally {
      navigation.reset({
        index: 0,
        routes: [{ name: "Start" }],
      });
    }
  };

  const sessionItems = [
    {
      title: "Switch Student",
      subtitle: "View another child's progress",
      icon: "people-outline",
      danger: false,
      onPress: () => navigation.navigate("SelectStudent"),
    },
    {
      title: "Log Out",
      subtitle: "Sign out of LittleStep on this device",
      icon: "log-out-outline",
      danger: true,
      onPress: () => void handleLogout(),
    },
  ];

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
      />

      <View
        style={[
          styles.header,
          isDark && darkStyles.container,
          { paddingTop: insets.top + 10 },
        ]}
      >
        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Profile</Text>
        <Text style={[styles.headerSubtitle, isDark && darkStyles.mutedText]}>
          Manage your account
        </Text>
      </View>

      <ScrollView
        style={[styles.content, isDark && darkStyles.container]}
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={account.refreshing}
            onRefresh={account.refresh}
            tintColor="#0BA8BD"
            colors={["#0BA8BD"]}
          />
        }
      >
        <View style={[styles.profileCard, isDark && darkStyles.iconContainer]}>
          <View style={[styles.avatarContainer, isDark && darkStyles.avatarContainer]}>
            {initials ? (
              <Text style={[styles.avatarInitials, isDark && darkStyles.text]}>{initials}</Text>
            ) : (
              <Ionicons name="person" size={28} color={isDark ? DarkColors.icon : COLORS.green} />
            )}
          </View>

          <View style={styles.profileDetails}>
            {account.data ? (
              <>
                <Text style={[styles.studentName, isDark && darkStyles.text]} numberOfLines={1}>
                  {guardianName}
                </Text>
                <Text style={[styles.studentEmail, isDark && darkStyles.mutedText]} numberOfLines={1}>
                  {account.data.email}
                </Text>
                {account.data.relationship_to_child ? (
                  <Text style={[styles.accountBadgeText, isDark && darkStyles.mutedText]}>
                    Guardian · {account.data.relationship_to_child}
                  </Text>
                ) : null}
              </>
            ) : account.error ? (
              <>
                <Text style={[styles.errorText, isDark && darkStyles.danger]} accessibilityRole="alert">
                  {account.error}
                </Text>
                <TouchableOpacity onPress={account.retry} accessibilityRole="button">
                  <Text style={styles.retryLink}>Try again</Text>
                </TouchableOpacity>
              </>
            ) : (
              <Text style={[styles.studentEmail, isDark && darkStyles.mutedText]}>
                Loading your profile...
              </Text>
            )}
          </View>

          <Ionicons
            name="person-circle-outline"
            size={24}
            color={isDark ? DarkColors.icon : COLORS.green}
          />
        </View>

        <View style={styles.sectionHeader}>
          <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>
            Account Settings
          </Text>
        </View>

        <View style={[styles.menuCard, isDark && darkStyles.card]}>
          {profileItems.map((item, index) => (
            <React.Fragment key={item.title}>
              <TouchableOpacity
                style={styles.menuItem}
                activeOpacity={0.7}
                onPress={() => handleMenuPress(item.title)}
              >
                <View style={styles.menuItemLeft}>
                  <View style={[styles.menuIconContainer, isDark && darkStyles.surfaceMuted]}>
                    <Ionicons
                      name={item.icon as any}
                      size={21}
                      color={isDark ? DarkColors.icon : COLORS.green}
                    />
                  </View>

                  <View style={styles.menuTextContainer}>
                    <Text style={[styles.menuTitle, isDark && darkStyles.text]}>
                      {item.title}
                    </Text>
                    <Text
                      style={[
                        styles.menuSubtitle,
                        isDark && darkStyles.mutedText,
                        item.warning && styles.menuSubtitleWarning,
                        item.warning && isDark && darkStyles.warning,
                      ]}
                    >
                      {item.subtitle}
                    </Text>
                  </View>
                </View>

                <Ionicons
                  name="chevron-forward"
                  size={19}
                  color={isDark ? DarkColors.textMuted : COLORS.gray}
                />
              </TouchableOpacity>

              {index < profileItems.length - 1 && (
                <View style={[styles.divider, isDark && darkStyles.divider]} />
              )}
            </React.Fragment>
          ))}
        </View>

        <View style={[styles.sectionHeader, styles.sessionSectionHeader]}>
          <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>
            Session
          </Text>
        </View>

        <View style={[styles.menuCard, isDark && darkStyles.card]}>
          {sessionItems.map((item, index) => (
            <React.Fragment key={item.title}>
              <TouchableOpacity
                style={styles.menuItem}
                activeOpacity={0.7}
                accessibilityRole="button"
                onPress={item.onPress}
              >
                <View style={styles.menuItemLeft}>
                  <View
                    style={[
                      styles.menuIconContainer,
                      isDark && darkStyles.surfaceMuted,
                      item.danger && styles.dangerIconContainer,
                      item.danger && isDark && darkStyles.dangerIconContainer,
                    ]}
                  >
                    <Ionicons
                      name={item.icon as any}
                      size={21}
                      color={
                        item.danger
                          ? isDark ? DarkColors.danger : COLORS.danger
                          : isDark ? DarkColors.icon : COLORS.green
                      }
                    />
                  </View>

                  <View style={styles.menuTextContainer}>
                    <Text
                      style={[
                        styles.menuTitle,
                        isDark && darkStyles.text,
                        item.danger && styles.dangerText,
                        item.danger && isDark && darkStyles.danger,
                      ]}
                    >
                      {item.title}
                    </Text>
                    <Text style={[styles.menuSubtitle, isDark && darkStyles.mutedText]}>
                      {item.subtitle}
                    </Text>
                  </View>
                </View>

                <Ionicons
                  name="chevron-forward"
                  size={19}
                  color={isDark ? DarkColors.textMuted : COLORS.gray}
                />
              </TouchableOpacity>

              {index < sessionItems.length - 1 && (
                <View style={[styles.divider, isDark && darkStyles.divider]} />
              )}
            </React.Fragment>
          ))}
        </View>
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
          onPress={() => openScreen("Dashboard")}
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
          onPress={() => openScreen("ECCDChecklist")}
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
          onPress={() => openScreen("Profile")}
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
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
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
    alignItems: "center",
    justifyContent: "center",
  },

  avatarInitials: {
    fontSize: 20,
    fontWeight: "800",
    color: COLORS.green,
  },

  errorText: {
    fontSize: 12,
    lineHeight: 17,
    color: "#B42318",
  },

  retryLink: {
    fontSize: 12,
    fontWeight: "700",
    color: "#0BA8BD",
    marginTop: 4,
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
    marginTop: 4,
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

  menuSubtitleWarning: {
    color: "#B54708",
    fontWeight: "600",
  },

  sessionSectionHeader: {
    marginTop: 26,
  },

  dangerIconContainer: {
    backgroundColor: COLORS.dangerBackground,
  },

  dangerText: {
    color: COLORS.danger,
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

  iconContainer: {
    backgroundColor: DarkColors.surface,
  },

  avatarContainer: {
    borderColor: DarkColors.surface,
  },

  danger: {
    color: DarkColors.danger,
  },

  dangerIconContainer: {
    backgroundColor: "rgba(240, 68, 56, 0.16)",
  },

  warning: {
    color: "#F5C76B",
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
