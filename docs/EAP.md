# EAP — Entrega Completa do HECATE

## 1. Finalidade

Esta EAP organiza a entrega completa do **HECATE — Plataforma Institucional de Governança e Controle de Impressão** em pacotes de trabalho orientados a produto, implantação e operação.

A estrutura é prática e serve como referência para desenvolvimento, homologação, implantação piloto, replicação em outras OM e sustentação. Não substitui cronograma, plano de projeto ou procedimento técnico detalhado.

---

## 2. Objetivo da entrega

Entregar uma solução institucional capaz de:

- centralizar o fluxo de impressão;
- autenticar usuários do domínio;
- relacionar identidade, lotação e divisão;
- controlar acesso a filas e impressoras;
- aplicar cotas separadas para P&B e colorida;
- controlar contratos por consumo ou franquia;
- permitir transferência de cotas entre divisões;
- exigir liberação deliberada de jobs por usuário;
- manter auditoria e rastreabilidade;
- monitorar componentes da solução e impressoras;
- detectar suprimentos e contadores quando disponíveis;
- restringir bypass do fluxo controlado;
- permitir implantação e administração local pela OM;
- permitir replicação padronizada em outras OM.

---

# 3. Estrutura Analítica do Produto

## 3.1. HECATE — Produto completo

### 3.1.1. Arquitetura e decisões técnicas

- [ ] Consolidar arquitetura lógica e física.
- [ ] Consolidar responsabilidades de HECATE, SavaPage, CUPS, Keycloak, PostgreSQL, Samba AD, Catálogo MB, Podman, Nexus e `hecate-agent`.
- [ ] Definir fluxo completo de impressão.
- [ ] Definir fluxo completo de autenticação e autorização.
- [ ] Definir fluxo completo de liberação segura.
- [ ] Definir integração entre componentes sem acesso direto a bancos internos de terceiros.
- [ ] Definir política de retenção de dados e documentos.
- [ ] Definir padrão de implantação por OM.
- [ ] Definir matriz de compatibilidade de Oracle Linux homologada.
- [ ] Registrar decisões técnicas relevantes em `docs/DECISOES.md`.

**Critério de conclusão:** arquitetura documentada, coerente com o produto e sem dependência de mecanismos não homologados.

---

### 3.1.2. Identidade visual e experiência do usuário

- [ ] Consolidar nome institucional: **HECATE**.
- [ ] Consolidar descrição oficial do produto.
- [ ] Aplicar logo horizontal.
- [ ] Aplicar logo vertical.
- [ ] Aplicar símbolo isolado.
- [ ] Aplicar favicons.
- [ ] Aplicar background de login com área de respiro para formulário real.
- [ ] Aplicar background discreto de dashboard.
- [ ] Consolidar paleta azul-marinho, dourado, branco e neutros.
- [ ] Padronizar tipografia, espaçamento, cards, tabelas, alertas e estados.
- [ ] Implementar sidebar retrátil.
- [ ] Implementar topbar.
- [ ] Implementar navbar/breadcrumb contextual.
- [ ] Implementar footerbar.
- [ ] Garantir responsividade mínima para desktop e tablet.
- [ ] Garantir contraste e legibilidade.
- [ ] Evitar mockups ou elementos gráficos que interfiram nos campos reais da aplicação.

**Critério de conclusão:** identidade visual aplicada de forma consistente em login, dashboard, navegação e documentação.

---

### 3.1.3. Base da aplicação web

- [ ] Manter estrutura compatível com `yii2-app-basic`.
- [ ] Utilizar Bootstrap 5.
- [ ] Configurar aplicação por variáveis de ambiente.
- [ ] Configurar logs da aplicação.
- [ ] Configurar tratamento de exceções.
- [ ] Configurar autenticação via Keycloak/OIDC.
- [ ] Implementar controle de acesso por perfil.
- [ ] Implementar layout administrativo padrão.
- [ ] Implementar dashboard principal.
- [ ] Implementar mensagens, alertas e feedback de operação.
- [ ] Implementar paginação, filtros e pesquisa nos cadastros.
- [ ] Implementar validação de formulários.
- [ ] Implementar trilha de auditoria para ações administrativas.

