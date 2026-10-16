
import React from "react";
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

const GuardianDetailsScreen = ({ navigation, route }: any) => {
  const insets = useSafeAreaInsets();
  const student = route?.params?.student ?? {};

  const guardianName =
    student.guardianName ||
    student.parentName ||
    "Juan dela Cruz";

  const Field = ({ label, value }: { label: string; value: string }) => (
    <View style={styles.fieldContainer}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        style={styles.input}
        value={String(value ?? "")}
        editable={false}
        selectTextOnFocus={false}
      />
    </View>
  );

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

      {/* Page Header */}
      <View style={[styles.header, { paddingTop: insets.top + 10 }]}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="chevron-back" size={25} color="#24483D" />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>Guardian Profile</Text>
        <View style={styles.headerSpacer} />
      </View>

      {/* Guardian Information */}
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.content,
          { paddingBottom: insets.bottom + 24 },
        ]}
        showsVerticalScrollIndicator={false}
      >
        <Text style={styles.sectionTitle}>Guardian Information</Text>

        <Field label="Full Name" value={guardianName} />

        <Field
          label="Email Address"
          value={student.guardianEmail || "juan.delacruz@gmail.com"}
        />

        <Field
          label="Contact Number"
          value={student.guardianContact || "63 917 123 4567"}
        />

        <Field
          label="Address"
          value={
            student.guardianAddress ||
            student.address ||
            "246 Rizal Avenue, Barangay San Isidro, Taytay, Rizal"
          }
        />

        <Field
          label="Relationship to Child"
          value={student.relationship || "Uncle"}
        />
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
  fieldContainer: {
    flex: 1,
    marginBottom: 16,
  },
  label: {
    fontSize: 13,
    fontWeight: "600",
    color: "#53675D",
    marginBottom: 7,
  },
  input: {
    minHeight: 44,
    borderWidth: 1,
    borderColor: "#DCE7E1",
    borderRadius: 9,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: "#263B32",
    backgroundColor: "#FAFCFB",
  },
});
