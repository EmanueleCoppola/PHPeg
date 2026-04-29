<?php

declare(strict_types=1);

use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$grammarFile = __DIR__ . '/dotenv-config.cleanpeg';
$inputFile = __DIR__ . '/example.env';
$productionOutputFile = __DIR__ . '/production.env';
$stagingOutputFile = __DIR__ . '/staging.env';

$grammar = (new CleanPegGrammarLoader(skipPattern: null))->fromFile($grammarFile, startRule: 'DotEnv');

$source = file_get_contents($inputFile);
if ($source === false) {
    throw new RuntimeException(sprintf('Unable to read .env input file: %s', $inputFile));
}

$productionDocument = $grammar->parseDocument($source);
$productionSummary = ['updated' => [], 'added' => []];
applyProductionTransform($productionDocument, $productionSummary);
$productionPrinted = $productionDocument->print();
file_put_contents($productionOutputFile, $productionPrinted);

$stagingDocument = $grammar->parseDocument($source);
$stagingSummary = ['updated' => [], 'added' => []];
applyStagingTransform($stagingDocument, $stagingSummary);
$stagingPrinted = $stagingDocument->print();
file_put_contents($stagingOutputFile, $stagingPrinted);

echo 'Input: ' . $inputFile . PHP_EOL;
echo 'Production output: ' . $productionOutputFile . PHP_EOL;
echo 'Staging output: ' . $stagingOutputFile . PHP_EOL;
echo PHP_EOL;
echo 'Production validation: ' . ($productionDocument->validatePrintedSource()->isSuccess() ? 'ok' : 'failed') . PHP_EOL;
echo 'Staging validation: ' . ($stagingDocument->validatePrintedSource()->isSuccess() ? 'ok' : 'failed') . PHP_EOL;
echo PHP_EOL;

echo "Production changes:" . PHP_EOL;
echo formatChangeSummary($productionSummary) . PHP_EOL . PHP_EOL;

echo "Staging changes:" . PHP_EOL;
echo formatChangeSummary($stagingSummary) . PHP_EOL . PHP_EOL;

echo 'The generated files are:' . PHP_EOL;
echo '- ' . $productionOutputFile . PHP_EOL;
echo '- ' . $stagingOutputFile . PHP_EOL;

function applyProductionTransform(ParsedDocument $document, array &$summary): void
{
    $factory = new AstNodeFactory();

    replaceValue($document, $factory, $summary, 'APP_ENV', 'production');
    replaceValue($document, $factory, $summary, 'APP_DEBUG', 'false');
    replaceValue($document, $factory, $summary, 'APP_URL', 'https://production.example.com');
    replaceValue($document, $factory, $summary, 'CACHE_DRIVER', 'redis');
    replaceValue($document, $factory, $summary, 'SESSION_DRIVER', 'redis');
    replaceValue($document, $factory, $summary, 'QUEUE_CONNECTION', 'redis');
    replaceValue($document, $factory, $summary, 'LOG_LEVEL', 'warning');
    replaceValue($document, $factory, $summary, 'MAIL_MAILER', 'smtp');
    replaceValue($document, $factory, $summary, 'DB_DATABASE', 'phpeg_production');
    replaceValue($document, $factory, $summary, 'DB_PASSWORD', '$123my_secure_production_password$');
    replaceValue($document, $factory, $summary, 'MAIL_USERNAME', 'noreply@production.example.com');
    replaceValue($document, $factory, $summary, 'MAIL_PASSWORD', '$456_my_secure_production_password$');

    appendIfMissing($document, $factory, $summary, 'REDIS_HOST', 'redis-production.internal');
    appendIfMissing($document, $factory, $summary, 'REDIS_PORT', '6379');
    appendIfMissing($document, $factory, $summary, 'SENTRY_DSN', 'https://exampleProductionKey@sentry.io/12345');
}

function applyStagingTransform(ParsedDocument $document, array &$summary): void
{
    $factory = new AstNodeFactory();

    replaceValue($document, $factory, $summary, 'APP_ENV', 'staging');
    replaceValue($document, $factory, $summary, 'APP_DEBUG', 'true');
    replaceValue($document, $factory, $summary, 'APP_URL', 'https://staging.example.com');
    replaceValue($document, $factory, $summary, 'CACHE_DRIVER', 'redis');
    replaceValue($document, $factory, $summary, 'SESSION_DRIVER', 'redis');
    replaceValue($document, $factory, $summary, 'QUEUE_CONNECTION', 'redis');
    replaceValue($document, $factory, $summary, 'LOG_LEVEL', 'info');
    replaceValue($document, $factory, $summary, 'DB_DATABASE', 'phpeg_staging');
    replaceValue($document, $factory, $summary, 'DB_PASSWORD', '$123my_secure_staging_password$');
    replaceValue($document, $factory, $summary, 'MAIL_USERNAME', 'noreply@staging.example.com');
    replaceValue($document, $factory, $summary, 'MAIL_PASSWORD', '$456_my_secure_staging_password$');

    appendIfMissing($document, $factory, $summary, 'REDIS_HOST', 'redis-staging.internal');
    appendIfMissing($document, $factory, $summary, 'MAIL_MAILER', 'smtp');
    appendIfMissing($document, $factory, $summary, 'SENTRY_DSN', 'https://exampleStagingKey@sentry.io/12345');
}

function replaceValue(ParsedDocument $document, AstNodeFactory $factory, array &$summary, string $key, string $value): void
{
    $document->query(sprintf('Assignment[name="%s"]', $key))->first()?->replaceWith(
        $factory->token('Assignment', envLine($key, $value))
    );

    $summary['updated'][] = $key;
}

function appendIfMissing(ParsedDocument $document, AstNodeFactory $factory, array &$summary, string $key, string $value): void
{
    if (!$document->query(sprintf('Assignment[name="%s"]', $key))->isEmpty()) {
        return;
    }

    $document->query('Assignment')->last()?->after(
        $factory->token('Assignment', "\n" . envLine($key, $value))
    );

    $summary['added'][] = $key;
}

function envLine(string $key, string $value): string
{
    if (preg_match('/[ \t#]/', $value) === 1) {
        $value = '"' . addcslashes($value, "\\\"") . '"';
    }

    return $key . '=' . $value . "\n";
}

function formatChangeSummary(array $summary): string
{
    $updated = $summary['updated'];
    $added = $summary['added'];

    $lines = [];
    $lines[] = 'Updated (' . count($updated) . '): ' . implode(', ', $updated);
    $lines[] = 'Added (' . count($added) . '): ' . ($added === [] ? 'none' : implode(', ', $added));

    return implode(PHP_EOL, $lines);
}
