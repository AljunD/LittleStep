
import React, { useCallback, useEffect, useState } from "react";
import {
  ActivityIndicator,
  View,
  Text,
  StyleSheet,
  ImageBackground,
  Image,
  TouchableOpacity,
  StatusBar,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { Ionicons } from "@expo/vector-icons";
import {
  getGuardianApiErrorMessage,
  getGuardianChildren,
  GuardianChild,
} from "../services/guardianApi";

export default function SelectStudentScreen({ navigation }: any) {
  const [students, setStudents] = useState<GuardianChild[]>([]);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const loadStudents = useCallback(async () => {
    setLoading(true);
    setErrorMessage(null);

    try {
      setStudents(await getGuardianChildren());
    } catch (error) {
      setErrorMessage(getGuardianApiErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    let isMounted = true;

    getGuardianChildren()
      .then((children) => {
        if (isMounted) {
          setStudents(children);
        }
      })
      .catch((error: unknown) => {
        if (isMounted) {
          setErrorMessage(getGuardianApiErrorMessage(error));
        }
      })
      .finally(() => {
        if (isMounted) {
          setLoading(false);
        }
      });

    return () => {
      isMounted = false;
    };
  }, []);

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
          <View style={styles.skySpace} />

          {/* HEADER */}
          <View style={styles.header}>
            <Text style={styles.portalLabel}>WELCOME!</Text>
            <Text style={styles.title}>Who’s learning today?</Text>
          </View>

          {/* STUDENT CARDS */}
          <View style={styles.studentContainer}>
            {loading ? (
              <View style={styles.stateMessage}>
                <ActivityIndicator color="#0BA8BD" />
                <Text style={styles.stateText}>Loading your children...</Text>
              </View>
            ) : errorMessage ? (
              <View style={styles.stateMessage}>
                <Text accessibilityRole="alert" style={styles.errorText}>
                  {errorMessage}
                </Text>
                <TouchableOpacity
                  style={styles.retryButton}
                  activeOpacity={0.8}
                  onPress={() => void loadStudents()}
                >
                  <Text style={styles.retryText}>Try again</Text>
                </TouchableOpacity>
              </View>
            ) : students.length === 0 ? (
              <View style={styles.stateMessage}>
                <Text style={styles.stateText}>
                  No children are currently linked to your guardian account.
                </Text>
              </View>
            ) : students.map((student, index) => {
                const fullName = [
                  student.first_name,
                  student.middle_name,
                  student.last_name,
                ].filter(Boolean).join(" ");
                const image = index % 2 === 0
                  ? require("../assets/images/juan.jpg")
                  : require("../assets/images/maria.jpg");

                return (
                  <TouchableOpacity
                    key={student.id}
                    style={styles.studentCard}
                    activeOpacity={0.85}
                    onPress={() =>
                      navigation.navigate("Dashboard", {
                        student: {
                          id: student.id,
                          name: fullName,
                          firstName: student.first_name,
                          lastName: student.last_name,
                          image,
                        },
                      })
                    }
                  >
                    <Image
                      source={image}
                      style={styles.profile}
                      resizeMode="cover"
                    />

                    <View style={styles.studentInfo}>
                      <Text style={styles.studentName}>{fullName}</Text>
                      <Text style={styles.progress}>View Progress Records</Text>
                    </View>

                    <Ionicons
                      name="chevron-forward"
                      size={22}
                      color="#C7C7C7"
                    />
                  </TouchableOpacity>
                );
              })}
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
  skySpace: {
    height: 65,
  },
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
  studentContainer: {
    flex: 1,
    alignItems: "center",
    paddingTop: 80,
  },
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
    shadowOffset: { width: 0, height: 5 },
    shadowOpacity: 0.16,
    shadowRadius: 8,
    elevation: 6,
  },
  profile: {
    width: 55,
    height: 55,
    borderRadius: 28,
    marginRight: 15,
  },
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
  stateMessage: {
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 32,
    gap: 12,
  },
  stateText: {
    color: "#555555",
    fontSize: 14,
    lineHeight: 20,
    textAlign: "center",
  },
  errorText: {
    color: "#B42318",
    fontSize: 13,
    lineHeight: 19,
    textAlign: "center",
  },
  retryButton: {
    backgroundColor: "#0BA8BD",
    borderRadius: 20,
    paddingHorizontal: 22,
    paddingVertical: 10,
  },
  retryText: {
    color: "#FFFFFF",
    fontSize: 13,
    fontWeight: "700",
  },
});
