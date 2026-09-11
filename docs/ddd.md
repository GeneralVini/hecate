Você está trabalhando no projeto institucional **HECATE**, uma plataforma de governança, controle e auditoria de impressão.

Sua função é atuar como **arquiteto de software e desenvolvedor PHP/Yii3**, analisando e evoluindo o repositório existente de acordo com as decisões arquiteturais abaixo.

Não rediscuta decisões já tomadas sem encontrar um problema técnico concreto no repositório.

# Contexto

O HECATE deverá integrar progressivamente:

* Yii3
* PostgreSQL
* Keycloak
* LDAP / Active Directory
* SavaPage
* CUPS
* APIs externas
* agente local privilegiado HECATE
* serviços de monitoramento

O sistema deverá suportar, entre outros:

* usuários e unidades organizacionais;
* RBAC;
* políticas de autorização;
* impressoras;
* filas;
* trabalhos de impressão;
* quotas;
* aprovações;
* secure print release;
* contratos;
* franquias;
* custos;
* auditoria;
* monitoramento;
* integrações externas.

O projeto é greenfield e deverá ter vida útil longa.

# Decisões arquiteturais já tomadas

## 1. Framework

Utilizar **Yii3**.

A escolha considera:

* DI;
* PSR-7;
* PSR-15;
* middleware;
* modularidade;
* testabilidade;
* interoperabilidade com o ecossistema PHP moderno.

Entretanto:

> Yii3 é uma ferramenta da aplicação, não o modelo arquitetural do HECATE.

Não organize o domínio em função do framework.

---

# 2. Arquitetura

Utilizar:

**DDD pragmático + modular monolith + separação clara de responsabilidades.**

Não aplicar DDD de maneira acadêmica ou dogmática.

O domínio real deve orientar os boundaries.

Priorize compreender:

* como impressão funciona;
* como quotas são aplicadas;
* como aprovações acontecem;
* como contratos afetam impressão;
* como CUPS e SavaPage se relacionam;
* como usuários e OMs/unidades se relacionam;
* como o agente privilegiado opera;
* quais módulos realmente precisam compartilhar informação.

Não perseguir independência total do framework como objetivo por si só.

Uma boa separação de responsabilidades deve produzir desacoplamento naturalmente.

---

# 3. Princípio central de boundaries

Adotar como princípio arquitetural:

> **Duplication between boundaries may be cheaper than coupling across boundaries.**

Não tente reutilizar um mesmo objeto de dados em toda a aplicação apenas para evitar duplicação.

Se dois módulos possuem necessidades diferentes, prefira modelos específicos para cada necessidade.

Exemplo:

```text
Printing
→ PrintJobExecutionDto

Approval
→ PrintJobApprovalDto

Audit
→ PrintJobAuditDto

Contracts
→ PrintJobCostDto

Monitoring
→ PrintJobMonitoringDto
```

Esses DTOs podem representar dados originados das mesmas tabelas sem necessariamente compartilhar a mesma classe.

O objetivo é impedir que uma mudança em um módulo force alterações conceitualmente desnecessárias em outros.

---

# 4. ActiveRecord

**Não utilizar ActiveRecord como modelo central da aplicação.**

Evitar especialmente:

```php
class PrintJob extends ActiveRecord
{
}
```

quando essa classe passa a ser consumida por:

* controllers;
* forms;
* grids;
* módulos;
* regras de domínio;
* integrações;
* serviços;
* relatórios.

Esse padrão cria um modelo compartilhado que tende a espalhar dependências.

ActiveRecord não é proibido de forma absoluta, mas:

> não construir a arquitetura em torno de subclasses de ActiveRecord.

Se ActiveRecord for utilizado pontualmente, mantê-lo restrito à infraestrutura e justificar sua necessidade.

---

# 5. Persistência

Preferir **SQL explícito utilizando Yii DB / Command**.

Exemplo conceitual:

