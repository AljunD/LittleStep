import React from "react";
import { StyleSheet, Text, View } from "react-native";
import { DarkColors } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";
import { displayValue } from "../services/format";

/** A labelled, read-only value box used on the guardian/child detail screens. */
export function ReadOnlyField({
  label,
  value,
}: {
  label: string;
  value: string | number | null | undefined;
}) {
  const { isDark } = useAppTheme();

  return (
    <View style={styles.fieldContainer}>
      <Text style={[styles.label, isDark && darkStyles.label]}>{label}</Text>
      <Text
        style={[styles.value, isDark && darkStyles.value]}
        selectable
        accessibilityLabel={`${label}: ${displayValue(value)}`}
      >
        {displayValue(value)}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  fieldContainer: {
    flex: 1,
    marginBottom: 16,
  },

  label: {
    fontSize: 13,
    fontWeight: "600",
    color: "#53675D",
    marginBottom: 7,
  },

  value: {
    minHeight: 44,
    borderWidth: 1,
    borderColor: "#DCE7E1",
    borderRadius: 9,
    paddingHorizontal: 12,
    paddingVertical: 11,
    fontSize: 14,
    color: "#263B32",
    backgroundColor: "#FAFCFB",
  },
});

const darkStyles = StyleSheet.create({
  label: {
    color: DarkColors.textMuted,
  },

  value: {
    borderColor: DarkColors.border,
    color: DarkColors.text,
    backgroundColor: DarkColors.surface,
  },
});
