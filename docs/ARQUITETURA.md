# Arquitetura HECATE

## 1. Direção

HECATE é a **Plataforma Institucional de Governança e Controle de Impressão**. A aplicação segue **Yii3 + monólito modular + DDD pragmático**.

> **A complexidade deve ser justificada pelo domínio.**

O domínio real define os boundaries. Estruturas e abstrações só devem surgir quando houver necessidade concreta.

Yii3 é a base atual do backend. A tecnologia do frontend permanece aberta: views nativas podem ser usadas quando forem suficientes, mas um frontend separado consumindo APIs do HECATE também é compatível com a arquitetura. Regras de domínio e integrações não devem depender da tecnologia de apresentação. A direção API-first atende web, agente, integrações e possíveis consumidores futuros, sem impor SPA ou escolher React/Vue. As views atuais continuam compatíveis com essa direção.

Este documento descreve a arquitetura alvo. O estado implementado e suas evidências estão na [EAP](EAP.md#411-estado-da-evolução-arquitetural).

## 2. Arquitetura de referência

```text
Samba AD da OM ----LDAPS----> Keycloak
       |                         |
       |                         v
       +---------------------> HECATE <------ Catálogo MB
                                 |
                                 v
Cliente ---------------------> SavaPage
                                 |
                                 v
                               CUPS
                                 |
                                 v
                             Impressora
```

## 3. Responsabilidades

- **Samba AD:** identidade institucional em leitura.
- **Catálogo MB:** atributos funcionais e organizacionais.
- **Keycloak:** autenticação do portal via OIDC/SSO.
- **HECATE:** organização, políticas, cotas, contratos, aprovações, auditoria e indicadores.
- **SavaPage:** retenção, contabilização e controle do fluxo de impressão.
- **CUPS:** filas físicas e transporte ao equipamento.
- **PostgreSQL:** persistência com isolamento lógico por componente.
- **hecate-agent:** operações locais, descoberta, monitoramento e diagnóstico.
- **Nexus:** distribuição institucional de pacotes, imagens e artefatos homologados.

## 4. Fluxo de impressão

```text
Usuário envia
    ↓
SavaPage retém
    ↓
HECATE valida identidade, contexto, política e cota
    ↓
HECATE reserva cota e obtém confirmação por PIN
    ↓
HECATE solicita liberação
    ↓
SavaPage -> CUPS -> impressora
    ↓
contabilização e reconciliação
```

A confirmação de liberação não equivale à confirmação de impressão concluída. O consumo efetivo deve ser reconciliado com a contabilização.

## 5. Identidade e organização

```text
Samba AD     -> identidade
Catálogo MB  -> contexto organizacional
HECATE       -> política e autorização
SavaPage     -> execução do controle de impressão
```

O HECATE mantém seu próprio modelo de OM, divisões, usuários/grupos e impressoras. Não é obrigatório reproduzir a estrutura do domínio corporativo.

## 6. Persistência e boundaries

PostgreSQL é a persistência de referência. No HECATE, preferir SQL explícito e parametrizado via Yii DB quando isso tornar a intenção mais clara.

ActiveRecord pode ser utilizado pontualmente na infraestrutura, mas não deve ser o modelo compartilhado da aplicação.

Duplicação localizada entre boundaries pode ser preferível ao compartilhamento de um modelo universal.

### 6.1. Caminhos de escrita e leitura

```text
WRITE: Request -> Use Case -> Authorization / Policies -> Business Rules -> Persistence -> Audit
READ:  Request -> Read Authorization / Scope -> Query específica -> SQL otimizado -> DTO / Read Model
```

A separação é conceitual: não exige CQRS formal, command/query bus, event sourcing, broker ou bancos separados. A escrita concentra regras e invariantes no caso de uso. Persistência e auditoria de alterações críticas devem ser atômicas; a ordem do desenho não significa auditar somente depois de um commit independente. Chamadas externas ficam fora da transação local, com estados e reconciliação para resultados incertos.

No caminho de leitura, dashboards, grids, relatórios, indicadores, governança e acompanhamento contratual usam componentes de consulta identificáveis. PostgreSQL executa joins, filtros, agregações, ordenações e cálculos relacionais; SQL parametrizado não fica em handlers ou templates. Entidades, ActiveRecord e repositories não são necessários apenas para produzir essas projeções.

Componentes como `ContractUsageQuery` ou `GovernanceSummaryQuery` são exemplos conceituais, não classes obrigatórias. A consulta recebe escopo previamente autorizado e aplica a restrição no SQL, antes de agregar ou paginar. Permissão de leitura e alcance dos dados são controles distintos, definidos em [SEGURANCA.md](SEGURANCA.md#6-autorização).

## 7. Cotas e contratos

P&B e colorida são independentes.

```text
disponível = alocado - consumido - reservado
```

A reserva ocorre antes da liberação e deve ser protegida contra concorrência.

Contratos podem ser por consumo ou franquia mensal. A franquia contratual da OM e a distribuição interna de cotas são conceitos distintos.

## 8. Integrações

Integrações externas devem ficar atrás de fronteiras claras e expor apenas capacidades homologadas.

### LDAP/AD

Consulta de identidade, grupos e vínculos necessários ao funcionamento do sistema, preferencialmente por LDAPS.

### Catálogo MB

Integração por API, com cache controlado, registro da última sincronização e tratamento de divergências.

### SavaPage

Integração para retenção, metadados, contabilização e liberação de jobs por interfaces suportadas.

### Clientes

Windows e Ubuntu devem enviar jobs pelo caminho controlado do SavaPage. A validação dos clientes faz parte das POCs previstas na EAP.

## 9. Impressoras e telemetria

No cadastro, informar no mínimo nome lógico, IP/FQDN e localização.

O `hecate-agent` tenta enriquecer os dados na seguinte ordem:

```text
IPP/IPPS -> SNMPv3 -> SNMPv2c somente leitura -> EWS/API -> parser específico -> manual
```

Falha de telemetria não deve bloquear impressão. A origem e o momento da coleta devem acompanhar os dados detectados.

## 10. Implantação inicial

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

Alta disponibilidade não é requisito inicial. A prioridade é implantação reproduzível, backup/restore e reconstrução rápida.

## 11. Evolução modular

Antes de criar nova camada ou abstração, identificar:

1. qual problema concreto ela resolve;
2. qual boundary protege;
3. qual acoplamento reduz;
4. qual teste ou integração exige sua existência.

## 12. Homologações pendentes

POCs, evidências e critérios pendentes são mantidos exclusivamente na [EAP](EAP.md#42-pocs-críticas), incluindo o [incremento federado](EAP.md#5-incremento-de-leitura-e-federação). Decisão arquitetural aceita não significa integração homologada.

## 13. Federação e APIs

### 13.1. Fronteira Local → Master

```text
HECATE da OM -> agregação local -> autenticação da instância -> push -> HECATE Master
```

O Master é consumidor de governança federada, somente leitura em relação à operação local. Receber e persistir agregados no Master não lhe concede comandos administrativos sobre a OM. Ele não acessa o banco operacional completo nem inicia conexões de coleta para dentro das OMs. A sincronização é iniciada localmente e deve funcionar automaticamente após configuração e enrollment.

Os detalhes operacionais permanecem locais. O Master recebe séries consolidadas: páginas totais/P&B/coloridas, jobs, custos, consumo contratual, quantidade de impressoras e disponibilidade, conforme contrato aprovado. A analogia `history -> trends` descreve agregação, sem introduzir dependência de Zabbix.

Preservar `count`, `sum`, `min`, `max` e `avg` quando úteis. Médias globais devem resultar de numeradores e denominadores compatíveis, não da média simples das médias das OMs. Custo por página usa custo total/páginas; páginas por job usa páginas/jobs. Períodos, unidades, ausência de amostras e denominador zero precisam de semântica explícita. Última coleta de telemetria não equivale a disponibilidade medida.

### 13.2. Contrato de sincronização

A federação deve ser idempotente, versionável, auditável e resiliente à falha de rede. Envelope conceitual, ainda não um schema implementado:

```text
instance_id | om_id | period | generated_at | schema_version | sequence | metrics
```

Antes de implementar, fechar granularidade/período e fuso, unidades e precisão monetária, métricas, identidade de cada envio, tratamento de duplicatas, ordenação, correções tardias, confirmação de recebimento e compatibilidade de versões. `sequence` isoladamente não resolve duplicação após reinstalação ou restore. Reenvios não podem duplicar consumo; correções de accounting local devem poder atualizar agregados já enviados.

O vínculo instância/OM é validado pela identidade cadastrada no Master, não confiado apenas ao payload. Controles de identidade e dados estão em [SEGURANCA.md](SEGURANCA.md#17-segurança-da-federação); configuração, enrollment e recuperação em [IMPLANTACAO.md](IMPLANTACAO.md#18-enrollment-e-operação-federada).

### 13.3. Apresentação e federação

A Presentation Read API atende dashboards e UIs controlados pelo projeto. A Federation API tem contrato independente e mais estável para sincronização entre instâncias. Compartilhar a origem dos dados não exige compartilhar DTOs, permissões ou endpoints.

As rotas atuais são web com views. `/api/v1/...` e `/federation/v1/...` são possibilidades para quando houver endpoints, não rotas disponíveis ou obrigação de migração agora. O receptor de push pertence ao Master; a OM não precisa expor uma API federada de coleta. Versionamento concreto será definido com o protocolo.

## 14. Documentos relacionados

- `DECISOES.md` — decisões e seus motivos;
- `EAP.md` — fonte única de escopo, POCs, critérios e acompanhamento;
- `SEGURANCA.md` — controles de segurança e auditoria;
- `DESENVOLVIMENTO.md` — ambiente, qualidade e convenções de código;
- `IMPLANTACAO.md` — instalação, operação e replicação.
