# EAP — Entrega Completa do HECATE

## 1. Finalidade

Esta EAP organiza a entrega completa do **HECATE — Plataforma Institucional de Governança e Controle de Impressão** em pacotes de trabalho orientados a produto, implantação, qualidade, homologação e operação.

Ela serve como referência prática para desenvolvimento, homologação, piloto, replicação em outras OM e sustentação. Não substitui cronograma, plano formal de projeto ou procedimento técnico detalhado.

---

## 2. Objetivo da entrega

Entregar uma solução institucional capaz de centralizar o fluxo de impressão, autenticar usuários do domínio, relacionar identidade e lotação, controlar acesso a filas e impressoras, aplicar cotas separadas P&B/colorida, controlar contratos, exigir liberação deliberada de jobs, manter auditoria, monitorar o stack e as impressoras, reduzir bypass do fluxo controlado e permitir implantação padronizada em diferentes OM.

### 2.1. Regra de acompanhamento

A EAP deve ser usada também como **checklist oficial de entrega**. Cada item executável possui checkbox Markdown:

- `[ ]` pendente;
- `[x]` concluído e validado.

Um pacote de trabalho somente deve ter sua checkbox de entrega marcada quando **todos os itens aplicáveis estiverem concluídos e o critério de conclusão tiver sido atendido**. Itens não aplicáveis devem ser justificados em revisão/decisão técnica, e não simplesmente marcados como concluídos.

### 2.2. Quadro geral de entregas

- [ ] **3.1.1** Arquitetura e decisões técnicas
- [ ] **3.1.2** Identidade visual e experiência do usuário
- [ ] **3.1.3** Base da aplicação web
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

---

# 3. Estrutura Analítica do Produto

## 3.1. HECATE — Produto completo

### 3.1.1. Arquitetura e decisões técnicas

- [ ] Consolidar arquitetura lógica e física.
- [ ] Consolidar responsabilidades de HECATE, SavaPage, CUPS, Keycloak, PostgreSQL, Samba AD, Catálogo MB, Podman, Nexus e `hecate-agent`.
- [ ] Definir fluxo de impressão, autenticação, autorização e liberação.
- [ ] Definir política de retenção de conteúdo e metadados.
- [ ] Definir padrão de implantação por OM.
- [ ] Definir matriz de compatibilidade Oracle Linux/PHP/Yii2/PostgreSQL/SavaPage/Keycloak.
- [ ] Manter decisões relevantes em `docs/DECISOES.md`.

**Critério de conclusão:** arquitetura documentada, coerente e sem dependência de mecanismos não homologados.

- [ ] **Pacote 3.1.1 concluído e validado.**

---

### 3.1.2. Identidade visual e experiência do usuário

- [ ] Aplicar identidade HECATE sem nomenclaturas legadas.
- [ ] Aplicar logo horizontal, vertical, símbolo e favicons.
- [ ] Aplicar background de login com área livre para o formulário HTML real.
- [ ] Aplicar background discreto do dashboard.
- [ ] Padronizar paleta, tipografia, cards, tabelas, alertas e estados.
- [ ] Implementar sidebar retrátil, topbar, breadcrumb/navbar contextual e footerbar.
- [ ] Garantir responsividade mínima em desktop e tablet.
- [ ] Garantir contraste, legibilidade e navegação consistente.

**Critério de conclusão:** identidade e navegação consistentes em login, dashboard e módulos administrativos.

- [ ] **Pacote 3.1.2 concluído e validado.**

---

### 3.1.3. Base da aplicação web

- [ ] Manter estrutura compatível com `yii2-app-basic`.
- [ ] Utilizar Bootstrap 5.
- [ ] Configurar aplicação por variáveis de ambiente.
- [ ] Configurar logs, tratamento de exceções e páginas de erro.
- [ ] Implementar autenticação via Keycloak/OIDC.
- [ ] Implementar controle de acesso por perfil.
- [ ] Implementar dashboard principal.
- [ ] Implementar paginação, filtros, pesquisa e validação de formulários.
- [ ] Implementar trilha de auditoria administrativa.
- [ ] Criar componentes Yii2 reutilizáveis para padrões recorrentes de interface.
- [ ] Padronizar GridView, FlashAlert, badges, cards e demais widgets institucionais aplicáveis.
- [ ] Evitar arquivos CSS/JS específicos por página, salvo exceção justificada.

