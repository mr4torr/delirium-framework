# Data Model: Switch to Swoole

**Feature**: Switch to Swoole (010-switch-to-swoole)

## Entities

*No persistent data model changes.*

## Runtime Objects

### HttpServer
- **Class**: `Swoole\Http\Server`
- **Role**: The main reactor process handling incoming TCP connections.

### HttpRequest
- **Class**: `Swoole\Http\Request`
- **Mapped To**: `Psr\Http\Message\ServerRequestInterface` via `SwoolePsrAdapter`.

### HttpResponse
- **Class**: `Swoole\Http\Response`
- **Mapped From**: `Psr\Http\Message\ResponseInterface` via `SwoolePsrAdapter`.
