# Dependency Graph & Data Model

This document outlines the allowed dependencies and the "Data Model" equivalent for the architectural enforcement feature.

## 1. Package Layers & Dependencies

The following table defines the STRICTLY allowed relationships. If a cell is NO, any `use` statement or `require` entry is forbidden.

| FROM Package | TO Core | TO Http | TO Di | TO Validation | TO Support | TO DevTools | TO Vendor |
|--------------|---------|---------|-------|---------------|------------|-------------|-----------|
| **Support** | NO | NO | NO | NO | NO | NO | YES |
| **Http** | NO | NO | NO* | NO | YES | NO (dev only)| YES |
| **Di** | NO | NO | NO | NO | YES | NO (dev only)| YES |
| **Validation**| NO | NO | NO | NO | YES | NO (dev only)| YES |
| **Core** | YES | YES | YES | YES | YES | NO (dev only)| YES |
| **DevTools** | NO | NO | NO | NO | NO | NO | YES |

*\* Http may use `Psr\Container` (from Vendor) but NOT `Delirium\Di` implementation directly.*

## 2. Key Entities

### Package
A directory within `packages/` that constitutes a standalone unit of functionality.
- **Attributes**: `name`, `type`, `require`, `autoload`.
- **Constraint**: Must have isolated `composer.json`.

### Layer (Deptrac)
A logical collection of classes defined by a Collector.
- **Example**: `Layer: Http` collects `packages/http-router/src/**/*`.

### Violation
Any code import that crosses forbidden layer boundaries.
- **Severity**: Error (Build Fail).
