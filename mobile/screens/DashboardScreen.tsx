import React, { useCallback, useEffect, useRef, useState } from "react";
import {
  ActivityIndicator,
  Alert,
  RefreshControl,
  View,
  Text,
  Image,
  ImageBackground,
  TouchableOpacity,
  StyleSheet,
  StatusBar,
  Platform,
  ScrollView,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useFocusEffect } from "@react-navigation/native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import {
  getGuardianApiErrorMessage,
  getGuardianChildPhotoSource,
  getGuardianDashboard,
  GuardianDashboard,
  GuardianPhotoSource,
  logoutGuardian,
} from "../services/guardianApi";
import { DarkColors, Layout } from "../constants/theme";
import { ECCD_DOMAINS, EccdDomain, getEccdDomain } from "../constants/eccd-domains";
import { useAppTheme } from "../hooks/use-app-theme";

const RECENT_ACTIVITY_COUNT = 3;

export default function DashboardScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();
  const { width, height } = useWindowDimensions();
  const { isDark } = useAppTheme();

  const compact = height < Layout.compactHeight;
  const small = width < Layout.smallWidth;

  const student = route?.params?.student;
  const studentId = student?.id;
  const [dashboard, setDashboard] = useState<GuardianDashboard | null>(null);
  const [photo, setPhoto] = useState<{
    childId: number;
    source: GuardianPhotoSource;
  } | null>(null);
  const [photoLoading, setPhotoLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const loadedChildIdRef = useRef<number | null>(null);
  const photoSource = photo && photo.childId === studentId ? photo.source : null;

  useFocusEffect(
    useCallback(() => {
      if (typeof studentId !== "number") {
        return undefined;
      }

      let isActive = true;
      getGuardianDashboard(studentId)
        .then((data) => {
          if (isActive) {
            loadedChildIdRef.current = data.child.id;
            setDashboard(data);
          }
        })
        .catch((error: unknown) => {
          if (isActive && loadedChildIdRef.current !== studentId) {
            Alert.alert("Unable to load dashboard", getGuardianApiErrorMessage(error));
          }
        });

      return () => {
        isActive = false;
      };
    }, [studentId]),
  );

  const handleRefresh = async () => {
    if (typeof studentId !== "number") {
      return;
    }

    setRefreshing(true);
    try {
      const data = await getGuardianDashboard(studentId);
      loadedChildIdRef.current = data.child.id;
      setDashboard(data);
    } catch (error) {
      Alert.alert("Unable to refresh dashboard", getGuardianApiErrorMessage(error));
    } finally {
      setRefreshing(false);
    }
  };

  const currentDashboard = dashboard?.child.id === studentId ? dashboard : null;
  const photoUrl = currentDashboard?.child.photo_url ?? null;

  useEffect(() => {
    if (!photoUrl || typeof studentId !== "number") {
      return undefined;
    }

    let isMounted = true;
    const requestedStudentId = studentId;

    setPhotoLoading(true);
    getGuardianChildPhotoSource(photoUrl)
      .then((source) => {
        if (isMounted && source) {
          setPhoto({ childId: requestedStudentId, source });
        }
      })
      .catch(() => {
      })
      .finally(() => {
        if (isMounted) {
          setPhotoLoading(false);
        }
      });

    return () => {
      isMounted = false;
    };
  }, [photoUrl, studentId]);
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

  const recentDomains = (currentDashboard?.progress.recent_activity ?? [])
    .map((entry) => getEccdDomain(entry.domain))
    .filter((domain): domain is EccdDomain => domain !== undefined);

  const activities = [
    ...(recentDomains.length > 0 ? recentDomains : ECCD_DOMAINS)
      .slice(0, RECENT_ACTIVITY_COUNT)
      .map((domain) => ({
        title: domain.title,
        subtitle: domain.label,
        domainKey: domain.key as string | null,
        icon: domain.icon,
        color: domain.color,
      })),
    {
      title: "View Full",
      subtitle: "ECCD Checklist",
      domainKey: null,
      icon: "clipboard-outline" as const,
      color: "#CDEBE4",
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

  const openActivity = (domainKey: string | null) => {
    if (domainKey) {
      navigation.navigate("Domain", { student, domainKey });
    } else {
      navigation.navigate("ECCDChecklist", { student });
    }
  };

  return (
    <View style={[styles.screen, isDark && darkStyles.screen]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
      />

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

      <ImageBackground
        source={require("../assets/images/back.jpg")}
        style={styles.background}
        imageStyle={[styles.backgroundImage, isDark && darkStyles.backgroundImage]}
        resizeMode="cover"
      >
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={[
            styles.content,
            {
              paddingHorizontal: small ? 12 : 18,
              paddingTop: compact ? 6 : 10,
              paddingBottom: 6,
            },
          ]}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => void handleRefresh()}
              tintColor="#0BA8BD"
              colors={["#0BA8BD"]}
            />
          }
        >
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

          {latestRoundData && (
            <TouchableOpacity
              style={[styles.resultCard, compact && styles.compactResultCard, isDark && darkStyles.surface]}
              activeOpacity={0.85}
              accessibilityRole="button"
              accessibilityLabel="Why this result? See how the result was computed"
              onPress={() => navigation.navigate("Insights", { student })}
            >
              <View style={[styles.resultIcon, isDark && darkStyles.surfaceMuted]}>
                <Ionicons name="analytics-outline" size={22} color={isDark ? DarkColors.icon : "#365C55"} />
              </View>
              <View style={styles.resultText}>
                <Text style={[styles.resultLabel, isDark && darkStyles.mutedText]}>LATEST RESULT</Text>
                <Text style={[styles.resultValue, isDark && darkStyles.text]} numberOfLines={2}>
                  {latestRoundData.standard_score !== null && latestRoundData.interpretation
                    ? latestRoundData.interpretation
                    : `${latestRoundData.completed_domains} of 7 areas scored so far`}
                </Text>
                <Text style={[styles.resultLink, isDark && darkStyles.accentText]}>Why this result? ›</Text>
              </View>
              {latestRoundData.standard_score !== null && (
                <View style={styles.resultScore}>
                  <Text style={[styles.resultScoreValue, isDark && darkStyles.text]}>{latestRoundData.standard_score}</Text>
                  <Text style={[styles.resultScoreCaption, isDark && darkStyles.mutedText]}>Standard</Text>
                </View>
              )}
            </TouchableOpacity>
          )}

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
                  accessibilityRole="button"
                  onPress={() => openActivity(activity.domainKey)}
                >
                  <View style={styles.activityCardTop}>
                    <View style={styles.activityIcon}>
                      <Ionicons
                        name={activity.icon}
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
        </ScrollView>
      </ImageBackground>

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

  scrollView: {
    flex: 1,
  },

  content: {
    flexGrow: 1,
    justifyContent: "space-evenly",
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
  },

  studentCard: {
    minHeight: 94,
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    padding: 13,
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 6,
    ...Platform.select({
      web: { boxShadow: "0px 3px 8px rgba(49, 87, 79, 0.08)" },
      default: {
        shadowColor: "#31574F",
        shadowOpacity: 0.08,
        shadowRadius: 8,
        shadowOffset: { width: 0, height: 3 },
        elevation: 2,
      },
    }),
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
  resultCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    padding: 13,
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    marginTop: 6,
    marginBottom: 6,
    ...Platform.select({
      web: { boxShadow: "0px 3px 8px rgba(49, 87, 79, 0.08)" },
      default: {
        shadowColor: "#31574F",
        shadowOpacity: 0.08,
        shadowRadius: 8,
        shadowOffset: { width: 0, height: 3 },
        elevation: 2,
      },
    }),
  },
  compactResultCard: {
    padding: 10,
  },
  resultIcon: {
    width: 44,
    height: 44,
    borderRadius: 14,
    backgroundColor: "#EEF5F1",
    alignItems: "center",
    justifyContent: "center",
  },
  resultText: {
    flex: 1,
  },
  resultLabel: {
    fontSize: 10,
    fontWeight: "700",
    letterSpacing: 0.6,
    color: "#8A9692",
  },
  resultValue: {
    fontSize: 15,
    fontWeight: "700",
    color: "#263447",
    marginTop: 2,
  },
  resultLink: {
    fontSize: 12,
    fontWeight: "600",
    color: "#0B8C9E",
    marginTop: 3,
  },
  resultScore: {
    alignItems: "center",
    minWidth: 54,
  },
  resultScoreValue: {
    fontSize: 24,
    fontWeight: "800",
    color: "#263447",
  },
  resultScoreCaption: {
    fontSize: 10,
    color: "#8A9692",
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

  accentText: {
    color: DarkColors.accent,
  },

  bottomNav: {
    backgroundColor: DarkColors.surface,
    borderTopColor: DarkColors.border,
  },

  activeNavText: {
    color: DarkColors.accent,
  },
});