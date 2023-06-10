<?php

/**
 * Rebuilds the types table in the README file and creates a typescript file
 *
 * Reads a spec.json file and creates a new types table.
 * Leaves the remainder of the file as-is.
 *
 * @author Code for Recovery <noreply@code4recovery.org>
 *
 * @since 1.0.0
 */

namespace Code4Recovery;

class Build
{
    /**
     * The path to the json file containing spec data.
     * @var string
     */
    public string $specFile = './data/types.json';

    /**
     * The path to the readme.md that will be created.
     * @var string
     */
    public string $readmeFile = './README.md';

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
     * Constructor.
     *
     * @param $specFile: Path to spec json file, relative to project root.
     * @param $readmeFile: Path to readme file, relative to project root.
     */
    public function __construct()
    {
        $this->writeReadme($this->createTable());
        $this->writeTypeScript();
    }

    /**
     * Builds the types table.
     *
     * @return string Markdown for table
     */
    private function createTable(): string
    {
        return implode(PHP_EOL, [
            $this->tableDelimiterTop,
            $this->createTableHeader(),
            $this->createTableRows(),
            $this->tableDelimiterBottom,
        ]);
    }

    /**
     * Creates the table header markup.
     *
     * @return string
     */
    private function createTableHeader(): string
    {
        // Columns
        $headerColumns = array_merge(['Code'], array_values($this->languages));

        // Dashes
        $rows = [$headerColumns, array_fill(0, count($headerColumns), '---')];

        // Build final markup with line breaks
        return implode(PHP_EOL, array_map([$this, 'createTableRow'], $rows));
    }

    /**
     * Creates a table row separated (and surrounded) by pipes.
     *
     * @param array $row
     *
     * @return string
     */
    private function createTableRow($row): string
    {
        return implode('|', array_merge([''], $row, ['']));
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
        $languages = array_keys($this->languages);

        // Loop through types from spec
        foreach ($specJson['types'] as $key => $value) {
            // Begin row output. Empty the $columns array each time.
            $specColumns = ['`' . $key . '`'];

            // Loop through languages
            foreach ($languages as $languageKey) {

                // Add translation to columns
                $specColumns[] = array_key_exists($languageKey, $value) ? $value[$languageKey] : '-';
            }

            // Add empty array values so our row is surrounded by pipes
            $specRows[] = $this->createTableRow($specColumns);
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
    private function writeReadme(string $tableContent): void
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

    /**
     * Write the typescript file
     *
     * @return void
     */
    private function writeTypeScript(): void
    {
        $json = file_get_contents($this->specFile);
        file_put_contents('./src/index.ts', 'export const types = ' . $json . ';');
    }
}

new Build();
