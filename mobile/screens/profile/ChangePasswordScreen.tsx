import React, { useRef, useState } from "react";
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { DarkColors, Layout } from "../../constants/theme";
import { useAppTheme } from "../../hooks/use-app-theme";
import { useSessionEndedRedirect } from "../../hooks/use-api-resource";
import {
  changeGuardianPassword,
  ChangePasswordInput,
  getGuardianApiErrorMessage,
  GuardianApiError,
  isUsingDefaultPassword,
} from "../../services/guardianApi";

/** Same limits as the server (App\Support\FormRules::password()). */
const MIN_PASSWORD_LENGTH = 12;
const MAX_PASSWORD_BYTES = 72;

type FieldName = "current_password" | "password" | "password_confirmation";
type FieldErrors = Partial<Record<FieldName, string>>;

/** Checks the form before it is sent; the server checks again and has the final say. */
function validatePasswordChange(input: ChangePasswordInput): FieldErrors {
  const errors: FieldErrors = {};

  if (!input.currentPassword) {
    errors.current_password = "Enter your current password.";
  }

  if (input.password.length < MIN_PASSWORD_LENGTH) {
    errors.password = `Use at least ${MIN_PASSWORD_LENGTH} characters.`;
  } else if (new TextEncoder().encode(input.password).length > MAX_PASSWORD_BYTES) {
    errors.password = "This password is too long.";
  } else if (input.password === input.currentPassword) {
    errors.password = "The new password must be different from your current password.";
  }

  if (input.passwordConfirmation !== input.password) {
    errors.password_confirmation = "The passwords do not match.";
  }

  return errors;
}

