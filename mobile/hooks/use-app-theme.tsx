import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";
import { Platform, useColorScheme } from "react-native";
import * as SecureStore from "expo-secure-store";

export type DisplayMode = "light" | "dark" | "system";

const DISPLAY_MODE_KEY = "littlestep.display-mode";
const DEFAULT_DISPLAY_MODE: DisplayMode = "light";

type AppTheme = {
  mode: DisplayMode;
  setMode: (mode: DisplayMode) => void;
  isDark: boolean;
};

const AppThemeContext = createContext<AppTheme>({
  mode: DEFAULT_DISPLAY_MODE,
  setMode: () => {},
  isDark: false,
});

function isDisplayMode(value: unknown): value is DisplayMode {
  return value === "light" || value === "dark" || value === "system";
}

async function readStoredDisplayMode(): Promise<DisplayMode> {
  try {
    const stored = Platform.OS === "web"
      ? globalThis.localStorage?.getItem(DISPLAY_MODE_KEY)
      : await SecureStore.getItemAsync(DISPLAY_MODE_KEY);

    return isDisplayMode(stored) ? stored : DEFAULT_DISPLAY_MODE;
  } catch {
    return DEFAULT_DISPLAY_MODE;
  }
}

async function storeDisplayMode(mode: DisplayMode): Promise<void> {
  try {
    if (Platform.OS === "web") {
      globalThis.localStorage?.setItem(DISPLAY_MODE_KEY, mode);
    } else {
      await SecureStore.setItemAsync(DISPLAY_MODE_KEY, mode);
    }
  } catch {
    // Not critical: the choice still applies for this session.
  }
}

export function AppThemeProvider({ children }: { children: React.ReactNode }) {
  const systemScheme = useColorScheme();
  const [mode, setModeState] = useState<DisplayMode | null>(null);

  useEffect(() => {
    let isMounted = true;

    void readStoredDisplayMode().then((storedMode) => {
      if (isMounted) {
        setModeState(storedMode);
      }
    });

    return () => {
      isMounted = false;
    };
  }, []);

  const setMode = useCallback((nextMode: DisplayMode) => {
    setModeState(nextMode);
    void storeDisplayMode(nextMode);
  }, []);

  const value = useMemo(() => {
    const currentMode = mode ?? DEFAULT_DISPLAY_MODE;
    const scheme = currentMode === "system" ? systemScheme : currentMode;

    return { mode: currentMode, setMode, isDark: scheme === "dark" };
  }, [mode, setMode, systemScheme]);

  // Wait for the saved preference so screens don't flash the wrong theme.
  if (mode === null) {
    return null;
  }

  return (
    <AppThemeContext.Provider value={value}>
      {children}
    </AppThemeContext.Provider>
  );
}

export function useAppTheme(): AppTheme {
  return useContext(AppThemeContext);
}
