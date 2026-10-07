import React from "react";
import {
  View,
  StyleSheet,
  ImageBackground,
  Image,
  TouchableOpacity,
  SafeAreaView,
  Dimensions,
  StatusBar,
  Text,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { StackNavigationProp } from "@react-navigation/stack";

const { width, height } = Dimensions.get("window");

type RootStackParamList = {
  Welcome: undefined;
  Login: undefined;
};

type WelcomeScreenNavigationProp = StackNavigationProp<
  RootStackParamList,
  "Welcome"
>;

type Props = {
  navigation: WelcomeScreenNavigationProp;
};

export default function WelcomeScreen({ navigation }: Props) {
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
          <Image
            source={require("../assets/images/logo.png")}
            style={styles.logo}
            resizeMode="contain"
          />

          <View style={styles.bottomContent}>
            <TouchableOpacity
              style={styles.button}
              activeOpacity={0.85}
              onPress={() => navigation.navigate("Login")}
            >
              <Text style={styles.buttonText}>Get Started</Text>

              <Ionicons
                name="arrow-forward"
                size={20}
                color="#FFFFFF"
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
  },

  safeArea: {
    flex: 1,
    alignItems: "center",
    justifyContent: "space-between",
  },

  logo: {
    width: width * 0.55,
    height: height * 0.16,
    marginTop: height * 0.06,
  },

  bottomContent: {
    width: "100%",
    alignItems: "center",
    paddingHorizontal: 28,
    paddingBottom: height * 0.045,
  },

  button: {
    width: "88%",
    height: 52,
    borderRadius: 26,
    backgroundColor: "#08A9C1",
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
  },

  buttonText: {
    color: "#FFFFFF",
    fontSize: 16,
    fontWeight: "700",
  },
});