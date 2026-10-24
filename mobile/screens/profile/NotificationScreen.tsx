import React from "react";
import {
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
import { getEccdDomain } from "../../constants/eccd-domains";
import { useAppTheme } from "../../hooks/use-app-theme";
import { useApiResource } from "../../hooks/use-api-resource";
import { EmptyState, ErrorState, LoadingState } from "../../components/screen-state";
import { getGuardianProgressUpdates, GuardianProgressUpdate } from "../../services/guardianApi";
import { fullName } from "../../services/format";

function formatUpdateTime(iso: string): string {
  const date = new Date(iso);
  return Number.isNaN(date.getTime())
    ? ""
    : date.toLocaleString(undefined, {
      month: "short",
      day: "numeric",
      year: "numeric",
      hour: "numeric",
      minute: "2-digit",
    });
}

export default function NotificationScreen({ navigation, route }: any) {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();
  const student = route?.params?.student;
  const updates = useApiResource(getGuardianProgressUpdates);

  const navIconColor = isDark ? DarkColors.textMuted : "#89918F";

  const openUpdate = (update: GuardianProgressUpdate) => {
    navigation.navigate("Domain", {
      student: {
        id: update.child.id,
        name: fullName(update.child),
        firstName: update.child.first_name,
        lastName: update.child.last_name,
        photo_url: update.child.photo_url,
      },
      domainKey: update.domain,
    });
  };

  const renderBody = () => {
    if (updates.loading && !updates.data) {
      return <LoadingState message="Loading updates..." />;
    }

    if (!updates.data) {
      return (
        <ErrorState
          message={updates.error ?? "Updates could not be loaded."}
          onRetry={updates.retry}
        />
      );
    }

    if (updates.data.length === 0) {
      return (
        <EmptyState
          icon="notifications-outline"
          title="No updates yet"
          message="You'll see an update here each time your child's teacher records new ECCD checklist results."
        />
      );
    }

    return (
      <View style={[styles.listCard, isDark && darkStyles.card]}>
        {updates.data.map((update, index) => {
          const domain = getEccdDomain(update.domain);

          return (
            <React.Fragment key={`${update.child.id}-${update.domain}`}>
              {index > 0 && <View style={[styles.divider, isDark && darkStyles.divider]} />}
              <TouchableOpacity
                style={styles.updateRow}
                activeOpacity={0.7}
                accessibilityRole="button"
                onPress={() => openUpdate(update)}
              >
                <View style={[styles.updateIcon, { backgroundColor: domain?.color ?? "#EEF5F1" }]}>
                  <Ionicons name={domain?.icon ?? "clipboard-outline"} size={20} color="#365C55" />
                </View>

                <View style={styles.updateText}>
                  <Text style={[styles.updateTitle, isDark && darkStyles.text]}>
                    New {update.label} results for {update.child.first_name}
                  </Text>
                  <Text style={[styles.updateTime, isDark && darkStyles.mutedText]}>
                    {formatUpdateTime(update.updatedAt)}
                  </Text>
                </View>

                <Ionicons name="chevron-forward" size={19} color={navIconColor} />
              </TouchableOpacity>
            </React.Fragment>
          );
        })}
      </View>
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

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Notifications</Text>

        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={updates.refreshing}
            onRefresh={updates.refresh}
            tintColor="#0BA8BD"
            colors={["#0BA8BD"]}
          />
        }
      >
        <Text style={[styles.pageTitle, isDark && darkStyles.text]}>Teacher updates</Text>
        <Text style={[styles.pageDescription, isDark && darkStyles.mutedText]}>
          The latest ECCD checklist results recorded for your children, newest first.
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

  listCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    paddingHorizontal: 14,
  },

  updateRow: {
    minHeight: 76,
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: 12,
  },

  updateIcon: {
    width: 42,
    height: 42,
    borderRadius: 12,
    alignItems: "center",
    justifyContent: "center",
    marginRight: 12,
  },

  updateText: {
    flex: 1,
    paddingRight: 8,
  },

  updateTitle: {
    fontSize: 14,
    fontWeight: "600",
    lineHeight: 19,
    color: "#263447",
  },

  updateTime: {
    fontSize: 11,
    color: "#858D91",
    marginTop: 4,
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
