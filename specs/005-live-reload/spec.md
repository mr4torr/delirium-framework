# Especificação da Funcionalidade: Live Reload (Hot Reload)

## Descrição
Implementar um mecanismo de "Live Reload" que reinicia automaticamente o servidor de aplicação quando alterações são detectadas nos arquivos de código fonte. Isso visa acelerar o ciclo de desenvolvimento, eliminando a necessidade de reiniciar manualmente o servidor a cada mudança.

## Casos de Uso (User Scenarios)

### 1. Desenvolvimento Ativo
**Ator:** Desenvolvedor
**Cenário:**
1. O desenvolvedor inicia o servidor em modo de desenvolvimento (ex: via um comando `composer watch` ou script dedicado).
2. O servidor inicia e o terminal exibe logs da aplicação.
3. O desenvolvedor altera um arquivo PHP dentro de `packages/` ou `src/` e salva.
4. O sistema detecta a alteração.
5. O servidor atual é encerrado graciosamente e um novo processo é iniciado automaticamente.
6. O terminal exibe uma indicação de que o reload ocorreu.

## Requisitos Funcionais

1.  **Monitoramento de Arquivos:**
    *   O sistema deve monitorar recursivamente diretórios especificados.
    *   **Configuração via Código:** Os diretórios monitorados devem ser configuráveis através da classe `Delirium\Core\Options\DebugOptions`.
    *   Diretórios padrão (se não configurados): `packages/` e `src/`.
    *   Deve detectar modificações em arquivos `.php`.

2.  **Gerenciamento de Processo:**
    *   O "watcher" deve ser capaz de iniciar o servidor da aplicação (atualmente `public/index.php`) como um sub-processo.
    *   Ao detectar uma mudança, o watcher deve encerrar o sub-processo atual e iniciar um novo imediatamente.

3.  **Desempenho e Limitações:**
    *   O intervalo de verificação (polling) deve ser curto o suficiente para ser perceptível como "instântaneo" (ex: 1 segundo ou menos), mas não tão curto a ponto de consumir CPU excessiva.
    *   Deve ignorar diretórios como `vendor/`, `.git/`, `var/`.

4.  **Interface:**
    *   Deve ser executável via linha de comando (CLI).
    *   Deve fornecer feedback visual simples no terminal (ex: "Mudança detectada em [arquivo]. Reiniciando...").

5.  **Integração com DebugOptions:**
    *   O recurso deve ser habilitado/desabilitado e configurado utilizando a classe `Delirium\Core\Options\DebugOptions`.
    *   O desenvolvedor deve poder passar uma lista de diretórios para aplicar o polling.

## Critérios de Sucesso

1.  **Automação:** Alterar qualquer arquivo PHP na árvore de diretórios monitorada resulta no reinício do servidor sem intervenção humana.
2.  **Confiabilidade:** O servidor reinicia corretamente mesmo se a aplicação tiver quebrado na execução anterior (desde que o erro de sintaxe seja corrigido, o próximo reload deve funcionar).
3.  **Escopo:** Funciona corretamente para a estrutura mono-repo atual (`packages/*` e app principal).

## Suposições e Restrições
*   Ambiente Linux (conforme contexto do usuário).
*   Uso de OpenSwoole: O reinício deve garantir que a porta do servidor seja liberada ou reutilizada (SO_REUSEPORT) para evitar erros de "Address already in use" durante reinícios rápidos.
*   Implementação em PHP puro para evitar dependências de binários externos complexos (como `watchman` ou `nodemon`) se possível, mas ferramentas nativas do ecossistema PHP são aceitáveis.

## Levantamentos (Needs Clarification)
*(Nenhum ponto crítico identificado que impeça o planejamento inicial. Assumindo implementação via script PHP com tick/loop de verificação).*
