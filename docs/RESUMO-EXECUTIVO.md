# Resumo Executivo — HECATE

HECATE é a **Plataforma Institucional de Governança e Controle de Impressão** destinada a padronizar, controlar e auditar o serviço de impressão nas OM.

A solução coordena identidade, autorização, políticas, cotas P&B/colorida, contratos, aprovações, liberação segura, auditoria, indicadores, monitoramento e telemetria, mantendo responsabilidades claras entre HECATE, Keycloak, Samba AD, SavaPage, CUPS, PostgreSQL e `hecate-agent`.

## Diretriz arquitetural

O HECATE adota **Yii3 + monólito modular + DDD pragmático**.

> **A complexidade deve ser justificada pelo domínio.**

O projeto não deve antecipar padrões DDD, camadas, interfaces, repositories, eventos, Value Objects ou outras indireções sem necessidade concreta.

O desenho preferencial é o mais simples que preserve:

- boundaries claros;
- segurança;
- testabilidade;
- baixo acoplamento conceitual;
- evolução previsível.

Duplicação localizada entre boundaries pode ser preferível a compartilhamento que introduza acoplamento indevido.

## Persistência

PostgreSQL é a persistência principal. SQL explícito via Yii DB/Command é preferível quando expressar melhor a intenção da operação.

ActiveRecord pode ser utilizado pontualmente, mas não deve se tornar modelo global compartilhado da aplicação.

Repositories, DTOs, read models, entidades e Value Objects são ferramentas condicionais. Devem existir somente quando houver regra de domínio, integração, necessidade de teste ou problema real de acoplamento que justifique sua criação.

## Integrações

- Samba AD: identidade institucional, somente leitura;
- Catálogo MB: atributos funcionais e organizacionais;
- Keycloak: autenticação SSO/OIDC;
- HECATE: governança, autorização, cotas, contratos, aprovação e auditoria;
- SavaPage: retenção, accounting e enforcement;
- CUPS: spool e transporte do job;
- `hecate-agent`: operações privilegiadas locais e diagnóstico;
- Nexus: distribuição de artefatos homologados.

O HECATE não deve escrever diretamente no banco ou spool interno do SavaPage e não deve executar operações privilegiadas arbitrárias a partir da aplicação web.

## Evolução

Novas abstrações devem responder a uma necessidade real. Antes de adicioná-las, deve ser possível explicar:

1. qual problema concreto resolvem;
2. qual boundary protegem;
3. qual acoplamento reduzem;
4. qual regra, integração, teste ou variação exige sua existência.

Se a justificativa for apenas aderência a padrão ou possibilidade futura, a abstração não deve ser introduzida.

Este resumo deve ser lido em conjunto com `docs/ddd.md`, `docs/ARQUITETURA.md`, `docs/DECISOES.md` e `AGENTS.md`.
