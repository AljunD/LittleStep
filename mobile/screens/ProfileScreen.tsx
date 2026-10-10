
import React from "react";
import {
  View,
  Text,
  Image,
  ImageBackground,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  ScrollView,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

const menuItems = [
  { title: "Personal Information", icon: "person-outline" as const },
  { title: "Notification", icon: "notifications-outline" as const },
  { title: "Display", icon: "moon-outline" as const },
  { title: "Language", icon: "globe-outline" as const },
  { title: "Contact Us", icon: "call-outline" as const },
];

export default function ProfileScreen({ navigation, route }: any) {
  const { width } = useWindowDimensions();
  const insets = useSafeAreaInsets();

  const student = route.params?.student ?? {
    name: "Dela Cruz, Juan",
    firstName: "Juan",
    email: "juan.delacruz@gmail.com",
    image: require("../assets/images/juan.jpg"),
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

      {/* HEADER */}
      <View style={[styles.header, { paddingTop: insets.top }]}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="chevron-back" size={25} color="#222222" />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>Profile</Text>
        <View style={styles.headerSpacer} />
      </View>

      {/* SCROLLABLE PROFILE CONTENT */}
      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* PROFILE BANNER */}
        <ImageBackground
          source={require("../assets/images/loginback.png")}
          style={styles.profileBanner}
          resizeMode="cover"
        >
          <Image
            source={student.image}
            style={[
              styles.profileImage,
              {
                width: Math.min(width * 0.29, 105),
                height: Math.min(width * 0.29, 105),
              },
            ]}
            resizeMode="cover"
          />

          <View style={styles.profileDetails}>
            <Text style={styles.studentName} numberOfLines={2}>
              {student.name}
            </Text>
            <Text style={styles.email} numberOfLines={1}>
              {student.email ?? "juan.delacruz@gmail.com"}
            </Text>
          </View>
        </ImageBackground>

        {/* MENU ITEMS */}
        <View style={styles.menu}>
          {menuItems.map((item) => (
            <TouchableOpacity
              key={item.title}
              style={styles.menuItem}
              activeOpacity={0.7}
              onPress={() => {
                console.log("Selected:", item.title);
              }}
            >
              <View style={styles.menuIcon}>
                <Ionicons
                  name={item.icon}
                  size={20}
                  color="#FFFFFF"
                />
              </View>

              <Text style={styles.menuTitle}>{item.title}</Text>

              <Ionicons
                name="chevron-forward"
                size={20}
                color="#C8C8C8"
              />
            </TouchableOpacity>
          ))}
        </View>

        {/* SWITCH STUDENT AND LOGOUT */}
        <View style={styles.actions}>
          <TouchableOpacity
            style={styles.switchButton}
            activeOpacity={0.8}
            onPress={() => navigation.navigate("SelectStudent")}
          >
            <Ionicons
              name="sync-outline"
              size={21}
              color="#333333"
            />
            <Text style={styles.switchText}>Switch Student</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.logoutButton}
            activeOpacity={0.8}
            onPress={() => navigation.navigate("Login")}
          >
            <Ionicons
              name="log-out-outline"
              size={21}
              color="#FF4545"
            />
            <Text style={styles.logoutText}>Log Out</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* BOTTOM NAVIGATION */}
      <View
        style={[
          styles.bottomNav,
          { paddingBottom: Math.max(insets.bottom, 5) },
        ]}
      >
        <TouchableOpacity
          style={styles.navItem}
          onPress={() => navigation.navigate("Dashboard", { student })}
        >
          <Ionicons name="home-outline" size={22} color="#BBBBBB" />
          <Text style={styles.navLabel}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          onPress={() =>
            navigation.navigate("ECCDChecklist", { student })
          }
        >
          <Ionicons
            name="stats-chart-outline"
            size={22}
            color="#BBBBBB"
          />
          <Text style={styles.navLabel}>ECCD Checklist</Text>
        </TouchableOpacity>

        <TouchableOpacity style={styles.navItem} activeOpacity={0.8}>
          <Ionicons name="person-outline" size={22} color="#08AEC2" />
          <Text style={[styles.navLabel, styles.activeLabel]}>
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
    minHeight: 69,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: "#EEEEEE",
  },
  backButton: {
    width: 40,
    height: 44,
    justifyContent: "center",
    alignItems: "flex-start",
  },
  headerTitle: {
    flex: 1,
    textAlign: "center",
    color: "#111111",
    fontSize: 15,
    fontWeight: "800",
  },
  headerSpacer: {
    width: 40,
  },
  scroll: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingBottom: 12,
  },
  profileBanner: {
    minHeight: 150,
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 20,
    paddingVertical: 18,
    overflow: "hidden",
  },
  profileImage: {
    borderRadius: 100,
    borderWidth: 2,
    borderColor: "#FFFFFF",
    marginRight: 13,
  },
  profileDetails: {
    flex: 1,
  },
  studentName: {
    fontSize: 15,
    fontWeight: "800",
    color: "#111111",
  },
  email: {
    fontSize: 10,
    color: "#777777",
    marginTop: 4,
  },
  menu: {
    paddingHorizontal: 18,
    paddingTop: 8,
  },
  menuItem: {
    minHeight: 39,
    flexDirection: "row",
    alignItems: "center",
  },
  menuIcon: {
    width: 31,
    height: 31,
    borderRadius: 17,
    backgroundColor: "#0BA8BD",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 11,
  },
  menuTitle: {
    flex: 1,
    color: "#111111",
    fontSize: 12,
    fontWeight: "500",
  },
  actions: {
    marginTop: "auto",
    paddingHorizontal: 14,
    paddingTop: 30,
    paddingBottom: 24,
    gap: 8,
  },
  switchButton: {
    minHeight: 40,
    borderRadius: 17,
    borderWidth: 1,
    borderColor: "#333333",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
    backgroundColor: "#FFFFFF",
    elevation: 2,
  },
  switchText: {
    color: "#111111",
    fontSize: 12,
  },
  logoutButton: {
    minHeight: 40,
    borderRadius: 17,
    borderWidth: 1,
    borderColor: "#FF4545",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
    backgroundColor: "#FFFFFF",
    elevation: 2,
  },
  logoutText: {
    color: "#111111",
    fontSize: 12,
  },
  bottomNav: {
    minHeight: 58,
    backgroundColor: "#FFFFFF",
    borderTopWidth: 1,
    borderTopColor: "#DDDDDD",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-around",
    paddingTop: 5,
  },
  navItem: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    paddingVertical: 3,
  },
  navLabel: {
    fontSize: 8,
    color: "#AAAAAA",
    marginTop: 3,
    textAlign: "center",
  },
  activeLabel: {
    color: "#08AEC2",
  },
});
