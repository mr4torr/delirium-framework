# Delirium Framework

Um framework PHP de alta performance baseado em Swoole.

## 🚀 Como Iniciar o Servidor

O Delirium Framework possui modos distintos para desenvolvimento e produção.

### 🛠️ Ambiente de Desenvolvimento (Live Reload)

Para desenvolver, utilize o modo `dev`. Isso habilita o **Watcher**, que reinicia automaticamente o servidor sempre que você altera um arquivo, acelerando o ciclo de feedback.

```bash
# Necessário definir APP_ENV=dev para carregar as ferramentas de desenvolvimento
APP_ENV=dev php bin/console server:watch
```

> **Nota:** O comando `server:watch` só está disponível quando `APP_ENV=dev`.

### ⚡ Ambiente de Produção

Em produção, o servidor roda em modo otimizado, sem watcher e com cache de configurações ativado.

```bash
# O padrão é production se APP_ENV não for informado
php bin/console server:start
```

Ou explicitamente:

```bash
APP_ENV=prod php bin/console server:start
```

## 📋 Comandos Disponíveis

Para ver a lista de comandos disponíveis para o seu ambiente:

```bash
# Listar comandos de Produção
php bin/console list

# Listar comandos de Desenvolvimento (inclui server:watch e outros)
APP_ENV=dev php bin/console list
```
