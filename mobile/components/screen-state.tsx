import React from "react";
import {
  ActivityIndicator,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { DarkColors } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";

/** Shared loading / error / empty placeholders for screens that load API data. */

export function LoadingState({ message = "Loading..." }: { message?: string }) {
  const { isDark } = useAppTheme();

  return (
    <View style={styles.box}>
      <ActivityIndicator color="#0BA8BD" />
      <Text style={[styles.message, isDark && darkStyles.mutedText]}>{message}</Text>
    </View>
  );
}

export function ErrorState({ message, onRetry }: { message: string; onRetry: () => void }) {
  const { isDark } = useAppTheme();

  return (
    <View style={styles.box}>
      <Ionicons
        name="cloud-offline-outline"
        size={36}
        color={isDark ? DarkColors.textMuted : "#8A9692"}
      />
      <Text accessibilityRole="alert" style={[styles.error, isDark && darkStyles.danger]}>
        {message}
      </Text>
      <TouchableOpacity style={styles.retryButton} activeOpacity={0.8} onPress={onRetry}>
        <Text style={styles.retryText}>Try again</Text>
      </TouchableOpacity>
    </View>
  );
}

export function EmptyState({
  icon,
  title,
  message,
}: {
  icon: React.ComponentProps<typeof Ionicons>["name"];
  title: string;
  message: string;
}) {
  const { isDark } = useAppTheme();

  return (
    <View style={[styles.emptyCard, isDark && darkStyles.card]}>
      <View style={[styles.emptyIcon, isDark && darkStyles.surfaceMuted]}>
        <Ionicons name={icon} size={30} color={isDark ? DarkColors.icon : "#365C55"} />
      </View>
      <Text style={[styles.emptyTitle, isDark && darkStyles.text]}>{title}</Text>
      <Text style={[styles.message, styles.centered, isDark && darkStyles.mutedText]}>{message}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  box: {
    alignItems: "center",
    justifyContent: "center",
    paddingVertical: 40,
    paddingHorizontal: 24,
    gap: 12,
  },

  message: {
    fontSize: 13,
    lineHeight: 19,
    color: "#788781",
  },

  centered: {
    textAlign: "center",
  },

  error: {
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

  emptyCard: {
    alignItems: "center",
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#E9EEEC",
    backgroundColor: "#FFFFFF",
    paddingHorizontal: 22,
    paddingVertical: 28,
  },

  emptyIcon: {
    width: 60,
    height: 60,
    borderRadius: 18,
    backgroundColor: "#EEF5F1",
    alignItems: "center",
    justifyContent: "center",
    marginBottom: 12,
  },

  emptyTitle: {
    fontSize: 16,
    fontWeight: "800",
    color: "#284F47",
    marginBottom: 6,
  },
});

const darkStyles = StyleSheet.create({
  text: {
    color: DarkColors.text,
  },

  mutedText: {
    color: DarkColors.textMuted,
  },

  danger: {
    color: DarkColors.danger,
  },

  card: {
    backgroundColor: DarkColors.surface,
    borderColor: DarkColors.border,
  },

  surfaceMuted: {
    backgroundColor: DarkColors.surfaceMuted,
  },
});
