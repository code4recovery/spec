import { languages } from "./languages";
import { types } from "./types";

export type Language = (typeof languages)[number];
export type MeetingType = keyof typeof types;

export function isMeetingType(type: string): type is MeetingType {
  return type in types;
}

export function isSupportedLanguage(language: string): language is Language {
  return language in languages;
}

export function getTypesForLanguage(language: string) {
  const typesForLanguage: Record<MeetingType, string> = {} as any;
  for (const type in types) {
    if (isMeetingType(type) && isSupportedLanguage(language)) {
      typesForLanguage[type] = types[type][language];
    }
  }
  return typesForLanguage;
}
