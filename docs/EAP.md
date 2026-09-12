# EAP — Entrega Completa do HECATE

## 1. Finalidade

Esta EAP organiza a entrega completa do **HECATE — Plataforma Institucional de Governança e Controle de Impressão** em pacotes de trabalho orientados a produto, implantação, qualidade, homologação e operação.

Ela funciona como checklist prático de entrega. Não substitui cronograma, plano formal de projeto ou procedimento técnico detalhado.

A EAP é também a **fonte única de acompanhamento do MVP**. O escopo, as POCs, os critérios de aceite e os itens fora do MVP permanecem consolidados neste documento, evitando duplicação com documentação paralela.

## 2. Objetivo da entrega

Entregar uma solução institucional capaz de centralizar o fluxo de impressão, autenticar usuários do domínio, controlar acesso a filas e impressoras, aplicar cotas P&B/colorida, controlar contratos, exigir liberação deliberada de jobs, manter auditoria, monitorar o stack e permitir implantação padronizada em diferentes OM.

### 2.1. Regra de acompanhamento

- `[ ]` pendente;
- `[x]` concluído e validado.

Um pacote somente deve ser marcado como concluído quando todos os itens aplicáveis e seu critério de conclusão estiverem atendidos.

### 2.2. Quadro geral

- [ ] **3.1.1** Arquitetura e decisões técnicas
- [ ] **3.1.2** Identidade visual e experiência do usuário
- [ ] **3.1.3** Base da aplicação web Yii3
- [ ] **3.1.4** Qualidade, compliance e padrões de código
- [ ] **3.1.5** Banco de dados HECATE
- [ ] **3.1.6** Samba AD / LDAP
- [ ] **3.1.7** Catálogo MB
- [ ] **3.1.8** Keycloak e autenticação
- [ ] **3.1.9** SavaPage
- [ ] **3.1.10** CUPS
- [ ] **3.1.11** HECATE Agent
- [ ] **3.1.12** Cadastro, descoberta e telemetria de impressoras
- [ ] **3.1.13** Organização e usuários
- [ ] **3.1.14** Políticas de acesso a impressoras
- [ ] **3.1.15** Cotas P&B e colorida
- [ ] **3.1.16** Transferência de cotas
- [ ] **3.1.17** Contratos
- [ ] **3.1.18** Jobs e liberação segura
- [ ] **3.1.19** Monitoramento, diagnóstico e suprimentos
- [ ] **3.1.20** Segurança e auditoria
- [ ] **3.1.21** Testes e homologação técnica
- [ ] **3.1.22** Empacotamento e distribuição
- [ ] **3.1.23** Implantação piloto
- [ ] **3.1.24** Documentação operacional
- [ ] **3.1.25** Replicação institucional
- [ ] **3.1.26** Release 1.0

# 3. Estrutura Analítica do Produto

## 3.1. HECATE — Produto completo

### 3.1.1. Arquitetura e decisões técnicas

- [ ] Consolidar arquitetura lógica e física.
- [ ] Consolidar responsabilidades de HECATE, SavaPage, CUPS, Keycloak, PostgreSQL, Samba AD, Catálogo MB, Podman, Nexus e `hecate-agent`.
- [ ] Definir fluxo de impressão, autenticação, autorização e liberação.
- [ ] Definir política de retenção de conteúdo e metadados.
- [ ] Definir padrão de implantação por OM.
- [ ] Definir matriz Oracle Linux/PHP/Yii3/PostgreSQL/SavaPage/Keycloak.
- [ ] Manter decisões em `docs/DECISOES.md`.

**Critério de conclusão:** arquitetura documentada, coerente e sem dependência de mecanismos não homologados.

### 3.1.2. Identidade visual e experiência do usuário

- [ ] Aplicar identidade HECATE sem nomenclaturas legadas.
- [ ] Aplicar logos, símbolo e favicons.
- [ ] Aplicar background de login e dashboard.
- [ ] Padronizar paleta, tipografia, cards, tabelas, alertas e estados.
- [ ] Garantir responsividade e contraste adequados.

**Critério de conclusão:** identidade e navegação consistentes nos módulos web.

### 3.1.3. Base da aplicação web Yii3

