<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Commands;

use EmanueleCoppola\PHPeg\App\Support\AstJsonExporter;
use EmanueleCoppola\PHPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPeg\Error\ParseError;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;
use EmanueleCoppola\PHPeg\Loader\Peg\PegGrammarLoader;
use LaravelZero\Framework\Commands\Command;
use Throwable;

/**
 * Parses a source file with a file-based grammar and exports JSON output.
 */
class ParseCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'parse
                            {--grammar= : Path to the grammar file}
                            {--i|input= : Path to the source file to parse}
                            {--o|output= : Write the JSON payload to a file}
                            {--grammar-format=auto : Grammar format: auto, peg, or cleanpeg}
                            {--start-rule= : Override the grammar start rule}
                            {--query= : AST selector used to filter the output nodes}
                            {--json-style=full : JSON style: full or simple}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Parse a source file and export matching AST nodes as JSON.';

    /**
     * Executes the parse command.
     */
    public function handle(): int
    {
        try {
            $grammarPath = $this->normalizeRequiredPath($this->option('grammar'), 'grammar');
            $inputPath = $this->normalizeRequiredPath($this->option('input'), 'input');
            $outputPath = $this->normalizeOptionalString($this->option('output'));
            $grammarFormat = $this->normalizeGrammarFormat((string) $this->option('grammar-format'));
            $startRule = $this->normalizeOptionalString($this->option('start-rule'));
            $query = $this->normalizeOptionalString($this->option('query'));
            $jsonStyle = $this->normalizeJsonStyle((string) $this->option('json-style'));

            $grammar = $this->loadGrammar($grammarPath, $grammarFormat, $startRule);
            $source = $this->loadFile($inputPath);
            $result = $grammar->parse($source, $startRule);

            if (!$result->isSuccess() || $result->node() === null) {
                $this->outputFailure($grammarPath, $inputPath, $grammarFormat, $startRule, $result->error());

                return self::FAILURE;
            }

            $document = new ParsedDocument($grammar, $source, $result->node());
            $nodes = $query !== null ? $document->query($query)->all() : [$document->root()];
            $payload = $this->buildPayload(
                jsonStyle: $jsonStyle,
                grammarPath: $grammarPath,
                grammarFormat: $grammarFormat,
                startRule: $grammar->startRule(),
                inputPath: $inputPath,
                sourceLength: strlen($source),
                finalOffset: $result->finalOffset(),
                matchedText: $result->matchedText(),
                query: $query,
                nodes: $nodes,
            );
            $this->writeJsonPayload($payload, $outputPath);

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->output->write($this->renderException($throwable) . PHP_EOL);

            return self::FAILURE;
        }
    }

    /**
     * Normalizes a required path-like option or argument.
     */
    private function normalizeRequiredPath(mixed $value, string $name): string
    {
        if (!is_string($value) || $value === '') {
            throw new \InvalidArgumentException(sprintf('Missing required %s path.', $name));
        }

        return $value;
    }

    /**
     * Loads a grammar from disk using the requested format.
     */
    private function loadGrammar(string $path, string $format, ?string $startRule): Grammar
    {
        return match ($format) {
            'peg' => $this->loadPegGrammar($path),
            'cleanpeg' => $this->loadCleanPegGrammar($path, $startRule),
            default => throw new \InvalidArgumentException(sprintf('Unsupported grammar format "%s".', $format)),
        };
    }

    /**
     * Loads a classic PEG grammar file.
     */
    private function loadPegGrammar(string $path): Grammar
    {
        return (new PegGrammarLoader())->fromFile($path);
    }

    /**
     * Loads a CleanPeg grammar file.
     */
    private function loadCleanPegGrammar(string $path, ?string $startRule): Grammar
    {
        return (new CleanPegGrammarLoader())->fromFile($path, $startRule);
    }

    /**
     * Reads the full contents of a file.
     */
    private function loadFile(string $path): string
    {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new \InvalidArgumentException(sprintf('Unable to read file: %s', $path));
        }

        return $contents;
    }

    /**
     * Normalizes a possibly empty string option.
     */
    private function normalizeOptionalString(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Normalizes the grammar format option and infers auto-detection when needed.
     */
    private function normalizeGrammarFormat(string $format): string
    {
        $normalized = strtolower(trim($format));

        if ($normalized === 'auto') {
            return $this->inferGrammarFormat($this->normalizeRequiredPath($this->option('grammar'), 'grammar'));
        }

        return $normalized;
    }

    /**
     * Normalizes the JSON style option.
     */
    private function normalizeJsonStyle(string $style): string
    {
        $normalized = strtolower(trim($style));

        if (!in_array($normalized, ['full', 'simple'], true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported JSON style "%s". Use "full" or "simple".', $style));
        }

        return $normalized;
    }

    /**
     * Builds the JSON payload for the selected output style.
     *
     * @param list<\EmanueleCoppola\PHPeg\Ast\AstNode> $nodes
     * @return array<string, mixed>
     */
    private function buildPayload(
        string $jsonStyle,
        string $grammarPath,
        string $grammarFormat,
        string $startRule,
        string $inputPath,
        int $sourceLength,
        int $finalOffset,
        string $matchedText,
        ?string $query,
        array $nodes,
    ): array {
        $exporter = new AstJsonExporter();

        if ($jsonStyle === 'simple') {
            return [
                'success' => true,
                'matches' => $exporter->exportCompactNodes($nodes),
            ];
        }

        return [
            'success' => true,
            'grammar' => [
                'path' => $grammarPath,
                'format' => $grammarFormat,
                'startRule' => $startRule,
            ],
            'input' => [
                'path' => $inputPath,
                'length' => $sourceLength,
            ],
            'parse' => [
                'finalOffset' => $finalOffset,
                'matchedText' => $matchedText,
            ],
            'query' => [
                'selector' => $query,
                'count' => count($nodes),
            ],
            'matches' => $exporter->exportNodes($nodes),
        ];
    }

    /**
     * Writes the JSON payload to stdout or to a file path.
     *
     * @param array<string, mixed> $payload
     */
    private function writeJsonPayload(array $payload, ?string $outputPath): void
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
        );

        if ($json === false) {
            throw new \RuntimeException('Unable to encode JSON output.');
        }

        if ($outputPath === null) {
            $this->output->write($json . PHP_EOL);

            return;
        }

        if (file_put_contents($outputPath, $json . PHP_EOL) === false) {
            throw new \RuntimeException(sprintf('Unable to write JSON output to %s.', $outputPath));
        }
    }

    /**
     * Infers the grammar format from the file extension.
     */
    private function inferGrammarFormat(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'peg' => 'peg',
            'cleanpeg' => 'cleanpeg',
            default => throw new \InvalidArgumentException(sprintf(
                'Unable to infer grammar format from "%s". Pass --grammar-format=peg or --grammar-format=cleanpeg.',
                $path,
            )),
        };
    }

    /**
     * Renders a parse failure as JSON.
     */
    private function outputFailure(string $grammarPath, string $inputPath, string $grammarFormat, ?string $startRule, ?ParseError $error): void
    {
        $payload = [
            'success' => false,
            'grammar' => [
                'path' => $grammarPath,
                'format' => $grammarFormat,
                'startRule' => $startRule,
            ],
            'input' => [
                'path' => $inputPath,
            ],
            'error' => $error === null ? null : [
                'message' => $error->message(),
                'offset' => $error->offset(),
                'line' => $error->line(),
                'column' => $error->column(),
                'expected' => $error->expected(),
                'snippet' => $error->snippet(),
            ],
        ];

        $this->output->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL);
    }

    /**
     * Renders a caught exception as JSON.
     */
    private function renderException(Throwable $throwable): string
    {
        return json_encode([
            'success' => false,
            'error' => [
                'message' => $throwable->getMessage(),
                'type' => $throwable::class,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{"success":false,"error":{"message":"Unable to render exception."}}';
    }
}
