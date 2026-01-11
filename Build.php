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
     * @var array
     */
    private array $tables = [
        [
            'file' => './data/types.json',
            'delimiterTop' => '<!-- Types -->',
            'delimiterBottom' => '<!-- End Types -->',
        ],
        [
            'file' => './data/proposed-new.json',
            'delimiterTop' => '<!-- Proposed Types -->',
            'delimiterBottom' => '<!-- End Proposed Types -->',

        ],
        [
            'file' => './data/proposed-change.json',
            'delimiterTop' => '<!-- Proposed Changed Types -->',
            'delimiterBottom' => '<!-- End Proposed Changed Types -->',

        ]
    ];

    /**
     * The path to the readme.md that will be created.
     * @var string
     */
    private string $readmeFile = './README.md';

    /**
     * Languages used in the types table
     * @var array
     */
    private array $languages = [
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'ja' => '日本語',
        'nl' => 'Nederlands',
        'pt' => 'Português',
        'sk' => 'Slovenčina',
        'sv' => 'Svenska',
        'th' => 'ไทย',
    ];

    /**
     * Constructor.
     *
     * @param $specFile: Path to spec json file, relative to project root.
     * @param $readmeFile: Path to readme file, relative to project root.
     */
    public function __construct()
    {
        foreach ($this->tables as $table) {
            $this->writeReadme($table);
        }
        $this->writeTypeScript();
        $this->writePHPObject();
    }

    /**
     * Builds a types table.
     *
     * @return string Markdown for table
     */
    private function createTable($table): string
    {
        return implode(PHP_EOL, [
            $table['delimiterTop'],
            $this->createTableHeader(),
            $this->createTableRows($table['file']),
            $table['delimiterBottom'],
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
    private function createTableRows($specFile): string
    {
        // Init empty array
        $specRows = [];

        // Get spec data & language codes
        $specJson = json_decode(file_get_contents($specFile), true);
        $languages = array_keys($this->languages);

        // Loop through types from spec
        foreach ($specJson as $key => $value) {
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
    private function writeReadme(array $table): void
    {
        // Get the current readme contents
        $readmeContents = file_get_contents($this->readmeFile);

        // Get the new table content
        $tableContent = $this->createTable($table);

        // Replace existing table
        $result = preg_replace('#(' . preg_quote($table['delimiterTop']) . ')(.*)(' . preg_quote($table['delimiterBottom']) . ')#siU', $tableContent, $readmeContents);

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
        file_put_contents('./src/languages.ts', 'export const languages = ' . json_encode(array_keys($this->languages)) . ' as const;');
        $json = file_get_contents('./data/types.json');
        file_put_contents('./src/types.ts', 'export const types = ' . $json . ';');
    }

    private function writePHPObject(): void
    {
        // Fetch the JSON file
        $jsonData = file_get_contents('./data/types.json');
        if ($jsonData === false) {
            die("Error fetching JSON file.");
        }

        // Decode JSON data
        $dataObject = json_decode($jsonData);
        if ($dataObject === null) {
            die("Error decoding JSON data.");
        }

        // Prepare the PHP content
        $phpContent = "<?php\n\n" . 'return ' . var_export($dataObject, true) . ";\n";

        // Write to types.php in the src directory
        $fileWritten = file_put_contents(__DIR__ . '/src/types.php', $phpContent);

        if ($fileWritten === false) {
            die("Error writing to types.php.");
        }

        echo "types.php successfully written.\n";
    }
}

new Build();