- [x] Adotar o template oficial `yiisoft/app` como referência estrutural.
- [x] Usar `src/`, `config/`, `public/`, `assets/`, `tests/` e `yii` conforme o padrão Yii3.
- [x] Configurar autoload PSR-4 pelo Composer.
- [x] Configurar container DI/PSR-11.
- [x] Configurar HTTP com PSR-7/PSR-17.
- [x] Configurar pipeline middleware PSR-15.
- [x] Configurar roteamento explícito.
- [x] Configurar logs e tratamento de exceções.
- [x] Configurar PostgreSQL via `yiisoft/db-pgsql`.
- [x] Implementar dashboard inicial.
- [x] Implementar cadastro e listagem inicial de impressoras.
- [x] Manter CSRF ativo nas operações web mutáveis.
- [ ] Implementar autenticação Keycloak/OIDC.
- [ ] Implementar autorização por perfil.
- [ ] Consolidar componentes visuais reutilizáveis em `src/Web/Shared` e assets compartilhados.
- [ ] Implementar paginação, filtros e pesquisa conforme os módulos crescerem.

**Critério de conclusão:** aplicação web Yii3 executável, autenticada, tipada e pronta para os módulos funcionais.

### 3.1.4. Qualidade, compliance e padrões de código

- [x] Adotar PSR-1, PSR-4 e PSR-12.
- [x] Adotar PSR-7, PSR-11, PSR-15 e PSR-17 onde fazem parte da arquitetura Yii3.
- [x] Configurar `composer validate`.
- [x] Configurar ECS/PSR-12 como coding standard e autofix.
- [x] Configurar Rector com regras compatíveis com a versão mínima de PHP.
- [x] Configurar Lefthook para `pre-commit` e `pre-push`.
- [x] Configurar PHPStan.
- [x] Configurar Psalm.
- [x] Configurar PHPUnit.
- [x] Versionar `composer.lock`.
- [x] Configurar scripts Composer `fix`, `lint`, `rector`, `stan`, `psalm`, `test` e `qa`.
- [x] Configurar `make setup`, `make hooks`, `make fix` e `make qa`.
- [x] Configurar GitHub Actions para `composer install`, `composer validate` e `composer qa`.
- [x] Sincronizar `composer.lock` com Rector, ECS e a versão de PHPStan requerida pelo novo baseline. Evidência da revisão de 2026-09-12: `composer validate --no-interaction --no-check-publish` aprovado; lock contém Rector 2.6.6, ECS 13.3.2 e PHPStan 2.2.13. Isso não comprova aprovação do QA/CI.
- [ ] Obter pipeline integral de QA aprovado após a migração do baseline.
- [ ] Configurar `composer audit` conforme política de vulnerabilidades.
- [ ] Configurar checks obrigatórios antes de merge na branch principal.
- [ ] Manter SonarQube opcional/futuro, sem torná-lo requisito do ambiente local.

**Critério de conclusão:** QA reproduzível localmente e no CI, sem redução artificial de regras para aprovar código.

### 3.1.5. Banco de dados HECATE

- [x] Configurar driver PostgreSQL para Yii3.
- [x] Criar migration inicial Yii3.
- [x] Criar tabelas iniciais de divisão, impressora, cota, contrato e auditoria.
- [ ] Executar e validar a migration inicial em PostgreSQL local.
- [ ] Modelar OM, usuários, filas e políticas.
- [ ] Modelar reservas e transferências de cota.
- [ ] Modelar jobs sem armazenar conteúdo documental.
- [ ] Definir índices e constraints de concorrência.
- [ ] Definir backup, restore e teste de restauração.

**Critério de conclusão:** banco íntegro, migrável, auditável e restaurável.

### 3.1.6. Samba AD / LDAP

- [ ] Configurar LDAPS.
- [ ] Usar contas técnicas somente leitura.
- [ ] Consultar usuários, grupos e memberships necessários.
- [ ] Implementar diagnóstico de conectividade.

**Critério de conclusão:** identidade institucional consumida sem modificação do domínio.

### 3.1.7. Catálogo MB

- [ ] Documentar endpoints utilizados.
- [ ] Integrar API REST.
- [ ] Consultar dados funcionais e divisão.
- [ ] Implementar cache e timestamp de sincronização.
- [ ] Permitir override local controlado e auditado.

**Critério de conclusão:** lotação funcional determinada de forma auditável.