**Critério de conclusão:** aplicação web executável, autenticada e pronta para suportar todos os módulos funcionais.

---

### 3.1.4. Banco de dados HECATE

- [ ] Definir modelo de dados institucional.
- [ ] Criar migrations versionadas.
- [ ] Criar estrutura de OM.
- [ ] Criar estrutura de divisões/setores.
- [ ] Criar vínculos de usuários com estrutura organizacional.
- [ ] Criar cadastro de impressoras.
- [ ] Criar cadastro de filas.
- [ ] Criar políticas de acesso.
- [ ] Criar cotas P&B.
- [ ] Criar cotas coloridas.
- [ ] Criar reservas de cota.
- [ ] Criar movimentações/transferências.
- [ ] Criar contratos.
- [ ] Criar autorizações excepcionais.
- [ ] Criar registros de jobs e metadados.
- [ ] Criar estrutura de auditoria.
- [ ] Criar estrutura de monitoramento.
- [ ] Criar histórico de suprimentos.
- [ ] Criar estrutura de integrações e sincronizações.
- [ ] Definir índices, integridade referencial e constraints.
- [ ] Definir rotinas de backup e restore.

**Critério de conclusão:** banco íntegro, migrável, auditável e suficiente para todos os módulos funcionais do HECATE.

---

### 3.1.5. Integração com Samba AD / LDAP

- [ ] Configurar LDAPS.
- [ ] Utilizar conta de serviço somente leitura.
- [ ] Validar certificado da cadeia LDAP.
- [ ] Consultar usuários.
- [ ] Consultar grupos.
- [ ] Consultar associação usuário/grupo quando necessária.
- [ ] Não criar, alterar ou excluir objetos no domínio.
- [ ] Não alterar OU, GPO, DNS ou senha de usuários.
- [ ] Implementar teste de conectividade no HECATE.
- [ ] Implementar diagnóstico de falhas LDAP.
- [ ] Implementar sincronização controlada de dados mínimos necessários.

**Critério de conclusão:** usuários do domínio podem ser autenticados/consultados sem qualquer modificação no Samba AD da OM.

---

### 3.1.6. Integração com Catálogo MB

- [ ] Documentar endpoints utilizados.
- [ ] Integrar com API REST/Swagger.
- [ ] Consultar nome.
- [ ] Consultar posto/graduação.
- [ ] Consultar função.
- [ ] Consultar telefone quando necessário.
- [ ] Consultar departamento/divisão.
- [ ] Associar usuário à organização do HECATE.
- [ ] Implementar cache controlado.
- [ ] Registrar data/hora da última sincronização.
- [ ] Detectar dados ausentes.
- [ ] Detectar divergências.
- [ ] Permitir override local controlado.
- [ ] Exigir justificativa, responsável e validade para override.
- [ ] Auditar alterações locais.

**Critério de conclusão:** o HECATE consegue determinar a lotação funcional do usuário sem depender exclusivamente de estrutura de OU/grupo do AD.

---

### 3.1.7. Keycloak e autenticação

- [ ] Implantar Keycloak no Podman.
- [ ] Configurar database próprio.
- [ ] Federar com Samba AD/LDAP em modo leitura.
- [ ] Configurar realm do HECATE.
- [ ] Configurar client OIDC do portal.
- [ ] Configurar roles do HECATE.
- [ ] Implementar login único.
- [ ] Implementar logout.
- [ ] Implementar expiração e renovação de sessão.
- [ ] Preparar suporte futuro a MFA.
- [ ] Restringir administração do Keycloak.
- [ ] Monitorar disponibilidade do Keycloak no dashboard técnico.

**Critério de conclusão:** autenticação do portal realizada por OIDC sem que a aplicação PHP manipule diretamente a senha do domínio.

---

### 3.1.8. SavaPage

