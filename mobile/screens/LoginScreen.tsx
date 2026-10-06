import React, { useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  ImageBackground,
  Image,
  TextInput,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";

export default function LoginScreen({ navigation }: any) {
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);

  return (
    <ImageBackground
      source={require("../assets/images/loginback.png")}
      style={styles.background}
      resizeMode="cover"
    >
      <StatusBar
        translucent
        backgroundColor="transparent"
        barStyle="dark-content"
      />

      <SafeAreaView style={styles.safeArea}>

        {/* BACK */}
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons
            name="chevron-back"
            size={21}
            color="#111"
          />
        </TouchableOpacity>

        <View style={styles.content}>

          {/* LOGO */}
          <Image
            source={require("../assets/images/logo.png")}
            style={styles.logo}
            resizeMode="contain"
          />

          {/* TITLE */}
          <Text style={styles.title}>
            Welcome back!
          </Text>

          <Text style={styles.subtitle}>
            Log in to your Account
          </Text>

          {/* EMAIL */}
          <View style={styles.inputBox}>
            <Ionicons
              name="mail"
              size={12}
              color="#A5A5A5"
            />

            <TextInput
              style={styles.input}
              placeholder="Enter your email"
              placeholderTextColor="#A5A5A5"
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
            />
          </View>

          {/* PASSWORD */}
          <View style={styles.inputBox}>
            <Ionicons
              name="lock-closed-outline"
              size={13}
              color="#A5A5A5"
            />

            <TextInput
              style={styles.input}
              placeholder="Enter your password"
              placeholderTextColor="#A5A5A5"
              secureTextEntry={!passwordVisible}
              autoCapitalize="none"
            />

            <TouchableOpacity
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

          {/* OPTIONS */}
          <View style={styles.options}>

            {/* REMEMBER ME */}
            <TouchableOpacity
              style={styles.remember}
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

            {/* FORGOT PASSWORD */}
            <TouchableOpacity>
              <Text style={styles.forgot}>
                Forgot password?
              </Text>
            </TouchableOpacity>

          </View>

          {/* LOGIN */}
          <TouchableOpacity
            style={styles.loginButton}
            activeOpacity={0.85}
            onPress={() =>
              navigation.navigate("SelectStudent")
            }
          >
            <Text style={styles.loginText}>
              Login
            </Text>
          </TouchableOpacity>

        </View>
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

  content: {
    flex: 1,
    justifyContent: "center",
    marginHorizontal: "12%",
  },

  /* BACK BUTTON */

  backButton: {
    position: "absolute",
    top: 8,
    left: 18,
    zIndex: 10,
    padding: 5,
  },

  /* LOGO */

  logo: {
    width: 150,
    height: 100,
    alignSelf: "center",
    marginBottom: 30,
  },

  /* TITLE */

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

  /* INPUT */

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

  /* OPTIONS */

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

  /* LOGIN BUTTON */

  loginButton: {
    height: 47,
    borderRadius: 25,
    backgroundColor: "#0BA8BD",
    alignItems: "center",
    justifyContent: "center",
    elevation: 3,
  },

  loginText: {
    color: "#FFF",
    fontSize: 15,
    fontWeight: "800",
  },
});