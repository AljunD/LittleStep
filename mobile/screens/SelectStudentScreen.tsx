import React from "react";
import {
  View,
  Text,
  StyleSheet,
  ImageBackground,
  Image,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";

export default function SelectStudentScreen({ navigation }: any) {
  return (
    <View style={styles.container}>
      <StatusBar
        translucent
        backgroundColor="transparent"
        barStyle="dark-content"
      />

      <ImageBackground
        source={require("../assets/images/back.jpg")}
        style={styles.background}
        resizeMode="cover"
      >

        <SafeAreaView style={styles.safeArea}>

          {/* SPACE ABOVE HEADER */}
          <View style={styles.skySpace} />

          {/* HEADER */}
          <View style={styles.header}>
            <Text style={styles.portalLabel}>
              PARENT PORTAL
            </Text>

            <Text style={styles.title}>
              Who’s learning today?
            </Text>
          </View>

          {/* STUDENTS */}
          <View style={styles.studentContainer}>

            {/* JUAN */}
            <TouchableOpacity
              style={styles.studentCard}
              activeOpacity={0.85}
            >
              <Image
                source={require("../assets/images/juan.jpg")}
                style={styles.profile}
              />

              <View style={styles.studentInfo}>
                <Text style={styles.studentName}>
                  Dela Cruz, Juan
                </Text>

                <Text style={styles.progress}>
                  View Progress Records
                </Text>
              </View>

              <Ionicons
                name="chevron-forward"
                size={22}
                color="#C7C7C7"
              />
            </TouchableOpacity>

            {/* MARIA */}
            <TouchableOpacity
              style={styles.studentCard}
              activeOpacity={0.85}
            >
              <Image
                source={require("../assets/images/maria.jpg")}
                style={styles.profile}
              />

              <View style={styles.studentInfo}>
                <Text style={styles.studentName}>
                  Dela Cruz, Maria
                </Text>

                <Text style={styles.progress}>
                  View Progress Records
                </Text>
              </View>

              <Ionicons
                name="chevron-forward"
                size={22}
                color="#C7C7C7"
              />
            </TouchableOpacity>

          </View>

        </SafeAreaView>
      </ImageBackground>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },

  background: {
    flex: 1,
    width: "100%",
    height: "100%",
  },

  safeArea: {
    flex: 1,
  },

  /* SPACE ABOVE BLUE HEADER */

  skySpace: {
    height: 65,
  },

  /* BLUE HEADER */

  header: {
    backgroundColor: "#0BA8BD",
    paddingHorizontal: 28,
    paddingTop: 20,
    paddingBottom: 27,
  },

  portalLabel: {
    color: "#FFFFFF",
    fontSize: 12,
    fontWeight: "800",
    letterSpacing: 0.5,
    marginBottom: 9,
  },

  title: {
    color: "#FFFFFF",
    fontSize: 27,
    fontWeight: "800",
  },

  /* STUDENTS */

  studentContainer: {
    flex: 1,
    alignItems: "center",
    paddingTop: 80,
  },

  /* CARD */

  studentCard: {
    width: "76%",
    minHeight: 90,
    backgroundColor: "#FFFFFF",
    borderRadius: 18,

    flexDirection: "row",
    alignItems: "center",

    paddingHorizontal: 18,
    marginBottom: 24,

    shadowColor: "#000",
    shadowOffset: {
      width: 0,
      height: 5,
    },
    shadowOpacity: 0.16,
    shadowRadius: 8,

    elevation: 6,
  },

  /* PROFILE IMAGE */

  profile: {
    width: 55,
    height: 55,
    borderRadius: 28,
    marginRight: 15,
  },

  /* STUDENT TEXT */

  studentInfo: {
    flex: 1,
  },

  studentName: {
    color: "#222222",
    fontSize: 16,
    fontWeight: "800",
    marginBottom: 5,
  },

  progress: {
    color: "#B8B8B8",
    fontSize: 11,
  },
});