import {
  isMeetingType,
  isSupportedLanguage,
  getTypesForLanguage,
} from "../index";

describe("isMeetingType", () => {
  it("should return true for supported meeting types", () => {
    expect(isMeetingType("T")).toBe(true);
  });

  it("should return false for unsupported meeting types", () => {
    expect(isMeetingType("FOO")).toBe(false);
  });

  it("should return true for supported language", () => {
    expect(isSupportedLanguage("en")).toBe(true);
  });

  it("should return false for unsupported language", () => {
    expect(isSupportedLanguage("FOO")).toBe(false);
  });

  it("should return supported languages", () => {
    expect(getTypesForLanguage("en")).toBeTruthy();
  });
});