- [ ] Instalar e configurar SavaPage.
- [ ] Configurar PostgreSQL dedicado ao SavaPage.
- [ ] Integrar com LDAP/AD em leitura.
- [ ] Configurar filas proxy/controladas.
- [ ] Impedir exposição desnecessária das filas físicas do CUPS.
- [ ] Validar accounting de usuário.
- [ ] Validar captura de nome do documento.
- [ ] Validar data/hora.
- [ ] Validar IP/host de origem quando disponível.
- [ ] Validar quantidade de páginas.
- [ ] Validar distinção P&B/colorida.
- [ ] Configurar retenção temporária de jobs.
- [ ] Definir expiração de jobs retidos.
- [ ] Validar mecanismo oficial de liberação de job existente.
- [ ] Validar ACL por grupo/divisão.
- [ ] Validar exceções temporárias sem alteração do AD.
- [ ] Encapsular integração em adapter do HECATE.
- [ ] Nunca alterar diretamente banco ou spool interno do SavaPage.

**Critério de conclusão:** SavaPage opera como motor de impressão, retenção, accounting e enforcement sob políticas definidas pelo HECATE.

---

### 3.1.9. CUPS

- [ ] Instalar CUPS nativamente no host.
- [ ] Configurar filas físicas.
- [ ] Configurar drivers/PPDs quando necessários.
- [ ] Priorizar IPP Everywhere quando suportado.
- [ ] Integrar fluxo SavaPage -> CUPS.
- [ ] Restringir publicação direta das filas físicas aos usuários.
- [ ] Validar impressão P&B.
- [ ] Validar impressão colorida.
- [ ] Validar duplex.
- [ ] Validar formatos de papel relevantes.
- [ ] Implementar diagnóstico de fila.
- [ ] Implementar restart controlado via `hecate-agent`.

**Critério de conclusão:** CUPS funciona apenas como spool/transportador interno do fluxo controlado.

---

### 3.1.10. HECATE Agent

- [ ] Implementar serviço nativo systemd.
- [ ] Definir protocolo de comunicação HECATE Web -> Agent.
- [ ] Preferir Unix socket local.
- [ ] Não expor execução arbitrária de comandos.
- [ ] Implementar lista fechada de operações permitidas.
- [ ] Consultar status de serviços.
- [ ] Iniciar/parar/reiniciar serviços autorizados.
- [ ] Consultar logs autorizados.
- [ ] Testar LDAP.
- [ ] Testar Catálogo MB.
- [ ] Testar PostgreSQL.
- [ ] Testar SavaPage.
- [ ] Testar CUPS.
- [ ] Testar Keycloak.
- [ ] Testar Podman.
- [ ] Testar impressora.
- [ ] Executar descoberta de impressora.
- [ ] Coletar telemetria SNMP/IPP/EWS.
- [ ] Auditar todas as ações privilegiadas.
- [ ] Exigir identidade e justificativa para operações sensíveis.

**Critério de conclusão:** aplicação web administra e diagnostica o host sem conceder shell ou sudo genérico ao PHP.

---

### 3.1.11. Cadastro e descoberta de impressoras

- [ ] Cadastro mínimo por nome lógico.
- [ ] Cadastro por IP/FQDN.
- [ ] Cadastro de localização física.
- [ ] Botão `Detectar`.
- [ ] Teste de conectividade.
- [ ] Detecção IPP/IPPS.
- [ ] Detecção SNMPv3.
- [ ] Fallback SNMPv2c somente leitura quando autorizado.
- [ ] Detecção de portas/protocolos 631/9100/515 quando pertinente.
- [ ] Detecção de fabricante.
- [ ] Detecção de modelo.
- [ ] Detecção de número de série.
- [ ] Detecção de capacidade colorida/P&B.
- [ ] Detecção de duplex.
- [ ] Detecção de formatos de papel.
- [ ] Detecção de contadores.
- [ ] Detecção de suprimentos.
- [ ] Detecção EWS/API HTTP/HTTPS.
- [ ] Implementar parsers específicos por fabricante somente quando necessário.
- [ ] Permitir correção manual pelo administrador.
- [ ] Registrar origem de cada atributo detectado.
- [ ] Permitir redetecção posterior.

**Critério de conclusão:** administrador consegue cadastrar impressora informando apenas dados mínimos e obter automaticamente o máximo de informações disponíveis.

---

### 3.1.12. Organização e usuários

