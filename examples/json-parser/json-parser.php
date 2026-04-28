<?php

declare(strict_types=1);

use EmanueleCoppola\PHPPeg\Loader\CleanPeg\CleanPegGrammarLoader;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$loader = new CleanPegGrammarLoader();
$grammar = $loader->fromFile(__DIR__ . '/json.cleanpeg', startRule: 'Json');

$input = file_get_contents(__DIR__ . '/json-file.json');
if ($input === false) {
    fwrite(STDERR, "Unable to read JSON input file." . PHP_EOL);
    exit(1);
}

$result = $grammar->parse($input);

if (!$result->isSuccess()) {
    fwrite(STDERR, $result->error()?->message() . PHP_EOL);
    exit(1);
}

try {
    /** @var array<string, mixed> $data */
    $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}

$services = is_array($data['services'] ?? null) ? $data['services'] : [];
$owners = is_array($data['owners'] ?? null) ? $data['owners'] : [];
$features = is_array($data['features'] ?? null) ? $data['features'] : [];
$database = is_array($data['database'] ?? null) ? $data['database'] : [];

echo 'Parsed root: Json' . PHP_EOL;
echo 'App: ' . ($data['app']['name'] ?? 'unknown') . ' v' . ($data['app']['version'] ?? 'n/a') . PHP_EOL;
echo 'Environment: ' . ($data['app']['environment'] ?? 'n/a') . PHP_EOL;
echo 'Database: ' . ($database['engine'] ?? 'n/a') . '@' . ($database['host'] ?? 'n/a') . ':' . (string) ($database['port'] ?? 'n/a') . PHP_EOL;
echo 'Owners: ' . count($owners) . PHP_EOL;
echo 'Services: ' . count($services) . PHP_EOL;
echo 'Enabled features: ' . count(array_filter($features, static fn (mixed $value): bool => $value === true)) . PHP_EOL;
