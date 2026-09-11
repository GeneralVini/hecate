# AGENTS.md — HECATE

Este arquivo orienta agentes de código e assistentes que alterem o HECATE.

## Regra central

> **A complexidade deve ser justificada pelo domínio.**

Não introduza antecipadamente padrões DDD, abstrações, camadas, interfaces, repositories, eventos, Value Objects ou indireções.

Prefira o desenho mais simples que preserve boundaries claros e testabilidade. Adicione complexidade arquitetural somente quando uma regra de domínio, integração ou problema concreto de acoplamento a justificar.

## Contexto obrigatório

Antes de alterar arquitetura ou código relevante, consulte:

1. `docs/ddd.md` — diretrizes arquiteturais e de boundaries;
2. `docs/ARQUITETURA.md` — arquitetura do produto e responsabilidades;
3. `docs/DECISOES.md` — decisões técnicas já consolidadas;
4. `docs/SEGURANCA.md` — controles de segurança;
5. `docs/QUALIDADE-CODIGO.md` — baseline de qualidade;
6. `docs/EAP.md` — escopo, POCs e critérios de aceite.

Não rediscuta decisão consolidada sem evidência de problema técnico concreto.

## Critério de decisão arquitetural

Ao escolher entre duas soluções, priorize nesta ordem:

1. representar corretamente a regra ou fluxo real do domínio;
2. preservar boundaries entre responsabilidades;
3. reduzir acoplamento conceitual;
4. manter a solução simples;
5. manter testabilidade e observabilidade;
6. aproveitar recursos nativos e produtivos do Yii3;
7. introduzir abstração somente quando houver justificativa concreta.

Duplicação localizada entre boundaries pode ser preferível a compartilhamento que introduza acoplamento indevido.

## Yii3 e organização

- Yii3 é framework de aplicação, não o modelo do domínio.
- Usar DI e configuração do Yii3; evitar service locator e estado global.
- Handlers HTTP devem permanecer finos.
- Organizar código por responsabilidade/módulo quando isso melhorar coesão; não criar árvores DDD vazias apenas por convenção.
- Não criar `Domain/`, `Application/`, `Infrastructure/`, repositories ou interfaces sem consumidores e responsabilidades reais.

## Persistência

- PostgreSQL é a persistência principal do HECATE.
- Preferir SQL explícito com `yiisoft/db` para queries e operações cuja intenção fique mais clara dessa forma.
- Sempre usar parâmetros/bindings; nunca concatenar input em SQL.
- ActiveRecord pode ser usado pontualmente na infraestrutura, mas não deve se tornar modelo compartilhado da aplicação ou do domínio.
- Não criar `GenericRepository`, `BaseRepository`, `BaseService`, Data Mapper genérico ou camadas equivalentes sem necessidade demonstrável.

## DTOs, modelos e domínio

- DTOs são adequados para input/output, forms, queries, grids, relatórios e integrações quando houver uma fronteira de dados real.
- Não criar DTO apenas para transferir os mesmos campos entre duas funções próximas sem ganho de boundary ou clareza.
- Entidades e Value Objects devem existir quando encapsularem invariantes, comportamento ou semântica de domínio relevante.
- Não criar Value Object para cada coluna ou identificador por padrão.
- Read models específicos são aceitáveis quando uma tela, relatório ou integração tiver necessidades próprias.

## Integrações

Manter fronteiras claras para SavaPage, CUPS, Keycloak, LDAP/AD, Catálogo MB, APIs externas e `hecate-agent`.

- Não acessar banco ou spool interno do SavaPage.
- Não executar comandos privilegiados arbitrários a partir do PHP.
- O `hecate-agent` deve expor somente operações fechadas e auditáveis.
- Criar adapter/interface somente quando houver uma integração real ou necessidade concreta de substituição/teste; não antecipar ports vazias.

## Segurança

Toda alteração deve considerar, conforme aplicável:

- autenticação e autorização no servidor;
- RBAC + políticas contextuais;
- CSRF;
- XSS;
- SQL injection;
- SSRF;
- command injection;
- privilege escalation;
- path traversal;
- secrets fora do código;
- least privilege;
- logs e auditoria sem segredos ou conteúdo dos documentos.

## Frontend

- Reutilizar componentes, layouts, widgets, grids, alerts e assets compartilhados.
- Não criar CSS ou JavaScript específico por página salvo quando houver justificativa funcional clara.
- Evitar duplicação visual e lógica de apresentação.

## Qualidade antes de concluir

Executar a suíte pertinente à alteração. Para validação integral:

```bash
composer qa
```

Baseline:

- PHPCS / PSR-12;
- PHPStan nível 8;
- Psalm;
- PHPUnit.

Ao alterar schema ou contratos de dados, localizar consumidores antes da mudança e atualizar testes relevantes.

## Regra prática para novas abstrações

Antes de criar uma camada, interface, repository, evento, entidade ou Value Object, responda:

1. qual problema concreto ela resolve agora?
2. qual boundary ou regra protege?
3. qual acoplamento real reduz?
4. qual teste, integração ou variação exige essa abstração?

Se as respostas forem apenas “boa prática”, “DDD”, “SOLID”, “pode ser útil no futuro” ou “para padronizar”, não introduza a abstração.
