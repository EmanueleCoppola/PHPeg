<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

use EmanueleCoppola\PHPeg\Result\ParseResult;
use JsonException;
use RuntimeException;

/**
 * Measures parsing on a large structured JSON-like document.
 */
class LargeJsonLikeBenchmark extends AbstractBenchmarkCase
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Large JSON-like document';
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return 'json';
    }

    /**
     * @inheritDoc
     */
    public function input(string $scale): string
    {
        $count = $this->sizeForScale($scale, [
            'small' => 120,
            'medium' => 900,
            'large' => 2400,
        ]);

        $services = [];
        for ($index = 0; $index < $count; $index++) {
            $services[] = [
                'name' => sprintf('service-%03d', $index),
                'enabled' => $index % 3 !== 0,
                'version' => sprintf('%d.%d.%d', 1 + ($index % 5), $index % 10, $index % 7),
                'replicas' => ($index % 9) + 1,
                'threshold' => round(($index % 23) / 3, 2),
                'owners' => [
                    sprintf('owner-%02d', $index % 12),
                    sprintf('owner-%02d', ($index + 3) % 12),
                ],
                'metadata' => [
                    'region' => ['eu-west-1', 'us-east-1', 'ap-southeast-1'][$index % 3],
                    'deprecated' => $index % 11 === 0,
                    'notes' => $index % 4 === 0 ? null : sprintf('batch-%d', intdiv($index, 4)),
                ],
            ];
        }

        $document = [
            'app' => [
                'name' => 'phpeg-benchmark',
                'environment' => $scale,
                'services' => $services,
            ],
            'featureFlags' => [
                'strictMode' => true,
                'memoization' => true,
                'trace' => false,
            ],
            'limits' => [
                'maxConnections' => $count * 3,
                'timeoutSeconds' => 30,
                'sampleRate' => 0.75,
            ],
            'nullExample' => null,
        ];

        try {
            return json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate(ParseResult $result, string $input): void
    {
        $this->assertSuccessfulFullMatch($result, $input);

        try {
            $decoded = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException($exception->getMessage(), 0, $exception);
        }

        if (($decoded['app']['name'] ?? null) !== 'phpeg-benchmark') {
            throw new RuntimeException('Generated JSON benchmark input did not decode as expected.');
        }

        if (!is_array($decoded['app']['services'] ?? null) || $decoded['app']['services'] === []) {
            throw new RuntimeException('Generated JSON benchmark input is missing services.');
        }
    }

    /**
     * @inheritDoc
     */
    protected function grammarSource(string $scale): string
    {
        return <<<'CLEANPEG'
Json = Spacing Value Spacing EOF
Value = Object / Array / String / Number / "true" / "false" / "null"
Object = "{" Spacing PairList? Spacing "}"
PairList = Pair (Spacing "," Spacing Pair)*
Pair = String Spacing ":" Spacing Value
Array = "[" Spacing ValueList? Spacing "]"
ValueList = Value (Spacing "," Spacing Value)*
String = r'"(?:[^"\\]|\\.)*"'
Number = r'-?[0-9]+(?:\.[0-9]+)?'
Spacing = r'[ \t\r\n]*'
Start = Json
CLEANPEG;
    }

    /**
     * @inheritDoc
     */
    protected function startRule(): string
    {
        return 'Start';
    }
}