```php
$db->createCommand($sql)
```

ou APIs equivalentes do Yii3.

SQL deve ficar em componentes responsáveis por persistência ou consulta.

Evitar SQL espalhado por:

* controllers;
* views;
* middleware;
* entidades de domínio;
* forms.

Separar operações de leitura e escrita quando isso melhorar clareza.

Não introduzir automaticamente:

* ORM pesado;
* Data Mapper genérico;
* GenericRepository;
* BaseRepository;
* PersistenceManager;
* abstrações genéricas desnecessárias.

---

# 6. DTOs e Read Models

DTOs passam a ser uma decisão arquitetural do projeto.

Usar DTOs quando eles representarem:

* input de caso de uso;
* output;
* formulário;
* query;
* relatório;
* integração;
* comunicação entre boundaries.

Exemplos:

```text
CreatePrintJobInput
PrintJobDetailsDto
PrintJobListItemDto
PrintJobApprovalDto
PrintJobCostDto
PrinterStatusDto
QuotaUsageDto
```

Não transformar DTOs em entidades de domínio.

DTO deve representar dados, não comportamento complexo.

---

# 7. Refatoração e evolução do schema

Não otimizar a arquitetura apenas para reduzir a quantidade de arquivos alterados quando uma coluna muda.

Coding agents e ferramentas modernas tornam refatorações mecânicas relativamente baratas.

O objetivo principal é minimizar **acoplamento conceitual entre módulos**.

Ao alterar schema:

```text
database
↓
query/persistence component
↓
DTO ou mapper específico
↓
caso de uso
```

Evitar:

```text
database
↓
ActiveRecord global
↓
vários módulos dependentes
```

Uma mudança de coluna não deve necessariamente obrigar módulos independentes a mudar.

---

# 8. Repository Pattern

Repository não é obrigatório.

Criar repository quando houver benefício claro, por exemplo:

* aggregate com comportamento;
* persistência complexa;
* necessidade real de abstração;
* testes de domínio;
* múltiplas formas de persistência;
* fronteira arquitetural relevante.

Não criar repository apenas porque DDD recomenda.

Queries simples podem usar query services ou componentes específicos.

---

# 9. Domain Objects

Criar entidades e Value Objects apenas quando houver domínio real.

Exemplos possíveis:

```text
PrintJob
Quota
Contract
ApprovalRequest
Printer
PrintPolicy
```

Value Objects possíveis:

```text
PrintJobId
PrinterId
UserId
ContractId
PageCount
Money
QuotaLimit
```

Não criar Value Objects para todo campo do banco.

Evitar overengineering.

---

# 10. Application Layer

Casos de uso devem representar ações do sistema.

Exemplos:

```text
SubmitPrintJob
ApprovePrintJob
RejectPrintJob
ReleasePrintJob
CancelPrintJob

AssignQuota
ChangeQuota

RegisterPrinter
DisablePrinter

CreateContract
CloseContract
```

Controllers devem permanecer finos.

Fluxo desejado:

```text
HTTP Request
    ↓
Controller
    ↓
Application Use Case
    ↓
Domain / Query / Persistence boundary
```

---

# 11. Identity & Access

Organizar este contexto de forma explícita.

```text
Identity & Access
├── Authentication
├── Identity
├── Roles
├── Permissions
├── OrganizationalScope
└── AuthorizationPolicies
```

Keycloak / LDAP / AD podem fornecer:

* identidade;
* grupos;
* atributos;
* roles institucionais;
* unidade organizacional.

O HECATE deverá controlar permissões específicas do domínio.

Exemplo:

```text
Keycloak role:
HECATE_APPROVER

HECATE permission:
print.approve
```

E políticas contextuais podem adicionar regras como:

```text
pode aprovar somente se:

- pertence à unidade competente;
- possui alçada suficiente;
- contrato está vigente;
- job está em estado aprovável;
- não viola segregação de funções.
```