**Critério de conclusão:** aplicação web executável, autenticada, consistente e pronta para suportar os módulos funcionais.

- [ ] **Pacote 3.1.3 concluído e validado.**

---

### 3.1.4. Qualidade, compliance e padrões de código

- [ ] Adotar **PSR-1** como padrão básico obrigatório.
- [ ] Adotar **PSR-4** para autoloading e namespaces.
- [ ] Adotar **PSR-12** como padrão obrigatório de estilo.
- [ ] Acompanhar o **PER Coding Style** da PHP-FIG e incorporar regras compatíveis com a versão PHP homologada.
- [ ] Avaliar PSR-3, PSR-11, PSR-6/16 e PSR-18 apenas onde houver benefício real de interoperabilidade.
- [ ] Não impor PSR-7/PSR-15 ao núcleo Yii2 apenas por conformidade formal.
- [ ] Configurar lint de sintaxe PHP (`php -l`).
- [ ] Configurar PHP_CodeSniffer/PHPCS com ruleset PSR-12.
- [ ] Configurar PHPCBF para correções automáticas seguras.
- [ ] Configurar PHPStan com extensão compatível com Yii2.
- [ ] Adotar PHPStan nível 8 como gate inicial mínimo.
- [ ] Planejar elevação progressiva para nível 9 e 10/`max`.
- [ ] Proibir redução de nível PHPStan para contornar falhas.
- [ ] Controlar baseline PHPStan e impedir crescimento sem justificativa.
- [ ] Configurar `composer validate`.
- [ ] Configurar `composer audit`.
- [ ] Versionar `composer.lock`.
- [ ] Integrar SonarQube institucional quando disponível.
- [ ] Configurar Quality Gate bloqueante para `main`/release.
- [ ] Avaliar reliability, security, security hotspots, maintainability, duplicação, complexidade e cobertura.
- [ ] Impedir merge com vulnerabilidades/bugs novos críticos ou altos sem aceite formal.
- [ ] Padronizar PHPDoc para contratos, tipos complementares, exceções e comportamento não trivial.
- [ ] Padronizar JSDoc para JavaScript reutilizável e contratos de funções/componentes.
- [ ] Integrar VS Code aos diagnósticos de PHPStan, PHPCS/lint e SonarQube.
- [ ] Tratar warnings das ferramentas pela correção da causa, evitando suppressions para apenas silenciar análise.
- [ ] Documentar critérios em `docs/QUALIDADE-CODIGO.md`, `docs/DOCUMENTACAO-CODIGO.md` e `docs/AMBIENTE-DESENVOLVIMENTO.md`.

**Critério de conclusão:** toda alteração passa por lint, style check, análise estática, testes, auditoria de dependências e Quality Gate aplicável antes de merge/release.

- [ ] **Pacote 3.1.4 concluído e validado.**

---

### 3.1.5. Banco de dados HECATE

- [ ] Definir modelo institucional de dados.
- [ ] Criar migrations versionadas.
- [ ] Modelar OM, divisões, usuários, impressoras, filas e políticas.
- [ ] Modelar cotas P&B/colorida, reservas e transferências.
- [ ] Modelar contratos e autorizações excepcionais.
- [ ] Modelar jobs e metadados sem armazenar conteúdo do documento.
- [ ] Modelar auditoria, monitoramento, suprimentos e sincronizações.
- [ ] Definir índices, constraints e integridade referencial.
- [ ] Definir backup, restore e validação de restauração.

**Critério de conclusão:** banco íntegro, migrável, auditável e restaurável.

- [ ] **Pacote 3.1.5 concluído e validado.**

---

### 3.1.6. Samba AD / LDAP

- [ ] Configurar LDAPS.
- [ ] Utilizar contas técnicas somente leitura.
- [ ] Validar cadeia de certificados.
- [ ] Consultar usuários, grupos e memberships necessários.
- [ ] Não criar, alterar ou excluir objetos do domínio.
- [ ] Não alterar OU, GPO, DNS ou senhas.
- [ ] Implementar teste de conectividade e diagnóstico.

