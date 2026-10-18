import type { ComponentProps } from "react";
import type { Ionicons } from "@expo/vector-icons";

export type EccdDomainKey =
  | "gross_motor"
  | "fine_motor"
  | "self_help"
  | "receptive_language"
  | "expressive_language"
  | "cognitive"
  | "social_emotional";

export type EccdDomain = {
  key: EccdDomainKey;
  label: string;
  title: string;
  icon: ComponentProps<typeof Ionicons>["name"];
  color: string;
};

export const ECCD_DOMAINS: EccdDomain[] = [
  {
    key: "gross_motor",
    label: "Gross Motor",
    title: "Physical Movement",
    icon: "body-outline",
    color: "#F9C96B",
  },
  {
    key: "fine_motor",
    label: "Fine Motor",
    title: "Hand & Finger Skills",
    icon: "hand-left-outline",
    color: "#A8D8B9",
  },
  {
    key: "self_help",
    label: "Self-Help",
    title: "Self-Care & Independence",
    icon: "shirt-outline",
    color: "#D9CCF5",
  },
  {
    key: "receptive_language",
    label: "Receptive Language",
    title: "Understanding & Listening",
    icon: "ear-outline",
    color: "#B9C9F8",
  },
  {
    key: "expressive_language",
    label: "Expressive Language",
    title: "Speaking & Communication",
    icon: "chatbubble-ellipses-outline",
    color: "#F3B9C7",
  },
  {
    key: "cognitive",
    label: "Cognitive",
    title: "Thinking & Learning",
    icon: "bulb-outline",
    color: "#FFC09F",
  },
  {
    key: "social_emotional",
    label: "Social-Emotional",
    title: "Social & Emotional Skills",
    icon: "people-outline",
    color: "#DCEBFF",
  },
];

export function getEccdDomain(key: unknown): EccdDomain | undefined {
  return ECCD_DOMAINS.find((domain) => domain.key === key);
}
