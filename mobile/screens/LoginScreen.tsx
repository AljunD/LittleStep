import React, { useEffect, useRef, useState } from "react";
import {
  AccessibilityInfo,
  ActivityIndicator,
  Animated,
  View,
  Text,
  StyleSheet,
  ImageBackground,
  Image,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  TextInput,
  TouchableOpacity,
  StatusBar,
} from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import { Ionicons } from "@expo/vector-icons";
import * as Haptics from "expo-haptics";
import { DarkColors, Layout } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";
import {
  GuardianApiError,
  getGuardianApiErrorMessage,
  isRememberMeSupported,
  loginGuardian,
} from "../services/guardianApi";
import { openPrivacyPolicy } from "../services/privacy-policy";

type LoginField = "email" | "password";

type LoginError = {
  tone: "error" | "warning";
  icon: React.ComponentProps<typeof Ionicons>["name"];
  title: string;
  message: string;
  fields: LoginField[];
};

function describeLoginError(error: unknown): LoginError {
  const status = error instanceof GuardianApiError ? error.status : undefined;

  if (status === 401) {
    return {
      tone: "error",
      icon: "alert-circle",
      title: "Incorrect email or password",
      message: "Check your details and try again. Passwords are case-sensitive.",
      fields: ["email", "password"],
    };
  }

  if (status === 403) {
    return {
      tone: "warning",
      icon: "mail-unread",
      title: "Verify your email first",
      message: "Open the verification link we sent to your email, then log in again. Ask your child's teacher to resend it if you can't find it.",
      fields: [],
    };
  }

  if (status === 429) {
    return {
      tone: "warning",
      icon: "time",
      title: "Too many attempts",
      message: "For your security, sign-in is paused for a minute. Please wait and try again.",
      fields: [],
    };
  }

  if (status === 422 && error instanceof GuardianApiError) {
    const fields = (["email", "password"] as const).filter((field) => error.fieldErrors[field]);

    return {
      tone: "error",
      icon: "alert-circle",
      title: "Check your details",
      message: error.fieldErrors.email ?? error.fieldErrors.password ?? error.message,
      fields: [...fields],
    };
  }

  if (status === undefined) {
    return {
      tone: "error",
      icon: "cloud-offline",
      title: "Can't reach LittleStep",
      message: getGuardianApiErrorMessage(error),
      fields: [],
    };
  }

  return {
    tone: "error",
    icon: "alert-circle",
    title: "Sign-in failed",
    message: getGuardianApiErrorMessage(error),
    fields: [],
  };
}

