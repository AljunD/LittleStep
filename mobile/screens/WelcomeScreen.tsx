import React from "react";
import { View, Text, StyleSheet } from "react-native";

export default function WelcomeScreen() {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Welcome to LittleStep</Text>
      <Text style={styles.subtitle}>
        Your child's learning journey starts here.
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    padding: 20,
  },
  title: {
    fontSize: 28,
    fontWeight: "bold",
  },
  subtitle: {
    fontSize: 16,
    marginTop: 10,
    textAlign: "center",
  },
});