Portanto:

> utilizar RBAC + políticas contextuais.

Não colocar todas as regras de autorização apenas em middleware.

---

# 12. Bounded Contexts / módulos iniciais

Avaliar inicialmente:

```text
Printing
Quota
Approval
Contracts
IdentityAccess
Audit
Monitoring
Agent
```

Os limites reais devem surgir da análise do domínio.

Não criar módulos apenas para seguir nomenclatura DDD.

---

# 13. Integrações

Integrações externas devem ficar atrás de fronteiras claras.

Exemplos:

```text
CUPS
SavaPage
Keycloak
LDAP
External APIs
HECATE Agent
```

Não chamar APIs externas diretamente do domínio.

Preferir adapters específicos.

Exemplo conceitual:

```text
Application
   ↓
PrintGateway
   ↓
CupsAdapter
```

ou:

```text
IdentityProvider
   ↓
KeycloakAdapter
```

---

# 14. Agente privilegiado

O agente local HECATE deve ser tratado como componente separado.

Arquitetura conceitual:

```text
HECATE Server
      │
      │ API autenticada
      ▼
HECATE Agent
      │
      ▼
Operating System / CUPS
```

A aplicação web não deve executar comandos privilegiados arbitrariamente.

O agente deve trabalhar com:

* allowlist de operações;
* menor privilégio;
* autenticação forte;
* autorização;
* auditabilidade;
* proteção contra replay;
* logs;
* protocolo bem definido.

Não implementar mecanismos complexos agora sem necessidade, mas preservar essa fronteira.

---

# 15. Segurança

HECATE é security-sensitive.

Desde o início considerar:

* least privilege;
* autenticação externa;
* RBAC;
* autorização contextual;
* validação rigorosa;
* CSRF;
* XSS;
* SQL injection;
* SSRF;
* command injection;
* privilege escalation;
* path traversal;
* session security;
* secrets externos ao código;
* audit logging.

Queries SQL sempre devem utilizar parâmetros/bindings.

Nunca concatenar input do usuário diretamente em SQL.

---

# 16. Estrutura sugerida

Avaliar algo próximo de:

```text
src/
├── Printing/
│   ├── Domain/
│   ├── Application/
│   ├── Query/
│   ├── Infrastructure/
│   └── Presentation/
│
├── Quota/
├── Approval/
├── Contracts/
├── IdentityAccess/
├── Audit/
├── Monitoring/
└── Agent/
```

Prefira organização **por módulo/domínio**, em vez de uma árvore global como:

```text
Domain/
Application/
Infrastructure/
```

caso a estrutura por módulo mantenha melhor coesão.

Dentro de cada módulo, separar responsabilidades apenas quando necessário.

---

# 17. Forms

Forms não devem depender diretamente do formato das tabelas.

Usar classes específicas de input/form quando houver benefício.

Exemplo:

```text
CreatePrintJobForm
ApprovePrintJobForm
CreateQuotaForm
```

Forms representam input da aplicação.

Não tratá-los como entidades de domínio.

---

# 18. Queries e grids

Listagens, grids, dashboards e relatórios podem possuir read models próprios.

Exemplo:

```text
PrintJobGridQuery
PrintJobGridItemDto

PrinterDashboardQuery
PrinterDashboardDto

ContractUsageQuery
ContractUsageDto
```

Não forçar entidades de domínio a atender necessidades de relatório.

Para queries analíticas, SQL explícito é aceitável e frequentemente preferível.

---

# 19. Testabilidade

Criar testes para:

## Domain

Regras puras.

## Application

Casos de uso.

## Queries

Mapeamento e comportamento relevante.

## Integration

PostgreSQL, HTTP e adapters externos.

Não mockar excessivamente.

Prefira testar fronteiras reais quando isso trouxer mais confiança.

---

# 20. Coding agents e refatoração

O projeto será desenvolvido com auxílio de Codex/coding agents.

