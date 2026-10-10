
import React from "react";
import {
  View,
  Text,
  Image,
  ImageBackground,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  ScrollView,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

export default function DashboardScreen({ navigation, route }: any) {
  const { width, height } = useWindowDimensions();
  const insets = useSafeAreaInsets();

  const compact = height < 720;
  const sidePadding = Math.max(18, Math.min(width * 0.09, 34));

  const student = route.params?.student ?? {
    name: "Dela Cruz, Juan",
    firstName: "Juan",
    image: require("../assets/images/juan.jpg"),
  };

  const activities = [
    {
      title: "Physical Movement",
      subtitle: "Gross Motor",
      icon: "body-outline" as const,
      color: "#FFA5A5",
    },
    {
      title: "Hand & Finger Skills",
      subtitle: "Fine Motor",
      icon: "hand-left-outline" as const,
      color: "#FFD477",
    },
    {
      title: "Thinking & Learning",
      subtitle: "Cognitive",
      icon: "finger-print-outline" as const,
      color: "#FFC09F",
    },
    {
      title: "View Full",
      subtitle: "ECCD Checklist",
      icon: "document-text-outline" as const,
      color: "#B9DEC5",
    },
  ];

  return (
    <View style={styles.screen}>
      <StatusBar
        barStyle="light-content"
        backgroundColor="#202020"
      />

      <ImageBackground
        source={require("../assets/images/back.jpg")}
        style={styles.background}
        imageStyle={styles.backgroundImage}
        resizeMode="cover"
      >
        <SafeAreaView style={styles.safeArea}>
          {/* HEADER */}
          <View
            style={[
              styles.header,
              { paddingHorizontal: sidePadding },
            ]}
          >
            <Text
              style={[
                styles.greeting,
                { fontSize: Math.min(width * 0.061, 25) },
              ]}
            >
              Hello, {student.firstName}!
            </Text>
          </View>

          {/* SCROLLABLE DASHBOARD */}
          <ScrollView
            style={styles.scroll}
            contentContainerStyle={[
              styles.content,
              {
                paddingHorizontal: sidePadding,
                paddingTop: compact ? 14 : 24,
                paddingBottom: 18,
              },
            ]}
            showsVerticalScrollIndicator={false}
          >
            {/* STUDENT PROFILE */}
            <View
              style={[
                styles.studentCard,
                { minHeight: compact ? 78 : 90 },
              ]}
            >
              <Image
                source={student.image}
                style={[
                  styles.studentImage,
                  {
                    width: compact ? 45 : 52,
                    height: compact ? 45 : 52,
                  },
                ]}
                resizeMode="cover"
              />

              <View style={styles.studentDetails}>
                <Text
                  style={[
                    styles.studentName,
                    { fontSize: Math.min(width * 0.034, 14) },
                  ]}
                >
                  {student.name}
                </Text>

                <Text style={styles.studentStatus}>
                  Current Student
                </Text>
              </View>
            </View>

            {/* RECENT ACTIVITY */}
            <View style={styles.mainCard}>
              <Text
                style={[
                  styles.sectionTitle,
                  { fontSize: Math.min(width * 0.034, 14) },
                ]}
              >
                Recent Activity
              </Text>

              <View style={styles.activityGrid}>
                {activities.map((item) => (
                  <TouchableOpacity
                    key={item.title}
                    activeOpacity={0.85}
                    style={[
                      styles.activityCard,
                      {
                        backgroundColor: item.color,
                        minHeight: compact ? 64 : 76,
                      },
                    ]}
                    onPress={() => {
                      if (item.title === "View Full") {
                        navigation.navigate("ECCDChecklist", {
                          student,
                        });
                      }
                    }}
                  >
                    <View style={styles.activityIcon}>
                      <Ionicons
                        name={item.icon}
                        size={15}
                        color="#222222"
                      />
                    </View>

                    <Ionicons
                      name="chevron-forward"
                      size={13}
                      color="#222222"
                      style={styles.arrow}
                    />

                    <Text style={styles.activityTitle}>
                      {item.title}
                    </Text>

                    <Text style={styles.activitySubtitle}>
                      {item.subtitle}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>

              {/* QUICK ACTIONS */}
              <Text
                style={[
                  styles.quickTitle,
                  { fontSize: Math.min(width * 0.034, 14) },
                ]}
              >
                Quick Actions
              </Text>

              <View style={styles.quickGrid}>
                <TouchableOpacity
                  style={[
                    styles.quickCard,
                    { backgroundColor: "#A5E5D0" },
                  ]}
                  onPress={() =>
                    navigation.navigate("Display", { student })
                  }
                  activeOpacity={0.85}
                >
                  <Ionicons
                    name="moon-outline"
                    size={18}
                    color="#222222"
                  />
                  <Text style={styles.quickText}>Display</Text>
                  <Ionicons
                    name="chevron-forward"
                    size={10}
                    color="#222222"
                  />
                </TouchableOpacity>

                <TouchableOpacity
                  style={[
                    styles.quickCard,
                    { backgroundColor: "#9BD8F5" },
                  ]}
                  onPress={() =>
                    navigation.navigate("SelectStudent")
                  }
                  activeOpacity={0.85}
                >
                  <Ionicons
                    name="sync-outline"
                    size={18}
                    color="#222222"
                  />
                  <Text style={styles.quickText}>
                    Switch Student
                  </Text>
                  <Ionicons
                    name="chevron-forward"
                    size={10}
                    color="#222222"
                  />
                </TouchableOpacity>

                <TouchableOpacity
                  style={[
                    styles.quickCard,
                    { backgroundColor: "#D0C8FF" },
                  ]}
                  onPress={() => navigation.navigate("Login")}
                  activeOpacity={0.85}
                >
                  <Ionicons
                    name="log-out-outline"
                    size={18}
                    color="#222222"
                  />
                  <Text style={styles.quickText}>Logout</Text>
                  <Ionicons
                    name="chevron-forward"
                    size={10}
                    color="#222222"
                  />
                </TouchableOpacity>
              </View>
            </View>
          </ScrollView>

          {/* BOTTOM NAVIGATION */}
          <View
            style={[
              styles.bottomNav,
              {
                paddingBottom: Math.max(insets.bottom, 6),
              },
            ]}
          >
            <TouchableOpacity
              style={styles.navItem}
              activeOpacity={0.8}
            >
              <Ionicons
                name="home-outline"
                size={24}
                color="#08AEC2"
              />
              <Text style={[styles.navLabel, styles.activeLabel]}>
                Home
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.navItem}
              activeOpacity={0.8}
              onPress={() =>
                navigation.navigate("ECCDChecklist", { student })
              }
            >
              <Ionicons
                name="stats-chart-outline"
                size={24}
                color="#AAAAAA"
              />
              <Text style={styles.navLabel}>
                ECCD Checklist
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.navItem}
              activeOpacity={0.8}
              onPress={() =>
                navigation.navigate("Profile", { student })
              }
            >
              <Ionicons
                name="person-outline"
                size={24}
                color="#AAAAAA"
              />
              <Text style={styles.navLabel}>Profile</Text>
            </TouchableOpacity>
          </View>
        </SafeAreaView>
      </ImageBackground>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: "#202020",
  },

  background: {
    flex: 1,
    overflow: "hidden",
  },

  backgroundImage: {
    borderRadius: 14,
  },

  safeArea: {
    flex: 1,
  },

  header: {
    minHeight: 84,
    backgroundColor: "#0BA8BD",
    justifyContent: "center",
    paddingVertical: 18,
    borderBottomWidth: 2,
    borderBottomColor: "rgba(255,255,255,0.15)",
  },

  greeting: {
    color: "#FFFFFF",
    fontWeight: "800",
  },

  scroll: {
    flex: 1,
  },

  content: {
    flexGrow: 1,
  },

  studentCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 17,
    paddingHorizontal: 16,
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 14,
    borderWidth: 1,
    borderColor: "#E5E5E5",
    elevation: 4,
    shadowColor: "#000000",
    shadowOpacity: 0.12,
    shadowRadius: 5,
    shadowOffset: { width: 0, height: 3 },
  },

  studentImage: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: "#DDDDDD",
    marginRight: 12,
  },

  studentDetails: {
    flex: 1,
  },

  studentName: {
    fontWeight: "800",
    color: "#222222",
  },

  studentStatus: {
    fontSize: 10,
    color: "#999999",
    marginTop: 5,
  },

  mainCard: {
    backgroundColor: "#FFFFFF",
    borderRadius: 17,
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 22,
    borderWidth: 1,
    borderColor: "#E5E5E5",
    elevation: 4,
    shadowColor: "#000000",
    shadowOpacity: 0.12,
    shadowRadius: 5,
    shadowOffset: { width: 0, height: 3 },
  },

  sectionTitle: {
    color: "#222222",
    marginBottom: 13,
  },

  activityGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
  },

  activityCard: {
    width: "48.5%",
    borderRadius: 15,
    padding: 8,
    marginBottom: 9,
    justifyContent: "flex-start",
    elevation: 2,
  },

  activityIcon: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: "rgba(255,255,255,0.65)",
    alignItems: "center",
    justifyContent: "center",
  },

  arrow: {
    position: "absolute",
    right: 7,
    top: 8,
  },

  activityTitle: {
    fontSize: 8,
    fontWeight: "800",
    color: "#222222",
    marginTop: 5,
  },

  activitySubtitle: {
    fontSize: 7,
    color: "#333333",
    marginTop: 3,
  },

  quickTitle: {
    color: "#222222",
    marginTop: 12,
    marginBottom: 13,
  },

  quickGrid: {
    flexDirection: "row",
    justifyContent: "space-between",
  },

  quickCard: {
    width: "31.5%",
    minHeight: 59,
    borderRadius: 15,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 3,
    paddingVertical: 5,
    elevation: 2,
  },

  quickText: {
    fontSize: 8,
    color: "#222222",
    marginTop: 4,
    marginBottom: 3,
    textAlign: "center",
  },

  bottomNav: {
    minHeight: 62,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    justifyContent: "space-around",
    alignItems: "center",
    borderTopWidth: 1,
    borderTopColor: "#EEEEEE",
    paddingTop: 7,
  },

  navItem: {
    flex: 1,
    minWidth: 0,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 2,
  },

  navLabel: {
    fontSize: 9,
    color: "#AAAAAA",
    marginTop: 4,
    textAlign: "center",
  },

  activeLabel: {
    color: "#08AEC2",
  },
});
