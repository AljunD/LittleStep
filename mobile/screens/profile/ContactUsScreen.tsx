import React, { useMemo } from "react";
import {
  Alert,
  Linking,
  RefreshControl,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../../constants/theme";
import { useAppTheme } from "../../hooks/use-app-theme";
import { useApiResource } from "../../hooks/use-api-resource";
import { EmptyState, ErrorState, LoadingState } from "../../components/screen-state";
import { getGuardianContacts, GuardianContact } from "../../services/guardianApi";
import { fullName } from "../../services/format";

type TeacherGroup = {
  key: string;
  teacher: NonNullable<GuardianContact["teacher"]>;
  childNames: string[];
};

function groupByTeacher(contacts: GuardianContact[]) {
  const groups = new Map<string, TeacherGroup>();
  const unassigned: string[] = [];

  for (const { child, teacher } of contacts) {
    if (!teacher) {
      unassigned.push(child.first_name);
      continue;
    }

    const key = `${fullName(teacher)}|${teacher.contact_number ?? ""}`;
    const group = groups.get(key) ?? { key, teacher, childNames: [] };
    group.childNames.push(child.first_name);
    groups.set(key, group);
  }

  return { teachers: [...groups.values()], unassigned };
}

function joinNames(names: string[]): string {
  return names.length <= 1
    ? names.join("")
    : `${names.slice(0, -1).join(", ")} and ${names[names.length - 1]}`;
}

export default function ContactUsScreen({ navigation, route }: any) {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();
  const student = route?.params?.student;
  const contacts = useApiResource(getGuardianContacts);
  const grouped = useMemo(() => groupByTeacher(contacts.data ?? []), [contacts.data]);

  const openPhone = async (scheme: "tel" | "sms", number: string) => {
    const url = `${scheme}:${number.replace(/[^\d+]/g, "")}`;
    try {
      await Linking.openURL(url);
    } catch {
      Alert.alert(
        scheme === "tel" ? "Unable to call" : "Unable to send a message",
        `This device can't open the ${scheme === "tel" ? "phone" : "messages"} app. The number is ${number}.`,
      );
    }
  };

  const iconColor = isDark ? DarkColors.icon : "#365C55";
  const navIconColor = isDark ? DarkColors.textMuted : "#89918F";

  const renderBody = () => {
    if (contacts.loading && !contacts.data) {
      return <LoadingState message="Loading contacts..." />;
    }

    if (!contacts.data) {
      return (
        <ErrorState
          message={contacts.error ?? "Contacts could not be loaded."}
          onRetry={contacts.retry}
        />
      );
    }

    if (grouped.teachers.length === 0) {
      return (
        <EmptyState
          icon="people-outline"
          title="No teacher assigned yet"
          message="Contact details appear here once a teacher starts recording your child's progress."
        />
      );
    }

    return (
      <>
        {grouped.teachers.map(({ key, teacher, childNames }) => (
          <View key={key} style={[styles.card, isDark && darkStyles.card]}>
            <View style={styles.cardTop}>
              <View style={[styles.cardIcon, isDark && darkStyles.surfaceMuted]}>
                <Ionicons name="school-outline" size={22} color={iconColor} />
              </View>
              <View style={styles.cardText}>
                <Text style={[styles.cardTitle, isDark && darkStyles.text]}>{fullName(teacher)}</Text>
                <Text style={[styles.cardSubtitle, isDark && darkStyles.mutedText]}>
                  Teacher of {joinNames(childNames)}
                </Text>
              </View>
            </View>

            {teacher.contact_number ? (
              <>
                <View style={[styles.divider, isDark && darkStyles.divider]} />
                <View style={styles.phoneRow}>
                  <Ionicons name="call-outline" size={18} color={iconColor} />
                  <Text style={[styles.phoneText, isDark && darkStyles.text]} selectable>
                    {teacher.contact_number}
                  </Text>
                </View>
                <View style={styles.actions}>
                  <TouchableOpacity
                    style={styles.actionButton}
                    activeOpacity={0.8}
                    accessibilityRole="button"
                    accessibilityLabel={`Call ${fullName(teacher)}`}
                    onPress={() => void openPhone("tel", teacher.contact_number as string)}
                  >
                    <Ionicons name="call" size={16} color="#FFFFFF" />
                    <Text style={styles.actionText}>Call</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={[styles.actionButton, styles.actionButtonSecondary, isDark && darkStyles.secondaryButton]}
                    activeOpacity={0.8}
                    accessibilityRole="button"
                    accessibilityLabel={`Send a text message to ${fullName(teacher)}`}
                    onPress={() => void openPhone("sms", teacher.contact_number as string)}
                  >
                    <Ionicons name="chatbubble-outline" size={16} color="#0BA8BD" />
                    <Text style={[styles.actionText, styles.actionTextSecondary]}>Text</Text>
                  </TouchableOpacity>
                </View>
              </>
            ) : (
              <Text style={[styles.noPhone, isDark && darkStyles.mutedText]}>
                No contact number on file.
              </Text>
            )}
          </View>
        ))}

        {grouped.unassigned.length > 0 && (
          <Text style={[styles.note, isDark && darkStyles.mutedText]}>
            No teacher has been assigned to {joinNames(grouped.unassigned)} yet.
          </Text>
        )}
      </>
    );
  };

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
      />

      <View style={[styles.header, isDark && darkStyles.container, { paddingTop: insets.top + 10 }]}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
          accessibilityRole="button"
          accessibilityLabel="Go back"
        >
          <Ionicons name="chevron-back" size={26} color={isDark ? DarkColors.text : "#263447"} />
        </TouchableOpacity>

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Contact Us</Text>

        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={contacts.refreshing}
            onRefresh={contacts.refresh}
            tintColor="#0BA8BD"
            colors={["#0BA8BD"]}
          />
        }
      >
        <Text style={[styles.pageTitle, isDark && darkStyles.text]}>Get in touch</Text>
        <Text style={[styles.pageDescription, isDark && darkStyles.mutedText]}>
          Reach your child's teacher for questions about their progress.
        </Text>

        {renderBody()}
      </ScrollView>

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
          onPress={() => navigation.navigate("Dashboard", { student })}
        >
          <Ionicons name="home-outline" size={23} color={navIconColor} />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => navigation.navigate("ECCDChecklist", { student })}
        >
          <Ionicons name="clipboard-outline" size={23} color={navIconColor} />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => navigation.navigate("Profile", { student })}
        >
          <Ionicons name="person" size={23} color={isDark ? DarkColors.accent : "#365C55"} />
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
    marginBottom: 22,
  },

  card: {
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    padding: 16,
    marginBottom: 14,
  },

  cardTop: {
    flexDirection: "row",
    alignItems: "center",
  },

  cardIcon: {
    width: 46,
    height: 46,
    borderRadius: 14,
    backgroundColor: "#EEF5F1",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 13,
  },

  cardText: {
    flex: 1,
  },

  cardTitle: {
    fontSize: 16,
    fontWeight: "600",
    color: "#263447",
  },

  cardSubtitle: {
    fontSize: 12,
    lineHeight: 18,
    color: "#858D91",
    marginTop: 3,
  },

  divider: {
    height: 1,
    backgroundColor: "#EDF0EF",
    marginVertical: 14,
  },

  phoneRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    marginBottom: 14,
  },

  phoneText: {
    fontSize: 15,
    fontWeight: "600",
    color: "#263447",
  },

  actions: {
    flexDirection: "row",
    gap: 10,
  },

  actionButton: {
    flex: 1,
    height: 42,
    borderRadius: 21,
    backgroundColor: "#0BA8BD",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 7,
  },

  actionButtonSecondary: {
    backgroundColor: "#E6F7F9",
  },

  actionText: {
    color: "#FFFFFF",
    fontSize: 14,
    fontWeight: "700",
  },

  actionTextSecondary: {
    color: "#0BA8BD",
  },

  noPhone: {
    fontSize: 12,
    color: "#858D91",
    marginTop: 12,
  },

  note: {
    fontSize: 12,
    lineHeight: 18,
    color: "#858D91",
    marginTop: 4,
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

  secondaryButton: {
    backgroundColor: DarkColors.surfaceMuted,
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