**Critério de conclusão:** identidade institucional consumida sem modificação do Samba AD da OM.

- [ ] **Pacote 3.1.6 concluído e validado.**

---

### 3.1.7. Catálogo MB

- [ ] Documentar endpoints utilizados.
- [ ] Integrar API REST/Swagger.
- [ ] Consultar nome, posto/graduação, função, telefone e divisão quando disponíveis.
- [ ] Associar usuário à estrutura organizacional do HECATE.
- [ ] Implementar cache e timestamp de sincronização.
- [ ] Detectar dados ausentes ou divergentes.
- [ ] Permitir override local controlado, com justificativa, responsável, validade e auditoria.

**Critério de conclusão:** lotação funcional determinada de forma auditável sem depender exclusivamente de OU/grupos do AD.

- [ ] **Pacote 3.1.7 concluído e validado.**

---

### 3.1.8. Keycloak e autenticação

- [ ] Implantar Keycloak em Podman.
- [ ] Configurar database próprio.
- [ ] Federar com LDAP/AD em leitura.
- [ ] Configurar realm, client OIDC e roles do HECATE.
- [ ] Implementar login, logout e expiração/renovação de sessão.
- [ ] Preparar suporte futuro a MFA.
- [ ] Restringir administração e monitorar disponibilidade.

**Critério de conclusão:** portal autenticado por OIDC sem que o PHP manipule senha do domínio.

- [ ] **Pacote 3.1.8 concluído e validado.**

---

### 3.1.9. SavaPage

- [ ] Instalar e configurar SavaPage.
- [ ] Configurar database próprio no PostgreSQL.
- [ ] Integrar LDAP/AD em leitura.
- [ ] Configurar filas proxy/controladas.
- [ ] Validar accounting por usuário.
- [ ] Validar metadados: documento, data/hora, origem, páginas e P&B/colorida.
- [ ] Configurar retenção temporária e expiração de jobs.
- [ ] Validar mecanismo oficial de liberação de job existente.
- [ ] Validar ACL por grupo/divisão e exceções temporárias.
- [ ] Encapsular integração em adapter do HECATE.
- [ ] Nunca escrever diretamente no banco ou spool interno do SavaPage.

**Critério de conclusão:** SavaPage opera como motor de retenção, accounting e enforcement sob políticas do HECATE.

- [ ] **Pacote 3.1.9 concluído e validado.**

---

### 3.1.10. CUPS

- [ ] Instalar CUPS nativamente no host.
- [ ] Configurar filas físicas e drivers/PPDs quando necessários.
- [ ] Priorizar IPP Everywhere quando suportado.
- [ ] Integrar fluxo SavaPage → CUPS.
- [ ] Evitar exposição direta das filas físicas aos usuários.
- [ ] Validar P&B, colorida, duplex e formatos relevantes.
- [ ] Implementar diagnóstico e restart controlado via `hecate-agent`.

**Critério de conclusão:** CUPS funciona como spool/transportador interno do fluxo controlado.

- [ ] **Pacote 3.1.10 concluído e validado.**

---

### 3.1.11. HECATE Agent

- [ ] Implementar serviço nativo systemd.
- [ ] Preferir comunicação local por Unix socket.
- [ ] Não expor shell ou comando arbitrário.
- [ ] Implementar lista fechada de operações privilegiadas.
- [ ] Consultar/iniciar/parar/reiniciar serviços autorizados.
- [ ] Consultar logs autorizados.
- [ ] Testar LDAP, Catálogo MB, PostgreSQL, SavaPage, CUPS, Keycloak e Podman.
- [ ] Testar e descobrir impressoras.
- [ ] Coletar telemetria SNMP/IPP/EWS.
- [ ] Auditar identidade, ação, horário e justificativa de operações sensíveis.

**Critério de conclusão:** administração operacional sem conceder `sudo` ou shell genérico ao PHP.

- [ ] **Pacote 3.1.11 concluído e validado.**

