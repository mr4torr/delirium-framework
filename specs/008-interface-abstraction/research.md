# Research: Third-Party Interface Abstraction strategy

**Decision**: Implement the "Interface Extension" pattern for necessary third-party integrations that lack PSRs.

**Rationale**:
- **Consistency**: All interfaces used in type-hints should ideally belong to `Delirium\*` or `Psr\*`.
- **Future-Proofing**: Allows us to add methods or docblocks to the interface without modifying vendor code (though limited if we just extend).
- **Loose Coupling**: Codebase refers to our contract, not the vendor's.

**Candidates identified**:
1. **Symfony CompilerPassInterface**:
   - *Current*: `use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface`
   - *Proposed*: `interface Delirium\DI\Contract\CompilerPassInterface extends \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface {}`
   - *Analysis*: Useful if we want to expose compiler passes to users without forcing them to import Symfony classes directly, though `src/` users rarely write compiler passes. Low priority but good candidate for proof of concept.

2. **Symfony Validator**:
   - `packages/validation/src/Adapter/SymfonyValidatorAdapter.php` wraps the validator.
   - `ValidatorInterface` is already our own contract (`Delirium\Validation\Contract\ValidatorInterface`). This is already compliant (Adapter pattern).

**Alternatives Considered**:
- **Strict Adapter (No Extension)**:
  - *Pros*: Total decoupling.
  - *Cons*: Cannot be passed directly to the third-party library without an unwrapping step.
  - *Decision*: For deep integrations like DI Compiler Passes, extending is pragmatic. For libraries we consume (like Validation), strict Adapter is better.

**Conclusion**:
- Update Constitution to reflect the rule.
- Create example abstraction for `CompilerPassInterface` if it's exposed or widely used.
