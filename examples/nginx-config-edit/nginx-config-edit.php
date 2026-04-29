<?php

declare(strict_types=1);

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$baseDir = __DIR__;
$grammarFile = $baseDir . '/nginx-config-grammar.cleanpeg';
$inputFile = $baseDir . '/nginx-config.conf';
$outputFile = $baseDir . '/nginx-new-config.conf';
$relativeOutputFile = 'examples/nginx-config-edit/nginx-new-config.conf';

$loader = new CleanPegGrammarLoader();
$grammar = $loader->fromFile($grammarFile, startRule: 'NginxConfig');

$source = file_get_contents($inputFile);
if ($source === false) {
    throw new RuntimeException(sprintf('Unable to read Nginx config input file: %s', $inputFile));
}

$document = $grammar->parseDocument($source);
$factory = new AstNodeFactory();

/** @var ?\EmanueleCoppola\PHPeg\Ast\AstNode $workerProcesses */
$workerProcesses = $document->query('Directive[name="worker_processes"]')->first();
/** @var ?\EmanueleCoppola\PHPeg\Ast\AstNode $keepaliveTimeout */
$keepaliveTimeout = $document->query('Directive[name="keepalive_timeout"]')->first();

$workerProcessesBefore = trim($workerProcesses?->text() ?? '');
$keepaliveTimeoutBefore = trim($keepaliveTimeout?->text() ?? '');

$workerProcesses?->replaceWith(
    $factory->token('Statement', statementLine($document->root(), 'worker_processes 2;'))
);

/** @var ?\EmanueleCoppola\PHPeg\Ast\AstNode $keepaliveValue */
$keepaliveValue = $document->query('Directive[name="keepalive_timeout"] Value[text="65"]')->first();
$keepaliveValue?->replaceWith(
    $factory->token('Number', '120')
);

$server = $document->query('Block[name="server"]')->first();
$listenBefore = trim($server?->query('Directive[name="listen"]')->first()?->text() ?? '');
$serverNameBefore = trim($server?->query('Directive[name="server_name"]')->first()?->text() ?? '');

if ($server !== null) {
    $server->query('Directive[name="listen"] Number[text="80"]')->first()?->replaceWith(
        $factory->token('Number', '443')
    );

    $server->query('Directive[name="server_name"] Value[text="example.com"]')->first()?->replaceWith(
        $factory->token('Token', 'example.org')
    );

    $server->query('Directive[name="error_log"]')->first()?->after(
        $factory->token('Statement', statementLine($server, 'client_max_body_size 64m;', leadingBreak: true, trailingBreak: false))
    );
}

$printed = $document->print();
file_put_contents($outputFile, $printed);

echo 'Input: ' . basename($inputFile) . PHP_EOL;
echo 'Output: ' . basename($outputFile) . PHP_EOL;
echo PHP_EOL;
echo 'Changes:' . PHP_EOL;
echo '  worker_processes: ' . $workerProcessesBefore . ' -> worker_processes 2;' . PHP_EOL;
echo '  keepalive_timeout: ' . $keepaliveTimeoutBefore . ' -> keepalive_timeout 120;' . PHP_EOL;
echo '  listen: ' . ($listenBefore !== '' ? $listenBefore : 'n/a') . ' -> listen 443;' . PHP_EOL;
echo '  server_name: ' . ($serverNameBefore !== '' ? $serverNameBefore : 'n/a') . ' -> server_name example.org www.example.com;' . PHP_EOL;
echo '  client_max_body_size: added 64m after error_log' . PHP_EOL;
echo PHP_EOL;
echo 'Validation: ' . ($document->validatePrintedSource()->isSuccess() ? 'ok' : 'failed') . PHP_EOL;
echo PHP_EOL;
echo 'Server block directives: ' . ($server?->childrenByName('Statement') !== null ? count($server->childrenByName('Statement')) : 0) . PHP_EOL;
echo 'Output written to: ' . $relativeOutputFile . PHP_EOL;

/**
 * Formats a config statement with the correct indentation for the current block depth.
 */
function statementLine(AstNode $context, string $line, bool $leadingBreak = false, bool $trailingBreak = true): string
{
    return ($leadingBreak ? "\n" : '') . indentFor($context) . $line . ($trailingBreak ? "\n" : '');
}

/**
 * Returns the indentation prefix for a node based on nested Nginx blocks.
 */
function indentFor(AstNode $context, int $indentSize = 4): string
{
    $depth = 0;

    for ($node = $context; $node !== null; $node = $node->parent()) {
        if ($node->name() === 'Block') {
            $depth++;
        }
    }

    return str_repeat(' ', $depth * $indentSize);
}
