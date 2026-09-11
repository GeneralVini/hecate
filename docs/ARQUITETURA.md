# Arquitetura HECATE

## 1. Visão geral

HECATE é a **Plataforma Institucional de Governança e Controle de Impressão**. O produto coordena identidade, organização, política, cotas, contratos, autorização, liberação, auditoria e operação do serviço de impressão.

O HECATE não administra o Samba AD e não substitui o SavaPage ou o CUPS. Ele organiza e governa a solução completa.

A arquitetura da aplicação segue **Yii3 + monólito modular + DDD pragmático**.

> **A complexidade deve ser justificada pelo domínio.**

O domínio real define os boundaries. Camadas, interfaces, repositories, eventos, Value Objects e outras abstrações só devem ser introduzidos quando houver regra de domínio, integração ou problema concreto de acoplamento que os justifique.

O desenho preferencial é o mais simples que preserve boundaries claros, segurança e testabilidade.

Também se adota o princípio de que duplicação localizada entre boundaries pode ser mais barata que acoplamento conceitual entre módulos.

## 2. Arquitetura lógica

```text
                         Samba AD da OM
                              |
                            LDAPS
                   +----------+----------+
                   |                     |
               Keycloak               SavaPage
                   |                     ^
                  OIDC                   |
                   v                     |
               +-----------------------------+
               |            HECATE           |
               |-----------------------------|
               | Organização                 |
               | Políticas                   |
               | Cotas                       |
               | Contratos                   |
               | Autorizações                |
               | Transferências              |
               | Auditoria                   |
               | Monitoramento               |
               | Diagnóstico                 |
               +--------------+--------------+
                              |
                         PostgreSQL
                              ^
                              |
                         Catálogo MB
                           REST API

Cliente ------------------> SavaPage
                               |
                             CUPS
                               |
                          Impressora
```

## 3. Componentes e responsabilidades

### Samba AD da OM

Fonte de identidade institucional.

- autenticação e identidade de usuários;
- grupos quando úteis à política;
- acesso somente leitura;
- preferir LDAPS;
- nenhuma alteração de objetos do domínio pelo HECATE.

### Catálogo MB

Fonte preferencial para atributos funcionais e organizacionais.

Exemplos:

- nome;
- posto/graduação;
- telefone;
- função;
- departamento/divisão;
- vínculo com a OM.

O HECATE deve registrar divergências, ausência de dados e overrides locais auditados.

### Keycloak

Plano de autenticação do portal HECATE.

- SSO;
- OIDC;
- federação com LDAP/AD;
- base para MFA futuro;
- evita que a aplicação PHP trate diretamente a senha do domínio.

### HECATE Web

Fonte de verdade para governança administrativa e operacional.

Responsável por:

- estrutura OM/divisão;
- associação de usuários e grupos;
- política de acesso a impressoras;
- cotas P&B/colorida;
- contratos;
- transferências de quota;
- exceções temporárias/permanentes;
- fluxo de aprovação;
- liberação de jobs;
- auditoria;
- dashboard operacional;
- monitoramento da pilha;
- troubleshooting.

A organização interna deve evoluir por módulo e responsabilidade conforme o domínio real surgir. Não criar uma árvore DDD completa antecipadamente.

Handlers HTTP devem permanecer finos. Regras relevantes devem ser delegadas a componentes ou casos de uso apropriados somente quando essa separação trouxer clareza e testabilidade.

### SavaPage

Plano de execução e enforcement da impressão.

Responsável por:

- recepção dos jobs;
- retenção temporária;
- accounting;
- metadados do job;
- regras/ACLs materializadas pelo HECATE;
- liberação por interface suportada;
- encaminhamento ao CUPS.

Nunca escrever diretamente no banco ou spool interno do SavaPage.

### CUPS

Responsável por:

- filas físicas;
- spool local;
- drivers/PPD quando necessários;
- transporte do job ao dispositivo.

As filas físicas do CUPS não devem ser publicadas diretamente aos clientes, evitando bypass do fluxo controlado.

### PostgreSQL

Uma instância por OM/VM é aceitável, com databases e owners independentes:

```text
hecate
savapage
keycloak
```

Não usar a mesma credencial para os três componentes.

No HECATE, preferir SQL explícito via Yii DB/Command quando isso tornar a intenção mais clara. ActiveRecord pode ser usado pontualmente na infraestrutura, mas não deve funcionar como modelo global compartilhado entre módulos.

Não introduzir repositories genéricos, Data Mapper genérico ou abstrações equivalentes sem necessidade concreta.

### hecate-agent

Serviço nativo `systemd` com privilégio controlado.

Executa operações que não devem ser realizadas diretamente pelo PHP:

- health-check de serviços;
- start/stop/restart de serviços permitidos;
- descoberta de impressoras;
- IPP/SNMP/EWS;
- coleta de logs;
- teste LDAP;
- teste Catálogo MB;
- teste PostgreSQL;
- teste de conectividade com impressora;
- diagnóstico CUPS/SavaPage;
- estado do Podman.

A interface deve expor somente ações fechadas, preferencialmente por Unix socket. Não haverá endpoint para execução arbitrária de shell.

### Nexus Repository

Componente central institucional, fora da OM e fora do caminho crítico da impressão.

Usado para distribuir:

- RPMs;
- repositórios DNF/Yum;
- imagens OCI;
- artefatos homologados.

## 4. Fluxo normal de impressão

```text
1. Usuário envia o job
2. SavaPage identifica e retém
3. SavaPage registra metadados
4. Usuário acessa o HECATE
5. HECATE valida identidade, divisão, política, impressora e quota
6. HECATE reserva quota, quando aplicável
7. Usuário confirma liberação com PIN
8. HECATE solicita release ao SavaPage
9. SavaPage encaminha ao CUPS
10. CUPS entrega à impressora
11. Accounting confirma consumo
12. HECATE converte reserva em consumo ou devolve a reserva em caso de falha
```

## 5. Identidade, lotação e política

A identidade não deve ser inferida por IP.

```text
Samba AD  -> quem é o usuário
Catálogo MB -> onde está lotado / função / divisão
HECATE -> o que pode fazer e quais impressoras pode usar
SavaPage -> aplica a regra de impressão
```

## 6. Cotas e concorrência

P&B e colorida são independentes.

Por divisão e competência:

```text
pb_allocated
pb_reserved
pb_consumed
pb_available

color_allocated
color_reserved
color_consumed
color_available
```

A reserva deve ser transacional para impedir que dois jobs simultâneos consumam a mesma disponibilidade.

## 7. Contratos

### Por consumo

Pagamento por volume efetivamente impresso, com valores unitários independentes para P&B e colorida.

### Por franquia mensal

Volume incluído por competência, com preço de excedente.

O HECATE separa franquia contratual da OM e alocação administrativa por divisão.

## 8. Transferências

Transferências ocorrem entre divisões e por tipo de quota e devem ser auditáveis.

## 9. Exceções de acesso

O HECATE pode conceder acesso temporário ou permanente a impressora fora da política normal, sempre com justificativa, validade e auditoria.

Não modificar grupos do AD para representar essas exceções.

## 10. Descoberta e telemetria de impressoras

Ao cadastrar uma impressora, o administrador informa no mínimo nome lógico, IP/FQDN e localização.

O `hecate-agent` tenta obter fabricante, modelo, serial, cor/P&B, duplex, formatos, protocolos, contadores, status e suprimentos.

Ordem preferencial:

```text
conectividade -> IPP/IPPS -> SNMPv3 -> SNMPv2c read-only -> EWS/API -> parser específico -> manual
```

Cada atributo detectado deve registrar a fonte.

## 11. Retenção e auditoria

Não manter cópia permanente do documento impresso. Preservar apenas metadados operacionais e de auditoria necessários.

O conteúdo é eliminado após impressão, cancelamento ou expiração.

## 12. Implantação física inicial

Uma VM dedicada por OM.

```text
Oracle Linux
  |
  +-- CUPS nativo
  +-- SavaPage nativo
  +-- PostgreSQL nativo
  +-- hecate-agent nativo/systemd
  +-- Podman
      +-- HECATE Web
      +-- Keycloak
```

Alta disponibilidade não é requisito inicial. A prioridade é instalação reproduzível, backup/restore e reconstrução rápida.

## 13. Critério para evolução modular

Novos módulos, serviços de aplicação, DTOs, read models, repositories, entidades, Value Objects, interfaces e adapters devem surgir de necessidades concretas.

Antes de criar uma abstração, identificar:

1. qual regra ou problema concreto ela resolve;
2. qual boundary protege;
3. qual acoplamento reduz;
4. qual teste, integração ou variação exige sua existência.

Se a justificativa for apenas aderência a padrão ou possibilidade futura, a abstração não deve ser criada.

## 14. Pontos de homologação

A arquitetura está fechada conceitualmente, mas os seguintes mecanismos devem ser validados em POC:

- API/interface oficial para liberar job já retido no SavaPage;
- atualização dinâmica de ACLs/internal groups e exceções individuais;
- atribuição de usuário e metadados nos clientes Windows e Ubuntu;
- accounting P&B/colorida em diferentes drivers e fabricantes;
- telemetria IPP/SNMP/EWS multi-vendor;
- fallback quando o Catálogo MB estiver desatualizado ou indisponível.