---

### 3.1.12. Cadastro, descoberta e telemetria de impressoras

- [ ] Cadastro mínimo por nome lógico, IP/FQDN e localização.
- [ ] Implementar ação `Detectar`.
- [ ] Detectar conectividade, IPP/IPPS e protocolos aplicáveis.
- [ ] Priorizar SNMPv3; permitir SNMPv2c read-only apenas quando autorizado.
- [ ] Detectar fabricante, modelo, serial, cor/P&B, duplex e formatos.
- [ ] Detectar contadores e suprimentos.
- [ ] Tentar EWS/API HTTP/HTTPS quando necessário.
- [ ] Implementar adapters/parsers específicos somente quando necessários.
- [ ] Registrar origem e timestamp de cada atributo detectado.
- [ ] Permitir correção manual e redetecção.

**Critério de conclusão:** cadastro simples com enriquecimento automático multi-vendor.

- [ ] **Pacote 3.1.12 concluído e validado.**

---

### 3.1.13. Organização e usuários

- [ ] Cadastrar OM e divisões/setores.
- [ ] Associar usuários à divisão.
- [ ] Sincronizar com Catálogo MB.
- [ ] Exibir fonte e data do vínculo organizacional.
- [ ] Exibir divergências e overrides.
- [ ] Manter histórico de lotação.
- [ ] Não utilizar IP como identidade ou lotação.

**Critério de conclusão:** HECATE responde de forma auditável quem é o usuário e onde está lotado.

- [ ] **Pacote 3.1.13 concluído e validado.**

---

### 3.1.14. Políticas de acesso a impressoras

- [ ] Associar divisões a impressoras/filas permitidas.
- [ ] Suportar regra por setor, andar e OM.
- [ ] Materializar política no SavaPage.
- [ ] Implementar autorização excepcional temporária ou permanente.
- [ ] Registrar solicitante, aprovador, justificativa e validade.
- [ ] Revogar automaticamente autorizações expiradas.
- [ ] Auditar concessão e revogação.

**Critério de conclusão:** usuário só consegue liberar impressão em equipamento autorizado pela política vigente.

- [ ] **Pacote 3.1.14 concluído e validado.**

---

### 3.1.15. Cotas P&B e colorida

- [ ] Manter saldos independentes para P&B e colorida.
- [ ] Registrar alocado, reservado, consumido e disponível.
- [ ] Implementar reserva transacional antes da liberação.
- [ ] Converter reserva em consumo após sucesso.
- [ ] Liberar reserva em cancelamento, falha ou expiração.
- [ ] Evitar consumo concorrente do mesmo saldo.
- [ ] Implementar políticas `BLOQUEAR`, `AVISAR_E_PERMITIR` e `EXIGIR_APROVACAO`.
- [ ] Exibir saldo e consumo por período/divisão.

**Critério de conclusão:** controle consistente, concorrente e separado entre P&B e colorida.

- [ ] **Pacote 3.1.15 concluído e validado.**

---

### 3.1.16. Transferência de cotas

- [ ] Permitir transferência entre divisões por competência.
- [ ] Tratar P&B e colorida independentemente.
- [ ] Registrar origem, destino, quantidade, solicitante, aprovador e justificativa.
- [ ] Registrar saldos antes/depois.
- [ ] Implementar aprovação e cancelamento/reversão auditável quando aplicável.

**Critério de conclusão:** toda movimentação de cota é rastreável e consistente.

- [ ] **Pacote 3.1.16 concluído e validado.**

---

### 3.1.17. Contratos

- [ ] Suportar contrato por consumo.
- [ ] Suportar franquia mensal P&B e colorida.
- [ ] Configurar preços unitários e excedentes.
- [ ] Relacionar contrato à OM e, quando necessário, às impressoras.
- [ ] Separar franquia contratual da OM e alocação interna por divisão.
- [ ] Calcular consumo, franquia utilizada e excedentes.
- [ ] Produzir visão administrativa por competência.

**Critério de conclusão:** HECATE representa os modelos contratuais definidos e calcula consumo sem depender do saldo financeiro interno do SavaPage.

