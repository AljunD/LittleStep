import React, { useEffect, useState } from "react";
import {
  ActivityIndicator,
  Alert,
  View,
  Text,
  Image,
  ImageBackground,
  TouchableOpacity,
  StyleSheet,
  StatusBar,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import {
  getGuardianApiErrorMessage,
  getGuardianChildPhotoSource,
  getGuardianDashboard,
  GuardianDashboard,
  GuardianPhotoSource,
  logoutGuardian,
} from "../services/guardianApi";
import { DarkColors } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";

export default function DashboardScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();
  const { width, height } = useWindowDimensions();
  const { isDark } = useAppTheme();

  // Automatically reduce spacing on shorter phones
  const compact = height < 760;
  const small = width < 360;

  const student = route?.params?.student;
  const studentId = student?.id;
  const [dashboard, setDashboard] = useState<GuardianDashboard | null>(null);
  const [photo, setPhoto] = useState<{
    childId: number;
    source: GuardianPhotoSource;
  } | null>(null);
  const [photoLoading, setPhotoLoading] = useState(false);
  const photoSource = photo && photo.childId === studentId ? photo.source : null;

  useEffect(() => {
    let isMounted = true;

    if (typeof studentId === "number") {
      const requestedStudentId = studentId;
      getGuardianDashboard(studentId)
        .then((data) => {
          if (!isMounted) {
            return;
          }

          setDashboard(data);

          if (!data.child.photo_url) {
            return;
          }

          setPhotoLoading(true);
          getGuardianChildPhotoSource(data.child.photo_url)
            .then((source) => {
              if (isMounted && source) {
                setPhoto({ childId: requestedStudentId, source });
              }
            })
            .catch(() => {
              // Keep the placeholder; the rest of the dashboard is still usable.
            })
            .finally(() => {
              if (isMounted) {
                setPhotoLoading(false);
              }
            });
        })
        .catch((error: unknown) => {
          if (isMounted) {
            Alert.alert("Unable to load dashboard", getGuardianApiErrorMessage(error));
          }
        });
    }

    return () => {
      isMounted = false;
    };
  }, [studentId]);

  const currentDashboard = dashboard?.child.id === studentId ? dashboard : null;
  const studentName = currentDashboard?.child
    ? [
        currentDashboard.child.first_name,
        currentDashboard.child.middle_name,
        currentDashboard.child.last_name,
      ].filter(Boolean).join(" ")
    : student?.name
      ?? [student?.firstName, student?.lastName].filter(Boolean).join(" ")
      ?? "";

  const latestRoundData = currentDashboard?.progress.latest_round
    ? currentDashboard.progress.rounds[currentDashboard.progress.latest_round]
    : null;

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
        routes: [{ name: "Welcome" }],
      });
    }
  };

  const activities = [
    {
      title: "Physical Movement",
      subtitle: "Gross Motor",
      domainKey: "gross_motor",
      icon: "body-outline",
      color: "#F9C96B",
    },
    {
      title: "Hand & Finger Skills",
      subtitle: "Fine Motor",
      domainKey: "fine_motor",
      icon: "hand-left-outline",
      color: "#A8D8B9",
    },
    {
      title: "Thinking & Learning",
      subtitle: "Cognitive",
      domainKey: "cognitive",
      icon: "bulb-outline",
      color: "#B9C9F8",
    },
    {
      title: "View Full",
      subtitle: "ECCD Checklist",
      domainKey: null,
      icon: "clipboard-outline",
      color: "#F3B9C7",
    },
  ];

  const quickActions = [
    {
      title: "Display",
      icon: "desktop-outline",
      color: "#DCEBFF",
      onPress: () =>
        navigation.navigate("Display", { student }),
    },
    {
      title: "Switch Student",
      icon: "people-outline",
      color: "#E5DDFB",
      onPress: () => navigation.navigate("SelectStudent"),
    },
    {
      title: "Logout",
      icon: "log-out-outline",
      color: "#FFE5D2",
      onPress: () => void handleLogout(),
    },
  ];

  const openActivity = (title: string) => {
    if (title === "View Full") {
      navigation.navigate("ECCDChecklist", { student });
    }
  };

  return (
    <View style={[styles.screen, isDark && darkStyles.screen]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
        backgroundColor={isDark ? DarkColors.surface : "#FFFFFF"}
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
        <View style={styles.headerTextContainer}>
          <Text style={[styles.headerTitle, compact && styles.compactHeaderTitle, isDark && darkStyles.text]}>
            Dashboard
          </Text>
        </View>

      </View>

      {/* FIXED CONTENT: NO SCROLLVIEW */}
      <ImageBackground
        source={require("../assets/images/back.jpg")}
        style={styles.background}
        imageStyle={[styles.backgroundImage, isDark && darkStyles.backgroundImage]}
        resizeMode="cover"
      >
        <View
          style={[
            styles.content,
            {
              paddingHorizontal: small ? 12 : 18,
              paddingTop: compact ? 6 : 10,
              paddingBottom: 6,
            },
          ]}
        >
          {/* STUDENT CARD */}
          <View
            style={[
              styles.studentCard,
              compact && styles.compactStudentCard,
              isDark && darkStyles.surface,
            ]}
          >
            {photoSource ? (
              <Image
                source={photoSource}
                style={[
                  styles.studentImage,
                  compact && styles.compactStudentImage,
                ]}
                resizeMode="cover"
                accessibilityLabel={`Photo of ${studentName}`}
                onError={() => setPhoto(null)}
              />
            ) : (
              <View
                style={[
                  styles.studentImage,
                  compact && styles.compactStudentImage,
                  styles.studentPhotoPlaceholder,
                  isDark && darkStyles.surfaceMuted,
                ]}
              >
                {photoLoading ? (
                  <ActivityIndicator size="small" color={isDark ? DarkColors.icon : "#6F9A8F"} />
                ) : (
                  <Ionicons name="person" size={compact ? 23 : 28} color={isDark ? DarkColors.icon : "#6F9A8F"} />
                )}
              </View>
            )}

            <View style={styles.studentInfo}>
              <Text style={[styles.studentLabel, isDark && darkStyles.mutedText]}>CURRENT STUDENT</Text>
              <Text
                style={[
                  styles.studentName,
                  compact && styles.compactStudentName,
                  isDark && darkStyles.text,
                ]}
                numberOfLines={1}
                adjustsFontSizeToFit
              >
                {studentName}
              </Text>
            </View>

          </View>

          {/* RECENT ACTIVITIES */}
          <View style={styles.section}>
            <View style={styles.sectionHeading}>
              <Text
                style={[
                  styles.sectionTitle,
                  compact && styles.compactSectionTitle,
                  isDark && darkStyles.text,
                ]}
              >
                Recent Activities
              </Text>
            </View>

            <View style={styles.activityGrid}>
              {activities.map((activity) => (
                <TouchableOpacity
                  key={activity.title}
                  style={[
                    styles.activityCard,
                    {
                      backgroundColor: activity.color,
                    },
                    compact && styles.compactActivityCard,
                  ]}
                  activeOpacity={0.8}
                  onPress={() => openActivity(activity.title)}
                >
                  <View style={styles.activityCardTop}>
                    <View style={styles.activityIcon}>
                      <Ionicons
                        name={activity.icon as any}
                        size={compact ? 21 : 24}
                        color="#365C55"
                      />
                    </View>
                    <Ionicons
                      name="arrow-forward-circle-outline"
                      size={20}
                      color="#496E65"
                    />
                  </View>

                  <View style={styles.activityTextContainer}>
                    <Text
                      style={[
                        styles.activityTitle,
                        compact && styles.compactActivityTitle,
                      ]}
                      numberOfLines={2}
                      adjustsFontSizeToFit
                    >
                      {activity.title}
                    </Text>
                    <Text style={styles.activitySubtitle}>
                      {activity.domainKey && latestRoundData?.domains[activity.domainKey]?.raw_score !== null
                        && latestRoundData?.domains[activity.domainKey]?.raw_score !== undefined
                        ? `${activity.subtitle} · ${latestRoundData.domains[activity.domainKey].raw_score}`
                        : activity.subtitle}
                    </Text>
                  </View>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          {/* QUICK ACTIONS */}
          <View style={styles.section}>
            <View style={styles.sectionHeading}>
              <Text
                style={[
                  styles.sectionTitle,
                  compact && styles.compactSectionTitle,
                  isDark && darkStyles.text,
                ]}
              >
                Quick Actions
              </Text>
            </View>

            <View style={styles.quickActionsRow}>
              {quickActions.map((action) => (
                <TouchableOpacity
                  key={action.title}
                  style={[
                    styles.quickActionCard,
                    {
                      backgroundColor: action.color,
                    },
                    compact && styles.compactQuickActionCard,
                  ]}
                  onPress={action.onPress}
                  activeOpacity={0.8}
                >
                  <Ionicons
                    name={action.icon as any}
                    size={compact ? 22 : 25}
                    color="#365C55"
                  />
                  <Text
                    style={[
                      styles.quickActionText,
                      compact && styles.compactQuickActionText,
                    ]}
                    numberOfLines={1}
                    adjustsFontSizeToFit
                  >
                    {action.title}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        </View>
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
          onPress={() => navigation.navigate("Dashboard", { student })}
          activeOpacity={0.7}
        >
          <Ionicons name="home" size={23} color={isDark ? DarkColors.accent : "#337D73"} />
          <Text style={[styles.navText, styles.activeNavText, isDark && darkStyles.activeNavText]}>
            Home
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          onPress={() =>
            navigation.navigate("ECCDChecklist", { student })
          }
          activeOpacity={0.7}
        >
          <Ionicons
            name="clipboard-outline"
            size={23}
            color={isDark ? DarkColors.textMuted : "#8A9692"}
          />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          onPress={() => navigation.navigate("Profile", { student })}
          activeOpacity={0.7}
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
    backgroundColor: "#FFFFFF",
    minHeight: 86,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: "#F0F2F0",
  },

  headerTextContainer: {
    flex: 1,
  },

  headerTitle: {
    fontSize: 20,
    fontWeight: "800",
    color: "#284F47",
  },

  compactHeaderTitle: {
    fontSize: 21,
  },

  headerSubtitle: {
    fontSize: 12,
    color: "#788781",
    marginTop: 3,
  },

  background: {
    flex: 1,
  },

  backgroundImage: {
    opacity: 0.35,
  },

  content: {
    flex: 1,
    justifyContent: "space-evenly",
  },

  studentCard: {
    minHeight: 94,
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    padding: 13,
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 6,
    shadowColor: "#31574F",
    shadowOpacity: 0.08,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },

  compactStudentCard: {
    minHeight: 78,
    padding: 10,
  },

  studentImage: {
    width: 64,
    height: 64,
    borderRadius: 14,
    backgroundColor: "#E7EFEB",
  },

  studentPhotoPlaceholder: {
    alignItems: "center",
    justifyContent: "center",
  },

  compactStudentImage: {
    width: 52,
    height: 52,
    borderRadius: 12,
  },

  studentInfo: {
    flex: 1,
    marginLeft: 13,
    marginRight: 8,
  },

  studentLabel: {
    fontSize: 9,
    fontWeight: "800",
    letterSpacing: 1,
    color: "#6F9A8F",
    marginBottom: 4,
  },

  studentName: {
    fontSize: 18,
    fontWeight: "800",
    color: "#284F47",
  },

  compactStudentName: {
    fontSize: 16,
  },
  section: {
    marginTop: 4,
    marginBottom: 4,
  },

  sectionHeading: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    marginBottom: 9,
  },

  sectionTitle: {
    fontSize: 18,
    fontWeight: "800",
    color: "#284F47",
  },

  compactSectionTitle: {
    fontSize: 16,
  },

  sectionCaption: {
    fontSize: 11,
    color: "#7C9189",
  },

  activityGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
  },

  activityCard: {
    width: "48.5%",
    minHeight: 112,
    borderRadius: 16,
    padding: 12,
    marginBottom: 9,
    justifyContent: "space-between",
  },

  compactActivityCard: {
    minHeight: 88,
    padding: 9,
    marginBottom: 7,
  },

  activityCardTop: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
  },

  activityIcon: {
    width: 36,
    height: 36,
    borderRadius: 12,
    backgroundColor: "rgba(255, 255, 255, 0.65)",
    alignItems: "center",
    justifyContent: "center",
  },

  activityTextContainer: {
    marginTop: 7,
  },

  activityTitle: {
    fontSize: 13,
    fontWeight: "800",
    color: "#294E46",
  },

  compactActivityTitle: {
    fontSize: 12,
  },

  activitySubtitle: {
    fontSize: 10,
    color: "#58736B",
    marginTop: 3,
  },

  quickActionsRow: {
    flexDirection: "row",
    justifyContent: "space-between",
  },

  quickActionCard: {
    width: "31.5%",
    minHeight: 82,
    borderRadius: 15,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 5,
    paddingVertical: 9,
  },

  compactQuickActionCard: {
    minHeight: 65,
    paddingVertical: 6,
  },

  quickActionText: {
    fontSize: 11,
    fontWeight: "700",
    color: "#365C55",
    marginTop: 7,
    textAlign: "center",
  },

  compactQuickActionText: {
    fontSize: 10,
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
  screen: {
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

  surface: {
    backgroundColor: DarkColors.surface,
  },

  surfaceMuted: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  bottomNav: {
    backgroundColor: DarkColors.surface,
    borderTopColor: DarkColors.border,
  },

  activeNavText: {
    color: DarkColors.accent,
  },
});