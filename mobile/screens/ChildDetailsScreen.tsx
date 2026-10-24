
import React, { useCallback } from "react";
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
import { getGuardianChild, GuardianApiError } from "../services/guardianApi";
import { formatDate, fullName } from "../services/format";

const ChildDetailsScreen = ({ navigation, route }: any) => {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();
  const studentId: unknown = route?.params?.student?.id;

  const loadChild = useCallback(
    () => typeof studentId === "number"
      ? getGuardianChild(studentId)
      : Promise.reject(new GuardianApiError("No child was selected.")),
    [studentId],
  );
  const childResource = useApiResource(loadChild);
  const child = childResource.data;

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

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Child Profile</Text>
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
            refreshing={childResource.refreshing}
            onRefresh={childResource.refresh}
            tintColor="#0BA8BD"
            colors={["#0BA8BD"]}
          />
        }
      >
        {childResource.loading && !child ? (
          <LoadingState message="Loading child details..." />
        ) : !child ? (
          <ErrorState
            message={childResource.error ?? "Child details could not be loaded."}
            onRetry={childResource.retry}
          />
        ) : (
          <>
            <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>STUDENT INFORMATION</Text>

            <ReadOnlyField label="Full Name" value={fullName(child)} />

            <View style={styles.row}>
              <View style={styles.halfField}>
                <ReadOnlyField label="Sex" value={child.sex} />
              </View>
              <View style={styles.halfField}>
                <ReadOnlyField label="Handedness" value={child.handedness} />
              </View>
            </View>

            <ReadOnlyField label="Birthday" value={formatDate(child.date_of_birth) ?? child.date_of_birth} />
            <ReadOnlyField label="Address" value={child.address} />

            <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>SCHOOL INFORMATION</Text>

            <ReadOnlyField label="Currently Studying" value={child.is_studying ? "Yes" : "No"} />
            <ReadOnlyField label="School Name" value={child.school_name} />

            <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>FATHER INFORMATION</Text>

            <ReadOnlyField label="Name" value={child.fathers_name} />
            <View style={styles.row}>
              <View style={styles.halfField}>
                <ReadOnlyField label="Age" value={child.fathers_age} />
              </View>
              <View style={styles.halfField}>
                <ReadOnlyField label="Occupation" value={child.fathers_occupation} />
              </View>
            </View>
            <ReadOnlyField label="Educational Attainment" value={child.fathers_education} />

            <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>MOTHER INFORMATION</Text>

            <ReadOnlyField label="Name" value={child.mothers_name} />
            <View style={styles.row}>
              <View style={styles.halfField}>
                <ReadOnlyField label="Age" value={child.mothers_age} />
              </View>
              <View style={styles.halfField}>
                <ReadOnlyField label="Occupation" value={child.mothers_occupation} />
              </View>
            </View>
            <ReadOnlyField label="Educational Attainment" value={child.mothers_education} />

            <Text style={[styles.sectionTitle, isDark && darkStyles.text]}>SIBLINGS</Text>

            <View style={styles.row}>
              <View style={styles.halfField}>
                <ReadOnlyField label="Number of Siblings" value={child.number_of_siblings} />
              </View>
              <View style={styles.halfField}>
                <ReadOnlyField label="Birth Order" value={child.birth_order} />
              </View>
            </View>
          </>
        )}
      </ScrollView>
    </View>
  );
};

export default ChildDetailsScreen;

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
