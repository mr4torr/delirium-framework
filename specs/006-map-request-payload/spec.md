# Feature Specification: Map Request Payload

**Feature Branch**: `006-map-request-payload`
**Created**: 2025-12-20
**Input**: User description: "Implementar recurso que permita passar na função do controler uma classe Dto ou Entidade que quando o parametro do payload ter o mesmo nome da propriedade da classe será automaticamente preenchida, algo semelhante a funcionalidade MapRequestPayload do Symfony."

## User Scenarios & Testing

### User Story 1 - Automatic DTO Mapping (Priority: P1)

Como um Desenvolvedor usando o framework, eu quero que os argumentos dos meus Controllers sejam automaticamente preenchidos com os dados da requisição (JSON body) quando eu usar um DTO tipado e um Atributo, para que eu não precise fazer parse manual de JSON e instanciar objetos repetitivamente.

**Why this priority**: Elimina boilerplate code crítico em quase todos os endpoints que recebem dados, melhorando significativamente a DX (Developer Experience).

**Independent Test**: Criar um Controller com um método que recebe um DTO. Enviar um POST request com JSON. Verificar se o método recebe o DTO preenchido corretamente.

**Acceptance Scenarios**:

1. **Given** um DTO `CreateUserDto` com propriedades `string $name` e `int $age`,
   **And** um Controller action `create(#[MapRequestPayload] CreateUserDto $dto)`,
   **When** eu envio um POST com body `{"name": "Alice", "age": 30}`,
   **Then** o controller deve receber uma instância de `CreateUserDto` com `$dto->name === "Alice"` e `$dto->age === 30`.

2. **Given** o mesmo DTO e Controller,
   **When** eu envio um POST com body com campos extras (ex: `{"age": 30, "extra": "ignored"}`) ou faltando campos opcionais,
   **Then** o framework deve preencher o que for possível e ignorar campos desconhecidos ou incompatíveis (sem lançar erro 400), a menos que o tipo PHP exija valor (ex: propertymanager não inicializada). Se o campo faltar e não tiver default, a propriedade permanece não inicializada (ou lança erro de Type Error do PHP posteriormente se acessada). O Mapper em si é permissivo.

---

### User Story 2 - Entity Mapping (Priority: P2)

Como um Desenvolvedor, quero poder usar Entidades de domínio diretamente nos argumentos do Controller da mesma forma que DTOs.

**Why this priority**: Flexibilidade para arquiteturas mais simples onde DTOs não são desejados.

**Independent Test**: Similar ao US1, mas usando uma classe que representa uma Entidade.

**Acceptance Scenarios**:

1. **Given** uma classe `Product` (Entity),
   **When** usada como argumento com `#[MapRequestPayload]`,
   **Then** o framework instancia e popula a classe via Reflection properties ou Constructor (dependendo da implementação).

---

### User Story 3 - Mixed Usage (Priority: P1)

Como um Desenvolvedor, quero continuar usando Injeção de Dependência e Parâmetros de Rota nos meus controllers, mesmo quando utilizo o `#[MapRequestPayload]`, para que eu possa combinar serviços, IDs de rota e dados do corpo da requisição no mesmo método.

**Why this priority**: Crítico para endpoints reais que raramente recebem *apenas* o corpo. Geralmente precisam de um ID para update (`/users/{id}`) ou serviços.

**Independent Test**: Endpoint que recebe um Serviço (DI), um ID (Rota) e um DTO (Payload).

**Acceptance Scenarios**:

1. **Given** Rota `PUT /users/{id}`,
   **And** Controller action `update(string $id, UserRepository $repo, #[MapRequestPayload] UpdateUserDto $dto)`,
   **When** Request `PUT /users/123` com body `{"name": "Bob"}`,
   **Then** `$id` é "123", `$repo` é uma instância válida, e `$dto` tem name="Bob".

   **Then** `$id` é "123", `$repo` é uma instância válida, e `$dto` tem name="Bob".

---

### User Story 4 - DTO Validation (Priority: P2)

Como um Desenvolvedor, quero que as propriedades do meu DTO sejam validadas automaticamente (ex: `@NotEmpty`, `@Email`) antes do meu Controller ser executado, para garantir a integridade dos dados sem sujar o código do controller com `if/else` de validação.

