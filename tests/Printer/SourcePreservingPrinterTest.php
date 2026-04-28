<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Printer;

use EmanueleCoppola\PHPPeg\Ast\AstNodeFactory;
use PHPUnit\Framework\TestCase;

class SourcePreservingPrinterTest extends TestCase
{
    /**
     * Verifies the printer keeps untouched source and renders mutations.
     */
    public function testPrintsMutatedDocumentsWithoutLosingUnchangedText(): void
    {
        $document = $this->nginxDocument();
        $factory = new AstNodeFactory();
        $server = $document->query('Block[name="server"]')->first();

        self::assertNotNull($server);
        $server->query('Directive[name="listen"]')->first()?->replaceWith(
            $factory->token('Statement', "        listen 443;\n")
        );
        $server->query('Directive[name="server_name"]')->first()?->replaceWith(
            $factory->token('Statement', "        server_name example.org www.example.com;\n")
        );

        $printed = $document->print();

        self::assertStringContainsString('listen 443;', $printed);
        self::assertStringContainsString('server_name example.org www.example.com;', $printed);
        self::assertStringContainsString('server_tokens off;', $printed);
    }

    /**
     * Builds the Nginx document fixture for printer assertions.
     */
    private function nginxDocument(): \EmanueleCoppola\PHPPeg\Document\ParsedDocument
    {
        $grammar = $this->nginxGrammar();

        return $grammar->parseDocument($this->nginxSource());
    }

    /**
     * Returns the Nginx grammar used by the printer test.
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
     * Returns the sample Nginx source used by the printer test.
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
