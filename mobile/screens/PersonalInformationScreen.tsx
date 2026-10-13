import React from "react";
import {
  View,
  Text,
  Image,
  StyleSheet,
  ScrollView,
  StatusBar,
  useWindowDimensions,
  TouchableOpacity,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

export default function PersonalInformationScreen({
  navigation,
  route,
}: any) {
  const { width, height } = useWindowDimensions();
  const insets = useSafeAreaInsets();

  const compact = height < 760;
  const small = width < 360;

  const student = route?.params?.student ?? {};

const studentName =
  student.name ||
  [student.firstName, student.lastName].filter(Boolean).join(" ") ||
  "Sofia Mae Reyes";

const studentImage =
  student.image ?? require("../assets/images/juan.jpg");

const information = [
  {
    title: "Student Information",
    icon: "person-outline" as const,
    fields: [
      {
        label: "Full Name",
        value: student.fullName || student.name || "Sofia Mae Reyes",
      },
      {
        label: "Sex",
        value: student.sex || "Female",
      },
      {
        label: "Handedness",
        value: student.handedness || "Right-handed",
      },
      {
        label: "Address",
        value: student.address || "Quezon City, Metro Manila",
      },
      {
        label: "Birthday",
        value: student.birthday || "March 15, 2022",
      },
    ],
  },
  {
    title: "School Information",
    icon: "school-outline" as const,
    fields: [
      {
        label: "School Name",
        value: student.schoolName || "Little Steps Learning Center",
      },
    ],
  },
  {
    title: "Family Information",
    icon: "people-outline" as const,
    fields: [
      {
        label: "Father's Name",
        value: student.fathersName || "Mark Anthony Reyes",
      },
      {
        label: "Mother's Name",
        value: student.mothersName || "Angela Marie Reyes",
      },
      {
        label: "Number of Siblings",
        value: student.siblings ?? 2,
      },
      {
        label: "Birth Order",
        value: student.birthOrder || "1",
      },
    ],
  },
];

  return (
    <View
      style={[
        styles.container,
        { paddingTop: insets.top },
      ]}
    >
      <StatusBar
        barStyle="dark-content"
        backgroundColor="#FFFFFF"
      />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
          accessibilityLabel="Go back"
        >
          <Ionicons
            name="chevron-back"
            size={25}
            color="#294E46"
          />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>
          Personal Information
        </Text>

        <View style={styles.headerSpacer} />
      </View>

      {/* Information Content */}
      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={[
          styles.scrollContent,
          {
            paddingHorizontal: small ? 16 : 22,
            paddingBottom: Math.max(insets.bottom, 20) + 20,
          },
        ]}
      >
        {/* Student Profile */}
        <View
          style={[
            styles.profileCard,
            { padding: compact ? 16 : 20 },
          ]}
        >
          <Image
            source={studentImage}
            style={styles.avatar}
          />

          <View style={styles.profileText}>
            <Text style={styles.profileName}>
              {studentName}
            </Text>

            <Text style={styles.profileSubtitle}>
              Student Profile
            </Text>
          </View>
        </View>
        {/* Information Sections */}
        {information.map((section) => (
          <View
            style={styles.section}
            key={section.title}
          >
            <View style={styles.sectionHeading}>
              <View style={styles.sectionIcon}>
                <Ionicons
                  name={section.icon}
                  size={19}
                  color="#34796A"
                />
              </View>

              <Text style={styles.sectionTitle}>
                {section.title}
              </Text>
            </View>

            {section.fields.map((field) => (
              <View
                style={styles.field}
                key={field.label}
              >
                <Text style={styles.fieldLabel}>
                  {field.label}
                </Text>

                <View style={styles.readOnlyInput}>
                  <Text style={styles.readOnlyText}>
                    {String(field.value || "Not provided")}
                  </Text>
                </View>
              </View>
            ))}
          </View>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  header: {
    minHeight: 62,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    backgroundColor: "#FFFFFF",
    borderBottomWidth: 1,
    borderBottomColor: "#EEF2EF",
  },

  backButton: {
    width: 40,
    height: 42,
    alignItems: "center",
    justifyContent: "center",
  },

  headerTitle: {
    flex: 1,
    textAlign: "center",
    fontSize: 18,
    fontWeight: "700",
    color: "#294E46",
  },

  headerSpacer: {
    width: 40,
  },

  scrollContent: {
    paddingTop: 20,
    gap: 18,
  },

  profileCard: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: "#F2F8F5",
    borderRadius: 18,
    gap: 15,
  },

  avatar: {
    width: 76,
    height: 76,
    borderRadius: 38,
    backgroundColor: "#E1EBE5",
  },

  profileText: {
    flex: 1,
    gap: 5,
  },

  profileName: {
    fontSize: 17,
    fontWeight: "700",
    color: "#294E46",
  },

  profileSubtitle: {
    fontSize: 12,
    color: "#75867E",
  },
  section: {
    padding: 16,
    borderRadius: 16,
    backgroundColor: "#FFFFFF",
    borderWidth: 1,
    borderColor: "#E9EFEB",
    gap: 14,
  },

  sectionHeading: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    marginBottom: 2,
  },

  sectionIcon: {
    width: 34,
    height: 34,
    borderRadius: 11,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "#EAF4EF",
  },

  sectionTitle: {
    fontSize: 15,
    fontWeight: "700",
    color: "#294E46",
  },

  field: {
    gap: 7,
  },

  fieldLabel: {
    fontSize: 12,
    fontWeight: "600",
    color: "#75867E",
  },

  readOnlyInput: {
    minHeight: 43,
    borderWidth: 1,
    borderColor: "#E0E7E3",
    borderRadius: 11,
    paddingHorizontal: 11,
    paddingVertical: 10,
    justifyContent: "center",
    backgroundColor: "#F8FAF9",
  },

  readOnlyText: {
    fontSize: 13,
    lineHeight: 19,
    color: "#294E46",
  },
});