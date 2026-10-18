import React, { useCallback, useState } from "react";
import {
  RefreshControl,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../constants/theme";
import { ECCD_DOMAINS } from "../constants/eccd-domains";
import { useAppTheme } from "../hooks/use-app-theme";
import { useApiResource } from "../hooks/use-api-resource";
import { EmptyState, ErrorState, LoadingState } from "../components/screen-state";
import { formatDate } from "../services/format";
import { getGuardianChildInsights, GuardianApiError } from "../services/guardianApi";

const ROUND_LABELS: Record<number, string> = {
  1: "1st Eval",
  2: "2nd Eval",
  3: "3rd Eval",
};

/**
 * "Why this result?": the expert system's step-by-step scoring for each evaluation round,
 * plus checklist skills to practise at home. Read-only.
 */
export default function InsightsScreen({ navigation, route }: any) {
  const insets = useSafeAreaInsets();
  const { width } = useWindowDimensions();
  const { isDark } = useAppTheme();
  const small = width < Layout.smallWidth;

  const student = route?.params?.student;
  const studentId: unknown = student?.id;
  const [selectedRound, setSelectedRound] = useState<number | null>(null);

  const loadInsights = useCallback(() => {
    if (typeof studentId !== "number") {
      return Promise.reject(new GuardianApiError("No child was selected."));
    }

    return getGuardianChildInsights(studentId);
  }, [studentId]);
  const insights = useApiResource(loadInsights);

  const iconColor = isDark ? DarkColors.icon : "#365C55";
  const navIconColor = isDark ? DarkColors.textMuted : "#89918F";
  const childName = insights.data?.child.first_name ?? student?.first_name ?? "your child";

  const renderBody = () => {
    if (insights.loading && !insights.data) {
      return <LoadingState message="Loading results..." />;
    }

    if (!insights.data) {
      return (
        <ErrorState
          message={insights.error ?? "Results could not be loaded."}
          onRetry={insights.retry}
        />
      );
    }

    const { rounds, skills_to_practice: skills } = insights.data;
    if (rounds.length === 0) {
      return (
        <EmptyState
          icon="analytics-outline"
          title="No results yet"
          message={`Results appear here once ${childName}'s teacher records the first evaluation.`}
        />
      );
    }

    const fallbackRound = insights.data.latest_round ?? rounds[rounds.length - 1].round;
    const explanation = rounds.find((entry) => entry.round === (selectedRound ?? fallbackRound))
      ?? rounds[rounds.length - 1];
    const skillGroups = ECCD_DOMAINS.filter((domain) => skills[domain.key]?.items.length);

    return (
      <>
        {/* ROUND SELECTOR */}
        {rounds.length > 1 && (
          <View style={[styles.roundTabs, isDark && darkStyles.card]} accessibilityRole="tablist">
            {rounds.map((entry) => {
              const selected = entry.round === explanation.round;

              return (
                <TouchableOpacity
                  key={entry.round}
                  style={[styles.roundTab, selected && styles.roundTabSelected, selected && isDark && darkStyles.roundTabSelected]}
                  activeOpacity={0.8}
                  accessibilityRole="tab"
                  accessibilityState={{ selected }}
                  onPress={() => setSelectedRound(entry.round)}
                >
                  <Text style={[styles.roundTabTitle, isDark && darkStyles.mutedText, selected && styles.roundTabTitleSelected, selected && isDark && darkStyles.text]}>
                    {ROUND_LABELS[entry.round]}
                  </Text>
                  <Text style={[styles.roundTabDate, isDark && darkStyles.mutedText]} numberOfLines={1}>
                    {formatDate(entry.evaluation_date) ?? "—"}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        )}

        {/* SUMMARY */}
        <View style={[styles.summaryCard, isDark && darkStyles.card]}>
          <Text style={[styles.summaryLabel, isDark && darkStyles.mutedText]}>
            {ROUND_LABELS[explanation.round].replace(" Eval", " evaluation")}
            {explanation.age ? ` · Age ${explanation.age.label}` : ""}
          </Text>
          {explanation.standard_score !== null ? (
            <>
              <Text style={[styles.summaryScore, isDark && darkStyles.text]}>{explanation.standard_score}</Text>
              <Text style={[styles.summaryCaption, isDark && darkStyles.mutedText]}>Standard score</Text>
              <Text style={[styles.summaryOverall, isDark && darkStyles.accentText]}>{explanation.overall}</Text>
            </>
          ) : (
            <Text style={[styles.summaryOverall, isDark && darkStyles.accentText]}>
              {explanation.scored_domains} of 7 areas scored so far
            </Text>
          )}
        </View>

        {/* NOTES */}
        {explanation.warnings.length > 0 && (
          <View style={[styles.noteCard, isDark && darkStyles.noteCard]}>
            <Ionicons name="information-circle-outline" size={20} color={isDark ? DarkColors.accent : "#8A6D1F"} />
            <View style={styles.noteTextContainer}>
              {explanation.warnings.map((warning) => (
                <Text key={warning} style={[styles.noteText, isDark && darkStyles.text]}>{warning}</Text>
              ))}
            </View>
          </View>
        )}

        {/* STEPS */}
        <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>How it was computed</Text>
        <View style={[styles.card, isDark && darkStyles.card]}>
          {explanation.steps.map((step, index) => (
            <View key={step} style={[styles.stepRow, index > 0 && styles.stepRowSpaced]}>
              <View style={[styles.stepNumber, isDark && darkStyles.surfaceMuted]}>
                <Text style={[styles.stepNumberText, isDark && darkStyles.text]}>{index + 1}</Text>
              </View>
              <Text style={[styles.stepText, isDark && darkStyles.text]}>{step}</Text>
            </View>
          ))}
        </View>

        {/* AREAS */}
        <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>Development areas</Text>
        <View style={[styles.card, styles.cardFlush, isDark && darkStyles.card]}>
          {ECCD_DOMAINS.map((domain, index) => {
            const result = explanation.domains[domain.key];
            if (!result) {
              return null;
            }

            return (
              <TouchableOpacity
                key={domain.key}
                style={[styles.areaRow, index > 0 && styles.areaDivider, index > 0 && isDark && darkStyles.divider]}
                activeOpacity={0.75}
                accessibilityRole="button"
                accessibilityLabel={`${domain.title}: ${result.interpretation ?? "not scored yet"}`}
                onPress={() => navigation.navigate("Domain", { student, domainKey: domain.key })}
              >
                <View style={[styles.areaIcon, { backgroundColor: domain.color }]}>
                  <Ionicons name={domain.icon} size={18} color="#365C55" />
                </View>
                <View style={styles.areaText}>
                  <Text style={[styles.areaTitle, isDark && darkStyles.text]} numberOfLines={1}>{domain.title}</Text>
                  <Text style={[styles.areaMeta, isDark && darkStyles.mutedText]}>
                    ✓ {result.items_present} of {result.items_total} skills · {result.items_answered < result.items_total
                      ? `${result.items_answered}/${result.items_total} checked`
                      : `scaled ${result.scaled_score ?? "—"}`}
                  </Text>
                  <Text style={[styles.areaInterpretation, isDark && darkStyles.accentText]}>
                    {result.interpretation ?? "Not scored yet"}
                  </Text>
                </View>
                <Ionicons name="chevron-forward" size={18} color={navIconColor} />
              </TouchableOpacity>
            );
          })}
        </View>

        {/* SKILLS TO PRACTISE */}
        <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>Things to try at home</Text>
        {skillGroups.length === 0 ? (
          <View style={[styles.card, isDark && darkStyles.card]}>
            <Text style={[styles.stepText, isDark && darkStyles.mutedText]}>
              Every checked skill was present in the latest evaluation. Keep up the everyday play and routines.
            </Text>
          </View>
        ) : (
          skillGroups.map((domain) => {
            const group = skills[domain.key];

            return (
              <View key={domain.key} style={[styles.card, isDark && darkStyles.card]}>
                <View style={styles.skillHeader}>
                  <View style={[styles.areaIcon, { backgroundColor: domain.color }]}>
                    <Ionicons name={domain.icon} size={18} color="#365C55" />
                  </View>
                  <View style={styles.areaText}>
                    <Text style={[styles.areaTitle, isDark && darkStyles.text]}>{domain.title}</Text>
                    <Text style={[styles.areaMeta, isDark && darkStyles.mutedText]}>
                      Not yet observed in the {ROUND_LABELS[group.round].replace(" Eval", " evaluation")}
                    </Text>
                  </View>
                </View>
                {group.items.map((item) => (
                  <View key={item.id} style={styles.skillRow}>
                    <Ionicons name="sparkles-outline" size={15} color={iconColor} />
                    <Text style={[styles.skillText, isDark && darkStyles.text]}>{item.activity}</Text>
                  </View>
                ))}
              </View>
            );
          })
        )}

        <Text style={[styles.disclaimer, isDark && darkStyles.mutedText]}>
          These results follow the official ECCD Checklist scoring. They suggest how {childName} is developing and are
          not a diagnosis. Talk to the teacher if you have questions.
        </Text>
      </>
    );
  };

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
          accessibilityRole="button"
          accessibilityLabel="Go back"
        >
          <Ionicons name="chevron-back" size={26} color={isDark ? DarkColors.text : "#263447"} />
        </TouchableOpacity>

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Why This Result?</Text>

        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[styles.content, { paddingHorizontal: small ? 16 : 22 }]}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={insights.refreshing}
            onRefresh={insights.refresh}
            tintColor="#0BA8BD"
            colors={["#0BA8BD"]}
          />
        }
      >
        <Text style={[styles.pageDescription, isDark && darkStyles.mutedText]}>
          LittleStep scores the ECCD Checklist step by step, the same way for the teacher and for you. Here is how{" "}
          {childName}'s result was reached.
        </Text>

        {renderBody()}
      </ScrollView>

      {/* Bottom Navigation */}
      <View style={[styles.bottomNav, isDark && darkStyles.bottomNav, { paddingBottom: Math.max(insets.bottom, 10) }]}>
        <TouchableOpacity style={styles.navItem} activeOpacity={0.7} onPress={() => navigation.navigate("Dashboard", { student })}>
          <Ionicons name="home" size={23} color={isDark ? DarkColors.accent : "#365C55"} />
          <Text style={[styles.navText, styles.activeNavText, isDark && darkStyles.activeNavText]}>Home</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.navItem} activeOpacity={0.7} onPress={() => navigation.navigate("ECCDChecklist", { student })}>
          <Ionicons name="clipboard-outline" size={23} color={navIconColor} />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>ECCD Checklist</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.navItem} activeOpacity={0.7} onPress={() => navigation.navigate("Profile", { student })}>
          <Ionicons name="person-outline" size={23} color={navIconColor} />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>Profile</Text>
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
    paddingTop: 10,
    paddingBottom: 30,
  },

  pageDescription: {
    fontSize: 14,
    lineHeight: 21,
    color: "#858D91",
    marginBottom: 18,
  },

  roundTabs: {
    flexDirection: "row",
    backgroundColor: "#F3F6F5",
    borderRadius: 16,
    padding: 4,
    marginBottom: 14,
  },

  roundTab: {
    flex: 1,
    alignItems: "center",
    paddingVertical: 9,
    borderRadius: 12,
  },

  roundTabSelected: {
    backgroundColor: "#FFFFFF",
  },

  roundTabTitle: {
    fontSize: 13,
    fontWeight: "600",
    color: "#7B8783",
  },

  roundTabTitleSelected: {
    color: "#263447",
  },

  roundTabDate: {
    fontSize: 11,
    color: "#98A29F",
    marginTop: 2,
  },

  summaryCard: {
    alignItems: "center",
    backgroundColor: "#EEF7F5",
    borderRadius: 20,
    borderWidth: 1,
    borderColor: "#DCEDE8",
    paddingVertical: 20,
    paddingHorizontal: 16,
    marginBottom: 14,
  },

  summaryLabel: {
    fontSize: 12,
    fontWeight: "600",
    color: "#6F7C78",
    textTransform: "uppercase",
    letterSpacing: 0.5,
  },

  summaryScore: {
    fontSize: 44,
    fontWeight: "800",
    color: "#263447",
    marginTop: 6,
  },

  summaryCaption: {
    fontSize: 12,
    color: "#858D91",
  },

  summaryOverall: {
    fontSize: 16,
    fontWeight: "700",
    color: "#2F6F62",
    textAlign: "center",
    marginTop: 10,
  },

  noteCard: {
    flexDirection: "row",
    gap: 10,
    backgroundColor: "#FFF7E3",
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#F6E3B4",
    padding: 14,
    marginBottom: 14,
  },

  noteTextContainer: {
    flex: 1,
    gap: 6,
  },

  noteText: {
    fontSize: 13,
    lineHeight: 19,
    color: "#6B5417",
  },

  sectionTitle: {
    fontSize: 17,
    fontWeight: "700",
    color: "#263447",
    marginTop: 8,
    marginBottom: 10,
  },

  card: {
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    padding: 16,
    marginBottom: 14,
  },

  cardFlush: {
    paddingVertical: 4,
  },

  stepRow: {
    flexDirection: "row",
    gap: 12,
  },

  stepRowSpaced: {
    marginTop: 12,
  },

  stepNumber: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: "#E6F7F9",
    alignItems: "center",
    justifyContent: "center",
  },

  stepNumberText: {
    fontSize: 12,
    fontWeight: "700",
    color: "#0B7F8F",
  },

  stepText: {
    flex: 1,
    fontSize: 14,
    lineHeight: 21,
    color: "#3D4A52",
  },

  areaRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    paddingVertical: 12,
  },

  areaDivider: {
    borderTopWidth: 1,
    borderTopColor: "#EDF0EF",
  },

  areaIcon: {
    width: 38,
    height: 38,
    borderRadius: 12,
    alignItems: "center",
    justifyContent: "center",
  },

  areaText: {
    flex: 1,
  },

  areaTitle: {
    fontSize: 15,
    fontWeight: "600",
    color: "#263447",
  },

  areaMeta: {
    fontSize: 12,
    color: "#858D91",
    marginTop: 2,
  },

  areaInterpretation: {
    fontSize: 13,
    fontWeight: "600",
    color: "#2F6F62",
    marginTop: 3,
  },

  skillHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    marginBottom: 10,
  },

  skillRow: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 9,
    marginTop: 8,
  },

  skillText: {
    flex: 1,
    fontSize: 14,
    lineHeight: 20,
    color: "#3D4A52",
  },

  disclaimer: {
    fontSize: 12,
    lineHeight: 18,
    color: "#858D91",
    marginTop: 6,
    textAlign: "center",
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

  accentText: {
    color: DarkColors.accent,
  },

  card: {
    backgroundColor: DarkColors.surface,
    borderColor: DarkColors.border,
  },

  surfaceMuted: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  roundTabSelected: {
    backgroundColor: DarkColors.selected,
  },

  noteCard: {
    backgroundColor: DarkColors.surfaceMuted,
    borderColor: DarkColors.border,
  },

  divider: {
    borderTopColor: DarkColors.border,
  },

  bottomNav: {
    backgroundColor: DarkColors.surface,
    borderTopColor: DarkColors.border,
  },

  activeNavText: {
    color: DarkColors.accent,
  },
});
