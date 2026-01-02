# Data Model: PSR-7 Http Message Wrappers

## Entities

### Request (Delirium\Http\Message\Request)

Represents an incoming HTTP request. Wraps `Nyholm\Psr7\ServerRequest`.

| Property/State | Type | Description |
|----------------|------|-------------|
| `psrRequest` | `Psr\Http\Message\ServerRequestInterface` | The underlying PSR-7 request object. |
| `attributes` | `array` | Request attributes (route params, etc). |
| `parsedBody` | `array\|object\|null` | The deserialized body content. |

**Key Behavior**:
- **Aggregation**: `input($key)` aggregates data from `parsedBody` and `queryParams`. `parsedBody` takes precedence.
- **Immutability**: Methods like `withAttribute` return a new instance (clone), respecting PSR-7 immutability.

### Response (Delirium\Http\Message\Response)

Represents an outgoing HTTP response. Wraps `Nyholm\Psr7\Response`.

| Property/State | Type | Description |
|----------------|------|-------------|
| `psrResponse` | `Psr\Http\Message\ResponseInterface` | The underlying PSR-7 response object. |
| `stream` | `Psr\Http\Message\StreamInterface` | The response body stream. |

**Key Behavior**:
- **Helper Mutations**: Methods like `json($data)` mutation the underlying state (or return new instance) to set `Content-Type` and body stream.

## Value Objects

### Route Attributes

| Attribute | Parameters | Description |
|-----------|------------|-------------|
| `#[Get]`, `#[Post]`, etc. | `path` (string), `name` (string), `type` (string), `status` (int) | Metadata defining route behavior and default response formatting. |
| `type` enum | `json`, `xml`, `html`, `stream`, `raw` | Determines which `ResponseResolver` strategy is applied. |
