# MVP HECATE

## 1. Objetivo

O MVP valida a arquitetura institucional do HECATE antes da integração completa com o ambiente real das OM.

O foco não é reproduzir todas as funções finais, mas provar os fluxos críticos com baixo risco e sem comprometer decisões estruturais já fechadas.

## 2. Escopo funcional do MVP

### Plataforma web

- Yii3 com referência estrutural no template oficial `yiisoft/app`;
- PSR-7/PSR-17 para HTTP;
- middleware PSR-15;
- container DI/PSR-11;
- roteamento explícito;
- PostgreSQL via `yiisoft/db-pgsql`;
- ActiveRecord Yii3 onde houver ganho prático;
- layout administrativo HECATE;
- assets oficiais HECATE;
- componentes compartilhados em `src/Web/Shared` e `assets/`.

### Organização

- OM;
- divisões no padrão institucional;
- usuários/vínculos externos;
- associação organizacional derivada do Catálogo MB;
- suporte conceitual a override local auditado.

### Impressão

- cadastro de impressoras;
- nome lógico;
- IP/FQDN;
- localização;
- associação de impressoras a divisões;
- estados online/offline;
- jobs pendentes;
- liberação;
- base para descoberta automática pelo `hecate-agent`.

### Controle

- políticas divisão -> impressoras;
- exceções de acesso;
- cotas P&B;
- cotas coloridas;
- contratos;
- transferências;
- auditoria.

### Operação

- painel de saúde da stack;
- status de HECATE, Agent, PostgreSQL, SavaPage, CUPS, Keycloak, LDAP e Catálogo MB;
- status de impressoras;
- suprimentos;
- logs e diagnóstico como evolução operacional.

## 3. Modelo de dados mínimo

Entidades mínimas:

- OM;
- divisão;
- usuário externo/vínculo;
- impressora;
- política de acesso;
- cota por competência;
- contrato;
- transferência;
- autorização excepcional;
- job/metadados;
- auditoria;
- integração/health status.

P&B e colorida devem permanecer independentes.

## 4. Fluxos que precisam ser demonstrados

### Fluxo organizacional

```text
usuário Samba AD
  -> Catálogo MB informa divisão
  -> HECATE associa política da divisão
  -> usuário vê apenas impressoras autorizadas
```

### Fluxo de cota

```text
job P&B de 40 páginas
  -> HECATE verifica disponível
  -> reserva 40
  -> libera
  -> accounting confirma
  -> reservado -40 / consumido +40
```

Em falha, cancelamento ou expiração:

```text
reservado -40
consumido permanece inalterado
```

### Fluxo de release

```text
cliente -> SavaPage retém
usuário -> HECATE -> autenticação + PIN
HECATE -> política + cota + impressora
HECATE -> SavaPage release
SavaPage -> CUPS -> impressora
```

## 5. POCs críticas

### POC 1 — Clientes Windows e Ubuntu

Validar username, documento, IP/hostname, páginas, P&B/colorida e envio até o SavaPage sem prompts redundantes.

### POC 2 — Hold/release SavaPage

Validar interface suportada para liberar job já retido, sem manipulação direta de banco/spool e sem automação de UI.

### POC 3 — ACL por divisão

Validar materialização dinâmica no SavaPage das impressoras permitidas para cada divisão.

### POC 4 — Exceção temporária

Validar acesso excepcional de usuário a impressora fora de sua divisão, com validade e auditoria, sem alterar grupo do AD.

### POC 5 — Accounting e cota

Validar P&B e colorida em jobs reais e reserva transacional antes do release.

### POC 6 — Descoberta multi-vendor

Validar equipamentos representativos usando:

```text
IPP/IPPS
SNMPv3
SNMPv2c read-only
EWS/API
```

Fabricantes específicos são referência de homologação, não dependência do produto.

### POC 7 — Catálogo MB

Validar API real, lookup por identificador, lotação/divisão, cache, indisponibilidade, divergência e override local auditado.

### POC 8 — Base Yii3

Validar em ambiente local:

- bootstrap baseado em `yiisoft/app`;
- container DI;
- pipeline middleware;
- CSRF nas operações mutáveis;
- conexão PostgreSQL;
- migration inicial;
- cadastro/listagem de impressoras;
- `composer qa` integralmente verde.

## 6. Critérios de aceite do MVP

O MVP será considerado tecnicamente válido quando demonstrar:

- base Yii3 executável e reproduzível;
- identidade institucional sem escrita no AD;
- associação usuário -> divisão;
- política divisão -> impressora;
- job retido;
- decisão de release pelo HECATE;
- accounting confiável;
- cota P&B/colorida separada;
- auditoria básica;
- cadastro/detecção de impressora;
- monitoramento mínimo da stack;
- execução sem dependência de software proprietário obrigatório;
- PHPCS, PHPStan, Psalm e PHPUnit aprovados no CI.

## 7. Fora do MVP

Não é objetivo imediato:

- alta disponibilidade;
- cluster PostgreSQL;
- substituição de todo o parque de impressoras;
- OCR de páginas EWS;
- browser headless para telemetria;
- aplicativo móvel nativo;
- MFA obrigatório desde a primeira POC;
- automação de alterações no Samba AD.

## 8. Evolução posterior

Após as POCs:

1. consolidar adapters homologados;
2. integrar Keycloak;
3. integrar Catálogo MB;
4. fechar release SavaPage;
5. implementar `hecate-agent` operacional;
6. fechar cotas e contratos;
7. adicionar observabilidade e troubleshooting;
8. empacotar via RPM/OCI;
9. publicar no Nexus;
10. implantar piloto em OM;
11. gerar checklist de homologação e replicação.