- [ ] Cadastro da OM.
- [ ] Cadastro de divisões/setores.
- [ ] Associação de usuários à divisão.
- [ ] Sincronização com Catálogo MB.
- [ ] Pesquisa de usuários.
- [ ] Exibição de fonte do vínculo organizacional.
- [ ] Exibição de divergências.
- [ ] Override local controlado.
- [ ] Histórico de mudanças de lotação.
- [ ] Não utilizar IP como identidade ou lotação.

**Critério de conclusão:** HECATE consegue responder de forma auditável quem é o usuário e a qual divisão/setor está associado.

---

### 3.1.13. Políticas de acesso a impressoras

- [ ] Associar divisões a impressoras permitidas.
- [ ] Associar divisões a filas permitidas.
- [ ] Suportar regra por setor/andar/OM.
- [ ] Materializar política no SavaPage.
- [ ] Implementar autorização excepcional.
- [ ] Suportar exceção temporária.
- [ ] Suportar exceção permanente quando autorizada.
- [ ] Registrar solicitante.
- [ ] Registrar aprovador.
- [ ] Registrar justificativa.
- [ ] Registrar validade.
- [ ] Revogar automaticamente autorização temporária vencida.
- [ ] Auditar concessão e revogação.

**Critério de conclusão:** um usuário somente consegue liberar impressão em equipamento autorizado pela política vigente.

---

### 3.1.14. Cotas P&B e colorida

- [ ] Criar cotas independentes P&B e colorida.
- [ ] Definir valor alocado.
- [ ] Registrar consumido.
- [ ] Registrar reservado.
- [ ] Calcular disponível.
- [ ] Implementar reserva transacional antes da liberação.
- [ ] Converter reserva em consumo após sucesso.
- [ ] Liberar reserva após cancelamento/falha/expiração.
- [ ] Evitar dupla utilização concorrente de saldo.
- [ ] Configurar comportamento ao atingir limite.
- [ ] Suportar `BLOQUEAR`.
- [ ] Suportar `AVISAR_E_PERMITIR`.
- [ ] Suportar `EXIGIR_APROVACAO`.
- [ ] Exibir consumo por período.
- [ ] Exibir saldo por divisão.

**Critério de conclusão:** o HECATE controla de forma consistente e concorrente o saldo organizacional separado de P&B e colorida.

---

### 3.1.15. Transferência de cotas

- [ ] Solicitar transferência entre divisões.
- [ ] Selecionar P&B ou colorida.
- [ ] Informar quantidade.
- [ ] Informar competência/período.
- [ ] Informar justificativa.
- [ ] Registrar solicitante.
- [ ] Registrar aprovador.
- [ ] Implementar aprovação/reprovação.
- [ ] Registrar saldo antes/depois.
- [ ] Atualizar cotas de forma transacional.
- [ ] Impedir intercâmbio automático entre P&B e colorida.
- [ ] Auditar integralmente a movimentação.

**Critério de conclusão:** toda transferência possui trilha administrativa completa e consistência de saldo.

---

### 3.1.16. Contratos de impressão

- [ ] Cadastro de contrato.
- [ ] Vigência.
- [ ] Fornecedor quando aplicável.
- [ ] Modalidade `POR_CONSUMO`.
- [ ] Modalidade `FRANQUIA_MENSAL`.
- [ ] Valor unitário P&B.
- [ ] Valor unitário colorida.
- [ ] Franquia P&B incluída.
- [ ] Franquia colorida incluída.
- [ ] Valor excedente P&B.
- [ ] Valor excedente colorida.
- [ ] Relacionar equipamentos ao contrato.
- [ ] Calcular consumo acumulado.
- [ ] Calcular excedente.
- [ ] Distinguir franquia contratual da OM e alocação interna por divisão.
- [ ] Emitir visão administrativa por competência.

**Critério de conclusão:** HECATE consegue representar e acompanhar os dois modelos contratuais definidos para o produto.

---

### 3.1.17. Jobs e liberação segura