- [ ] **Pacote 3.1.17 concluído e validado.**

---

### 3.1.18. Jobs e liberação segura

- [ ] Listar jobs do usuário autenticado.
- [ ] Validar ownership do job.
- [ ] Implementar PIN pessoal do HECATE com hash forte.
- [ ] Implementar tentativas, bloqueio, reset e auditoria do PIN.
- [ ] Validar divisão, política de impressora e saldo antes da liberação.
- [ ] Reservar cota antes da liberação.
- [ ] Acionar método oficial homologado do SavaPage.
- [ ] Finalizar consumo ou liberar reserva conforme resultado.
- [ ] Permitir cancelamento de job pelo proprietário conforme política.
- [ ] Excluir conteúdo temporário após impressão, cancelamento ou expiração.

**Critério de conclusão:** todo job exige autorização deliberada e passa pelas políticas do HECATE antes de chegar ao CUPS.

- [ ] **Pacote 3.1.18 concluído e validado.**

---

### 3.1.19. Monitoramento, diagnóstico e suprimentos

- [ ] Dashboard de saúde de HECATE, Agent, PostgreSQL, SavaPage, CUPS, Keycloak, LDAP e Catálogo MB.
- [ ] Exibir serviços OK/atenção/indisponíveis.
- [ ] Monitorar impressoras online/offline.
- [ ] Normalizar suprimentos e registrar histórico.
- [ ] Monitorar contadores quando disponíveis.
- [ ] Implementar diagnóstico em cadeia HECATE → SavaPage → CUPS → fila → impressora.
- [ ] Agregar logs técnicos com controle por perfil.
- [ ] Permitir operações controladas de serviço via Agent.
- [ ] Não bloquear impressão por falha exclusiva de telemetria.

**Critério de conclusão:** suporte local consegue localizar a camada provável da falha sem acesso irrestrito ao servidor.

- [ ] **Pacote 3.1.19 concluído e validado.**

---

### 3.1.20. Segurança e auditoria

- [ ] Restringir acesso administrativo por perfil.
- [ ] Separar Administrador Técnico, Administrador Funcional, Aprovador, Auditor e Usuário.
- [ ] Utilizar contas de serviço com privilégio mínimo.
- [ ] Proteger segredos e tokens fora do código/repositório.
- [ ] Validar TLS/LDAPS.
- [ ] Não registrar senha, token, PIN ou conteúdo de documento em logs.
- [ ] Preservar trilha de auditoria administrativa.
- [ ] Registrar operações privilegiadas do Agent.
- [ ] Revisar periodicamente permissões e grupos.
- [ ] Definir retenção de logs e metadados conforme política institucional.
- [ ] Validar proteção contra XSS, SQL Injection, CSRF, IDOR/BOLA, mass assignment, SSRF, command injection e path traversal.
- [ ] Implementar escaping de saída e sanitização quando aplicável.
- [ ] Utilizar queries parametrizadas e allowlists para identificadores dinâmicos.
- [ ] Implementar headers de segurança e CSP compatível com a aplicação.

**Critério de conclusão:** ações críticas possuem autorização, rastreabilidade e proteção de dados adequadas, sem vulnerabilidades bloqueadoras conhecidas.

- [ ] **Pacote 3.1.20 concluído e validado.**

---

### 3.1.21. Testes e homologação técnica

- [ ] Testes unitários das regras de negócio críticas.
- [ ] Testes de integração com PostgreSQL.
- [ ] Testes de migrations e rollback aplicável.
- [ ] Testes de autorização e perfis.
- [ ] Testes concorrentes de reserva de cotas.
- [ ] Testes de contratos e transferências.
- [ ] Testes de adapters com mocks/doubles.
- [ ] Testes end-to-end do fluxo de impressão.
- [ ] Testes Windows e Ubuntu como clientes.
- [ ] Homologação multi-vendor de impressoras.
- [ ] Testes de falha e recuperação de serviços.
- [ ] Validar backup/restore.
- [ ] Executar pipeline completo de qualidade/compliance.
- [ ] Executar testes negativos de autorização, validação e entradas maliciosas.

