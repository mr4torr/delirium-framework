# Delirium Framework

## Compilação de Binários

Este projeto suporta a compilação da aplicação em dois formatos:

### 1. PHAR (PHP Archive)

Arquivo `.phar` standalone que requer PHP instalado no sistema de destino:

```bash
php -d phar.readonly=0 bin/compile
```

Artefato gerado: `build/delirium.phar` (~6 MB)

### 2. Binário Estático (Micro SAPI)

Executável standalone que **não requer PHP** instalado no sistema de destino:

```bash
php -d phar.readonly=0 bin/compile
```

Artefato gerado: `build/delirium` (~31 MB)

## Requisitos

- **Docker**: Obrigatório (usado para compilação do binário estático)
- **PHP 8.4+**: Para executar o script de build
- **Composer**: Para gerenciar dependências

### Para usuários WSL

Certifique-se de que o Docker Desktop está rodando e a integração WSL está habilitada:

1. Abra Docker Desktop
2. Settings → Resources → WSL Integration
3. Ative a integração para sua distribuição WSL

## Como Funciona

O processo de compilação:

1. **Staging**: Copia o projeto para `build/staging` e instala apenas dependências de produção
2. **PHAR**: Empacota o staging em um arquivo `.phar`
3. **Binário**: Usa Docker para:
   - Baixar fontes PHP e extensões
   - Compilar Micro SAPI com extensões estáticas
   - Fundir Micro + PHAR = binário final

## Verificação

Após a compilação, verifique os artefatos:

```bash
./bin/verify-build.sh
```

## Notas

- O binário estático inclui todas as extensões necessárias (Swoole, OpenSSL, cURL, etc.)
- A compilação via Docker evita problemas de dependências do sistema
- Downloads são cacheados em `downloads/` para builds subsequentes