- [ ] Receber metadados do job do SavaPage.
- [ ] Identificar proprietário do job.
- [ ] Exibir jobs pendentes ao usuário.
- [ ] Exibir documento, páginas, P&B/colorida, horário e destino.
- [ ] Implementar PIN pessoal HECATE.
- [ ] Armazenar somente hash forte do PIN.
- [ ] Implementar criação/reset de PIN.
- [ ] Implementar bloqueio por tentativas inválidas.
- [ ] Validar sessão autenticada.
- [ ] Validar propriedade do job.
- [ ] Validar divisão.
- [ ] Validar impressora permitida.
- [ ] Validar exceção quando existente.
- [ ] Validar cota.
- [ ] Reservar cota.
- [ ] Solicitar liberação oficial ao SavaPage.
- [ ] Confirmar resultado.
- [ ] Atualizar accounting/cota.
- [ ] Permitir cancelamento.
- [ ] Tratar expiração.
- [ ] Auditar liberação, bloqueio, cancelamento e falha.

**Critério de conclusão:** nenhum job controlado é enviado à impressora sem autorização deliberada do usuário e validação da política HECATE.

---

### 3.1.18. Metadados, auditoria e privacidade

- [ ] Registrar usuário.
- [ ] Registrar nome do documento/job.
- [ ] Registrar data/hora.
- [ ] Registrar IP/host de origem quando disponível.
- [ ] Registrar tamanho do arquivo quando disponível.
- [ ] Registrar número de páginas.
- [ ] Registrar P&B/colorida.
- [ ] Registrar impressora de destino.
- [ ] Registrar status.
- [ ] Registrar custo quando aplicável.
- [ ] Enriquecer com OM/divisão/contrato/cota/autorização.
- [ ] Não manter cópia permanente do documento.
- [ ] Excluir conteúdo após impressão, cancelamento ou expiração.
- [ ] Definir retenção dos metadados.
- [ ] Restringir consulta de logs conforme perfil.
- [ ] Registrar alterações administrativas.

**Critério de conclusão:** rastreabilidade administrativa completa sem arquivamento permanente do conteúdo impresso.

---

### 3.1.19. Monitoramento do stack

- [ ] Monitorar HECATE Web.
- [ ] Monitorar `hecate-agent`.
- [ ] Monitorar PostgreSQL.
- [ ] Monitorar SavaPage.
- [ ] Monitorar CUPS.
- [ ] Monitorar Keycloak.
- [ ] Monitorar LDAP/AD.
- [ ] Monitorar Catálogo MB.
- [ ] Monitorar Podman.
- [ ] Monitorar filas.
- [ ] Monitorar impressoras.
- [ ] Exibir `OK`, `ATENÇÃO` e `INDISPONÍVEL`.
- [ ] Exibir última verificação.
- [ ] Exibir falhas recentes.
- [ ] Implementar troubleshooting encadeado HECATE -> SavaPage -> CUPS -> fila -> impressora.
- [ ] Permitir coleta de logs conforme permissão.

**Critério de conclusão:** suporte técnico local identifica rapidamente em qual componente ocorreu a falha.

---

### 3.1.20. Monitoramento de suprimentos e contadores

- [ ] Normalizar toner.
- [ ] Normalizar tinta.
- [ ] Normalizar ink pack/bolsa quando existente.
- [ ] Normalizar waste toner/resíduo.
- [ ] Normalizar papel quando disponível.
- [ ] Registrar valor atual.
- [ ] Registrar máximo.
- [ ] Calcular percentual.
- [ ] Registrar fonte da medição.
- [ ] Registrar timestamp.
- [ ] Manter histórico.
- [ ] Exibir normal/atenção/crítico.
- [ ] Calcular tendência estimada de consumo quando houver histórico suficiente.
- [ ] Marcar previsões claramente como estimativas.
- [ ] Garantir que ausência de telemetria não bloqueie impressão.

**Critério de conclusão:** equipamentos compatíveis apresentam suprimentos e contadores consolidados no HECATE.

---

### 3.1.21. Perfis e autorização administrativa

- [ ] Perfil `Administrador Técnico`.
- [ ] Perfil `Administrador Funcional`.
- [ ] Perfil `Aprovador`.
- [ ] Perfil `Auditor`.
- [ ] Perfil `Usuário`.
- [ ] Restringir operações privilegiadas.
- [ ] Restringir logs sensíveis.
- [ ] Restringir configuração de integrações.
- [ ] Restringir contratos e cotas conforme função.
- [ ] Auditar mudanças de perfil e permissão.

