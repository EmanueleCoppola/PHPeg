<?php

declare(strict_types=1);

use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;
use EmanueleCoppola\PHPeg\Support\AstPrinter;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$baseDir = __DIR__;
$grammarFile = $baseDir . '/access-policy.cleanpeg';
$inputFile = $baseDir . '/access-policy.txt';

$loader = new CleanPegGrammarLoader();
$grammar = $loader->fromFile($grammarFile, startRule: 'PolicyFile');

$source = file_get_contents($inputFile);
if ($source === false) {
    throw new RuntimeException(sprintf('Unable to read policy input file: %s', $inputFile));
}

$document = $grammar->parseDocument(
    $source,
);

echo 'Parse success: yes' . PHP_EOL;
echo 'Root: ' . $document->root()->name() . PHP_EOL;
echo 'Policy rules: ' . $document->query('PolicyRule')->count() . PHP_EOL;
echo 'Effect nodes: ' . $document->query('Effect')->count() . PHP_EOL;
echo 'Access expressions: ' . $document->query('Access')->count() . PHP_EOL;
echo 'Comparisons: ' . $document->query('Comparison')->count() . PHP_EOL;
echo PHP_EOL;

echo 'Rules:' . PHP_EOL;
foreach ($document->query('PolicyRule')->all() as $rule) {
    echo '  - ' . preg_replace('/\s+/', ' ', trim($rule->text())) . PHP_EOL;
}

echo PHP_EOL . 'Access paths:' . PHP_EOL;
foreach (array_slice($document->query('Access')->all(), 0, 8) as $access) {
    echo '  - ' . trim($access->text()) . PHP_EOL;
}

echo PHP_EOL . 'AST:' . PHP_EOL;
echo AstPrinter::print($document->root()) . PHP_EOL;