### 3.1.8. Keycloak e autenticação

- [ ] Implantar Keycloak em Podman.
- [ ] Configurar database próprio.
- [ ] Federar com LDAP/AD em leitura.
- [ ] Configurar realm, client OIDC e roles.
- [ ] Implementar login, logout e renovação/expiração de sessão.
- [ ] Preparar MFA futuro.

**Critério de conclusão:** autenticação institucional integrada e administrável.

### 3.1.9. SavaPage

- [ ] Homologar integração suportada para accounting e release.
- [ ] Mapear usuários e filas.
- [ ] Integrar retenção, contabilização e liberação.
- [ ] Não manipular banco ou spool interno diretamente.

**Critério de conclusão:** HECATE controla política e autorização sem acoplamento não suportado ao SavaPage.

### 3.1.10. CUPS

- [ ] Configurar filas físicas no servidor Linux.
- [ ] Restringir exposição direta aos clientes.
- [ ] Definir drivers/IPP Everywhere quando aplicável.
- [ ] Integrar fluxo SavaPage -> CUPS -> impressora.

**Critério de conclusão:** transporte de impressão centralizado e controlado.

### 3.1.11. HECATE Agent

- [ ] Implementar serviço nativo `hecate-agent` via systemd.
- [ ] Definir API local de operações fechadas.
- [ ] Proibir shell arbitrário e `sudo` genérico pelo PHP.
- [ ] Implementar autenticação, autorização e auditoria entre Web e Agent.

**Critério de conclusão:** operações privilegiadas isoladas da aplicação web.

### 3.1.12. Cadastro, descoberta e telemetria de impressoras

- [x] Implementar cadastro mínimo de impressoras no MVP Yii3.
- [x] Proteger cadastro e detecção com CSRF no fluxo web.
- [ ] Implementar descoberta via Agent.
- [ ] Priorizar IPP/IPPS e SNMPv3.
- [ ] Permitir SNMPv2c read-only como fallback controlado.
- [ ] Detectar fabricante, modelo, serial, recursos e suprimentos quando disponíveis.
- [ ] Garantir que falha de telemetria não bloqueie impressão.

**Critério de conclusão:** inventário consistente e descoberta replicável.

### 3.1.13. Organização e usuários

- [ ] Modelar OM, divisões, usuários e vínculos.
- [ ] Integrar AD e Catálogo MB sem duplicar suas responsabilidades.
- [ ] Implementar tratamento para dados ausentes ou desatualizados.

**Critério de conclusão:** contexto organizacional disponível para políticas e auditoria.

### 3.1.14. Políticas de acesso a impressoras

- [ ] Definir acesso por usuário, grupo, setor, andar ou OM.
- [ ] Validar autorização no servidor.
- [ ] Restringir impressão direta quando tecnicamente possível.

**Critério de conclusão:** apenas usuários autorizados acessam cada recurso de impressão.

### 3.1.15. Cotas P&B e colorida

- [ ] Implementar saldos separados P&B/colorido.
- [ ] Implementar `disponível = alocado - consumido - reservado`.
- [ ] Reservar antes da liberação.
- [ ] Converter reserva em consumo conforme accounting confirmado, inclusive consumo parcial.
- [ ] Liberar reserva em falha, cancelamento ou expiração confirmados sem consumo; preservar saldo reservado em resultado incerto até reconciliação.
- [ ] Implementar comportamentos BLOQUEAR, AVISAR_E_PERMITIR e EXIGIR_APROVACAO.

**Critério de conclusão:** reservas e consumo sem dupla contabilização, respeitando a política de limite. Na política BLOQUEAR, novas reservas não podem exceder o saldo disponível.

Permanece aberta a representação de excedente em AVISAR_E_PERMITIR e EXIGIR_APROVACAO, inclusive quando o consumo real superar a estimativa reservada. Accounting deve preservar o consumo real; não truncar valores para aparentar saldo não negativo. O código atual implementa somente BLOQUEAR.

### 3.1.16. Transferência de cotas

- [ ] Implementar transferência autorizada entre unidades organizacionais.
- [ ] Validar saldo e concorrência.
- [ ] Registrar origem, destino, quantidade, responsável, motivo e timestamp.

**Critério de conclusão:** transferências rastreáveis e transacionais.

### 3.1.17. Contratos

