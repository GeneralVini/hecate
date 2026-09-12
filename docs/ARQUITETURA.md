# Arquitetura HECATE

## 1. Direção

HECATE é a **Plataforma Institucional de Governança e Controle de Impressão**. A aplicação segue **Yii3 + monólito modular + DDD pragmático**.

> **A complexidade deve ser justificada pelo domínio.**

O domínio real define os boundaries. Estruturas e abstrações só devem surgir quando houver necessidade concreta.

Yii3 é a base atual do backend. A tecnologia do frontend permanece aberta: views nativas podem ser usadas quando forem suficientes, mas um frontend separado consumindo APIs do HECATE também é compatível com a arquitetura. Regras de domínio e integrações não devem depender da tecnologia de apresentação.

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

Permanecem sujeitos a POC ou validação técnica:

- liberação de job retido no SavaPage;
- regras dinâmicas de acesso;
- contabilização P&B/colorida em diferentes ambientes;
- atribuição confiável de usuário nos clientes;
- telemetria multi-fabricante;
- fallback do Catálogo MB.

## 13. Documentos relacionados

- `DECISOES.md` — decisões e seus motivos;
- `EAP.md` — fonte única de escopo, POCs, critérios e acompanhamento;
- `SEGURANCA.md` — controles de segurança e auditoria;
- `DESENVOLVIMENTO.md` — ambiente, qualidade e convenções de código;
- `IMPLANTACAO.md` — instalação, operação e replicação.
