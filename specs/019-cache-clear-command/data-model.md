# Data Model: Cache Clear Command

## Entities

### RegenerationListener (Contract)
- **Interface**: `Delirium\Core\Console\Contracts\RegenerationListenerInterface`
- **Purpose**: Defines the contract for any service that needs to regenerate a cache file after a clear operation.
- **Methods**:
    - `shouldRun(): bool`: Check if the listener should execute (e.g., check environment).
    - `regenerate(): void`: The actual logic to write the cache file.
    - `getName(): string`: Human-readable name for feedback.

### RegenerationRegistry (Registry)
- **Class**: `Delirium\Core\Foundation\Cache\RegenerationRegistry`
- **Purpose**: Collects and executes all registered listeners.
- **Attributes**:
    - `listeners`: `array<RegenerationListenerInterface>`
