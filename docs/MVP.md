# MVP HECATE

## 1. Objetivo

O MVP valida a arquitetura institucional do HECATE antes da integração completa com o ambiente real das OM.

O foco não é reproduzir todas as funções finais, mas provar os fluxos críticos com baixo risco e sem comprometer decisões estruturais já fechadas.

## 2. Escopo funcional do MVP

### Interface

- Yii2 Basic;
- Bootstrap 5;
- layout admin dashboard;
- sidebar retrátil;
- topbar;
- breadcrumb/navbar contextual;
- footerbar;
- dashboard operacional;
- aplicação dos assets oficiais HECATE.

### Organização

- OM;
- divisões no padrão `DCTIM-xx`;
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
- status conceitual de HECATE, agente, PostgreSQL, SavaPage, CUPS, Keycloak, LDAP e Catálogo MB;
- status de impressoras;
- suprimentos;
- logs/diagnóstico como ponto de evolução.

## 3. Modelo de dados mínimo

Entidades mínimas:

- OM;
- divisão;
- usuário externo/vínculo;
- impressora;
- política de acesso;
- quota por competência;
- contrato;
- transferência;
- autorização excepcional;
- job/metadados;
- auditoria;
- integração/health status.

P&B e colorida devem ser campos/contadores independentes.

## 4. Fluxos que precisam ser demonstrados

### Fluxo organizacional

```text
usuário Samba AD
  -> Catálogo MB informa DCTIM-33
  -> HECATE associa política da DCTIM-33
  -> usuário vê apenas impressoras autorizadas
```

### Fluxo de quota

```text
job P&B de 40 páginas
  -> HECATE verifica disponível
  -> reserva 40
  -> libera
  -> accounting confirma
  -> reservado -40 / consumido +40
```

Em falha/cancelamento/expiração:

```text
reservado -40
consumido permanece inalterado
```

### Fluxo de release

```text
cliente -> SavaPage retém
usuário -> HECATE -> autenticação + PIN
HECATE -> política + quota + impressora
HECATE -> SavaPage release
SavaPage -> CUPS -> impressora
```

## 5. POCs críticas

### POC 1 — Clientes Windows e Ubuntu

Validar:

- username correto;
- documento;
- IP/hostname;
- páginas;
- P&B/colorida;
- envio sem prompts redundantes;
- fluxo controlado até SavaPage.

### POC 2 — Hold/release SavaPage

Validar a interface suportada para liberar um job já retido.

Critério: nenhuma manipulação direta de banco/spool e nenhuma automação de UI.

### POC 3 — ACL por divisão

Exemplo:

```text
DCTIM-33
  -> IMP-DCTIM-4A-01
  -> IMP-DCTIM-4A-02
```

Validar materialização dinâmica no SavaPage.

### POC 4 — Exceção temporária

Validar acesso excepcional de um usuário a uma impressora fora de sua divisão, com validade e auditoria, sem alterar grupo do AD.

### POC 5 — Accounting e quota

Validar P&B e colorida em jobs reais e a reserva transacional antes do release.

### POC 6 — Descoberta multi-vendor

Validar pelo menos equipamentos representativos de fabricantes distintos usando:

```text
IPP/IPPS
SNMPv3
SNMPv2c read-only
EWS/API
```

HP, Epson, Xerox e Ricoh são referências de homologação, não dependências do produto.

### POC 7 — Catálogo MB

Validar:

- Swagger/API real;
- lookup por identificador;
- lotação/divisão;
- cache;
- indisponibilidade;
- divergência;
- override local auditado.

## 6. Critérios de aceite do MVP

O MVP será considerado tecnicamente válido quando demonstrar:

- identidade institucional sem escrita no AD;
- associação usuário -> divisão;
- política divisão -> impressora;
- job retido;
- decisão de release pelo HECATE;
- accounting confiável;
- quota P&B/colorida separada;
- auditoria básica;
- cadastro/detecção de impressora;
- monitoramento mínimo da stack;
- execução sem dependência de software proprietário obrigatório.

## 7. Fora do MVP

Não é objetivo imediato:

- alta disponibilidade;
- cluster PostgreSQL;
- substituição de todo o parque de impressoras;
- OCR de páginas EWS;
- browser headless para telemetria;
- app mobile nativo;
- MFA obrigatório desde a primeira POC;
- automação de alterações no Samba AD.

## 8. Evolução posterior

Após as POCs:

1. consolidar adapters homologados;
2. integrar Keycloak;
3. integrar Catálogo MB;
4. fechar release SavaPage;
5. implementar `hecate-agent` operacional;
6. fechar quotas e contratos;
7. adicionar observabilidade e troubleshooting;
8. empacotar via RPM;
9. publicar no Nexus;
10. implantar piloto em OM;
11. gerar checklist de homologação e replicação.
