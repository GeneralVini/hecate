# AGENTS.md — HECATE

Este arquivo orienta agentes de código e assistentes que alterem o HECATE.

## Regra central

> **A complexidade deve ser justificada pelo domínio.**

Não introduzir antecipadamente camadas, interfaces, repositories, eventos, Value Objects ou outras indireções sem problema concreto a resolver.

## Contexto obrigatório

Antes de alterar arquitetura ou código relevante, consultar:

1. `docs/ARQUITETURA.md` — arquitetura, boundaries, fluxos e integrações;
2. `docs/DECISOES.md` — decisões técnicas e respectivos motivos;
3. `docs/SEGURANCA.md` — controles de segurança e auditoria;
4. `docs/DESENVOLVIMENTO.md` — ambiente, qualidade e documentação de código;
5. `docs/EAP.md` — escopo, POCs e critérios de aceite.

A EAP é a fonte única para dizer se algo está concluído, validado ou ainda pendente.

## Critério de decisão

Ao escolher entre soluções, priorizar:

1. representar corretamente o domínio real;
2. preservar boundaries entre responsabilidades;
3. reduzir acoplamento conceitual;
4. manter a solução simples;
5. manter testabilidade e observabilidade;
6. aproveitar recursos nativos e produtivos do Yii3;
7. introduzir abstração somente quando houver justificativa concreta.

Duplicação localizada entre boundaries pode ser preferível a compartilhamento que aumente acoplamento.

## Yii3 e organização

- Yii3 é framework de aplicação, não o modelo do domínio.
- Usar DI e configuração do Yii3.
- Handlers HTTP devem permanecer finos.
- Organizar código por responsabilidade/módulo quando isso melhorar coesão.
- Não criar árvores DDD vazias por convenção.

## Persistência

- PostgreSQL é a persistência principal.
- Preferir SQL explícito e parametrizado via `yiisoft/db` quando isso tornar a intenção mais clara.
- ActiveRecord pode ser usado pontualmente na infraestrutura, mas não como modelo compartilhado da aplicação.
- Não criar abstrações genéricas de persistência sem necessidade demonstrável.

## DTOs e domínio

- DTOs são adequados para input/output, forms, queries, grids, relatórios e integrações quando houver fronteira real.
- Não criar DTO apenas para mover os mesmos campos entre funções próximas.
- Entidades e Value Objects devem existir quando encapsularem semântica ou invariantes relevantes.
- Read models específicos são aceitáveis quando uma tela, relatório ou integração tiver necessidades próprias.

## Integrações

Manter fronteiras claras para SavaPage, CUPS, Keycloak, LDAP/AD, Catálogo MB, APIs externas e `hecate-agent`.

O HECATE deve depender apenas de interfaces e capacidades homologadas desses componentes. O `hecate-agent` deve oferecer operações fechadas e auditáveis.

## Frontend

- Reutilizar componentes, layouts, grids, alerts e assets compartilhados.
- Não criar CSS ou JavaScript específico por página salvo justificativa funcional clara.
- Evitar duplicação visual e lógica de apresentação.

## Qualidade

Para validação integral:

```bash
composer qa
```

Baseline:

- PHPCS / PSR-12;
- PHPStan nível 8;
- Psalm;
- PHPUnit.

Ao alterar schema ou contratos de dados, localizar consumidores antes da mudança e atualizar os testes pertinentes.

## Novas abstrações

Antes de criar uma camada, interface, repository, evento, entidade ou Value Object, responder:

1. qual problema concreto resolve agora?
2. qual boundary ou regra protege?
3. qual acoplamento real reduz?
4. qual teste, integração ou variação exige essa abstração?

Se as respostas forem apenas “boa prática”, “DDD”, “SOLID” ou “pode ser útil no futuro”, não introduzir a abstração.

## Documentação

Não criar novo arquivo Markdown quando o conteúdo puder ser incorporado claramente a um documento canônico existente.

Documentos canônicos em `docs/`:

```text
EAP.md
ARQUITETURA.md
DECISOES.md
DESENVOLVIMENTO.md
SEGURANCA.md
IMPLANTACAO.md
IDENTIDADE-VISUAL.md
```

Novo `.md` exige responsabilidade própria, público ou ciclo de manutenção distinto.