**Why this priority**: Complemento essencial da hidratação. Dados tipados mas inválidos (ex: idade negativa, email sem @) são tão perigosos quanto dados não tipados.

**Independent Test**: DTO com atributo `#[Assert\Email]`. Enviar string inválida. Verificar erro 422 Unprocessable Entity.

**Acceptance Scenarios**:

1. **Given** DTO `CreateUserDto` com propriedade `#[Assert\Email] public string $email`,
   **When** Request com body `{"email": "invalid-email"}`,
   **Then** o sistema retorna `422 Unprocessable Entity` (ou 400), e o Controller **NÃO** é executado.

2. **Given** o mesmo DTO,
   **When** Request com body `{"email": "valid@example.com"}`,
   **Then** o Controller é executado normalmente.

---

### Edge Cases

- **Propriedades Faltando**: O payload não contém uma propriedade do DTO. (Deve ignorar e não preencher).
- **Tipos Incompatíveis**: Payload envia string onde se espera int. (Deve ignorar e não preencher, ou tentar coerção segura. Decisão: Não preencher).
- **Construtor vs Propriedades Públicas**: O DTO pode ser readonly com construtor ou ter propriedades públicas. O mapper deve suportar ambos (ou focar em Construtor que é mais robusto).
- **JSON Aninhado**: Suporte a objetos dentro de objetos? (Assumindo suporte básico inicial, talvez raso para V1, ou recursivo se fácil). *Assunção: Suporte recursivo básico.*

## Requirements

### Functional Requirements

- **FR-001**: O sistema DEVE fornecer um Atributo `#[MapRequestPayload]` (ou similar) para marcar argumentos de Controller que devem ser mapeados.
- **FR-002**: O sistema DEVE interceptar a requisição antes da execução do Controller Action.
- **FR-003**: O sistema DEVE ler o corpo da requisição (Body) e decodificar (ex: JSON).
- **FR-004**: O sistema DEVE instanciar a classe alvo (DTO/Entity) e popular suas propriedades com os dados decodificados.
- **FR-005**: O sistema DEVE ignorar campos do payload que não correspondem a propriedades da classe e ignorar propriedades da classe que não existem no payload (Best Effort Mapping). Erros de tipo devem resultar no não-preenchimento da propriedade.
- **FR-006**: O sistema DEVE suportar argumentos resolvidos via Construtor (promoted properties) e propriedades públicas.
- **FR-007**: O sistema DEVE garantir que argumentos NÃO marcados com `#[MapRequestPayload]` continuem sendo resolvidos pelos mecanismos existentes (Injeção de Dependência e Parâmetros de Rota). A ordem dos argumentos na assinatura não deve importar.
- **FR-008**: O sistema DEVE suportar validação de propriedades via Atributos (ex: `#[Assert\Length]`).
- **FR-009**: A lógica de validação DEVE residir em um pacote separado `packages/validation`.
- **FR-010**: A validação DEVE ocorrer APÓS a hidratação e ANTES da execução do Controller. Se a validação falhar, uma exceção deve interromper o fluxo.

### Key Entities

- **AttributeMapper**: O componente responsável por ler o atributo e orquestrar o mapeamento.
- **Hydrator**: O componente responsável por pegar array de dados e criar o objeto tipado.
- **Validator**: O componente (novo pacote) responsável por verificar as regras de validação no objeto instanciado.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Desenvolvedores podem remover 100% do código de `json_decode` e validação manual de tipos simples dentro dos controllers para endpoints de escrita.
- **SC-002**: O sistema realiza o mapeamento de "melhor esforço" (loose mapping), permitindo evolução de APIs sem quebras rígidas por campos extras ou faltantes.
- **SC-003**: Implementação compatível com PHP 8.4 (Attributes, Constructor Promotion).

## Suposições e Restrições
- **Formato**: Foco inicial em JSON payloads (`application/json`).
- **Validação**: A validação de *regras de negócio* (Constraint Violations) é coberta pelo novo pacote `Validation`. O `MapRequestPayload` orquestra a chamada ao validador.
