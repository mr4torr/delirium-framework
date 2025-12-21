# Quickstart: Using Delirium Responses

**Feature**: 009-response-class-implementation

## The `Response` Class

The `Delirium\Http\Response` class simplifies return values from controllers.

```php
use Delirium\Http\Response;

// Standard Usage (PSR-7 Style)
return new Response(200, [], 'Hello World');

// Fluent Body Usage
$response = new Response();
return $response->body(['status' => 'ok']); // Automagically JSON
```

## The `JsonResponse` Class

For explicit JSON APIs, use `JsonResponse`.

```php
use Delirium\Http\JsonResponse;

// Constructor automatically handles encoding and headers
return new JsonResponse(['id' => 123], 201);
```

### Auto-Serialization Rules

- **Arrays/Objects**: Converted to JSON. `Content-Type: application/json` added.
- **Strings**: Using as-is.
- **Int/Bool**: Cast to string.
