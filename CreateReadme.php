<?php
/**
 * Updates the README.md
 *
 * Reads a spec.json file and creates a new types table.
 * Leaves the remainder of the file as-is.
 *
 * ALL CHRACTER WIDTHS EXCLUDE SPACES BEFORE & AFTER PIPES.
 * THESE NUMBERS ARE EQUAL TO THE NUMBER OF DASHES IN THE HEADER ROW
 *
 * @author Anthony B <anthony.baggett@gmail.com>
 *
 * @since 1.0.0
 */

namespace Code4Recovery;

class CreateReadme {
    public string $specFile, $readmeFile, $tableContent;

    /**
     * Number of characters the Code table column uses.
     * @var int
     */
    private int $codeCharWidth = 7;

    /**
     * Number of characters the English table column uses
     * @var int
     */
    private int $enCharWidth = 30;

    /**
     * Number of characters the Español table column uses.
     * @var int
     */
    private int $esCharWidth = 36;

    /**
     * Number of characters the Français table column uses.
     * @var int
     */
    private int $frCharWidth = 44;

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
     * Table header markdown.
     * @var string
     */
    private string $specTableHeader = <<<'EOT'
| Code    | English                        | Español                              | Français                                     |
| ------- | ------------------------------ | ------------------------------------ | -------------------------------------------- |
EOT;

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

        // Processing
        $this->create_rows();
        $this->write();
    }

    /**
     * Gets the contents of the spec file and creates the table rows markup.
     *
     * @return void
     */
    private function create_rows(): void
    {
        // Init empty array
        $specRows = [];
        $specJson = json_decode(file_get_contents($this->specFile));
        // Replace table with contents of spec.json
        foreach ($specJson->types as $key => $value) {
            // Space padding (key Padding removese 2 extra spaces due to backticks)
            $keyPadding       = $this->codeCharWidth - (mb_strlen( trim( $key)) + 2 );
            $enPadding        = $this->enCharWidth - mb_strlen(trim( $value->en));
            $esPadding        = $this->esCharWidth - mb_strlen(trim( $value->es));
            $frPadding        = $this->frCharWidth - mb_strlen(trim( $value->fr));
            $specRows[] = '| `' . trim( $key ) . '`' . str_repeat(' ', $keyPadding) . ' | ' . trim( $value->en ) . str_repeat( ' ', $enPadding ) . ' | ' . trim( $value->es ) . str_repeat( ' ', $esPadding ) . ' | ' . trim( $value->fr ) . str_repeat( ' ', $frPadding ) . ' |';
        }
        $this->tableContent = $this->tableDelimiterTop . PHP_EOL;
        $this->tableContent .= $this->specTableHeader . PHP_EOL;
        $this->tableContent .= implode(PHP_EOL, $specRows) . PHP_EOL;
        $this->tableContent .= $this->tableDelimiterBottom;
    }

    /**
     * Get the contents of the readme, replace the types table, and re-write.
     * @return void
     */
    private function write(): void
    {
        // Get the current readme contents
        $readmeContents = file_get_contents( $this->readmeFile );
        // Replace existing table
        $result = preg_replace( '#(' . preg_quote( $this->tableDelimiterTop ) . ')(.*)(' . preg_quote( $this->tableDelimiterBottom ) . ')#siU', $this->tableContent, $readmeContents );
        // Write new file
        $readmeHandle = fopen( $this->readmeFile, "w" ) or die( "Unable to open file!" );
        fwrite( $readmeHandle, $result );
        fclose( $readmeHandle );
    }
}

new CreateReadme( './spec.json', 'README.md' );
