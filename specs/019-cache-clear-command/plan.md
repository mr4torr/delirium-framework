# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Implementação do comando `cache:clear` no `packages/core`. O comando removerá recursivamente arquivos em `var/cache/` e disparará uma fase de "warmup" executando implementações registradas de `RegenerationListenerInterface` para regenerar `discovery.php` e `dependency-injection.php`. Além disso, integrará com `optimize` e `route:list` para garantir consistência.

## Technical Context

**Language/Version**: PHP 8.4+
**Primary Dependencies**: `symfony/console`, `delirium/core`, `delirium/dependency-injection`
**Storage**: File system (`var/cache/`)
**Testing**: PHPUnit (Unit and Integration tests)
**Target Platform**: Linux (CLI)
**Project Type**: PHP Framework / Console Tool
**Performance Goals**: Execução do comando < 5s para até 1000 arquivos ou 10MB.
**Constraints**: Uso obrigatório de listeners para regeneração (design desacoplado).
**Scale/Scope**: Comando de manutenção core e integração do framework.

## Constitution Check

- [x] **Mandatory Testing**: Cada nova classe (`CacheClearCommand`, `RegenerationRegistry`) terá testes unitários/integração correspondentes.
- [x] **Code Quality**: Adere ao SOLID/DRY. Uso do **Command Pattern** para CLI e **Observer/Listener** para o registro de regeneração.
- [x] **Swoole-First**: Operações de arquivo compatíveis com processos de longa duração.
- [x] **PSR Compliance**: Segue PSR-12, PSR-14 (event/listener logic) e PSR-4.

## Project Structure

### Documentation (this feature)

```text
specs/019-cache-clear-command/
├── plan.md              # Este arquivo
├── research.md          # Fase 0: Decisões técnicas
├── data-model.md        # Fase 1: Interfaces e Registro
├── quickstart.md        # Fase 1: Guia rápido
├── checklists/          # Checklists de qualidade
└── tasks.md             # Fase 2: Lista de tarefas
```

### Source Code

```text
packages/core/src/
├── Console/
│   ├── Command/
│   │   └── CacheClearCommand.php
│   ├── Contract/
│   │   └── RegenerationListenerInterface.php
│   └── Listener/
│       ├── DiscoveryRegenerationListener.php
│       └── ContainerRegenerationListener.php
├── Foundation/
│   └── Cache/
│       └── RegenerationRegistry.php
└── ...
```

**Structure Decision**: A lógica reside no `packages/core`. Implementamos um `RegenerationRegistry` que coleta classes de `RegenerationListenerInterface` para disparar o "warmup" após a deleção.


## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
