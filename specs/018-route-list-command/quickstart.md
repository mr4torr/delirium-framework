# Quickstart: Route List Command

**Feature**: Route List Command
**Prerequisites**: Application installed, dependencies updated.

## Usage

To list all registered routes in the application:

```bash
bin/console route:list
```

### Expected Output

```text
+--------+------------------+--------------------------------------+
| Method | URI              | Handler                              |
+--------+------------------+--------------------------------------+
| GET    | /                | App\Controller\HomeController::index |
| POST   | /api/users       | App\Controller\UserController::store |
| GET    | /api/status      | Closure                              |
+--------+------------------+--------------------------------------+
```

## Troubleshooting

- **Command not found**: Ensure `Delirium\Http\HttpRouterServiceProvider` is registered in `Delirium\Core\Foundation\ProviderRepository`.
- **Empty list**: Ensure routes are actually defined in your application (e.g., via Attributes or `routes.php`).
