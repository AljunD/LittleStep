import React, { useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  StatusBar,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

type Status = "passed" | "failed" | "notYet";

type ChecklistItem = {
  id: string;
  title: string;
  status: Status;
};

type Evaluation = {
  date: string | null;
  items: ChecklistItem[];
};

const initialItems: ChecklistItem[] = [
  { id: "gm-01", title: "Maintains balance while standing", status: "passed" },
  { id: "gm-02", title: "Walks independently", status: "passed" },
  { id: "gm-03", title: "Runs with coordination", status: "passed" },
  { id: "gm-04", title: "Jumps with both feet", status: "passed" },
  { id: "gm-05", title: "Climbs steps with support", status: "passed" },
  { id: "gm-06", title: "Kicks a stationary ball", status: "passed" },
  { id: "gm-07", title: "Throws a ball forward", status: "failed" },
  { id: "gm-08", title: "Stands on one foot briefly", status: "notYet" },
];

const createEvaluation = (items: ChecklistItem[]): Evaluation => ({
  date: null,
  items: items.map((item) => ({ ...item })),
});

const initialEvaluations: Evaluation[] = [
  createEvaluation(initialItems),
  createEvaluation(
    initialItems.map((item) => ({ ...item, status: "notYet" as Status }))
  ),
  createEvaluation(
    initialItems.map((item) => ({ ...item, status: "notYet" as Status }))
  ),
];

const evaluationLabels = [
  "1st Evaluation",
  "2nd Evaluation",
  "3rd Evaluation",
];

const statusConfig = {
  passed: {
    label: "Passed",
    icon: "checkmark-circle" as const,
    color: "#16A765",
    background: "#D9F8E7",
  },
  failed: {
    label: "Failed",
    icon: "close-circle" as const,
    color: "#E64B4B",
    background: "#FFE0E0",
  },
  notYet: {
    label: "Not Yet",
    icon: "time-outline" as const,
    color: "#A2A2A2",
    background: "#E7E7E7",
  },
};

export default function GrossMotorScreen({
  navigation,
  route,
}: any) {
  const insets = useSafeAreaInsets();
  const student = route?.params?.student;

  const [selectedEvaluation, setSelectedEvaluation] = useState(0);
  const [evaluations, setEvaluations] =
    useState<Evaluation[]>(initialEvaluations);

  const currentEvaluation = evaluations[selectedEvaluation];
  const items = currentEvaluation.items;

  const passed = items.filter((item) => item.status === "passed").length;
  const failed = items.filter((item) => item.status === "failed").length;
  const notYet = items.filter((item) => item.status === "notYet").length;
  const total = items.length;

  // Tap a row to cycle its status for prototype testing.
  const cycleStatus = (itemId: string) => {
    const order: Status[] = ["passed", "failed", "notYet"];

    setEvaluations((previous) =>
      previous.map((evaluation, index) => {
        if (index !== selectedEvaluation) {
          return evaluation;
        }

        return {
          ...evaluation,
          items: evaluation.items.map((item) => {
            if (item.id !== itemId) return item;

            const nextIndex =
              (order.indexOf(item.status) + 1) % order.length;

            return { ...item, status: order[nextIndex] };
          }),
        };
      })
    );
  };

  const goTo = (screen: string) => {
    navigation.navigate(screen, { student });
  };

  return (
    <View style={styles.screen}>
      <StatusBar backgroundColor="#FFFFFF" barStyle="dark-content" />

      {/* Header */}
      <View
        style={[
          styles.header,
          { paddingTop: Math.max(insets.top, 8) },
        ]}
      >
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
        >
          <Ionicons name="chevron-back" size={26} color="#202020" />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>Gross Motor</Text>

        <View style={styles.headerSpacer} />
      </View>

      {/* Evaluation tabs */}
      <View style={styles.tabsWrapper}>
        <View style={styles.tabsContainer}>
          {evaluationLabels.map((label, index) => {
            const active = selectedEvaluation === index;

            return (
              <TouchableOpacity
                key={label}
                style={[styles.tab, active && styles.activeTab]}
                onPress={() => setSelectedEvaluation(index)}
                activeOpacity={0.8}
              >
                <Text
                  style={[
                    styles.tabText,
                    active && styles.activeTabText,
                  ]}
                  numberOfLines={1}
                  adjustsFontSizeToFit
                >
                  {label}
                </Text>
              </TouchableOpacity>
            );
          })}
        </View>
      </View>

      {/* Checklist */}
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.checklistCard}>
          {items.map((item, index) => {
            const config = statusConfig[item.status];

            return (
              <TouchableOpacity
                key={item.id}
                style={[
                  styles.checklistRow,
                  index === items.length - 1 && styles.lastRow,
                ]}
                onPress={() => cycleStatus(item.id)}
                activeOpacity={0.7}
              >
                <Text style={styles.itemTitle} numberOfLines={2}>
                  {item.title}
                </Text>

                <View
                  style={[
                    styles.statusBadge,
                    { backgroundColor: config.background },
                  ]}
                >
                  <Ionicons
                    name={config.icon}
                    size={12}
                    color={config.color}
                  />
                  <Text style={[styles.statusText, { color: config.color }]}>
                    {config.label}
                  </Text>
                </View>

                <Ionicons
                  name="chevron-forward"
                  size={17}
                  color="#C5C5C5"
                  style={styles.rowChevron}
                />
              </TouchableOpacity>
            );
          })}
        </View>

        <Text style={styles.hint}>
          Tap an item to change its status. Sample data is for UI testing.
        </Text>
      </ScrollView>

      {/* Summary */}
      <View style={styles.summaryWrapper}>
        <View style={styles.summaryBar}>
          <Text style={styles.summaryText}>{passed} Passed</Text>
          <Text style={styles.summaryText}>{failed} Failed</Text>
          <Text style={styles.summaryText}>{notYet} Not Yet</Text>
          <Text style={styles.summaryText}>
            {passed} / {total}
          </Text>
        </View>
      </View>

      {/* Bottom navigation */}
      <View
        style={[
          styles.bottomNav,
          { paddingBottom: Math.max(insets.bottom, 6) },
        ]}
      >
        <TouchableOpacity
          style={styles.navItem}
          onPress={() => goTo("Dashboard")}
          activeOpacity={0.7}
        >
          <Ionicons name="home-outline" size={22} color="#A7A7A7" />
          <Text style={styles.navText}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          onPress={() => goTo("ECCDChecklist")}
          activeOpacity={0.7}
        >
          <Ionicons name="stats-chart" size={22} color="#00AFC8" />
          <Text style={styles.activeNavText}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          onPress={() => goTo("Profile")}
          activeOpacity={0.7}
        >
          <Ionicons name="person-outline" size={22} color="#A7A7A7" />
          <Text style={styles.navText}>Profile</Text>
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
    height: 69,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 15,
    borderBottomWidth: 1,
    borderBottomColor: "#D9D9D9",
  },
  backButton: {
    width: 38,
    height: 42,
    justifyContent: "center",
    alignItems: "flex-start",
  },
  headerTitle: {
    flex: 1,
    textAlign: "center",
    fontSize: 15,
    fontWeight: "800",
    color: "#111111",
  },
  headerSpacer: {
    width: 38,
  },
  tabsWrapper: {
    paddingHorizontal: 11,
    paddingTop: 15,
    paddingBottom: 9,
  },
  tabsContainer: {
    height: 31,
    flexDirection: "row",
    borderRadius: 12,
    backgroundColor: "#E5E5E5",
    elevation: 2,
    shadowColor: "#000000",
    shadowOpacity: 0.12,
    shadowRadius: 3,
    shadowOffset: { width: 0, height: 2 },
  },
  tab: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 2,
    borderRadius: 11,
  },
  activeTab: {
    backgroundColor: "#FFA6A3",
  },
  tabText: {
    fontSize: 9,
    fontWeight: "600",
    color: "#888888",
  },
  activeTabText: {
    color: "#FFFFFF",
    fontWeight: "800",
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 11,
    paddingTop: 12,
    paddingBottom: 10,
    flexGrow: 1,
  },
  checklistCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 12,
    paddingHorizontal: 10,
    paddingTop: 48,
    paddingBottom: 70,
    borderWidth: 1,
    borderColor: "#EAEAEA",
    elevation: 2,
    shadowColor: "#000000",
    shadowOpacity: 0.12,
    shadowRadius: 3,
    shadowOffset: { width: 0, height: 2 },
  },
  checklistRow: {
    minHeight: 30,
    flexDirection: "row",
    alignItems: "center",
    borderBottomWidth: 1,
    borderBottomColor: "#D5D5D5",
    paddingVertical: 4,
  },
  lastRow: {
    borderBottomWidth: 0,
  },
  itemTitle: {
    flex: 1,
    fontSize: 9,
    lineHeight: 13,
    color: "#222222",
    paddingRight: 4,
  },
  statusBadge: {
    minWidth: 55,
    borderRadius: 12,
    paddingHorizontal: 6,
    paddingVertical: 4,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 4,
  },
  statusText: {
    fontSize: 8,
    fontWeight: "700",
  },
  rowChevron: {
    marginLeft: 8,
  },
  hint: {
    fontSize: 10,
    color: "#888888",
    marginTop: 10,
    textAlign: "center",
  },
  summaryWrapper: {
    paddingHorizontal: 11,
    paddingTop: 4,
    paddingBottom: 11,
  },
  summaryBar: {
    minHeight: 36,
    backgroundColor: "#FFA6A3",
    borderRadius: 10,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-around",
    paddingHorizontal: 5,
    elevation: 2,
    shadowColor: "#000000",
    shadowOpacity: 0.15,
    shadowRadius: 3,
    shadowOffset: { width: 0, height: 2 },
  },
  summaryText: {
    color: "#FFFFFF",
    fontSize: 8,
    fontWeight: "800",
  },
  bottomNav: {
    minHeight: 55,
    borderTopWidth: 1,
    borderTopColor: "#E7E7E7",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-around",
    paddingTop: 5,
    backgroundColor: "#FFFFFF",
  },
  navItem: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    gap: 2,
  },
  navText: {
    fontSize: 8,
    color: "#A7A7A7",
  },
  activeNavText: {
    fontSize: 8,
    color: "#00AFC8",
    fontWeight: "700",
  },
});