
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

const ChildDetailsScreen = ({ navigation, route }: any) => {
  const insets = useSafeAreaInsets();
  const student = route?.params?.student ?? {};

  const childName =
    student.fullName ||
    student.name ||
    [student.lastName, student.firstName].filter(Boolean).join(", ") ||
    "Dela Cruz, Juan";

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

        <Text style={styles.headerTitle}>Child Profile</Text>
        <View style={styles.headerSpacer} />
      </View>

      {/* Student Information */}
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.content,
          { paddingBottom: insets.bottom + 24 },
        ]}
        showsVerticalScrollIndicator={false}
      >
        <Text style={styles.sectionTitle}>Student Information</Text>

        <Field label="Full Name" value={childName} />

        <View style={styles.row}>
          <View style={styles.halfField}>
            <Field
              label="Sex"
              value={student.sex || student.gender || "Male"}
            />
          </View>
          <View style={styles.halfField}>
            <Field
              label="Handedness"
              value={student.handedness || "Right-handed"}
            />
          </View>
        </View>

        <Field
          label="Address"
          value={
            student.address ||
            "246 Rizal Avenue, Barangay San Isidro, Taytay, Rizal"
          }
        />

        <Field
          label="Birthday"
          value={student.birthday || "January 1, 2024"}
        />

        <Text style={styles.sectionTitle}>School Information</Text>

        <Field
          label="School Name"
          value={
            student.schoolName || "Barangay Balite Day Care Center"
          }
        />

        <Text style={styles.sectionTitle}>Family Information</Text>

        <Field
          label="Father's Name"
          value={student.fatherName || "Samuel Alejandro"}
        />

        <Field
          label="Mother's Name"
          value={student.motherName || "Amelia Dela Cruz"}
        />

        <View style={styles.row}>
          <View style={styles.halfField}>
            <Field
              label="Number of Siblings"
              value={String(student.siblings ?? 3)}
            />
          </View>
          <View style={styles.halfField}>
            <Field
              label="Birth Order"
              value={String(student.birthOrder ?? 1)}
            />
          </View>
        </View>
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
  row: {
    flexDirection: "row",
    gap: 14,
  },
  halfField: {
    flex: 1,
  },
});
