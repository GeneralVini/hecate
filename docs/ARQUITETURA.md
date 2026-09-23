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
       +---------------------> HECATE da OM
                                 |
                                 +------> SavaPage -> CUPS -> Impressora
                                 |
                                 +------> Catálogo MB
```

Cada instalação HECATE atende uma OM e consulta diretamente as integrações institucionais necessárias.

## 3. Responsabilidades

- **Samba AD:** usuários, grupos e identidade institucional em leitura; o domínio auxilia a identificação inicial da OM.
- **Catálogo MB:** fonte autoritativa dos dados institucionais e organizacionais utilizados pelo HECATE.
- **Keycloak:** autenticação do portal via OIDC/SSO.
- **HECATE da OM:** políticas, cotas, contratos, aprovações, auditoria, indicadores e operação local; consulta o Catálogo MB.
- **SavaPage:** retenção, contabilização e controle do fluxo de impressão.
- **CUPS:** filas físicas e transporte ao equipamento.
- **PostgreSQL:** persistência com isolamento lógico por componente e cache institucional local.
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
Samba AD     -> identidade/autenticação
Catálogo MB  -> identidade institucional da OM e contexto organizacional
HECATE       -> política, autorização e dados operacionais próprios
SavaPage     -> execução do controle de impressão
```

Dados institucionais oriundos do Catálogo MB são somente leitura no HECATE. Correções devem ocorrer na fonte oficial. O HECATE mantém localmente apenas os dados necessários à operação e governança de impressão; não deve reproduzir ou permitir edição paralela da estrutura institucional apenas para manter um cadastro próprio.

Exemplos de dados próprios do HECATE incluem impressoras, locais físicos de impressão, políticas, cotas, contratos e demais configurações operacionais. Estruturas organizacionais e vínculos institucionais devem ser sincronizados do Catálogo MB quando efetivamente utilizados pelo produto.

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

Consulta de identidade, grupos e vínculos necessários ao funcionamento do sistema, preferencialmente por LDAPS. No bootstrap, o domínio Samba AD é validado localmente e usado como referência inicial para descoberta da OM.

### Catálogo MB

Integração por API pela instalação HECATE da OM. O Catálogo MB é a fonte autoritativa dos dados institucionais utilizados pelo HECATE.

Cada instalação mantém cache técnico somente leitura dos dados necessários à sua OM. Esse cache é derivado, descartável e não oferece edição dos dados recebidos. Em cache miss ou necessidade de atualização, o HECATE consulta a API e atualiza o snapshot.

O mesmo caso de uso deve suportar sincronização diária e execução manual. Dados atualizados do Catálogo MB permitem correlacionar a estrutura institucional com usuários e grupos consultados em leitura no Samba AD; divergências exigem verificação, sem alteração automática do AD.

Quando não houver necessidade de consultar internamente cada elemento do domínio do Catálogo MB, preferir snapshot JSONB persistido no PostgreSQL da OM em vez de reconstruir um modelo relacional paralelo.

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

O `hecate-setup` solicita o domínio Samba AD da OM e valida o ambiente local. A integração com o Catálogo MB deve ser configurada na instalação da OM, com credencial própria protegida e conectividade homologada.

## 11. Evolução modular

Antes de criar nova camada ou abstração, identificar:

1. qual problema concreto ela resolve;
2. qual boundary protege;
3. qual acoplamento reduz;
4. qual teste ou integração exige sua existência.

## 12. Homologações pendentes

POCs, evidências e critérios pendentes são mantidos exclusivamente na [EAP](EAP.md#42-pocs-críticas). Decisão arquitetural aceita não significa integração homologada.

## 13. Sincronização institucional e APIs

Na instalação da OM, o domínio AD validado serve de referência inicial; o Catálogo MB confirma a identidade oficial da OM. O operador verifica divergências antes de vincular os dados. O HECATE guarda `organization_code`, indicativo, versão/hash, `last_success_at`, estado de refresh e o último snapshot válido como cache técnico.

A sincronização automática diária e a ação administrativa **Sincronizar agora** usam o mesmo fluxo. Falha temporária não apaga o snapshot anterior; o sistema sinaliza dados desatualizados. A leitura de usuários e grupos do AD é independente e não escreve no domínio.

As rotas atuais são web com views. Uma API de apresentação para dashboards e outros consumidores do próprio produto pode ser criada quando houver necessidade, sempre com autenticação, autorização e escopo de leitura obrigatórios.

## 14. Documentos relacionados

- `DECISOES.md` — decisões e seus motivos;
- `EAP.md` — fonte única de escopo, POCs, critérios e acompanhamento;
- `SEGURANCA.md` — controles de segurança e auditoria;
- `DESENVOLVIMENTO.md` — ambiente, qualidade e convenções de código;
- `IMPLANTACAO.md` — instalação, operação e replicação.
