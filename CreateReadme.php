<?php
/**
 * Updates the README.md
 *
 * Reads a spec.json file and creates a new types table.
 * Leaves the remainder of the file as-is.
 *
 * @author Anthony B <anthony.baggett@gmail.com>
 *
 * @since 1.0.0
 */

namespace Code4Recovery;

class CreateReadme {
    /**
     * The path to the json file containing spec data.
     * @var string
     */
    public string $specFile;

    /**
     * The path to the readme.md that will be created.
     * @var string
     */
    public string $readmeFile;

    /**
     * Replacement begins on the line after the appearance of this string.
     * @var string
     */
    private string $tableDelimiterTop = '<!-- Types -->';

    /**
     * Replacement ends on the line before the appearance of this string.
     * @var string
     */
    private string $tableDelimiterBottom = '<!-- End Types -->';

    /**
     * Languages used in the types table
     * @var array
     */
    private array $languages = [
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'ja' => '日本語',
        'sv' => 'Svenska'
    ];

    /**
     * Used to track all languages found in the spec file to avoid empty
     * language columns
     * @var array
     */
    private array $languagesUsed = [];

    /**
     * Constructor.
     *
     * @param $specFile: Path to spec json file, relative to project root.
     * @param $readmeFile: Path to readme file, relative to project root.
     */
    public function __construct(string $specFile, string $readmeFile) {
        // Set variables
        $this->specFile   = $specFile;
        $this->readmeFile = $readmeFile;
        // Process
        $this->writeFile($this->createTable());
    }

    /**
     * Builds the types table.
     *
     * @return string Markdown for table
     */
    private function createTable(): string
    {
        $content[0] = $this->tableDelimiterTop;
        // Rows must be created before header, so we know what language columns to create
        $content[2] = $this->createTableRows();
        $content[1] = $this->createTableHeader();
        $content[3] = $this->tableDelimiterBottom;
        ksort($content);
        return implode(PHP_EOL, $content);
    }

    /**
     * Creates the table header markup.
     *
     * @return string
     */
    private function createTableHeader(): string
    {
        // Start columns & header dashes output
        $headerColumns[] = '| Code';
        $headerDashes[] = '| ---';
        // Loop through available languages, comparing them to the languages
        // available in the spec data and create columns
        foreach ($this->languages as $languageCode => $language) {
            if (in_array($languageCode, $this->languagesUsed)) {
                // Create columns
                $headerColumns[] = trim($language);
                $headerDashes[] = '---';
            }
        }
        // Add an empty array value so our implode will add an extra ' | ' at the end
        $markupArray['cols'] = implode(' | ', array_merge($headerColumns, ['']));
        $markupArray['dashes'] = implode(' | ', array_merge($headerDashes, ['']));
        // Build final markup with line breaks
        return implode(PHP_EOL, $markupArray);
    }

    /**
     * Gets the contents of the spec file and creates the table rows markup.
     *
     * @return string
     */
    private function createTableRows(): string
    {
        // Init empty array
        $specRows = [];
        // Get spec data & language codes
        $specJson = json_decode(file_get_contents($this->specFile), true);
        $availableLanguages = array_keys($this->languages);
        // Loop through tupes from spec
        foreach ($specJson['types'] as $key => $value) {
            // Begin row output. Empty the $columns array each time.
            $specColumns = [];
            $specColumns[] = '| `' . trim($key) . '`';
            // Loop through translated values
            foreach ($value as $languageKey => $translatedText) {
                // Only display values for available languages
                if (in_array($languageKey, $availableLanguages)) {
                    // Add the language key to an array for use in creating the header
                    if (!in_array($languageKey, $this->languagesUsed)) {
                        $this->languagesUsed[] = trim($languageKey);
                    }
                    // Add translation to columns
                    $specColumns[] = trim($translatedText);
                }
            }
            // Add empty array value so implode will create an extra "|" at then end of the markup
            $specRows[] = implode(' | ', array_merge($specColumns, ['']));
        }
        return implode(PHP_EOL, $specRows);
    }

    /**
     * Get the contents of the readme, replace the types table, and re-write.
     *
     * @param string $tableContent The markdown to write to the file
     *
     * @return void
     */
    private function writeFile(string $tableContent): void
    {
        // Get the current readme contents
        $readmeContents = file_get_contents($this->readmeFile);
        // Replace existing table
        $result = preg_replace('#(' . preg_quote($this->tableDelimiterTop) . ')(.*)(' . preg_quote($this->tableDelimiterBottom) . ')#siU', $tableContent, $readmeContents);
        // Write new file
        $readmeHandle = fopen($this->readmeFile, "w") or die("Unable to open file!");
        fwrite($readmeHandle, $result);
        fclose($readmeHandle);
    }
}

new CreateReadme('./data/types.json', 'README.md');
