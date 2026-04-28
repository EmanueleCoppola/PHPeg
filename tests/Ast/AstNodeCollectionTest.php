<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Ast;

use EmanueleCoppola\PHPPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPPeg\Ast\AstNodeCollection;
use PHPUnit\Framework\TestCase;

class AstNodeCollectionTest extends TestCase
{
    /**
     * Verifies collection accessors and callbacks.
     */
    public function testExposesNodesAndRunsCallbacks(): void
    {
        $document = $this->nginxDocument();
        $collection = $document->query('Block[name="server"]');
        $count = 0;

        self::assertInstanceOf(AstNodeCollection::class, $collection);
        self::assertSame(1, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame('Block', $collection->first()?->name());
        self::assertSame('Block', $collection->last()?->name());
        self::assertCount(1, $collection->all());

        $collection->each(static function () use (&$count): void {
            $count++;
        });

        self::assertSame(1, $count);
    }

    /**
     * Verifies inserted nodes are forwarded to each target node.
     */
    public function testCanAppendAndPrependNodes(): void
    {
        $factory = new AstNodeFactory();
        $document = $this->nginxDocument();
        $collection = $document->query('Block[name="server"]');
        $server = $collection->first();

        self::assertNotNull($server);
        $initialCount = count($server->children());

        $collection->prependNode($factory->token('Statement', 'server_tokens off;\n'));
        $collection->appendNode($factory->token('Statement', "\n    client_max_body_size 64m;\n"));

        self::assertGreaterThan($initialCount, count($server->children()));
        self::assertStringContainsString('server_tokens off;', $document->print());
        self::assertStringContainsString('client_max_body_size 64m;', $document->print());
    }

    /**
     * Builds the Nginx configuration document used by the collection tests.
     */
    private function nginxDocument(): \EmanueleCoppola\PHPPeg\Document\ParsedDocument
    {
        $grammar = $this->nginxGrammar();
        $source = $this->nginxSource();

        return $grammar->parseDocument($source);
    }

    /**
     * Returns the Nginx grammar used by the collection tests.
     */
    private function nginxGrammar(): \EmanueleCoppola\PHPPeg\Grammar\Grammar
    {
        $grammar = <<<'CLEANPEG'
NginxConfig = Spacing Statement* Spacing EOF
Statement = Spacing (Directive / Block)
Block = Identifier Spacing Value? Spacing "{" Spacing Statement* Spacing "}"
Directive = Identifier Spacing ValueList Spacing ";"
ValueList = Value (Spacing Value)*
Value = String / Number / Token
Identifier = r'[A-Za-z_][A-Za-z0-9_\.-]*'
Number = r'[0-9]+(?:\.[0-9]+)?'
String = r'"[^"]*"'
Token = r'[^\s;{}#"]+'
Comment = r'#[^\n]*'
Spacing = (r'[ \t\r\n]+' / Comment)*
CLEANPEG;

        return (new \EmanueleCoppola\PHPPeg\Loader\CleanPeg\CleanPegGrammarLoader())->fromString($grammar, startRule: 'NginxConfig');
    }

    /**
     * Returns the sample Nginx source used by the collection tests.
     */
    private function nginxSource(): string
    {
        return <<<'NGINX'
# main nginx configuration
worker_processes auto;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
    multi_accept on;
}

http {
    include mime.types;
    default_type application/octet-stream;
    sendfile on;
    keepalive_timeout 65;
    server_tokens off;

    upstream app_backend {
        server 10.0.0.11:8080 max_fails=3 fail_timeout=30s;
        server 10.0.0.12:8080 max_fails=3 fail_timeout=30s;
    }

    server {
        listen 80;
        server_name example.com www.example.com;
        root /var/www/example.com/public;
        index index.php index.html;
        access_log /var/log/nginx/example.access.log;
        error_log /var/log/nginx/example.error.log warn;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location /api/ {
            proxy_pass http://app_backend;
            proxy_http_version 1.1;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
NGINX;
    }
}
