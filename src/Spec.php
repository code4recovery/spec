<?php

namespace Code4Recovery;

class Spec
{
    private static array $languages = [
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'ja' => '日本語',
        'sv' => 'Svenska',
    ];

    /**
     * Get all languages.
     *
     * Retrieves and returns all languages with language code and expanded language translation that is stored
     * in the type repository.
     *
     * @return string[]
     */
    public static function getLanguages(): array
    {
        return self::$languages;
    }

    /**
     * Get all types.
     *
     * Retrieves and returns all types stored in the type repository.
     *
     * @return object The array of all types.
     */
    public static function getAllTypes (): object
    {
        $types = include 'types.php';
        return $types;
    }

    /**
     * Returns an array of types translated into the specified language.
     *
     * @param string $language The language code to retrieve the translations
     *     for.
     *
     * @return array An array of type translations in the specified language.
     */
    public static function getTypesByLanguage(string $language): array
    {
        $typesByLanguage = [];
        $types = self::getAllTypes();
        foreach ($types as $typeKey => $typeTranslations) {
            if (isset($typeTranslations->$language)) {
                $typesByLanguage[$typeKey] = $typeTranslations->$language;
            }
        }
        return $typesByLanguage;
    }
}