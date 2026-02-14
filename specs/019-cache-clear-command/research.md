# Research: Cache Clear Command

## Decision 1: Recursive Deletion in Swoole
**Decision**: Use `RecursiveDirectoryIterator` and `RecursiveIteratorIterator` for filesystem traversal and `unlink()`/`rmdir()` for deletion.
**Rationale**: Native PHP filesystem calls are generally safe in coroutines as long as they don't block the main loop for extended periods. For `var/cache`, the volume of files is typically low enough.
**Alternatives**: Using `Swoole\Coroutine\System::exec('rm -rf ...')` was considered but rejected to maintain portability and avoid external dependencies.

## Decision 2: Listener Discovery Mechanism
**Decision**: Manual registration via `RegenerationRegistry` service.
**Rationale**: Simple and explicit. Other packages can register their listeners during the bootstrap phase (e.g., in their `ServiceProvider`).
**Alternatives**:
- *Auto-discovery via class naming*: Rejected (Too "magic" and rigid).
- *Symfony Event Dispatcher*: Rejected (Avoid adding extra heavy dependencies for a simple internal framework task).

## Decision 3: Regeneration Strategy
**Decision**: Synchronous execution of listeners sequentially.
**Rationale**: Predictable and easier to debug. Since cache regeneration is crucial for the next request, we want to ensure it finishes before the command reports success.