**Critério de conclusão:** cenários críticos reproduzidos com resultado aprovado e sem falhas bloqueadoras abertas.

- [ ] **Pacote 3.1.21 concluído e validado.**

---

### 3.1.22. Empacotamento e distribuição

- [ ] Definir pacotes `hecate`, `hecate-agent`, `hecate-web` e artefatos auxiliares conforme necessidade.
- [ ] Disponibilizar RPMs/repositórios no Nexus institucional.
- [ ] Disponibilizar imagens OCI homologadas no Nexus.
- [ ] Implementar `hecate-setup` para configuração inicial mínima.
- [ ] Detectar automaticamente hostname/FQDN/IP/DNS local sem alterar DNS da OM.
- [ ] Solicitar somente parâmetros essenciais de infraestrutura.
- [ ] Registrar versão de todos os componentes instalados.

**Critério de conclusão:** uma OM homologada consegue iniciar instalação por fluxo padronizado e reproduzível.

- [ ] **Pacote 3.1.22 concluído e validado.**

---

### 3.1.23. Implantação piloto

- [ ] Preparar VM dedicada na OM piloto.
- [ ] Instalar stack HECATE.
- [ ] Integrar AD/LDAP e Catálogo MB.
- [ ] Cadastrar divisões e impressoras.
- [ ] Configurar políticas, cotas e contrato de teste.
- [ ] Validar clientes Windows e Ubuntu.
- [ ] Validar liberação por PIN.
- [ ] Validar accounting e auditoria.
- [ ] Validar monitoramento e troubleshooting.
- [ ] Registrar problemas e ajustes do piloto.

**Critério de conclusão:** operação piloto controlada durante período definido sem falhas críticas de arquitetura.

- [ ] **Pacote 3.1.23 concluído e validado.**

---

### 3.1.24. Documentação operacional

- [ ] README institucional atualizado.
- [ ] Arquitetura e decisões.
- [ ] Qualidade e padrões de código.
- [ ] Ambiente de desenvolvimento VS Code.
- [ ] PHPDoc/JSDoc e convenções de documentação de código.
- [ ] Manual de implantação.
- [ ] Manual de operação.
- [ ] Checklist de instalação.
- [ ] Checklist de validação.
- [ ] Integração LDAP/AD.
- [ ] Integração Catálogo MB.
- [ ] SavaPage/CUPS.
- [ ] Políticas e cotas.
- [ ] Contratos e transferências.
- [ ] Controle de acesso e exceções.
- [ ] Troubleshooting.
- [ ] Backup/restore.
- [ ] Replicação para novas OM.

**Critério de conclusão:** equipe local consegue instalar, validar, operar e diagnosticar o HECATE usando documentação versionada.

- [ ] **Pacote 3.1.24 concluído e validado.**

---

### 3.1.25. Replicação institucional

- [ ] Definir matriz de pré-requisitos da OM.
- [ ] Definir parâmetros variáveis por OM.
- [ ] Eliminar hardcodes de DCTIM, domínio, IP, fabricante ou versão específica do Oracle Linux.
- [ ] Automatizar instalação repetível.
- [ ] Definir processo de atualização e rollback.
- [ ] Definir exportação/importação de configuração quando aplicável.
- [ ] Validar implantação em segunda OM.

**Critério de conclusão:** implantação pode ser repetida em outra OM sem alteração estrutural do produto.

- [ ] **Pacote 3.1.25 concluído e validado.**

---

### 3.1.26. Release 1.0

- [ ] Todas as POCs bloqueadoras homologadas.
- [ ] Quality Gate aprovado.
- [ ] PHPStan no nível mínimo definido.
- [ ] PHPCS/PSR-12 sem violações bloqueadoras.
- [ ] `composer audit` aprovado ou riscos formalmente aceitos.
- [ ] Testes críticos aprovados.
- [ ] Vulnerabilidades críticas/altas tratadas.
- [ ] Documentação atualizada.
- [ ] Pacotes e imagens publicados no Nexus.
- [ ] Procedimento de instalação validado.
- [ ] Backup/restore testado.
- [ ] Piloto aprovado.
- [ ] Tag e versão da release criadas.

