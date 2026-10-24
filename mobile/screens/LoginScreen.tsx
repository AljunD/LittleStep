import React, { useEffect, useRef, useState } from "react";
import {
  ActivityIndicator,
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
import { DarkColors, Layout } from "../constants/theme";
import { useAppTheme } from "../hooks/use-app-theme";
import {
  getGuardianApiErrorMessage,
  isRememberMeSupported,
  loginGuardian,
} from "../services/guardianApi";
import { openPrivacyPolicy } from "../services/privacy-policy";

export default function LoginScreen({ navigation, route }: any) {
  const { isDark } = useAppTheme();
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [rememberMeSupported, setRememberMeSupported] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(
    route?.params?.initialError ?? null,
  );
  const submittingRef = useRef(false);
  const justVerified = route?.params?.verified === true && !errorMessage;

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
      setErrorMessage("Enter your email address and password.");
      return;
    }

    submittingRef.current = true;
    setSubmitting(true);
    setErrorMessage(null);

    try {
      const guardian = await loginGuardian(
        normalizedEmail,
        password,
        rememberMe && rememberMeSupported,
      );
      navigation.replace("SelectStudent", { guardian });
    } catch (error) {
      setErrorMessage(getGuardianApiErrorMessage(error));
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

              <View style={[styles.inputBox, isDark && darkStyles.card]}>
                <Ionicons
                  name="mail"
                  size={12}
                  color="#A5A5A5"
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
                  onChangeText={setEmail}
                  editable={!submitting}
                  returnKeyType="next"
                />
              </View>

              <View style={[styles.inputBox, isDark && darkStyles.card]}>
                <Ionicons
                  name="lock-closed-outline"
                  size={13}
                  color="#A5A5A5"
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
                  onChangeText={setPassword}
                  editable={!submitting}
                  returnKeyType="go"
                  onSubmitEditing={handleLogin}
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

              {errorMessage ? (
                <Text accessibilityRole="alert" style={[styles.errorMessage, isDark && darkStyles.danger]}>
                  {errorMessage}
                </Text>
              ) : null}

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

  errorMessage: {
    color: "#B42318",
    fontSize: 12,
    lineHeight: 17,
    marginTop: -9,
    marginBottom: 13,
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

  danger: {
    color: DarkColors.danger,
  },

  verifiedNotice: {
    backgroundColor: DarkColors.selected,
  },
});