**Critério de conclusão:** princípio do menor privilégio aplicado no portal e nas operações do agente.

---

### 3.1.22. Segurança

- [ ] Utilizar HTTPS no portal.
- [ ] Utilizar LDAPS.
- [ ] Proteger segredos e credenciais técnicas.
- [ ] Separar contas de serviço.
- [ ] Aplicar permissões mínimas.
- [ ] Não permitir shell remoto via HECATE Web.
- [ ] Não permitir comandos arbitrários no agente.
- [ ] Proteger socket do agente.
- [ ] Proteger PostgreSQL.
- [ ] Restringir interfaces administrativas.
- [ ] Preservar logs.
- [ ] Definir política de rotação de logs.
- [ ] Definir política de backup.
- [ ] Definir restore testado.
- [ ] Validar ausência de impressão direta fora do fluxo controlado, conforme recursos de rede da OM.
- [ ] Revisar periodicamente grupos, perfis e autorizações.

**Critério de conclusão:** fluxo controlado, segregação de privilégios e rastreabilidade validados em homologação.

---

### 3.1.23. Empacotamento e distribuição

- [ ] Criar pacote/meta-pacote `hecate`.
- [ ] Criar `hecate-agent`.
- [ ] Criar `hecate-setup`.
- [ ] Criar imagem OCI `hecate-web`.
- [ ] Criar imagem/configuração OCI do Keycloak quando aplicável.
- [ ] Publicar RPMs no Nexus.
- [ ] Publicar imagens OCI no Nexus.
- [ ] Assinar/homologar artefatos conforme política institucional.
- [ ] Permitir instalação via `dnf install hecate`.
- [ ] Executar configuração inicial via `hecate-setup`.
- [ ] Detectar automaticamente hostname/FQDN/IP/DNS local.
- [ ] Não alterar DNS institucional.
- [ ] Solicitar apenas parâmetros essenciais no setup.
- [ ] Deixar cadastros funcionais para o portal web.

**Critério de conclusão:** uma OM homologada consegue instalar uma nova instância por procedimento padronizado e reproduzível.

---

### 3.1.24. Implantação local por OM

- [ ] Provisionar VM dedicada.
- [ ] Instalar Oracle Linux homologado.
- [ ] Configurar repositório Nexus.
- [ ] Instalar PostgreSQL nativo.
- [ ] Criar databases separados para HECATE, SavaPage e Keycloak.
- [ ] Instalar CUPS nativo.
- [ ] Instalar SavaPage nativo.
- [ ] Instalar `hecate-agent` nativo.
- [ ] Instalar Podman.
- [ ] Subir HECATE Web.
- [ ] Subir Keycloak.
- [ ] Executar `hecate-setup`.
- [ ] Integrar LDAP/AD.
- [ ] Integrar Catálogo MB.
- [ ] Cadastrar impressoras.
- [ ] Cadastrar divisões.
- [ ] Configurar políticas.
- [ ] Configurar cotas.
- [ ] Configurar contratos.
- [ ] Validar fluxo de impressão ponta a ponta.

**Critério de conclusão:** instância da OM funcional e validada segundo checklist de homologação.

---

### 3.1.25. Testes e homologação

- [ ] Testes unitários do domínio crítico.
- [ ] Testes de migrations.
- [ ] Testes de integração LDAP.
- [ ] Testes de integração Catálogo MB.
- [ ] Testes OIDC/Keycloak.
- [ ] Testes SavaPage.
- [ ] Testes CUPS.
- [ ] Testes do agente.
- [ ] Testes de quota concorrente.
- [ ] Testes de transferência de cotas.
- [ ] Testes de contratos.
- [ ] Testes de exceções de acesso.
- [ ] Testes de PIN/liberação.
- [ ] Testes de expiração/cancelamento de jobs.
- [ ] Testes de impressora offline.
- [ ] Testes de falha SavaPage.
- [ ] Testes de falha CUPS.
- [ ] Testes de falha LDAP.
- [ ] Testes de falha Catálogo MB.
- [ ] Testes de backup e restore.
- [ ] Testes de segurança e permissões.
- [ ] Testes Windows.
- [ ] Testes Ubuntu.
- [ ] Testes com múltiplos fabricantes de impressoras.
- [ ] Testes P&B e colorida.
- [ ] Testes de volume/carga compatíveis com a OM piloto.

