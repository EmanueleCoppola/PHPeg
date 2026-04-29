<?php

declare(strict_types=1);

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$grammarFile = __DIR__ . '/bixby-language.cleanpeg';

$grammar = (new CleanPegGrammarLoader(skipPattern: null))->fromFile($grammarFile, startRule: 'Bixby');

$rows = [];
foreach (iterateModelFiles(__DIR__ . '/models') as $inputFile) {
    $source = file_get_contents($inputFile);
    if ($source === false) {
        throw new RuntimeException(sprintf('Unable to read Bixby model file: %s', $inputFile));
    }

    try {
        $document = $grammar->parseDocument($source);
    } catch (RuntimeException $e) {
        echo 'parse failed: ' . $inputFile . PHP_EOL;
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }

    $rows = array_merge($rows, extractModelRows($document, $inputFile, __DIR__ . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR));
}

echo "Model summary:" . PHP_EOL;
foreach ($rows as $row) {
    echo PHP_EOL;
    echo $row['type'] . ': ' . $row['name'] . PHP_EOL;
    echo '  Description: ' . $row['description'] . PHP_EOL;
    echo '  Source: ' . $row['source'] . PHP_EOL;
}

function iterateModelFiles(string $root): iterable
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $extension = strtolower($file->getExtension());
        if ($extension !== 'bxb' && $extension !== 'bxp') {
            continue;
        }

        yield $file->getPathname();
    }
}

/**
 * @return list<array{type: string, name: string, description: string, source: string}>
 */
function extractModelRows(ParsedDocument $document, string $source, string $relativeBase): array
{
    $rows = [];
    $root = $document->root();

    foreach (['action', 'structure'] as $type) {
        foreach ($root->query(sprintf('Bixby > DeclarationList > Declaration > Key[text="%s"]', $type))->all() as $typeKey) {
            $declaration = $typeKey->parent();
            if ($declaration === null) {
                continue;
            }

            $name = $declaration->firstChild('Parameter')?->attribute('value') ?? '';
            $description = extractDescription($declaration);

            $rows[] = [
                'type' => ucfirst($type),
                'name' => $name !== '' ? $name : '(unnamed)',
                'description' => $description !== '' ? $description : '(no description)',
                'source' => relativeModelPath($source, $relativeBase),
            ];
        }
    }

    return $rows;
}

function relativeModelPath(string $source, string $base): string
{
    $normalizedSource = str_replace('\\', '/', $source);
    $normalizedBase = rtrim(str_replace('\\', '/', $base), '/') . '/';

    if (str_starts_with($normalizedSource, $normalizedBase)) {
        return substr($normalizedSource, strlen($normalizedBase));
    }

    return basename($normalizedSource);
}

function extractDescription(AstNode $declaration): string
{
    $descriptionKey = $declaration->query('Tail > Block > DeclarationList > Declaration > Key[text="description"]')->first();
    if ($descriptionKey === null) {
        return '';
    }

    $descriptionDeclaration = $descriptionKey->parent();
    if ($descriptionDeclaration === null) {
        return '';
    }

    return $descriptionDeclaration->firstChild('Parameter')?->attribute('value') ?? '';
}
