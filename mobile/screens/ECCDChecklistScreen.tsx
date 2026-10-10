
import React from "react";
import {
  View,
  Text,
  StyleSheet,
  ImageBackground,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  ScrollView,
  useWindowDimensions,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";

const categories = [
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
    title: "Understanding & Listening",
    subtitle: "Receptive Language",
    icon: "ear-outline" as const,
    color: "#A5E5D0",
  },
  {
    title: "Speaking & Communication",
    subtitle: "Expressive Language",
    icon: "chatbubble-ellipses-outline" as const,
    color: "#9BD8F5",
  },
  {
    title: "Social & Emotional Skills",
    subtitle: "Social Emotional",
    icon: "people-outline" as const,
    color: "#D0C8FF",
  },
  {
    title: "Thinking & Learning",
    subtitle: "Cognitive",
    icon: "finger-print-outline" as const,
    color: "#FFC09F",
  },
];

export default function ECCDChecklistScreen({
  navigation,
  route,
}: any) {
  const { width, height } = useWindowDimensions();
  const insets = useSafeAreaInsets();
  const compact = height < 720;
  const student = route.params?.student;

  return (
    <View style={styles.container}>
      <StatusBar
        backgroundColor="#FFFFFF"
        barStyle="dark-content"
      />

      {/* TOP BAR */}
      <View
        style={[
          styles.topBar,
          { paddingTop: Math.max(insets.top, 0) },
        ]}
      >
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
        >
          <Ionicons
            name="chevron-back"
            size={25}
            color="#222222"
          />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>ECCD Checklist</Text>

        <View style={styles.headerSpacer} />
      </View>

      {/* CATEGORY LIST */}
      <ImageBackground
        source={require("../assets/images/back.jpg")}
        style={styles.background}
        resizeMode="cover"
      >
        <ScrollView
          contentContainerStyle={[
            styles.listContent,
            {
              paddingHorizontal: Math.max(24, width * 0.105),
              paddingTop: compact ? 12 : 18,
              paddingBottom: 18,
            },
          ]}
          showsVerticalScrollIndicator={false}
        >
          {categories.map((category) => (
            <TouchableOpacity
              key={category.title}
              style={[
                styles.categoryCard,
                {
                  backgroundColor: category.color,
                  minHeight: compact ? 63 : 75,
                },
              ]}
              activeOpacity={0.8}
              onPress={() => {
                // Connect each category to its detail screen later.
                console.log("Selected category:", category.title);
              }}
            >
              <View style={styles.iconCircle}>
                <Ionicons
                  name={category.icon}
                  size={19}
                  color="#444444"
                />
              </View>

              <View style={styles.categoryInfo}>
                <Text
                  style={[
                    styles.categoryTitle,
                    { fontSize: Math.min(width * 0.034, 14) },
                  ]}
                  numberOfLines={2}
                >
                  {category.title}
                </Text>

                <Text style={styles.categorySubtitle}>
                  {category.subtitle}
                </Text>
              </View>

              <Ionicons
                name="chevron-forward"
                size={17}
                color="#222222"
              />
            </TouchableOpacity>
          ))}
        </ScrollView>
      </ImageBackground>

      {/* BOTTOM NAVIGATION */}
      <View
        style={[
          styles.bottomNav,
          { paddingBottom: Math.max(insets.bottom, 5) },
        ]}
      >
        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.8}
          onPress={() => navigation.navigate("Dashboard", { student })}
        >
          <Ionicons
            name="home-outline"
            size={22}
            color="#AAAAAA"
          />
          <Text style={styles.navLabel}>Home</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.8}
        >
          <Ionicons
            name="stats-chart-outline"
            size={22}
            color="#08AEC2"
          />
          <Text style={[styles.navLabel, styles.activeLabel]}>
            ECCD Checklist
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.navItem}
          activeOpacity={0.8}
          onPress={() => navigation.navigate("Profile", { student })}
        >
          <Ionicons
            name="person-outline"
            size={22}
            color="#AAAAAA"
          />
          <Text style={styles.navLabel}>Profile</Text>
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

  topBar: {
    minHeight: 69,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: "#DDDDDD",
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
    fontSize: 15,
    fontWeight: "800",
    color: "#111111",
  },

  headerSpacer: {
    width: 40,
  },

  background: {
    flex: 1,
  },

  listContent: {
    flexGrow: 1,
  },

  categoryCard: {
    width: "100%",
    borderRadius: 16,
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 11,
    paddingVertical: 9,
    marginBottom: 12,
    elevation: 3,
    shadowColor: "#000000",
    shadowOpacity: 0.13,
    shadowRadius: 4,
    shadowOffset: { width: 0, height: 3 },
  },

  iconCircle: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: "rgba(255,255,255,0.78)",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 10,
  },

  categoryInfo: {
    flex: 1,
    paddingRight: 5,
  },

  categoryTitle: {
    fontWeight: "800",
    color: "#111111",
  },

  categorySubtitle: {
    fontSize: 10,
    color: "#222222",
    marginTop: 3,
  },

  bottomNav: {
    minHeight: 58,
    backgroundColor: "#FFFFFF",
    flexDirection: "row",
    justifyContent: "space-around",
    alignItems: "center",
    borderTopWidth: 1,
    borderTopColor: "#EEEEEE",
    paddingTop: 5,
  },

  navItem: {
    flex: 1,
    minWidth: 0,
    alignItems: "center",
    justifyContent: "center",
    paddingVertical: 3,
    paddingHorizontal: 2,
  },

  navLabel: {
    fontSize: 9,
    color: "#AAAAAA",
    marginTop: 3,
    textAlign: "center",
  },

  activeLabel: {
    color: "#08AEC2",
  },
});