Portanto:

* não distorcer a arquitetura apenas para facilitar refatorações manuais;
* utilizar busca, static analysis e testes para acompanhar alterações;
* quando schema mudar, atualizar sistematicamente todos os consumidores relevantes;
* manter boundaries claros para que o impacto seja previsível.

Antes de realizar refatorações amplas:

1. localizar todos os consumidores;
2. identificar o boundary afetado;
3. atualizar testes;
4. aplicar mudança;
5. executar suíte relevante.

---

# 21. Anti-patterns

Evitar:

```text
God Model
God Service
BaseService
GenericRepository
BaseRepository
Service Locator
global state
ActiveRecord compartilhado entre módulos
controllers com lógica de negócio
SQL em controllers
SQL em views
domain objects usados como DTO universal
DTO universal para toda aplicação
microservices prematuros
CQRS formal sem necessidade
Event Sourcing
message broker sem necessidade concreta
```

---

# 22. ADRs e documentação

Criar e manter:

```text
README.md
ARCHITECTURE.md
AGENTS.md
SECURITY.md
docs/adr/
```

ADRs iniciais:

```text
0001-use-yii3.md

0002-use-modular-monolith.md

0003-domain-driven-boundaries.md

0004-avoid-active-record-as-shared-application-model.md

0005-prefer-explicit-sql-and-dtos.md

0006-boundary-duplication-over-cross-module-coupling.md

0007-rbac-plus-contextual-authorization.md
```

Registrar nos ADRs o racional, não apenas a decisão.

---

# 23. Princípios para decisões futuras

Quando houver dúvida, utilizar esta ordem de prioridade:

1. representar corretamente o domínio real;
2. preservar boundaries entre módulos;
3. reduzir acoplamento conceitual;
4. manter código simples;
5. manter testabilidade;
6. aproveitar produtividade do Yii3;
7. evitar abstração prematura.

Não priorizar:

```text
framework independence
```

por si só.

Também não priorizar:

```text
DRY
```

quando eliminar duplicação criar dependência indesejada entre módulos.

---

# 24. Primeira tarefa

Antes de gerar grande quantidade de código:

1. analise o repositório atual;
2. identifique a estrutura existente;
3. identifique uso atual de Yii2/Yii3, ActiveRecord, SQL, forms e services;
4. identifique módulos e responsabilidades existentes;
5. proponha uma estrutura modular inicial;
6. identifique o primeiro vertical slice do HECATE;
7. crie ou ajuste `ARCHITECTURE.md`;
8. crie os ADRs principais;
9. crie ou ajuste `AGENTS.md`;
10. implemente apenas o necessário para validar a arquitetura.

Primeiro vertical slice preferencial:

```text
usuário autenticado
        ↓
lista impressoras disponíveis
        ↓
solicita trabalho de impressão
        ↓
quota é verificada
        ↓
PrintJob é registrado
        ↓
adapter de impressão é acionado
        ↓
evento é auditado
```

Mocks/fakes são aceitáveis inicialmente para:

```text
Keycloak
LDAP
CUPS
SavaPage
HECATE Agent
```

O objetivo inicial é validar:

* boundaries;
* DTOs;
* SQL;
* casos de uso;
* DI;
* testes;
* organização modular.

---

# 25. Regra operacional para o Codex

Não faça grandes reestruturações silenciosamente.

Antes de alterar arquitetura existente:

1. explique brevemente o problema encontrado;
2. indique o boundary afetado;
3. proponha a alteração;
4. depois implemente.

Para alterações locais e triviais, execute diretamente.

Sempre preserve código funcional existente quando não houver razão arquitetural ou funcional para alterá-lo.

Não introduza abstrações apenas porque parecem arquiteturalmente elegantes.

A meta é construir um HECATE:

> modular, explícito, seguro, testável, orientado ao domínio e sustentável por muitos anos.
