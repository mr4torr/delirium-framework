# Data Model: Map Request Payload

**Feature**: Map Request Payload (006)

## Data Flow

1.  **Request (JSON)**: `POST /users` Body: `{"name": "Bob", "age": 30}`.
2.  **AttributeScanner**: Detects `#[MapRequestPayload]` on `$dto` argument.
3.  **Hydrator**:
    - Input: `['name' => 'Bob', 'age' => 30]` + Class `CreateUserDto`.
    - Output: Instance of `CreateUserDto`.
      - Mismatched fields ignored (Best Effort).
4.  **Validator** (Adapter):
    - Input: Instance of `CreateUserDto`.
    - Checks: `#[Assert\...]` (Symfony Constraints) on properties.
    - Output: Success or `ConstraintViolationList` (mapped to generic violation list).
5.  **Controller**: Receives valid DTO.

## Entities (Logical)

- **DTO (Data Transfer Object)**: Plain PHP Class with public properties or promoted constructor properties. May contain Validation Attributes.
