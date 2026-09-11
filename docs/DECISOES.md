# Decisões Técnicas do HECATE

Este documento consolida as decisões já fechadas para o HECATE. Ele serve como referência para evitar regressões de arquitetura e mudanças contraditórias durante a evolução do produto.

## 1. Identidade do produto

- Nome oficial do produto: **HECATE**.
- Descrição: **Plataforma Institucional de Governança e Controle de Impressão**.
- O nome provisório `APP-PRINT` foi abandonado e não deve aparecer na interface, documentação, pacotes ou serviços novos.

## 2. Papel do HECATE

O HECATE não é apenas um frontend para SavaPage. Ele é o plano institucional de governança do serviço de impressão e concentra:

- modelo organizacional da OM;
- políticas de acesso;
- cotas P&B e colorida;
- contratos e franquias;
- transferências de quota;
- autorizações excepcionais;
- fluxo de liberação;
- auditoria administrativa;
- monitoramento da pilha;
- troubleshooting e diagnóstico;
- telemetria de impressoras e suprimentos.

## 3. Distribuição de responsabilidades

- **Samba AD da OM:** identidade institucional. Somente leitura.
- **Catálogo MB:** fonte preferencial de atributos organizacionais e funcionais.
- **Keycloak:** SSO/OIDC do portal HECATE e base para MFA futuro.
- **HECATE:** fonte de verdade para governança, política, quota, contrato, aprovação e auditoria administrativa.
- **SavaPage:** engine de impressão, retenção, accounting e enforcement.
- **CUPS:** spool local e transporte até a impressora.
- **PostgreSQL:** persistência das aplicações, com databases separados por componente.
- **hecate-agent:** operações privilegiadas locais, monitoramento, descoberta e diagnóstico.
- **Nexus:** distribuição institucional de RPMs, imagens OCI e artefatos homologados.

## 4. Samba AD e LDAP

- O HECATE não cria, altera ou remove usuários, grupos, OUs, GPOs, DNS ou senhas do domínio.
- Integrações com o domínio são somente leitura.
- Preferir LDAPS.
- Contas de serviço distintas para Keycloak e SavaPage.
- Privilégios mínimos necessários.

## 5. Catálogo MB

- Usado para enriquecer o usuário com nome, posto/graduação, telefone, função, departamento/divisão e outros atributos administrativos.
- Exemplo de associação: `09051937 -> DCTIM-33`.
- Como o Catálogo MB é mantido manualmente pelas OM, o HECATE deve detectar dados ausentes ou divergentes.
- Overrides locais só podem existir de forma controlada, com justificativa, autor, validade e auditoria.

## 6. Política organizacional

- O HECATE mantém seu próprio modelo OM -> divisão -> usuários/grupos -> impressoras.
- Não é obrigatório espelhar OUs ou grupos do AD.
- Exemplos de divisão devem seguir o padrão institucional real, como `DCTIM-01`, `DCTIM-10`, `DCTIM-33`.
- A política mestre vive no banco do HECATE e é materializada no SavaPage por interfaces suportadas.

## 7. Fluxo de impressão

Fluxo de referência:

```text
Cliente -> SavaPage -> retenção -> HECATE valida -> liberação -> CUPS -> impressora
```

- Filas físicas do CUPS não devem ser publicadas diretamente aos usuários.
- A impressão direta cliente -> IP da impressora deve ser restringida sempre que possível pela infraestrutura da OM.
- IP não é identidade nem critério de lotação.

## 8. Liberação segura

- Toda impressão deve passar por liberação deliberada.
- Não haverá release station como requisito do produto.
- Impressoras simples são suportadas.
- O PIN pertence ao HECATE, não à impressora.
- O PIN deve ser armazenado com hash forte.
- O HECATE controla tentativas, bloqueio, reset, expiração e auditoria.
- Sem estação física/NFC, a solução comprova autorização deliberada, não proximidade física à impressora.

## 9. Cotas

- Cotas P&B e colorida são independentes.
- Para cada tipo, manter `alocado`, `reservado`, `consumido` e `disponível`.
- `disponível = alocado - consumido - reservado`.
- A reserva ocorre antes da liberação para evitar corrida concorrente.
- Em sucesso, reserva vira consumo; em cancelamento, falha ou expiração, a reserva é devolvida.
- Política ao esgotar quota é configurável: bloquear, avisar e permitir, ou exigir aprovação.

## 10. Contratos

O HECATE suporta dois modelos principais:

1. **Por consumo:** cobrança por página P&B e colorida.
2. **Franquia mensal:** quantidade incluída P&B/colorida e preços unitários de excedente.

