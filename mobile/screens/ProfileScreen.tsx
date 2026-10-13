import React from "react";
import {
  View,
  Text,
  Image,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  ScrollView,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

const menuItems = [
  {
    title: "Personal Information",
    icon: "person-outline" as const,
    color: "#F9C96B",
  },
  {
    title: "Notification",
    icon: "notifications-outline" as const,
    color: "#A8D8B9",
  },
  {
    title: "Display",
    icon: "moon-outline" as const,
    color: "#263447",
  },
  {
    title: "Language",
    icon: "globe-outline" as const,
    color: "#B9C9F8",
  },
  {
    title: "Contact Us",
    icon: "call-outline" as const,
    color: "#F3B9C7",
  },
];

export default function ProfileScreen({
  navigation,
  route,
}: any) {
  const { width, height } = useWindowDimensions();
  const insets = useSafeAreaInsets();

  const compact = height < 760;
  const small = width < 360;

  const student = route?.params?.student ?? {
    name: "Juan Dela Cruz",
    firstName: "Juan",
    email: "juan.delacruz@gmail.com",
    image: require("../assets/images/juan.jpg"),
  };

  const studentName =
    student.name ??
    [student.firstName, student.lastName]
      .filter(Boolean)
      .join(" ") ??
    "Juan Dela Cruz";

  const studentImage =
    student.image ?? require("../assets/images/juan.jpg");

  return (
    <View style={styles.screen}>
      <StatusBar
        barStyle="dark-content"
        backgroundColor="#FFFFFF"
      />

      {/* FIXED HEADER */}
      <View
        style={[
          styles.header,
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
        >
          <Ionicons
            name="chevron-back"
            size={25}
            color="#284F47"
          />
        </TouchableOpacity>

        <Text
          style={[
            styles.headerTitle,
            compact && styles.compactHeaderTitle,
          ]}
        >
          Profile
        </Text>

        <View style={styles.headerSpacer} />
      </View>

      {/* PLAIN WHITE PROFILE CONTENT */}
      <View style={styles.background}>
        <ScrollView
          style={styles.scroll}
          contentContainerStyle={[
            styles.scrollContent,
            {
              paddingHorizontal: small ? 12 : 18,
              paddingTop: compact ? 10 : 16,
              paddingBottom: 18,
            },
          ]}
          showsVerticalScrollIndicator={false}
        >
          {/* PROFILE CARD */}
          <View
            style={[
              styles.profileCard,
              compact && styles.compactProfileCard,
            ]}
          >
            <Image
              source={studentImage}
              style={[
                styles.profileImage,
                {
                  width: Math.min(width * 0.22, compact ? 72 : 86),
                  height: Math.min(width * 0.22, compact ? 72 : 86),
                },
              ]}
              resizeMode="cover"
            />

            <View style={styles.profileDetails}>
              <Text
                style={[
                  styles.studentName,
                  compact && styles.compactStudentName,
                ]}
                numberOfLines={2}
                adjustsFontSizeToFit
              >
                {studentName}
              </Text>

              <Text
                style={styles.email}
                numberOfLines={2}
                adjustsFontSizeToFit
              >
                {student.email ?? "juan.delacruz@gmail.com"}
              </Text>
            </View>
          </View>

          {/* ACCOUNT SETTINGS */}
          <View style={styles.sectionHeading}>
            <Text
              style={[
                styles.sectionTitle,
                compact && styles.compactSectionTitle,
              ]}
            >
              Account Settings
            </Text>
          </View>

          <View style={styles.menu}>
            {menuItems.map((item) => {
              const isDisplay = item.title === "Display";

              return (
                <TouchableOpacity
                  key={item.title}
                  style={[
                    styles.menuItem,
                    compact && styles.compactMenuItem,
                  ]}
                  activeOpacity={0.8}
                  onPress={() => {
                      if (item.title === "Personal Information") {
                      navigation.navigate("PersonalInformation", {
                      student: student,
                  });
                  } else {
                    console.log("Selected:", item.title);
                  }
                  }}
                >
                  <View
                    style={[
                      styles.menuIcon,
                      { backgroundColor: item.color },
                    ]}
                  >
                    <Ionicons
                      name={item.icon}
                      size={21}
                      color={isDisplay ? "#FFFFFF" : "#365C55"}
                    />
                  </View>

                  <Text
                    style={[
                      styles.menuTitle,
                      compact && styles.compactMenuTitle,
                    ]}
                  >
                    {item.title}
                  </Text>

                  <Ionicons
                    name="chevron-forward"
                    size={20}
                    color="#8A9692"
                  />
                </TouchableOpacity>
              );
            })}
          </View>

          {/* STUDENT ACTIONS */}
          <View style={styles.sectionHeading}>
            <Text
              style={[
                styles.sectionTitle,
                compact && styles.compactSectionTitle,
              ]}
            >
              Student Account
            </Text>
          </View>

          <View style={styles.actions}>
            <TouchableOpacity
              style={styles.switchButton}
              activeOpacity={0.8}
              onPress={() =>
                navigation.navigate("SelectStudent")
              }
            >
              <Ionicons
                name="people-outline"
                size={21}
                color="#365C55"
              />

              <Text style={styles.switchText}>
                Switch Student
              </Text>

              <Ionicons
                name="chevron-forward"
                size={19}
                color="#8A9692"
              />
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.logoutButton}
              activeOpacity={0.8}
              onPress={() => navigation.navigate("Login")}
            >
              <Ionicons
                name="log-out-outline"
                size={21}
                color="#D94B4B"
              />

              <Text style={styles.logoutText}>
                Log Out
              </Text>

              <Ionicons
                name="chevron-forward"
                size={19}
                color="#D94B4B"
              />
            </TouchableOpacity>
          </View>
        </ScrollView>
      </View>

      {/* FIXED BOTTOM NAVIGATION */}
      <View
        style={[
          styles.bottomNav,
          {
            paddingBottom: Math.max(insets.bottom, 8),
          },
        ]}
      >
        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() =>
            navigation.navigate("Dashboard", { student })
          }
        >
          <Ionicons
            name="home-outline"
            size={23}
            color="#8A9692"
          />
          <Text style={styles.navText}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
          onPress={() =>
            navigation.navigate("ECCDChecklist", { student })
          }
        >
          <Ionicons
            name="clipboard-outline"
            size={23}
            color="#8A9692"
          />
          <Text style={styles.navText}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.7}
        >
          <Ionicons
            name="person"
            size={23}
            color="#337D73"
          />
          <Text style={[styles.navText, styles.activeNavText]}>
            Profile
          </Text>
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
    fontSize: 24,
    fontWeight: "800",
    color: "#284F47",
  },

  compactHeaderTitle: {
    fontSize: 20,
  },

  headerSpacer: {
    width: 40,
  },

  background: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  scroll: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  scrollContent: {
    flexGrow: 1,
    backgroundColor: "#FFFFFF",
  },

  profileCard: {
    minHeight: 118,
    backgroundColor: "#FFFFFF",
    borderRadius: 18,
    padding: 14,
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 20,
    shadowColor: "#31574F",
    shadowOpacity: 0.08,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },

  compactProfileCard: {
    minHeight: 94,
    padding: 10,
    marginBottom: 14,
  },

  profileImage: {
    borderRadius: 16,
    backgroundColor: "#E7EFEB",
    borderWidth: 2,
    borderColor: "#E9F4F0",
  },

  profileDetails: {
    flex: 1,
    marginLeft: 14,
  },

  profileLabel: {
    fontSize: 9,
    fontWeight: "800",
    letterSpacing: 1,
    color: "#6F9A8F",
    marginBottom: 5,
  },

  studentName: {
    fontSize: 20,
    fontWeight: "800",
    color: "#284F47",
  },

  compactStudentName: {
    fontSize: 16,
  },

  email: {
    fontSize: 11,
    color: "#788781",
    marginTop: 5,
  },

  sectionHeading: {
    marginBottom: 10,
    marginTop: 2,
  },

  sectionTitle: {
    fontSize: 18,
    fontWeight: "800",
    color: "#284F47",
  },

  compactSectionTitle: {
    fontSize: 16,
  },

  menu: {
    marginBottom: 18,
  },

  menuItem: {
    minHeight: 62,
    backgroundColor: "#FFFFFF",
    borderRadius: 15,
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 12,
    paddingVertical: 9,
    marginBottom: 9,
    shadowColor: "#31574F",
    shadowOpacity: 0.06,
    shadowRadius: 5,
    shadowOffset: { width: 0, height: 2 },
    elevation: 1,
  },

  compactMenuItem: {
    minHeight: 52,
    paddingVertical: 7,
    marginBottom: 7,
  },

  menuIcon: {
    width: 38,
    height: 38,
    borderRadius: 13,
    alignItems: "center",
    justifyContent: "center",
    marginRight: 12,
  },

  menuTitle: {
    flex: 1,
    color: "#294E46",
    fontSize: 13,
    fontWeight: "700",
  },

  compactMenuTitle: {
    fontSize: 12,
  },

  actions: {
    gap: 10,
    marginBottom: 8,
  },

  switchButton: {
    minHeight: 50,
    borderRadius: 16,
    backgroundColor: "#FFFFFF",
    borderWidth: 1,
    borderColor: "#DDE9E3",
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 15,
    gap: 11,
  },

  switchText: {
    flex: 1,
    color: "#365C55",
    fontSize: 13,
    fontWeight: "700",
  },

  logoutButton: {
    minHeight: 50,
    borderRadius: 16,
    backgroundColor: "#FFFFFF",
    borderWidth: 1,
    borderColor: "#F0D5D5",
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 15,
    gap: 11,
  },

  logoutText: {
    flex: 1,
    color: "#D94B4B",
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