- [ ] Implementar contrato por consumo.
- [ ] Implementar contrato por franquia mensal.
- [ ] Manter valores P&B e colorido separados.
- [ ] Separar franquia contratual da distribuição interna de cotas.

**Critério de conclusão:** consumo contratual apurável e auditável.

### 3.1.18. Jobs e liberação segura

- [ ] Sincronizar jobs retidos no SavaPage.
- [ ] Validar ownership, política, impressora e cota antes da liberação.
- [ ] Implementar PIN HECATE com hash forte, expiração e rate limit.
- [ ] Liberar por interface suportada pelo SavaPage.
- [ ] Auditar sucesso, falha e cancelamento.

**Critério de conclusão:** nenhum job é liberado sem autorização e rastreabilidade.

### 3.1.19. Monitoramento, diagnóstico e suprimentos

- [ ] Monitorar HECATE, PostgreSQL, Keycloak, SavaPage, CUPS e Agent.
- [ ] Coletar telemetria de impressoras sem bloquear o fluxo principal.
- [ ] Exibir status operacional e suprimentos.
- [ ] Implementar diagnóstico acionável para suporte local.

**Critério de conclusão:** equipe local consegue identificar indisponibilidades e causas prováveis.

### 3.1.20. Segurança e auditoria

- [ ] Implementar autenticação e autorização de menor privilégio.
- [x] Manter CSRF em operações web mutáveis do MVP atual.
- [ ] Implementar headers de segurança.
- [ ] Evitar XSS, SQL injection, SSRF, command injection e traversal.
- [ ] Não registrar secrets, PIN ou conteúdo documental.
- [ ] Preservar trilha de auditoria administrativa.

**Critério de conclusão:** controles de `docs/SEGURANCA.md` implementados e testados.

### 3.1.21. Testes e homologação técnica

- [x] Manter smoke test coerente com o autoload Yii3.
- [x] Executar QA integral no CI.
- [ ] Expandir testes unitários além do smoke test.
- [ ] Criar testes de integração de banco e serviços críticos.
- [ ] Testar autorização, cotas, concorrência e release.
- [ ] Testar falhas de LDAP, Catálogo, SavaPage, CUPS e Agent.

**Critério de conclusão:** funcionalidades críticas possuem cobertura compatível com o risco.

### 3.1.22. Empacotamento e distribuição

- [ ] Definir artefatos RPM/OCI.
- [ ] Publicar artefatos homologados no Nexus institucional.
- [ ] Definir versão, assinatura e rollback.
- [ ] Manter Nexus fora do caminho crítico de execução do serviço.

**Critério de conclusão:** instalação reproduzível sem download arbitrário da Internet.

### 3.1.23. Implantação piloto

- [ ] Selecionar OM piloto.
- [ ] Implantar VM dedicada.
- [ ] Validar integrações e fluxo completo.
- [ ] Medir incidentes, consumo e experiência operacional.
- [ ] Registrar correções antes da replicação.

**Critério de conclusão:** piloto executado e aprovado tecnicamente.

### 3.1.24. Documentação operacional

- [ ] Manual de implantação.
- [ ] Manual de operação.
- [ ] Checklist de instalação.
- [ ] Checklist de validação.
- [ ] Guia de filas e bloqueio de impressão direta.
- [ ] Guia de backup, restore e troubleshooting.

**Critério de conclusão:** equipe local consegue instalar, operar e diagnosticar a solução.

### 3.1.25. Replicação institucional

- [ ] Parametrizar diferenças por OM.
- [ ] Evitar forks locais da aplicação.
- [ ] Definir processo de atualização e rollback.
- [ ] Validar replicação em OM com porte diferente do piloto.

**Critério de conclusão:** implantação repetível sem customização estrutural por OM.

### 3.1.26. Release 1.0

- [ ] Todos os pacotes críticos concluídos.
- [ ] QA e testes aprovados.
- [ ] Documentação operacional publicada.
- [ ] Artefatos versionados e distribuíveis.
- [ ] Segurança e backup validados.
- [ ] Piloto aprovado.

**Critério de conclusão:** HECATE 1.0 apto para implantação institucional controlada.

# 4. MVP — recorte de validação

O MVP não é mantido em documento separado. Este recorte define o mínimo necessário para validar a arquitetura antes da implantação completa.