export default function LoginScreen({ navigation, route }: any) {
  const { isDark } = useAppTheme();
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [rememberMeSupported, setRememberMeSupported] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [loginError, setLoginError] = useState<LoginError | null>(() => {
    const initialError = route?.params?.initialError;

    return initialError
      ? { tone: "warning", icon: "lock-closed", title: "Please log in again", message: initialError, fields: [] }
      : null;
  });
  const [invalidFields, setInvalidFields] = useState<LoginField[]>([]);
  const shake = useRef(new Animated.Value(0)).current;
  const submittingRef = useRef(false);
  const justVerified = route?.params?.verified === true && !loginError;

  const showError = (error: LoginError) => {
    setLoginError(error);
    setInvalidFields(error.fields);
    AccessibilityInfo.announceForAccessibility(`${error.title}. ${error.message}`);

    if (Platform.OS !== "web") {
      void Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error).catch(() => {});
    }

    void AccessibilityInfo.isReduceMotionEnabled().then((reduceMotion) => {
      if (reduceMotion) {
        return;
      }

      shake.setValue(0);
      Animated.sequence(
        [10, -10, 7, -7, 3, 0].map((toValue) =>
          Animated.timing(shake, { toValue, duration: 55, useNativeDriver: Platform.OS !== "web" }),
        ),
      ).start();
    });
  };

  const clearFieldError = (field: LoginField) => {
    setInvalidFields((fields) => fields.filter((item) => item !== field));
  };

  const goBack = () => {
    if (navigation.canGoBack()) {
      navigation.goBack();
    } else {
      navigation.replace("Welcome");
    }
  };

  useEffect(() => {
    let isMounted = true;

    void isRememberMeSupported().then((supported) => {
      if (isMounted) {
        setRememberMeSupported(supported);
      }
    });

    return () => {
      isMounted = false;
    };
  }, []);

  const handleLogin = async () => {
    if (submittingRef.current) {
      return;
    }

    const normalizedEmail = email.trim().toLowerCase();
    if (!normalizedEmail || !password) {
      showError({
        tone: "error",
        icon: "alert-circle",
        title: "Missing details",
        message: "Enter your email address and password to continue.",
        fields: [
          ...(!normalizedEmail ? (["email"] as const) : []),
          ...(!password ? (["password"] as const) : []),
        ],
      });
      return;
    }

    submittingRef.current = true;
    setSubmitting(true);
    setLoginError(null);
    setInvalidFields([]);

    try {
      const guardian = await loginGuardian(
        normalizedEmail,
        password,
        rememberMe && rememberMeSupported,
      );
      navigation.replace("SelectStudent", { guardian });
    } catch (error) {
      showError(describeLoginError(error));
    } finally {
      submittingRef.current = false;
      setSubmitting(false);
    }
  };

  return (
    <ImageBackground
      source={require("../assets/images/loginback.png")}
      style={[styles.background, isDark && darkStyles.container]}
      imageStyle={isDark ? darkStyles.backgroundImage : undefined}
      resizeMode="cover"
    >
      <StatusBar
        barStyle={isDark ? "light-content" : "dark-content"}
      />

      <SafeAreaView style={styles.safeArea}>

        <TouchableOpacity
          style={styles.backButton}
          onPress={goBack}
          accessibilityRole="button"
          accessibilityLabel="Go back"
        >
          <Ionicons
            name="chevron-back"
            size={21}
            color={isDark ? DarkColors.text : "#111"}
          />
        </TouchableOpacity>

        <KeyboardAvoidingView
          style={styles.keyboardAvoider}
          behavior={Platform.OS === "ios" ? "padding" : undefined}
        >
          <ScrollView
            contentContainerStyle={styles.scrollContent}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
          >
            <View style={styles.content}>

              <Image
                source={require("../assets/images/logo.png")}
                style={[styles.logo, isDark && darkStyles.logoBadge]}
                resizeMode="contain"
              />

              <Text style={[styles.title, isDark && darkStyles.text]}>
                Welcome back!
              </Text>

              <Text style={[styles.subtitle, isDark && darkStyles.mutedText]}>
                Log in to your Account
              </Text>

              {justVerified ? (
                <View accessibilityRole="alert" style={[styles.verifiedNotice, isDark && darkStyles.verifiedNotice]}>
                  <Ionicons name="checkmark-circle" size={18} color={isDark ? DarkColors.accent : "#1E8E5A"} />
                  <Text style={[styles.verifiedText, isDark && darkStyles.text]}>
                    Your email is verified. Log in with your email and password to continue.
                  </Text>
                </View>
              ) : null}

              {loginError ? (
                <View
                  accessibilityRole="alert"
                  accessibilityLiveRegion="polite"
                  style={[
                    styles.alertCard,
                    loginError.tone === "warning" && styles.alertCardWarning,
                    isDark && darkStyles.alertCard,
                    isDark && loginError.tone === "warning" && darkStyles.alertCardWarning,
                  ]}
                >
                  <View
                    style={[
                      styles.alertIcon,
                      loginError.tone === "warning" && styles.alertIconWarning,
                      isDark && darkStyles.alertIcon,
                    ]}
                  >
                    <Ionicons
                      name={loginError.icon}
                      size={16}
                      color={loginError.tone === "warning" ? "#B54708" : "#D92D20"}
                    />
                  </View>

                  <View style={styles.alertBody}>
                    <Text
                      style={[
                        styles.alertTitle,
                        loginError.tone === "warning" && styles.alertTitleWarning,
                        isDark && darkStyles.text,
                      ]}
                    >
                      {loginError.title}
                    </Text>
                    <Text
                      style={[
                        styles.alertMessage,
                        loginError.tone === "warning" && styles.alertMessageWarning,
                        isDark && darkStyles.mutedText,
                      ]}
                    >
                      {loginError.message}
                    </Text>
                  </View>

                  <TouchableOpacity
                    style={styles.alertDismiss}
                    onPress={() => setLoginError(null)}
                    accessibilityRole="button"
                    accessibilityLabel="Dismiss message"
                    hitSlop={8}
                  >
                    <Ionicons name="close" size={16} color={isDark ? DarkColors.textMuted : "#98A2B3"} />
                  </TouchableOpacity>
                </View>
              ) : null}

              <Animated.View style={{ transform: [{ translateX: shake }] }}>
                <View
                  style={[
                    styles.inputBox,
                    isDark && darkStyles.card,
                    invalidFields.includes("email") && styles.inputBoxInvalid,
                    invalidFields.includes("email") && isDark && darkStyles.inputBoxInvalid,
                  ]}
                >
                  <Ionicons
                    name="mail"
                    size={12}
                    color={invalidFields.includes("email") ? "#D92D20" : "#A5A5A5"}
                  />

                  <TextInput
                    style={[styles.input, isDark && darkStyles.inputText]}
                    placeholder="Enter your email"
                    placeholderTextColor="#A5A5A5"
                    keyboardType="email-address"
                    autoCapitalize="none"
                    autoCorrect={false}
                    autoComplete="email"
                    textContentType="emailAddress"
                    maxLength={255}
                    value={email}
                    onChangeText={(value) => {
                      setEmail(value);
                      clearFieldError("email");
                    }}
                    editable={!submitting}
                    returnKeyType="next"
                    aria-invalid={invalidFields.includes("email")}
                  />
                </View>

                <View
                  style={[
                    styles.inputBox,
                    isDark && darkStyles.card,
                    invalidFields.includes("password") && styles.inputBoxInvalid,
                    invalidFields.includes("password") && isDark && darkStyles.inputBoxInvalid,
                  ]}
                >
                  <Ionicons
                    name="lock-closed-outline"
                    size={13}
                    color={invalidFields.includes("password") ? "#D92D20" : "#A5A5A5"}
                  />

                  <TextInput
                    style={[styles.input, isDark && darkStyles.inputText]}
                    placeholder="Enter your password"
                    placeholderTextColor="#A5A5A5"
                    secureTextEntry={!passwordVisible}
                    autoCapitalize="none"
                    autoCorrect={false}
                    autoComplete="current-password"
                    textContentType="password"
                    maxLength={1024}
                    value={password}
                    onChangeText={(value) => {
                      setPassword(value);
                      clearFieldError("password");
                    }}
                    editable={!submitting}
                    returnKeyType="go"
                    onSubmitEditing={handleLogin}
                    aria-invalid={invalidFields.includes("password")}
                  />

                  <TouchableOpacity
                    disabled={submitting}
                    accessibilityRole="button"
                    accessibilityLabel={passwordVisible ? "Hide password" : "Show password"}
                    onPress={() =>
                      setPasswordVisible(!passwordVisible)
                    }
                  >
                    <Ionicons
                      name={
                        passwordVisible
                          ? "eye-outline"
                          : "eye-off-outline"
                      }
                      size={13}
                      color="#A5A5A5"
                    />
                  </TouchableOpacity>
                </View>

                <View style={styles.options}>

                  {rememberMeSupported ? (
                    <TouchableOpacity
                      style={styles.remember}
                      disabled={submitting}
                      accessibilityRole="checkbox"
                      accessibilityState={{ checked: rememberMe, disabled: submitting }}
                      onPress={() =>
                        setRememberMe(!rememberMe)
                      }
                    >
                      <View
                        style={[
                          styles.checkbox,
                          rememberMe && styles.checked,
                        ]}
                      >
                        {rememberMe && (
                          <Ionicons
                            name="checkmark"
                            size={8}
                            color="#FFFFFF"
                          />
                        )}
                      </View>

                      <Text style={styles.smallText}>
                        Remember me
                      </Text>
                    </TouchableOpacity>
                  ) : (
                    <View />
                  )}

                  <TouchableOpacity disabled={submitting}>
                    <Text style={styles.forgot}>
                      Forgot password?
                    </Text>
                  </TouchableOpacity>

                </View>
              </Animated.View>

              <TouchableOpacity
                style={[styles.loginButton, submitting && styles.loginButtonDisabled]}
                activeOpacity={0.85}
                disabled={submitting}
                accessibilityRole="button"
                onPress={handleLogin}
              >
                {submitting ? (
                  <View style={styles.loadingContent}>
                    <ActivityIndicator color="#FFFFFF" size="small" />
                    <Text style={styles.loginText}>Signing in...</Text>
                  </View>
                ) : (
                  <Text style={styles.loginText}>Login</Text>
                )}
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.privacyLink}
                accessibilityRole="link"
                onPress={() => void openPrivacyPolicy()}
              >
                <Text style={[styles.privacyText, isDark && darkStyles.mutedText]}>Privacy Policy</Text>
              </TouchableOpacity>

            </View>
          </ScrollView>
        </KeyboardAvoidingView>
      </SafeAreaView>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
  },

  safeArea: {
    flex: 1,
  },

  keyboardAvoider: {
    flex: 1,
  },

  scrollContent: {
    flexGrow: 1,
    justifyContent: "center",
    paddingVertical: 48,
  },

  content: {
    width: "76%",
    maxWidth: Layout.maxFormWidth,
    alignSelf: "center",
  },

  backButton: {
    position: "absolute",
    top: 8,
    left: 18,
    zIndex: 10,
    padding: 5,
  },

  logo: {
    width: 150,
    height: 100,
    alignSelf: "center",
    marginBottom: 30,
  },

  title: {
    fontSize: 21,
    fontWeight: "800",
    color: "#111",
  },

  subtitle: {
    fontSize: 13,
    color: "#555",
    marginBottom: 20,
  },

  verifiedNotice: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 8,
    backgroundColor: "#E7F6EE",
    borderRadius: 14,
    padding: 12,
    marginTop: -6,
    marginBottom: 16,
  },

  verifiedText: {
    flex: 1,
    fontSize: 12,
    lineHeight: 17,
    color: "#1D5C3F",
    fontWeight: "600",
  },

  inputBox: {
    height: 46,
    backgroundColor: "#FFF",
    borderRadius: 25,
    borderWidth: 1,
    borderColor: "#E1E1E1",
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 12,
    marginBottom: 12,
    elevation: 2,
  },

  input: {
    flex: 1,
    fontSize: 13,
    paddingHorizontal: 8,
  },

  options: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 20,
  },

  remember: {
    flexDirection: "row",
    alignItems: "center",
  },

  checkbox: {
    width: 12,
    height: 12,
    borderWidth: 1.5,
    borderColor: "#0BA8BD",
    borderRadius: 2,
    alignItems: "center",
    justifyContent: "center",
    marginRight: 5,
  },

  checked: {
    backgroundColor: "#0BA8BD",
  },

  smallText: {
    fontSize: 10,
    color: "#AAA",
  },

  forgot: {
    fontSize: 10,
    color: "#0BA8BD",
    fontWeight: "600",
  },

  inputBoxInvalid: {
    borderColor: "#F04438",
    borderWidth: 1.5,
  },

  alertCard: {
    flexDirection: "row",
    alignItems: "flex-start",
    backgroundColor: "#FEF3F2",
    borderWidth: 1,
    borderColor: "#FECDCA",
    borderLeftWidth: 4,
    borderLeftColor: "#F04438",
    borderRadius: 14,
    paddingVertical: 12,
    paddingLeft: 12,
    paddingRight: 8,
    marginTop: -6,
    marginBottom: 16,
  },

  alertCardWarning: {
    backgroundColor: "#FFFAEB",
    borderColor: "#FEDF89",
    borderLeftColor: "#F79009",
  },

  alertIcon: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: "#FEE4E2",
    alignItems: "center",
    justifyContent: "center",
    marginRight: 10,
  },

  alertIconWarning: {
    backgroundColor: "#FEF0C7",
  },

  alertBody: {
    flex: 1,
    paddingTop: 1,
  },

  alertTitle: {
    fontSize: 13,
    fontWeight: "800",
    color: "#912018",
  },

  alertTitleWarning: {
    color: "#93370D",
  },

  alertMessage: {
    fontSize: 12,
    lineHeight: 17,
    color: "#B42318",
    marginTop: 2,
  },

  alertMessageWarning: {
    color: "#B54708",
  },

  alertDismiss: {
    padding: 4,
    marginLeft: 4,
  },

  loginButton: {
    height: 47,
    borderRadius: 25,
    backgroundColor: "#0BA8BD",
    alignItems: "center",
    justifyContent: "center",
    elevation: 3,
  },

  loginButtonDisabled: {
    opacity: 0.7,
  },

  loadingContent: {
    flexDirection: "row",
    alignItems: "center",
    gap: 9,
  },

  loginText: {
    color: "#FFF",
    fontSize: 15,
    fontWeight: "800",
  },

  privacyLink: {
    alignSelf: "center",
    marginTop: 16,
    padding: 6,
  },

  privacyText: {
    fontSize: 11,
    color: "#777",
    textDecorationLine: "underline",
  },
});

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: DarkColors.background,
  },

  backgroundImage: {
    opacity: 0.15,
  },

  logoBadge: {
    backgroundColor: "rgba(255, 255, 255, 0.92)",
    borderRadius: 20,
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

  inputText: {
    color: DarkColors.text,
  },

  alertCard: {
    backgroundColor: "rgba(240, 68, 56, 0.12)",
    borderColor: "rgba(240, 68, 56, 0.35)",
    borderLeftColor: DarkColors.danger,
  },

  alertCardWarning: {
    backgroundColor: "rgba(247, 144, 9, 0.12)",
    borderColor: "rgba(247, 144, 9, 0.35)",
    borderLeftColor: "#F5C76B",
  },

  alertIcon: {
    backgroundColor: "rgba(255, 255, 255, 0.9)",
  },

  inputBoxInvalid: {
    borderColor: DarkColors.danger,
  },

  verifiedNotice: {
    backgroundColor: DarkColors.selected,
  },
});
