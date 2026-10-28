import React from "react";
import { Modal, Platform, StyleSheet, Text, TouchableOpacity, View } from "react-native";
import { Ionicons } from "@expo/vector-icons";

import { DarkColors, Layout } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";

type PasswordReminderProps = {
  visible: boolean;
  onChangePassword: () => void;
  onRemindLater: () => void;
};

export function PasswordReminder({ visible, onChangePassword, onRemindLater }: PasswordReminderProps) {
  const { isDark } = useAppTheme();

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      statusBarTranslucent
      onRequestClose={onRemindLater}
    >
      <View style={styles.backdrop}>
        <View
          style={[styles.card, isDark && darkStyles.card]}
          accessibilityRole="alert"
          accessibilityViewIsModal
        >
          <View style={[styles.iconCircle, isDark && darkStyles.iconCircle]}>
            <Ionicons name="lock-closed" size={26} color={isDark ? DarkColors.accent : "#0BA8BD"} />
          </View>

          <Text style={[styles.title, isDark && darkStyles.text]}>Change your password</Text>
          <Text style={[styles.message, isDark && darkStyles.mutedText]}>
            You signed in with the temporary password your child&apos;s teacher gave you.
            For your security, please change it to a password only you know.
          </Text>

          <TouchableOpacity
            style={styles.primaryButton}
            activeOpacity={0.85}
            accessibilityRole="button"
            onPress={onChangePassword}
          >
            <Ionicons name="key-outline" size={17} color="#FFFFFF" />
            <Text style={styles.primaryButtonText}>Change Password</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.secondaryButton}
            activeOpacity={0.7}
            accessibilityRole="button"
            onPress={onRemindLater}
          >
            <Text style={[styles.secondaryButtonText, isDark && darkStyles.mutedText]}>Remind me later</Text>
          </TouchableOpacity>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    padding: 24,
    backgroundColor: "rgba(16, 24, 22, 0.55)",
  },
  card: {
    width: "100%",
    maxWidth: Layout.maxFormWidth,
    alignItems: "center",
    backgroundColor: "#FFFFFF",
    borderRadius: 20,
    paddingHorizontal: 22,
    paddingTop: 26,
    paddingBottom: 14,
    ...Platform.select({
      web: { boxShadow: "0px 8px 24px rgba(0, 0, 0, 0.18)" },
      default: {
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: 0.18,
        shadowRadius: 24,
        elevation: 10,
      },
    }),
  },
  iconCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "#E6F7F9",
    marginBottom: 14,
  },
  title: {
    fontSize: 18,
    fontWeight: "800",
    color: "#263447",
    marginBottom: 8,
    textAlign: "center",
  },
  message: {
    fontSize: 13,
    lineHeight: 19,
    color: "#5F6B70",
    textAlign: "center",
    marginBottom: 20,
  },
  primaryButton: {
    width: "100%",
    height: 47,
    borderRadius: 25,
    backgroundColor: "#0BA8BD",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 8,
  },
  primaryButtonText: {
    color: "#FFFFFF",
    fontSize: 15,
    fontWeight: "800",
  },
  secondaryButton: {
    width: "100%",
    height: 44,
    marginTop: 6,
    alignItems: "center",
    justifyContent: "center",
  },
  secondaryButtonText: {
    color: "#5F6B70",
    fontSize: 13,
    fontWeight: "700",
  },
});

const darkStyles = StyleSheet.create({
  card: {
    backgroundColor: DarkColors.surface,
  },
  iconCircle: {
    backgroundColor: DarkColors.selected,
  },
  text: {
    color: DarkColors.text,
  },
  mutedText: {
    color: DarkColors.textMuted,
  },
});