export default function ChangePasswordScreen({ navigation }: any) {
  const insets = useSafeAreaInsets();
  const { isDark } = useAppTheme();
  const redirectIfSessionEnded = useSessionEndedRedirect();
  const [form, setForm] = useState<ChangePasswordInput>({
    currentPassword: "",
    password: "",
    passwordConfirmation: "",
  });
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const submittingRef = useRef(false);
  const usingDefaultPassword = isUsingDefaultPassword();

  const updateField = (key: keyof ChangePasswordInput, value: string) => {
    setForm((current) => ({ ...current, [key]: value }));
  };

  const handleSubmit = async () => {
    if (submittingRef.current) {
      return;
    }

    const errors = validatePasswordChange(form);
    setFieldErrors(errors);
    setErrorMessage(null);
    if (Object.keys(errors).length > 0) {
      return;
    }

    submittingRef.current = true;
    setSubmitting(true);

    try {
      setSuccessMessage(await changeGuardianPassword(form));
      setForm({ currentPassword: "", password: "", passwordConfirmation: "" });
    } catch (error) {
      if (redirectIfSessionEnded(error)) {
        return;
      }

      if (error instanceof GuardianApiError && Object.keys(error.fieldErrors).length > 0) {
        setFieldErrors(error.fieldErrors);
      } else {
        setErrorMessage(getGuardianApiErrorMessage(error));
      }
    } finally {
      submittingRef.current = false;
      setSubmitting(false);
    }
  };

  return (
    <View style={[styles.container, isDark && darkStyles.container]}>
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
        backgroundColor={isDark ? DarkColors.background : "#FFFFFF"}
      />

      {/* Header */}
      <View style={[styles.header, isDark && darkStyles.container, { paddingTop: insets.top + 10 }]}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
          accessibilityRole="button"
          accessibilityLabel="Go back"
        >
          <Ionicons name="chevron-back" size={26} color={isDark ? DarkColors.text : "#263447"} />
        </TouchableOpacity>

        <Text style={[styles.headerTitle, isDark && darkStyles.text]}>Change Password</Text>

        <View style={styles.headerSpacer} />
      </View>

      <KeyboardAvoidingView
        style={styles.keyboardAvoider}
        behavior={Platform.OS === "ios" ? "padding" : undefined}
      >
        <ScrollView
          contentContainerStyle={[styles.content, { paddingBottom: Math.max(insets.bottom, 16) + 16 }]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {successMessage ? (
            <View style={[styles.card, styles.successCard, isDark && darkStyles.card]} accessibilityRole="alert">
              <View style={[styles.successIcon, isDark && darkStyles.successIcon]}>
                <Ionicons name="checkmark" size={28} color={isDark ? DarkColors.accent : "#1E8E5A"} />
              </View>
              <Text style={[styles.successTitle, isDark && darkStyles.text]}>{successMessage}</Text>
              <Text style={[styles.pageDescription, styles.centeredText, isDark && darkStyles.mutedText]}>
                Use your new password the next time you sign in. Any other devices signed in to your
                account have been signed out.
              </Text>
              <TouchableOpacity
                style={styles.primaryButton}
                activeOpacity={0.85}
                accessibilityRole="button"
                onPress={() => navigation.goBack()}
              >
                <Text style={styles.primaryButtonText}>Done</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <>
              <Text style={[styles.pageTitle, isDark && darkStyles.text]}>Change Password</Text>
              <Text style={[styles.pageDescription, isDark && darkStyles.mutedText]}>
                Choose a password only you know. It must have at least {MIN_PASSWORD_LENGTH} characters.
              </Text>

              {usingDefaultPassword ? (
                <View style={[styles.notice, isDark && darkStyles.notice]}>
                  <Ionicons name="alert-circle-outline" size={18} color={isDark ? "#F5C76B" : "#B54708"} />
                  <Text style={[styles.noticeText, isDark && darkStyles.noticeText]}>
                    You are using the default password from your child&apos;s teacher. Enter it as your
                    current password below.
                  </Text>
                </View>
              ) : null}

              <View style={[styles.card, isDark && darkStyles.card]}>
                <PasswordField
                  label="Current password"
                  value={form.currentPassword}
                  onChangeText={(value) => updateField("currentPassword", value)}
                  error={fieldErrors.current_password}
                  autoComplete="current-password"
                  editable={!submitting}
                  isDark={isDark}
                />
                <PasswordField
                  label="New password"
                  value={form.password}
                  onChangeText={(value) => updateField("password", value)}
                  error={fieldErrors.password}
                  autoComplete="new-password"
                  editable={!submitting}
                  isDark={isDark}
                />
                <PasswordField
                  label="Confirm new password"
                  value={form.passwordConfirmation}
                  onChangeText={(value) => updateField("passwordConfirmation", value)}
                  error={fieldErrors.password_confirmation}
                  autoComplete="new-password"
                  editable={!submitting}
                  isDark={isDark}
                  onSubmitEditing={handleSubmit}
                />

                {errorMessage ? (
                  <Text accessibilityRole="alert" style={[styles.errorMessage, isDark && darkStyles.danger]}>
                    {errorMessage}
                  </Text>
                ) : null}

                <TouchableOpacity
                  style={[styles.primaryButton, submitting && styles.buttonDisabled]}
                  activeOpacity={0.85}
                  disabled={submitting}
                  accessibilityRole="button"
                  accessibilityState={{ disabled: submitting, busy: submitting }}
                  onPress={handleSubmit}
                >
                  {submitting ? (
                    <View style={styles.loadingContent}>
                      <ActivityIndicator color="#FFFFFF" size="small" />
                      <Text style={styles.primaryButtonText}>Saving...</Text>
                    </View>
                  ) : (
                    <Text style={styles.primaryButtonText}>Save New Password</Text>
                  )}
                </TouchableOpacity>
              </View>
            </>
          )}
        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  );
}

type PasswordFieldProps = {
  label: string;
  value: string;
  onChangeText: (value: string) => void;
  error?: string;
  autoComplete: "current-password" | "new-password";
  editable: boolean;
  isDark: boolean;
  onSubmitEditing?: () => void;
};

/** Labelled password input with a show/hide toggle and its validation message. */
function PasswordField({
  label,
  value,
  onChangeText,
  error,
  autoComplete,
  editable,
  isDark,
  onSubmitEditing,
}: PasswordFieldProps) {
  const [visible, setVisible] = useState(false);

  return (
    <View style={styles.field}>
      <Text style={[styles.label, isDark && darkStyles.text]}>{label}</Text>
      <View style={[styles.inputBox, isDark && darkStyles.inputBox, error ? styles.inputBoxError : null]}>
        <Ionicons name="lock-closed-outline" size={15} color="#A5A5A5" />
        <TextInput
          style={[styles.input, isDark && darkStyles.text]}
          value={value}
          onChangeText={onChangeText}
          secureTextEntry={!visible}
          autoCapitalize="none"
          autoCorrect={false}
          autoComplete={autoComplete}
          textContentType={autoComplete === "new-password" ? "newPassword" : "password"}
          maxLength={1024}
          editable={editable}
          accessibilityLabel={label}
          returnKeyType={onSubmitEditing ? "go" : "next"}
          onSubmitEditing={onSubmitEditing}
        />
        <TouchableOpacity
          onPress={() => setVisible(!visible)}
          accessibilityRole="button"
          accessibilityLabel={visible ? `Hide ${label.toLowerCase()}` : `Show ${label.toLowerCase()}`}
          hitSlop={8}
        >
          <Ionicons name={visible ? "eye-outline" : "eye-off-outline"} size={16} color="#A5A5A5" />
        </TouchableOpacity>
      </View>
      {error ? (
        <Text style={[styles.fieldError, isDark && darkStyles.danger]} accessibilityRole="alert">
          {error}
        </Text>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FFFFFF",
  },
  header: {
    minHeight: 95,
    paddingHorizontal: 20,
    paddingBottom: 15,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: "#FFFFFF",
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: "center",
    justifyContent: "center",
  },
  headerTitle: {
    flex: 1,
    textAlign: "center",
    fontSize: 20,
    fontWeight: "700",
    color: "#263447",
  },
  headerSpacer: {
    width: 40,
  },
  keyboardAvoider: {
    flex: 1,
  },
  content: {
    width: "100%",
    maxWidth: Layout.maxContentWidth,
    alignSelf: "center",
    paddingHorizontal: 22,
    paddingTop: 8,
  },
  pageTitle: {
    fontSize: 22,
    fontWeight: "800",
    color: "#263447",
    marginBottom: 6,
  },
  pageDescription: {
    fontSize: 13,
    lineHeight: 19,
    color: "#858D91",
    marginBottom: 18,
  },
  centeredText: {
    textAlign: "center",
  },
  notice: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 8,
    backgroundColor: "#FFF6E5",
    borderRadius: 12,
    padding: 12,
    marginBottom: 18,
  },
  noticeText: {
    flex: 1,
    fontSize: 12,
    lineHeight: 17,
    color: "#7A4A06",
  },
  card: {
    backgroundColor: "#FFFFFF",
    borderWidth: 1,
    borderColor: "#EDF0EF",
    borderRadius: 16,
    padding: 16,
  },
  successCard: {
    alignItems: "center",
    paddingVertical: 26,
  },
  successIcon: {
    width: 58,
    height: 58,
    borderRadius: 29,
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: "#E7F6EE",
    marginBottom: 14,
  },
  successTitle: {
    fontSize: 17,
    fontWeight: "800",
    color: "#263447",
    marginBottom: 8,
    textAlign: "center",
  },
  field: {
    marginBottom: 14,
  },
  label: {
    fontSize: 12,
    fontWeight: "700",
    color: "#263447",
    marginBottom: 6,
  },
  inputBox: {
    height: 46,
    backgroundColor: "#FFFFFF",
    borderRadius: 25,
    borderWidth: 1,
    borderColor: "#E1E1E1",
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 14,
  },
  inputBoxError: {
    borderColor: "#F04438",
  },
  input: {
    flex: 1,
    fontSize: 13,
    paddingHorizontal: 8,
    color: "#111111",
  },
  fieldError: {
    color: "#B42318",
    fontSize: 11,
    lineHeight: 15,
    marginTop: 5,
    marginLeft: 12,
  },
  errorMessage: {
    color: "#B42318",
    fontSize: 12,
    lineHeight: 17,
    marginBottom: 12,
  },
  primaryButton: {
    alignSelf: "stretch",
    height: 47,
    borderRadius: 25,
    backgroundColor: "#0BA8BD",
    alignItems: "center",
    justifyContent: "center",
    marginTop: 6,
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  primaryButtonText: {
    color: "#FFFFFF",
    fontSize: 15,
    fontWeight: "800",
  },
  loadingContent: {
    flexDirection: "row",
    alignItems: "center",
    gap: 9,
  },
});

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: DarkColors.background,
  },
  text: {
    color: DarkColors.text,
  },
  mutedText: {
    color: DarkColors.textMuted,
  },
  card: {
    backgroundColor: DarkColors.surface,
    borderColor: DarkColors.border,
  },
  inputBox: {
    backgroundColor: DarkColors.surfaceMuted,
    borderColor: DarkColors.border,
  },
  notice: {
    backgroundColor: "#3A2E12",
  },
  noticeText: {
    color: "#F5DDA8",
  },
  successIcon: {
    backgroundColor: DarkColors.selected,
  },
  danger: {
    color: DarkColors.danger,
  },
});