## 4.1. Escopo do MVP

- [x] Base web Yii3 estruturada a partir do `yiisoft/app`.
- [x] HTTP PSR-7/PSR-17, middleware PSR-15 e container DI/PSR-11.
- [x] PostgreSQL configurado e migration inicial criada.
- [x] Dashboard inicial.
- [x] Cadastro e listagem de impressoras.
- [x] CSRF nas operações mutáveis já implementadas.
- [x] `composer.lock` versionado.
- [ ] ECS, Rector, PHPStan, Psalm e PHPUnit integrados ao `composer qa`, com Lefthook homologado localmente.
- [x] GitHub Actions executando QA integral antes da migração do baseline.
- [ ] Migration inicial executada e validada em PostgreSQL local.
- [ ] Aplicação iniciada localmente e fluxos web básicos validados.
- [ ] Integração mínima com identidade, SavaPage e CUPS demonstrada.
- [ ] Primeira descoberta de impressora pelo `hecate-agent` demonstrada.

### 4.1.1. Estado da evolução arquitetural

O código contém componentes por módulo para Printing, Quota, IdentityAccess, Audit e Monitoring, com consultas SQL, DTOs e um esqueleto de reserva/solicitação de release. A migration `M260911210000ReleaseArchitectureSlice` está presente, além da migration inicial. Isso registra implementação parcial, não homologação.

O login ainda é demonstrativo; o gateway configurado é `UnavailableHeldJobGateway`. Os dois smoke tests atuais não demonstram o fluxo novo. A consolidação documental e arquitetural não altera por si só o estado dos critérios de aceite.

Pendências de validação da fatia arquitetural, associadas às POCs 1, 3, 4 e 6:

- [ ] Validar ambas as migrations em PostgreSQL isolado e registrar versão, comando e resultado.
- [ ] Testar cadastro/listagem e indicadores SQL, defaults, mapeamento de tipos e erros esperados.
- [ ] Demonstrar identidade confiável, permissão e escopo divisão/impressora no fluxo integrado.
- [ ] Testar reserva e auditoria atômicas, rollback, idempotência e concorrência com conexões independentes.
- [ ] Integrar PIN e homologar release de job já retido no SavaPage.
- [ ] Demonstrar accounting e reconciliação de solicitações aceitas, pendentes ou de resultado desconhecido.

## 4.2. POCs críticas

- [ ] **POC 1 — Base Yii3:** bootstrap HTTP, DI, middleware, CSRF, PostgreSQL, migration, dashboard, cadastro/listagem e QA.
- [ ] **POC 2 — Clientes Windows e Ubuntu:** validar username, documento, origem, páginas, P&B/colorida e envio ao SavaPage sem prompts redundantes.
- [ ] **POC 3 — Hold/release SavaPage:** liberar job já retido exclusivamente por interface suportada, sem acesso direto a banco/spool e sem automação de UI.
- [ ] **POC 4 — ACL por divisão:** materializar no SavaPage as impressoras permitidas por divisão.
- [ ] **POC 5 — Exceção temporária:** permitir acesso excepcional auditado sem alterar grupos do AD.
- [ ] **POC 6 — Accounting e cota:** validar contagem P&B/colorida e reserva transacional antes do release.
- [ ] **POC 7 — Descoberta multi-vendor:** validar IPP/IPPS, SNMPv3, SNMPv2c read-only e EWS/API conforme disponibilidade.
- [ ] **POC 8 — Catálogo MB:** validar lookup, lotação/divisão, cache, indisponibilidade, divergência e override auditado.

## 4.3. Fluxos mínimos a demonstrar

```text
usuário Samba AD
  -> Catálogo MB informa divisão
  -> HECATE aplica política
  -> usuário visualiza apenas impressoras autorizadas
```

```text
job -> SavaPage retém
    -> HECATE valida identidade/política/cota/impressora
    -> reserva cota
    -> usuário confirma com PIN
    -> SavaPage libera
    -> CUPS entrega
    -> accounting confirma consumo
```

Em falha, cancelamento ou expiração confirmados sem consumo, a reserva deve ser devolvida sem incrementar consumo. Timeout ou interrupção após envio não comprova falha: preservar a reserva e reconciliar o resultado antes de devolver saldo ou reenviar. Accounting deve tratar consumo efetivo, inclusive parcial, sem duplicação.