**Critério de conclusão:** versão institucional apta a implantação controlada em novas OM.

- [ ] **Pacote 3.1.26 / Release 1.0 concluído e validado.**

---

# 4. POCs bloqueadoras

As seguintes validações devem ser tratadas como bloqueadoras de arquitetura/release e também controladas por checkbox:

- [ ] Captura correta de identidade e metadados dos jobs Windows/Ubuntu no SavaPage.
- [ ] Liberação de job retido por interface oficial suportada pelo SavaPage.
- [ ] Aplicação dinâmica de ACL por divisão e exceção sem modificar o AD.
- [ ] Accounting confiável P&B/colorida.
- [ ] Reserva transacional de cota antes da liberação.
- [ ] Descoberta multi-vendor por IPP/SNMP/EWS.
- [ ] Tratamento de indisponibilidade ou inconsistência do Catálogo MB.
- [ ] Instalação reproduzível em versão homologada do Oracle Linux.
- [ ] Pipeline de qualidade/compliance bloqueante e reproduzível.

---

# 5. Gate mínimo de qualidade para merge/release

Nenhum código de produção deve ser considerado concluído sem os controles aplicáveis abaixo:

- [ ] `composer validate` aprovado.
- [ ] Dependências instaladas de forma reproduzível a partir do lockfile.
- [ ] `php -l` aprovado.
- [ ] PHPCS / PSR-12 aprovado.
- [ ] PHPStan aprovado no nível homologado.
- [ ] Testes automatizados aprovados.
- [ ] `composer audit` aprovado ou risco formalmente aceito.
- [ ] SonarQube/SAST executado quando disponível.
- [ ] Quality Gate aprovado.
- [ ] Warnings relevantes do VS Code/analisadores tratados sem suppressions indevidas.

Para detalhes, consultar `docs/QUALIDADE-CODIGO.md`.

---

# 6. Sequência recomendada de execução

1. Arquitetura e decisões.
2. Qualidade/compliance e CI desde o início.
3. Base Yii2 e banco.
4. Keycloak + LDAP/AD + Catálogo MB.
5. SavaPage + CUPS.
6. POC de submissão/accounting.
7. POC de hold/release.
8. Organização e políticas.
9. Cotas, reservas e contratos.
10. HECATE Agent.
11. Descoberta/monitoramento de impressoras.
12. Segurança, auditoria e troubleshooting.
13. Homologação multi-vendor e multi-cliente.
14. Empacotamento/Nexus.
15. Piloto.
16. Replicação.
17. Release 1.0.

---

# 7. Critério global de entrega

O HECATE somente deve ser considerado entregue quando o fluxo completo estiver funcional e auditável:

```text
Usuário autenticado
        ↓
Job submetido ao fluxo controlado
        ↓
SavaPage retém e contabiliza
        ↓
HECATE identifica usuário e divisão
        ↓
HECATE valida política de impressora
        ↓
HECATE valida/reserva cota
        ↓
Usuário autoriza liberação
        ↓
SavaPage libera
        ↓
CUPS entrega à impressora
        ↓
HECATE consolida consumo e auditoria
```

Além da funcionalidade, a entrega exige instalação reproduzível, backup/restore, monitoramento, documentação, controles de segurança e pipeline de qualidade aprovado.

- [ ] Fluxo completo homologado.
- [ ] Instalação reproduzível homologada.
- [ ] Segurança e auditoria homologadas.
- [ ] Monitoramento e diagnóstico homologados.
- [ ] Documentação operacional aprovada.
- [ ] Pipeline/Quality Gate aprovado.
- [ ] **HECATE considerado entregue.**

---

# 8. Fora de escopo da entrega inicial

- alta disponibilidade multi-nó;
- reestruturação do domínio da OM;
- substituição obrigatória do parque de impressoras;
- dependência de hardware de release station;
- retenção permanente do conteúdo de documentos impressos;
- alteração de usuários/grupos/GPO/OU/DNS no AD;
- dependência obrigatória de software proprietário;
- uso de IP de origem como identidade organizacional;
- customizações específicas de uma única OM incorporadas ao núcleo do produto.