A franquia contratual da OM e a alocação interna por divisão são conceitos distintos.

## 11. Transferências e exceções

- Transferências de quota entre divisões são aprovadas e auditadas.
- P&B e colorida não são automaticamente intercambiáveis.
- Exceções de acesso a impressoras podem ser temporárias ou permanentes, sempre com validade e auditoria.
- Não modificar grupos do AD para representar exceções operacionais.

## 12. Retenção de conteúdo

- Não manter arquivo permanente dos documentos impressos.
- Conteúdo só permanece temporariamente enquanto necessário para spool/retenção/liberação.
- Após imprimir, cancelar ou expirar, o conteúdo deve ser eliminado.
- Preservar apenas metadados operacionais e de auditoria.

## 13. Integração com SavaPage

- Nunca escrever diretamente no banco do SavaPage.
- Nunca manipular diretamente spool interno do SavaPage.
- Priorizar CLI e interfaces oficialmente documentadas.
- REST só deve ser usado onde homologado e estável.
- O método exato de liberação de job retido deve permanecer encapsulado e sujeito a POC/homologação.

## 14. Impressoras e monitoramento

Sequência preferencial de descoberta:

1. conectividade básica;
2. IPP/IPPS;
3. SNMPv3;
4. SNMPv2c somente leitura, quando necessário;
5. portas/protocolos de impressão relevantes;
6. EWS/API HTTP/HTTPS;
7. parser específico por fabricante/modelo;
8. preenchimento manual.

- Nunca usar SNMP SET.
- Monitoramento não pode impedir impressão.
- Normalizar suprimentos e registrar fonte e timestamp do dado.

## 15. Servidor e empacotamento

- Um servidor/VM dedicado por OM é o padrão inicial.
- Oracle Linux conforme matriz de versões homologadas; não fixar o produto em uma única release.
- CUPS, SavaPage, PostgreSQL e `hecate-agent` nativos no host.
- HECATE Web e Keycloak em Podman.
- Um único PostgreSQL por OM é aceitável, com databases e owners separados para HECATE, SavaPage e Keycloak.
- Distribuição institucional via Nexus.
- UX de instalação pretendida: `dnf install hecate` seguido de `hecate-setup`.

## 16. Segurança operacional

- O PHP não recebe privilégio administrativo genérico.
- Operações privilegiadas passam pelo `hecate-agent`.
- Preferir comunicação local por Unix socket.
- O agente oferece apenas um conjunto fechado de operações permitidas.
- Toda ação administrativa sensível deve ser auditada.

## 17. Frontend e framework

- Aplicação web baseada no template oficial Yii3 `yiisoft/app`.
- Yii3 é ferramenta da aplicação, não o modelo arquitetural do HECATE.
- Rotas usam `yiisoft/router`; handlers web retornam respostas PSR-7 e recebem dependências pelo container PSR-11.
- Middleware e serviços devem ser configurados pelo mecanismo de DI/configuração do Yii3, evitando service locator global e estruturas paralelas.
- PostgreSQL usa os componentes `yiisoft/db` e `yiisoft/db-pgsql`; ActiveRecord pode ser usado pontualmente na infraestrutura, mas não como modelo compartilhado da aplicação.
- Bootstrap 5 permanece como referência visual onde aplicável.

## 18. Pontos ainda sujeitos a POC

- release de job já retido no SavaPage por interface suportada;
- ACLs dinâmicas e exceções temporárias no SavaPage;
- accounting P&B/colorida em diferentes drivers e fabricantes;
- atribuição confiável de usuário em Windows e Ubuntu;
- descoberta e telemetria multi-fabricante;
- estratégia de fallback para Catálogo MB desatualizado.

## 19. Complexidade arquitetural

> **A complexidade deve ser justificada pelo domínio.**

- Adotar DDD de forma pragmática, sem impor estrutura acadêmica ao projeto.
- Não introduzir antecipadamente camadas, interfaces, repositories, eventos, Value Objects ou indireções sem problema concreto a resolver.
- Preferir o desenho mais simples que preserve boundaries claros e testabilidade.
- DTOs, read models, repositories, entidades e Value Objects são ferramentas condicionais; devem existir quando representarem uma necessidade real do domínio, de integração, de persistência ou de isolamento entre boundaries.
- Duplicação localizada entre boundaries pode ser preferível a um modelo compartilhado que aumente o acoplamento conceitual.
- Evitar `GenericRepository`, `BaseRepository`, `BaseService`, DTO universal e ActiveRecord compartilhado entre módulos.
- SQL explícito e parametrizado via Yii DB é aceitável e preferível quando tornar a intenção mais clara.