## 4.4. Critérios de aceite do MVP

- [ ] Base Yii3 executável e reproduzível.
- [ ] Migration PostgreSQL validada.
- [ ] Identidade institucional consumida sem escrita no AD.
- [ ] Associação usuário -> divisão demonstrada.
- [ ] Política divisão -> impressora demonstrada.
- [ ] Job retido e liberado pelo HECATE via integração suportada.
- [ ] Accounting P&B/colorida confiável.
- [ ] Reserva de cota transacional demonstrada.
- [ ] Auditoria básica disponível.
- [ ] Cadastro e descoberta de impressora demonstrados.
- [ ] Monitoramento mínimo da stack disponível.
- [ ] ECS, Rector, PHPStan, Psalm e PHPUnit aprovados no CI após a migração do baseline.

## 4.5. Fora do MVP

- alta disponibilidade;
- cluster PostgreSQL;
- substituição integral do parque de impressoras;
- OCR de páginas EWS;
- browser headless para telemetria;
- aplicativo móvel nativo;
- MFA obrigatório na primeira POC;
- automação de alterações no Samba AD.

# 5. Incremento de leitura e federação

As decisões das seções 20–21 de [DECISOES.md](DECISOES.md) estão aceitas arquiteturalmente. Não comprovam implementação ou homologação. A inclusão da federação no MVP/release e a sequência de entrega permanecem abertas; este incremento não altera silenciosamente o recorte da seção 4.

## 5.1. Leitura e escrita

- [ ] Integrar identidade confiável, RBAC e resolução obrigatória de escopo nas leituras de inventário e indicadores.
- [ ] Distinguir administrador global local, gestor com contratos autorizados e consumidor federado, preservando segregação funcional.
- [ ] Testar acesso negado, escopo vazio, troca indevida de contrato/divisão e ausência de vazamento em totais, filtros, paginação e exportações.
- [ ] Integrar autorização e auditoria no cadastro de impressoras.

Na revisão documental, `src/Web/Printer/Index/Action.php` chama `PrinterListQuery::all()` sem autorização/escopo; `src/Web/HomePage/Action.php` usa `InventoryMetricsQuery::get()` com totais globais. A consulta `availableTo()` limita por divisão, mas está ligada a `print.release`; não representa a permissão de acompanhamento contratual. Correção mínima: resolver identidade/permissão/escopo no servidor e exigir esse escopo nas consultas, concedendo visão global apenas explicitamente.

`src/Printing/Application/RegisterPrinter.php` grava sem ator, política ou auditoria. Correção mínima: incorporar autorização do cadastro e registro auditável junto à persistência. O middleware atual em `config/web/di/application.php` oferece sessão e CSRF, mas não autenticação institucional/RBAC. CSRF não substitui esses controles. A implementação desses ajustes exige testes e permanece pendente; esta revisão não refatora o código.

A ação `src/Web/Printer/Detect/Action.php` apenas verifica existência e redireciona; não chama o agente nem coleta telemetria. Ao implementar a integração, autorizar a operação e o alvo antes da chamada e auditar seu resultado. A presença do botão e do CSRF não comprova descoberta funcional.

## 5.2. Federação

Não há implementação de enrollment, sincronização, APIs federadas ou configuração Master em `src/` e `config/` na revisão deste incremento.

- [ ] Definir recorte de entrega e responsabilidades operacionais do Master.
- [ ] Homologar autenticação M2M, enrollment, associação à OM, rotação e revogação.
- [ ] Fechar métricas, dimensões, granularidade, períodos/fuso, precisão, retenção e acesso central.
- [ ] Fechar envelope, versionamento, confirmação, duplicatas, correções tardias e comportamento após restore.
- [ ] Implementar agregação local e push automático com retomada e limites operacionais.
- [ ] Validar pacote genérico e novo enrollment ao replicar para outra OM.
- [ ] Testar isolamento entre OMs, credencial revogada, replay, falha de rede, envio duplicado, atualização e restore.
- [ ] Demonstrar que indisponibilidade do Master não bloqueia a operação local.

**Critério de conclusão:** agregados autorizados chegam ao Master sem duplicação ou exportação de detalhes operacionais, com recuperação e rastreabilidade demonstradas.
