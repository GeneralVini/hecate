# Resumo Executivo — HECATE

HECATE é a **Plataforma Institucional de Governança e Controle de Impressão** destinada a padronizar, controlar e auditar o serviço de impressão nas OM.

O escopo do produto prevê coordenação de identidade, autorização, políticas, cotas, contratos, aprovações, release, auditoria, indicadores e telemetria. Esses objetivos não equivalem a funcionalidades já homologadas.

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

## Estado e evidências atuais

O HECATE possui uma base Yii3 estruturada e iniciou a evolução para módulos com SQL explícito, DTOs e casos de uso. O código inclui cadastro/listagem de impressoras, indicadores e um esqueleto de reserva e solicitação de release. Os models ActiveRecord antigos continuam em `src/Model` como legado em transição.

A implementação permanece em estágio de POC. O login é demonstrativo e o adapter configurado de hold/release falha explicitamente por indisponibilidade. O esqueleto não é uma liberação operacional: OIDC, PIN, accounting, reconciliação e testes dos fluxos críticos ainda precisam ser demonstrados.

A suíte versionada contém dois smoke tests. Sua aprovação não comprova migrations, DI HTTP, autorização, integridade concorrente ou integração com SavaPage. Não há evidência consolidada na EAP que permita declarar o MVP pronto para produção.

## Bloqueios e prioridade de evolução

1. Demonstrar autenticação institucional e autorização contextual no servidor.
2. Validar cadastro, migrations e queries em PostgreSQL isolado, incluindo defaults, constraints e erros.
3. Demonstrar reserva e auditoria atômicas, idempotência e disputa de saldo com conexões independentes.
4. Homologar release e accounting suportados pelo SavaPage, com resultado desconhecido e recuperação após interrupção.
5. Validar implantação, backup/restore e operação em uma OM piloto.

O primeiro fluxo completo deve partir de um job já retido no SavaPage. O HECATE decide, reserva, solicita release e reconcilia o resultado; não recebe documentos nem substitui o spool como parte deste recorte.

Os critérios e seu status permanecem exclusivamente na [EAP](EAP.md). Este resumo sintetiza o estado e não cria um segundo checklist de MVP. As decisões estão em [DECISOES.md](DECISOES.md), com racional nos [ADRs](adr/README.md).
