# Governance: Interface Abstraction

When integrating third-party packages, follow this decision tree:

1. **Is there a PSR interface?**
   - **YES**: Use it. (e.g., `Psr\Log\LoggerInterface`, `Psr\Container\ContainerInterface`).
   - **NO**: Proceed to 2.

2. **Is it a core framework integration (Deep Integration)?**
   - *Example*: Extending the Container, writing a custom Driver for a specific library.
   - **YES**: Create a framework interface that **EXTENDS** the third-party interface.
     ```php
     namespace Delirium\Contract;
     use Vendor\Lib\VendorInterface;
     
     interface wrapperInterface extends VendorInterface {}
     ```
   - *Benefit*: Objects implementing this can be passed directly to the vendor library.

3. **Is it a consumed utility (Loose Integration)?**
   - *Example*: HTTP Client, Validation, Caching.
   - **YES**: Create a pure framework interface (Adapter Pattern) and write an adapter.
     ```php
     namespace Delirium\Contract;
     
     interface MyCacheInterface {
         public function get(string $key): mixed;
     }
     // Implementation wraps the vendor lib
     ```
   - *Benefit*: Total decoupling.
