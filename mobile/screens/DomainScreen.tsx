import React, { useCallback, useState } from "react";
import {
  ActivityIndicator,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  ScrollView,
  RefreshControl,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useFocusEffect } from "@react-navigation/native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../constants/theme";
import { getEccdDomain } from "../constants/eccd-domains";
import { useAppTheme } from "../hooks/use-app-theme";
import { formatDate } from "../services/format";
import {
  getGuardianApiErrorMessage,
  getGuardianChildDomain,
  GuardianApiError,
  GuardianDomainDetail,
  GuardianItemResult,
} from "../services/guardianApi";

const ROUNDS = [1, 2, 3] as const;
const ROUND_LABELS: Record<number, string> = {
  1: "1st Eval",
  2: "2nd Eval",
  3: "3rd Eval",
};

export default function DomainScreen({ navigation, route }: any) {
  const insets = useSafeAreaInsets();
  const { width, height } = useWindowDimensions();
  const { isDark } = useAppTheme();

  const compact = height < Layout.compactHeight;
  const small = width < Layout.smallWidth;

  const student = route?.params?.student;
  const studentId: unknown = student?.id;
  const domainMeta = getEccdDomain(route?.params?.domainKey);

  const [detail, setDetail] = useState<GuardianDomainDetail | null>(null);
  const [selectedRound, setSelectedRound] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const fetchDetail = useCallback(async () => {
    if (typeof studentId !== "number" || !domainMeta) {
      throw new GuardianApiError("No child or development area was selected.");
    }

    return getGuardianChildDomain(studentId, domainMeta.key);
  }, [studentId, domainMeta]);

  // Reload whenever the screen comes into view so new teacher entries show up.
  useFocusEffect(
    useCallback(() => {
      let isActive = true;

      fetchDetail()
        .then((data) => {
          if (isActive) {
            setDetail(data);
            setErrorMessage(null);
          }
        })
        .catch((error: unknown) => {
          if (isActive) {
            setErrorMessage(getGuardianApiErrorMessage(error));
          }
        })
        .finally(() => {
          if (isActive) {
            setLoading(false);
          }
        });

      return () => {
        isActive = false;
      };
    }, [fetchDetail]),
  );

  const reload = async (showSpinner: boolean) => {
    if (showSpinner) {
      setLoading(true);
    } else {
      setRefreshing(true);
    }

    try {
      setDetail(await fetchDetail());
      setErrorMessage(null);
    } catch (error) {
      setErrorMessage(getGuardianApiErrorMessage(error));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const round = selectedRound ?? detail?.progress.latest_round ?? 1;
  const roundData = detail?.progress.rounds[round] ?? null;
  const hasResults = (roundData?.assessed_items ?? 0) > 0;
  const lastUpdated = formatDate(detail?.progress.last_updated_at ?? null);
  const domainLabel = detail?.domain.label ?? domainMeta?.label ?? "";

  const iconColor = isDark ? DarkColors.icon : "#365C55";
  const mutedIconColor = isDark ? DarkColors.textMuted : "#8A9692";

  const renderMark = (result: GuardianItemResult) => {
    if (result?.present === "check") {
      return (
        <View style={[styles.mark, styles.markPresent]} accessibilityLabel="Present">
          <Text style={[styles.markSymbol, styles.markPresentText]}>✓</Text>
        </View>
      );
    }

    if (result?.present === "hyphen") {
      return (
        <View
          style={[styles.mark, styles.markAbsent, isDark && darkStyles.markAbsent]}
          accessibilityLabel="Not present"
        >
          <Text style={[styles.markSymbol, styles.markAbsentText, isDark && darkStyles.mutedText]}>—</Text>
        </View>
      );
    }

    return (
      <View
        style={[styles.mark, styles.markPending, isDark && darkStyles.markPending]}
        accessibilityLabel="Not yet assessed"
      >
        <Ionicons name="time-outline" size={14} color={mutedIconColor} />
      </View>
    );
  };

  const renderBody = () => {
    if (loading) {
      return (
        <View style={styles.stateBox}>
          <ActivityIndicator color="#0BA8BD" />
          <Text style={[styles.stateText, isDark && darkStyles.mutedText]}>Loading results...</Text>
        </View>
      );
    }

    if (errorMessage || !detail) {
      return (
        <View style={styles.stateBox}>
          <Ionicons name="cloud-offline-outline" size={36} color={mutedIconColor} />
          <Text accessibilityRole="alert" style={[styles.errorText, isDark && darkStyles.danger]}>
            {errorMessage ?? "Results could not be loaded."}
          </Text>
          <TouchableOpacity
            style={styles.retryButton}
            activeOpacity={0.8}
            onPress={() => void reload(true)}
          >
            <Text style={styles.retryText}>Try again</Text>
          </TouchableOpacity>
        </View>
      );
    }

    return (
      <>
        {/* ROUND SELECTOR */}
        <View style={[styles.roundTabs, isDark && darkStyles.card]}>
          {ROUNDS.map((roundNumber) => {
            const selected = roundNumber === round;
            const date = formatDate(detail.progress.rounds[roundNumber]?.date ?? null);

            return (
              <TouchableOpacity
                key={roundNumber}
                style={[
                  styles.roundTab,
                  selected && styles.roundTabSelected,
                  selected && isDark && darkStyles.roundTabSelected,
                ]}
                activeOpacity={0.8}
                accessibilityRole="tab"
                accessibilityState={{ selected }}
                onPress={() => setSelectedRound(roundNumber)}
              >
                <Text
                  style={[
                    styles.roundTabTitle,
                    isDark && darkStyles.mutedText,
                    selected && styles.roundTabTitleSelected,
                    selected && isDark && darkStyles.text,
                  ]}
                >
                  {ROUND_LABELS[roundNumber]}
                </Text>
                <Text style={[styles.roundTabDate, isDark && darkStyles.mutedText]} numberOfLines={1}>
                  {date ?? "Not scheduled"}
                </Text>
              </TouchableOpacity>
            );
          })}
        </View>

        {/* SCORES */}
        <View style={styles.scoreRow}>
          {[
            { label: "Raw score", value: roundData?.raw_score },
            { label: "Scaled score", value: roundData?.scaled_score },
            {
              label: "Skills assessed",
              value: `${roundData?.assessed_items ?? 0}/${detail.items.length}`,
            },
          ].map((score) => (
            <View key={score.label} style={[styles.scoreCard, isDark && darkStyles.card]}>
              <Text style={[styles.scoreValue, isDark && darkStyles.text]}>
                {score.value ?? "—"}
              </Text>
              <Text style={[styles.scoreLabel, isDark && darkStyles.mutedText]}>{score.label}</Text>
            </View>
          ))}
        </View>

        {/* INTERPRETATION (Technical Manual Table 6) + link to the full explanation */}
        {roundData?.interpretation ? (
          <TouchableOpacity
            style={[styles.interpretationCard, isDark && darkStyles.card]}
            activeOpacity={0.8}
            accessibilityRole="button"
            accessibilityLabel={`${roundData.interpretation}. Why this result?`}
            onPress={() => navigation.navigate("Insights", { student })}
          >
            <Ionicons name="analytics-outline" size={20} color={iconColor} />
            <View style={styles.interpretationTextContainer}>
              <Text style={[styles.interpretationText, isDark && darkStyles.text]}>{roundData.interpretation}</Text>
              <Text style={[styles.interpretationLink, isDark && darkStyles.accentText]}>Why this result? ›</Text>
            </View>
          </TouchableOpacity>
        ) : null}

        {!hasResults ? (
          /* EMPTY STATE */
          <View style={[styles.emptyCard, isDark && darkStyles.card]}>
            <View style={[styles.emptyIcon, isDark && darkStyles.surfaceMuted]}>
              <Ionicons name="document-text-outline" size={30} color={iconColor} />
            </View>
            <Text style={[styles.emptyTitle, isDark && darkStyles.text]}>No results yet</Text>
            <Text style={[styles.emptyText, isDark && darkStyles.mutedText]}>
              {detail.items.length === 0
                ? `The ${domainLabel} checklist is not available yet.`
                : `Your child's teacher hasn't recorded ${domainLabel} results for the ${ROUND_LABELS[round].replace(" Eval", " evaluation")} yet. Pull down to check again later.`}
            </Text>
          </View>
        ) : (
          /* CHECKLIST ITEMS */
          <View style={[styles.itemsCard, isDark && darkStyles.card]}>
            <View style={styles.legend}>
              <Text style={[styles.legendText, isDark && darkStyles.mutedText]}>✓ Present</Text>
              <Text style={[styles.legendText, isDark && darkStyles.mutedText]}>— Not present</Text>
            </View>

            {detail.items.map((item, index) => {
              const result = item.results[round] ?? null;

              return (
                <View key={item.id}>
                  {index > 0 && <View style={[styles.divider, isDark && darkStyles.divider]} />}
                  <View style={styles.itemRow}>
                    <Text style={[styles.itemNumber, isDark && darkStyles.mutedText]}>
                      {item.item_number ?? index + 1}
                    </Text>
                    <View style={styles.itemTextContainer}>
                      <Text style={[styles.itemActivity, isDark && darkStyles.text]}>{item.activity}</Text>
                      {result?.comments ? (
                        <Text style={[styles.itemComment, isDark && darkStyles.mutedText]}>
                          Teacher: {result.comments}
                        </Text>
                      ) : null}
                    </View>
                    {renderMark(result)}
                  </View>
                </View>
              );
            })}
          </View>
        )}

        {/* SKILLS TO PRACTISE: items marked — in the latest evaluation of this area */}
        {detail.skills_to_practice && detail.skills_to_practice.items.length > 0 && (
          <View style={[styles.itemsCard, styles.skillsCard, isDark && darkStyles.card]}>
            <Text style={[styles.skillsTitle, isDark && darkStyles.text]}>Things to try at home</Text>
            <Text style={[styles.skillsSubtitle, isDark && darkStyles.mutedText]}>
              Not yet observed in the{" "}
              {ROUND_LABELS[detail.skills_to_practice.round].replace(" Eval", " evaluation")}. Everyday play helps
              practise these.
            </Text>
            {detail.skills_to_practice.items.map((item) => (
              <View key={item.id} style={styles.skillRow}>
                <Ionicons name="sparkles-outline" size={15} color={iconColor} />
                <Text style={[styles.itemActivity, styles.skillText, isDark && darkStyles.text]}>{item.activity}</Text>
              </View>
            ))}
          </View>
        )}
      </>
    );
  };

  return (
    <View style={[styles.screen, isDark && darkStyles.container]}>
      <StatusBar
        backgroundColor={isDark ? DarkColors.surface : "#FFFFFF"}
        barStyle={isDark ? "light-content" : "dark-content"}
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
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.75}
          accessibilityRole="button"
          accessibilityLabel="Go back"
        >
          <Ionicons name="chevron-back" size={25} color={isDark ? DarkColors.text : "#284F47"} />
        </TouchableOpacity>

        <Text
          style={[styles.headerTitle, compact && styles.compactHeaderTitle, isDark && darkStyles.text]}
          numberOfLines={1}
          adjustsFontSizeToFit
        >
          {domainMeta?.title ?? "Development Area"}
        </Text>

        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.content,
          {
            paddingHorizontal: small ? 16 : 22,
            paddingTop: compact ? 12 : 18,
          },
        ]}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => void reload(false)}
            tintColor="#0BA8BD"
            colors={["#0BA8BD"]}
          />
        }
      >
        {/* DOMAIN SUMMARY */}
        {domainMeta ? (
          <View style={[styles.summaryCard, { backgroundColor: domainMeta.color }]}>
            <View style={styles.summaryIcon}>
              <Ionicons name={domainMeta.icon} size={compact ? 24 : 28} color="#365C55" />
            </View>
            <View style={styles.summaryInfo}>
              <Text style={styles.summaryLabel}>{domainLabel.toUpperCase()}</Text>
              <Text style={styles.summaryTitle}>{domainMeta.title}</Text>
              <Text style={styles.summaryUpdated}>
                {lastUpdated ? `Last updated ${lastUpdated}` : "No updates yet"}
              </Text>
            </View>
          </View>
        ) : null}

        {renderBody()}
      </ScrollView>

      {/* FIXED BOTTOM NAVIGATION */}
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
          onPress={() => navigation.navigate("Dashboard", { student })}
        >
          <Ionicons name="home-outline" size={23} color={mutedIconColor} />
          <Text style={[styles.navText, isDark && darkStyles.mutedText]}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => navigation.navigate("ECCDChecklist", { student })}
        >
          <Ionicons name="clipboard" size={23} color={isDark ? DarkColors.accent : "#337D73"} />
          <Text style={[styles.navText, styles.activeNavText, isDark && darkStyles.activeNavText]}>
            ECCD Checklist
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() => navigation.navigate("Profile", { student })}
        >
          <Ionicons name="person-outline" size={23} color={mutedIconColor} />
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
    minHeight: 86,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: "#F0F2F0",
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
    fontSize: 20,
    fontWeight: "800",
    color: "#284F47",
  },

  compactHeaderTitle: {
    fontSize: 18,
  },

  headerSpacer: {
    width: 40,
  },

  scrollView: {
    flex: 1,
  },

  content: {
    flexGrow: 1,
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
    paddingBottom: 24,
  },

  summaryCard: {
    flexDirection: "row",
    alignItems: "center",
    borderRadius: 18,
    padding: 14,
    marginBottom: 16,
  },

  summaryIcon: {
    width: 52,
    height: 52,
    borderRadius: 16,
    backgroundColor: "rgba(255,255,255,0.65)",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 13,
  },

  summaryInfo: {
    flex: 1,
  },

  summaryLabel: {
    fontSize: 10,
    fontWeight: "800",
    letterSpacing: 1,
    color: "#496E65",
  },

  summaryTitle: {
    fontSize: 17,
    fontWeight: "800",
    color: "#294E46",
    marginTop: 3,
  },

  summaryUpdated: {
    fontSize: 11,
    color: "#58736B",
    marginTop: 4,
  },

  roundTabs: {
    flexDirection: "row",
    backgroundColor: "#F4F8F6",
    borderRadius: 14,
    padding: 4,
    marginBottom: 14,
  },

  roundTab: {
    flex: 1,
    alignItems: "center",
    borderRadius: 11,
    paddingVertical: 8,
    paddingHorizontal: 4,
  },

  roundTabSelected: {
    backgroundColor: "#FFFFFF",
  },

  roundTabTitle: {
    fontSize: 13,
    fontWeight: "700",
    color: "#788781",
  },

  roundTabTitleSelected: {
    color: "#284F47",
  },

  roundTabDate: {
    fontSize: 10,
    color: "#8A9692",
    marginTop: 2,
  },

  scoreRow: {
    flexDirection: "row",
    gap: 10,
    marginBottom: 14,
  },

  scoreCard: {
    flex: 1,
    alignItems: "center",
    borderRadius: 14,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    backgroundColor: "#FFFFFF",
    paddingVertical: 12,
    paddingHorizontal: 4,
  },

  scoreValue: {
    fontSize: 20,
    fontWeight: "800",
    color: "#284F47",
  },

  scoreLabel: {
    fontSize: 10,
    color: "#788781",
    marginTop: 3,
    textAlign: "center",
  },

  itemsCard: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    backgroundColor: "#FFFFFF",
    paddingHorizontal: 14,
    paddingVertical: 6,
  },
  interpretationCard: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    backgroundColor: "#FFFFFF",
    paddingHorizontal: 14,
    paddingVertical: 12,
    marginBottom: 12,
  },
  interpretationTextContainer: {
    flex: 1,
  },
  interpretationText: {
    fontSize: 14,
    fontWeight: "700",
    color: "#294E46",
  },
  interpretationLink: {
    fontSize: 12,
    fontWeight: "600",
    color: "#0B8C9E",
    marginTop: 2,
  },
  skillsCard: {
    marginTop: 12,
    paddingVertical: 14,
  },
  skillsTitle: {
    fontSize: 15,
    fontWeight: "700",
    color: "#294E46",
  },
  skillsSubtitle: {
    fontSize: 12,
    lineHeight: 18,
    color: "#788781",
    marginTop: 3,
    marginBottom: 4,
  },
  skillRow: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 9,
    marginTop: 8,
  },
  skillText: {
    flex: 1,
  },

  legend: {
    flexDirection: "row",
    justifyContent: "flex-end",
    gap: 14,
    paddingVertical: 8,
  },

  legendText: {
    fontSize: 11,
    color: "#788781",
  },

  itemRow: {
    flexDirection: "row",
    alignItems: "flex-start",
    paddingVertical: 12,
  },

  itemNumber: {
    width: 26,
    fontSize: 12,
    fontWeight: "700",
    color: "#8A9692",
    paddingTop: 2,
  },

  itemTextContainer: {
    flex: 1,
    paddingRight: 10,
  },

  itemActivity: {
    fontSize: 13,
    lineHeight: 19,
    color: "#294E46",
  },

  itemComment: {
    fontSize: 12,
    lineHeight: 17,
    fontStyle: "italic",
    color: "#58736B",
    marginTop: 4,
  },

  mark: {
    width: 30,
    height: 30,
    borderRadius: 15,
    alignItems: "center",
    justifyContent: "center",
  },

  markSymbol: {
    fontSize: 15,
    fontWeight: "800",
  },

  markPresent: {
    backgroundColor: "#A8D8B9",
  },

  markPresentText: {
    color: "#1F5C45",
  },

  markAbsent: {
    backgroundColor: "#EEF1F0",
  },

  markAbsentText: {
    color: "#6B7A75",
  },

  markPending: {
    borderWidth: 1,
    borderColor: "#DCE3E0",
    borderStyle: "dashed",
  },

  divider: {
    height: 1,
    backgroundColor: "#EDF0EF",
  },

  emptyCard: {
    alignItems: "center",
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    backgroundColor: "#FFFFFF",
    paddingHorizontal: 22,
    paddingVertical: 28,
  },

  emptyIcon: {
    width: 60,
    height: 60,
    borderRadius: 18,
    backgroundColor: "#EEF5F1",
    alignItems: "center",
    justifyContent: "center",
    marginBottom: 12,
  },

  emptyTitle: {
    fontSize: 16,
    fontWeight: "800",
    color: "#284F47",
    marginBottom: 6,
  },

  emptyText: {
    fontSize: 13,
    lineHeight: 19,
    color: "#788781",
    textAlign: "center",
  },

  stateBox: {
    alignItems: "center",
    justifyContent: "center",
    paddingVertical: 40,
    paddingHorizontal: 24,
    gap: 12,
  },

  stateText: {
    fontSize: 13,
    color: "#788781",
  },

  errorText: {
    color: "#B42318",
    fontSize: 13,
    lineHeight: 19,
    textAlign: "center",
  },

  retryButton: {
    backgroundColor: "#0BA8BD",
    borderRadius: 20,
    paddingHorizontal: 22,
    paddingVertical: 10,
  },

  retryText: {
    color: "#FFFFFF",
    fontSize: 13,
    fontWeight: "700",
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
  container: {
    backgroundColor: DarkColors.background,
  },

  header: {
    backgroundColor: DarkColors.surface,
    borderBottomColor: DarkColors.border,
  },

  text: {
    color: DarkColors.text,
  },

  accentText: {
    color: DarkColors.accent,
  },

  mutedText: {
    color: DarkColors.textMuted,
  },

  danger: {
    color: DarkColors.danger,
  },

  card: {
    backgroundColor: DarkColors.surface,
    borderColor: DarkColors.border,
  },

  surfaceMuted: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  roundTabSelected: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  markAbsent: {
    backgroundColor: DarkColors.surfaceMuted,
  },

  markPending: {
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
