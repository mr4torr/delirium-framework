# Quickstart: PSR-7 Support and Response Attributes

## Dependency Injection

You can now type-hint `Delirium\Http\Contract\RequestInterface` and `Delirium\Http\Contract\ResponseInterface` in your controllers.

```php
use Delirium\Http\Contract\RequestInterface;
use Delirium\Http\Contract\ResponseInterface;

class UserController
{
    public function store(RequestInterface $request, ResponseInterface $response)
    {
        $name = $request->input('name');

        return $response->json(['status' => 'created', 'name' => $name])
                        ->withStatus(201);
    }
}
```

## Route Attributes

Define default response types directly in your attributes.

```php
use Delirium\Http\Attribute\Get;
use Delirium\Http\Attribute\Post;

class ApiController
{
    #[Get('/users', type: 'json')]
    public function index()
    {
        // Automatically converted to JSON response
        return ['user1', 'user2'];
    }

    #[Post('/users', status: 201)]
    public function create()
    {
        return ['id' => 123]; // JSON 201 Created
    }

    #[Get('/report', type: 'xml')]
    public function report()
    {
        return ['data' => 'content']; // Converted to XML
    }
}
```

## Helper Methods

### Request
- `$request->input('key')`: Get value from Body (priority) or Query.
- `$request->header('User-Agent')`: Get header value.
- `$request->file('avatar')`: Get uploaded file.

### Response
- `$response->json($data)`: Set JSON body and header.
- `$response->redirect('/home')`: Set redirect header.
- `$response->download('/path/to/file')`: Stream file download.