**Critério de conclusão:** todos os fluxos críticos homologados e defeitos bloqueadores resolvidos.

---

### 3.1.26. Piloto institucional

- [ ] Selecionar OM piloto.
- [ ] Inventariar impressoras e filas existentes.
- [ ] Inventariar estrutura organizacional relevante.
- [ ] Definir grupo inicial de usuários.
- [ ] Configurar políticas reais.
- [ ] Configurar cotas reais ou simuladas.
- [ ] Configurar contrato aplicável.
- [ ] Executar operação assistida.
- [ ] Registrar incidentes.
- [ ] Corrigir falhas.
- [ ] Medir estabilidade.
- [ ] Medir utilização.
- [ ] Validar suporte local.
- [ ] Obter aceite técnico do piloto.

**Critério de conclusão:** piloto operando de forma estável e com aceite para replicação.

---

### 3.1.27. Replicação para outras OM

- [ ] Criar checklist pré-implantação.
- [ ] Criar checklist de infraestrutura.
- [ ] Criar checklist de LDAP/AD.
- [ ] Criar checklist Catálogo MB.
- [ ] Criar checklist de impressoras.
- [ ] Criar checklist de validação.
- [ ] Criar procedimento de backup/restore.
- [ ] Criar procedimento de atualização.
- [ ] Criar procedimento de rollback.
- [ ] Criar modelo de configuração por OM.
- [ ] Criar matriz de compatibilidade de impressoras.
- [ ] Definir processo de homologação de nova versão.
- [ ] Validar instalação limpa em nova OM.

**Critério de conclusão:** implantação replicável sem depender da equipe desenvolvedora para tarefas rotineiras.

---

### 3.1.28. Documentação operacional

- [ ] README do produto.
- [ ] Arquitetura.
- [ ] Decisões técnicas.
- [ ] Segurança.
- [ ] Integrações.
- [ ] Identidade visual.
- [ ] Instalação.
- [ ] EAP.
- [ ] Manual de implantação.
- [ ] Manual de operação.
- [ ] Manual do administrador técnico.
- [ ] Manual do administrador funcional.
- [ ] Manual do aprovador.
- [ ] Manual do auditor.
- [ ] Guia do usuário.
- [ ] Checklist de instalação.
- [ ] Checklist de validação.
- [ ] Guia de troubleshooting.
- [ ] Guia de backup/restore.
- [ ] Guia de atualização.
- [ ] Guia de cadastro de impressoras.
- [ ] Guia de cotas e contratos.
- [ ] Guia de políticas e exceções.
- [ ] Guia de replicação em outras OM.

**Critério de conclusão:** equipe local consegue implantar, operar e diagnosticar o produto utilizando a documentação oficial.

---

### 3.1.29. Observabilidade e suporte

- [ ] Centralizar visão de saúde do stack no HECATE.
- [ ] Exibir logs autorizados por componente.
- [ ] Criar diagnóstico guiado.
- [ ] Criar coleta de informações para abertura de incidente.
- [ ] Definir níveis de severidade.
- [ ] Definir informações mínimas de suporte.
- [ ] Definir política de atualização de componentes.
- [ ] Definir procedimento de contingência.
- [ ] Definir procedimento de recuperação após falha da VM.
- [ ] Definir procedimento de reconstrução da instância.

**Critério de conclusão:** suporte local possui instrumentos suficientes para identificar e registrar falhas sem acesso irrestrito ao servidor.

---

### 3.1.30. Release 1.0

- [ ] Congelar requisitos da versão 1.0.
- [ ] Finalizar POCs críticas.
- [ ] Resolver vulnerabilidades bloqueadoras.
- [ ] Resolver defeitos bloqueadores.
- [ ] Executar homologação final.
- [ ] Atualizar documentação.
- [ ] Gerar artefatos de release.
- [ ] Publicar RPMs/imagens no Nexus.
- [ ] Definir versão dos componentes.
- [ ] Gerar notas de versão.
- [ ] Criar tag Git da versão.
- [ ] Validar instalação limpa.
- [ ] Validar atualização de versão anterior.
- [ ] Validar backup e restore.
- [ ] Formalizar aceite técnico.

