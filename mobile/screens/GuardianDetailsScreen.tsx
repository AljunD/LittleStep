
import React from "react";
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
  RefreshControl,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";
import { useApiResource } from "../hooks/use-api-resource";
import { ErrorState, LoadingState } from "../components/screen-state";
import { ReadOnlyField } from "../components/read-only-field";
import { getGuardianAccount } from "../services/guardianApi";
import { fullName } from "../services/format";

const GuardianDetailsScreen = ({ navigation }: any) => {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();
  const account = useApiResource(getGuardianAccount);
  const guardian = account.data;

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
      />

      <View style={[styles.header, isDark && darkStyles.header, { paddingTop: insets.top + 10 }]}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="chevron-back" size={25} color={isDark ? DarkColors.text : "#24483D"} />
        </TouchableOpacity>

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Guardian Profile</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.content,
          { paddingBottom: insets.bottom + 24 },
        ]}
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
        {account.loading && !guardian ? (
          <LoadingState message="Loading guardian details..." />
        ) : !guardian ? (
          <ErrorState message={account.error ?? "Guardian details could not be loaded."} onRetry={account.retry} />
        ) : (
          <>
            <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>GUARDIAN INFORMATION</Text>

            <ReadOnlyField label="Full Name" value={fullName(guardian)} />
            <ReadOnlyField label="Email Address" value={guardian.email} />
            <ReadOnlyField label="Contact Number" value={guardian.contact_number} />

            <View style={styles.row}>
              <View style={styles.halfField}>
                <ReadOnlyField label="Sex" value={guardian.sex} />
              </View>
              <View style={styles.halfField}>
                <ReadOnlyField label="Relationship to Child" value={guardian.relationship_to_child} />
              </View>
            </View>

            <ReadOnlyField label="Address" value={guardian.address} />
          </>
        )}
      </ScrollView>
    </View>
  );
};

export default GuardianDetailsScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },
  header: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 20,
    paddingBottom: 16,
    backgroundColor: "#FFFFFF",
    borderBottomWidth: 1,
    borderBottomColor: "#EDF1EE",
  },
  backButton: {
    width: 36,
    height: 36,
    justifyContent: "center",
    alignItems: "flex-start",
  },
  headerTitle: {
    flex: 1,
    textAlign: "center",
    fontSize: 18,
    fontWeight: "700",
    color: "#24483D",
  },
  headerSpacer: {
    width: 36,
  },
  scrollView: {
    flex: 1,
  },
  content: {
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
    paddingHorizontal: 24,
    paddingTop: 24,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: "700",
    color: "#24483D",
    marginBottom: 18,
    marginTop: 8,
  },
  row: {
    flexDirection: "row",
    gap: 14,
  },
  halfField: {
    flex: 1,
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
});
