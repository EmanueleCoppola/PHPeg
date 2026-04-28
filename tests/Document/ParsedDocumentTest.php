<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Document;

use EmanueleCoppola\PHPPeg\Ast\AstNodeFactory;
use PHPUnit\Framework\TestCase;

class ParsedDocumentTest extends TestCase
{
    /**
     * Verifies parsed documents expose source, queries, and validation.
     */
    public function testExposesAndValidatesDocumentState(): void
    {
        $document = $this->nginxDocument();
        $factory = new AstNodeFactory();
        $server = $document->query('Block[name="server"]')->first();

        self::assertNotNull($server);
        self::assertSame('NginxConfig', $document->root()->name());
        self::assertStringContainsString('worker_processes auto;', $document->source());
        self::assertFalse($document->isModified());
        self::assertSame(1, $document->query('Block[name="server"]')->count());

        $server->appendNode($factory->token('Statement', "\n        client_max_body_size 64m;\n"));

        self::assertTrue($document->isModified());
        self::assertStringContainsString('client_max_body_size 64m;', $document->print());
        self::assertTrue($document->validatePrintedSource()->isSuccess());
        self::assertTrue($document->validate()->isSuccess());
    }

    /**
     * Builds the Nginx document fixture.
     */
    private function nginxDocument(): \EmanueleCoppola\PHPPeg\Document\ParsedDocument
    {
        $grammar = $this->nginxGrammar();

        return $grammar->parseDocument($this->nginxSource());
    }

    /**
     * Returns the Nginx grammar used by the document test.
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
     * Returns the sample Nginx source used by the document test.
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
