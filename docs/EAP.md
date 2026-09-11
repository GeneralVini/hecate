# EAP — Entrega Completa do HECATE

## 1. Finalidade

Esta EAP organiza a entrega completa do **HECATE — Plataforma Institucional de Governança e Controle de Impressão** em pacotes de trabalho orientados a produto, implantação, qualidade, homologação e operação.

Ela funciona como checklist prático de entrega. Não substitui cronograma, plano formal de projeto ou procedimento técnico detalhado.

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
- [ ] Implementar autenticação Keycloak/OIDC.
- [ ] Implementar autorização por perfil.
- [ ] Consolidar componentes visuais reutilizáveis em `src/Web/Shared` e assets compartilhados.
- [ ] Implementar paginação, filtros e pesquisa conforme os módulos crescerem.

**Critério de conclusão:** aplicação web Yii3 executável, autenticada, tipada e pronta para os módulos funcionais.

### 3.1.4. Qualidade, compliance e padrões de código

- [x] Adotar PSR-1, PSR-4 e PSR-12.
- [x] Adotar PSR-7, PSR-11, PSR-15 e PSR-17 onde fazem parte da arquitetura Yii3.
- [x] Configurar `composer validate`.
- [x] Configurar PHPCS/PSR-12.
- [x] Configurar PHPStan nível 8.
- [x] Configurar Psalm.
- [x] Configurar PHPUnit.
- [x] Versionar `composer.lock`.
- [x] Configurar scripts Composer `lint`, `stan`, `psalm`, `test` e `qa`.
- [x] Configurar `make setup` e `make qa`.
- [x] Configurar GitHub Actions para `composer install`, `composer validate` e `composer qa`.
- [ ] Configurar `composer audit` conforme política de vulnerabilidades.
- [ ] Configurar checks obrigatórios antes de merge na branch principal.
- [ ] Evoluir PHPStan para níveis superiores quando o código permitir.
- [ ] Manter SonarQube opcional/futuro, sem torná-lo requisito do ambiente local.

**Critério de conclusão:** QA reproduzível localmente e no CI, sem redução artificial de regras para aprovar código.

### 3.1.5. Banco de dados HECATE

- [x] Configurar driver PostgreSQL para Yii3.
- [x] Criar migration inicial Yii3.
- [x] Criar tabelas iniciais de divisão, impressora, cota, contrato e auditoria.
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
- [ ] Converter reserva em consumo no sucesso.
- [ ] Liberar reserva em falha, cancelamento ou expiração.
- [ ] Implementar comportamentos BLOQUEAR, AVISAR_E_PERMITIR e EXIGIR_APROVACAO.

**Critério de conclusão:** controle concorrente de cotas sem saldo negativo ou dupla utilização.

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
- [ ] Manter CSRF em operações web mutáveis.
- [ ] Implementar headers de segurança.
- [ ] Evitar XSS, SQL injection, SSRF, command injection e traversal.
- [ ] Não registrar secrets, PIN ou conteúdo documental.
- [ ] Preservar trilha de auditoria administrativa.

**Critério de conclusão:** controles de `docs/SEGURANCA.md` implementados e testados.

### 3.1.21. Testes e homologação técnica

- [ ] Expandir testes unitários além do smoke test.
- [ ] Criar testes de integração de banco e serviços críticos.
- [ ] Testar autorização, cotas, concorrência e release.
- [ ] Testar falhas de LDAP, Catálogo, SavaPage, CUPS e Agent.
- [ ] Executar QA integral no CI.

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