**Critério de conclusão:** HECATE 1.0 homologado, documentado, distribuível e replicável.

---

# 4. POCs críticas antes do fechamento da versão 1.0

As seguintes provas de conceito são consideradas bloqueadoras para a arquitetura definitiva:

1. **Atribuição de usuário no SavaPage** a partir de clientes Windows e Ubuntu, preservando username, nome do job, páginas, origem e informação P&B/colorida.
2. **Liberação de job retido** por mecanismo oficial/documentado do SavaPage, acionado pelo HECATE após validação de sessão, PIN, política e cota.
3. **ACL dinâmica por divisão e exceção temporária**, sem alteração de grupos do Samba AD e sem escrita direta no banco do SavaPage.
4. **Accounting P&B/colorida e reserva transacional de cota**, garantindo consistência em liberações concorrentes.
5. **Descoberta e monitoramento multivendor** por IPP, SNMP e EWS/API, validando pelo menos fabricantes distintos no piloto.
6. **Integração com Catálogo MB**, incluindo tratamento de dados ausentes, divergentes ou desatualizados.

---

# 5. Sequência recomendada de execução

```text
Arquitetura e decisões
        ↓
Base Yii2 + PostgreSQL + identidade visual
        ↓
Keycloak + LDAP + Catálogo MB
        ↓
SavaPage + CUPS
        ↓
HECATE Agent
        ↓
Cadastro e descoberta de impressoras
        ↓
Organização + políticas
        ↓
Jobs + PIN + liberação
        ↓
Cotas + reservas + transferências
        ↓
Contratos
        ↓
Monitoramento + suprimentos + auditoria
        ↓
Segurança + backup + troubleshooting
        ↓
Homologação
        ↓
Piloto
        ↓
Replicação
        ↓
HECATE 1.0
```

---

# 6. Critério global de entrega completa

O produto será considerado completamente entregue quando:

- a instalação puder ser reproduzida em uma OM homologada;
- o usuário for autenticado pelo domínio através do fluxo definido;
- sua lotação puder ser determinada e auditada;
- todo job passar pelo fluxo SavaPage -> validação HECATE -> CUPS -> impressora;
- a liberação exigir ação deliberada do proprietário do job;
- políticas de impressora forem efetivamente aplicadas;
- cotas P&B e colorida forem aplicadas de forma consistente;
- contratos e transferências forem administrados pelo HECATE;
- metadados de impressão forem auditáveis;
- o conteúdo do documento não permanecer arquivado após o fim do ciclo do job;
- o stack e as impressoras puderem ser monitorados pelo portal;
- operações privilegiadas ocorrerem exclusivamente por mecanismos controlados;
- backup e restore tiverem sido testados;
- a OM piloto estiver homologada;
- uma segunda instalação puder ser realizada a partir da documentação e dos artefatos padronizados;
- a versão 1.0 estiver publicada e versionada.

---

# 7. Fora do escopo da entrega principal

Não fazem parte da versão inicial, salvo decisão posterior:

- substituição obrigatória do parque de impressoras;
- reestruturação do Samba AD das OM;
- criação ou alteração automática de usuários e grupos do domínio;
- dependência obrigatória de software proprietário;
- alta disponibilidade com múltiplos nós por OM;
- release station física dedicada;
- exigência de impressora com painel inteligente;
- retenção permanente do conteúdo dos documentos impressos;
- uso de IP/faixa de rede como identidade funcional do usuário;
- automação por manipulação direta de banco interno ou spool do SavaPage;
- execução genérica de shell a partir da aplicação web.

---

## 8. Documentos relacionados

- `README.md`
- `docs/ARQUITETURA.md`
- `docs/DECISOES.md`
- `docs/SEGURANCA.md`
- `docs/INTEGRACOES.md`
- `docs/IMPLANTACAO.md`
- `docs/IDENTIDADE-VISUAL.md`
- `docs/MVP.md`
