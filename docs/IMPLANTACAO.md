# Implantação HECATE

## 1. Objetivo

Este documento define o modelo técnico de implantação do HECATE em uma OM, priorizando simplicidade, repetibilidade e operação local.

## 2. Modelo inicial por OM

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

Alta disponibilidade não é requisito inicial. O foco é instalação padronizada, backup/restore e reconstrução rápida.

## 3. Sistema operacional

O HECATE deve ser homologado por matriz de versões Enterprise Linux compatíveis com Oracle Linux.

Não fixar a solução em uma única release.

A documentação de homologação deve declarar claramente as versões suportadas, por exemplo EL8/EL9/EL10 quando validadas.

## 4. PostgreSQL

Pode haver uma única instância por VM, com databases separados:

```text
hecate
savapage
keycloak
```

Cada database deve possuir owner e credencial próprios.

## 5. Podman

Componentes candidatos a container:

- HECATE Web;
- Keycloak.

O uso de Podman deve simplificar atualização e distribuição sem colocar CUPS/SavaPage no mesmo ciclo de containerização do frontend.

## 6. Componentes nativos

Devem permanecer inicialmente nativos no host:

- CUPS;
- SavaPage;
- PostgreSQL;
- `hecate-agent`.

Motivo: integração direta com sistema de impressão, systemd, dispositivos/rede e operação local.

## 7. Distribuição via Nexus

Nexus é central/institucional e não deve ser instalado em cada OM.

O Nexus pode hospedar:

- repositórios RPM/DNF;
- imagens OCI;
- pacotes do HECATE;
- artefatos homologados.

Objetivo de instalação:

```bash
dnf install hecate
hecate-setup
```

## 8. Pacotes previstos

Nomenclatura de referência:

```text
hecate
hecate-web
hecate-agent
hecate-setup
```

`hecate` pode atuar como meta-package quando conveniente.

## 9. hecate-setup

O setup deve ser executado após instalação e perguntar apenas informações essenciais de infraestrutura.

### Detectar automaticamente

- hostname;
- FQDN;
- IP local;
- DNS configurado;
- release do sistema operacional;
- estado dos serviços instalados.

### Não alterar automaticamente

- DNS da OM;
- Samba AD;
- OUs;
- GPOs;
- grupos;
- firewall institucional externo;
- roteamento.

### Perguntar apenas quando necessário

- nome/identificador da OM;
- servidor LDAP/LDAPS;
- base DN;
- conta de serviço;
- CA/certificados necessários;
- endpoint do Catálogo MB;
- credencial/token da API;
- parâmetros de TLS local;
- parâmetros de banco quando não automatizáveis.

Configurações de negócio não pertencem ao `hecate-setup`.

Devem ser feitas no frontend:

- divisões;
- impressoras;
- políticas;
- contratos;
- quotas;
- transferências;
- aprovações.

## 10. Sequência de implantação

1. Provisionar VM Oracle Linux homologada.
2. Configurar acesso aos repositórios institucionais/Nexus.
3. Instalar pacote `hecate`.
4. Executar `hecate-setup`.
5. Validar PostgreSQL.
6. Validar Keycloak.
7. Validar LDAP/LDAPS.
8. Validar Catálogo MB.
9. Validar SavaPage.
10. Validar CUPS.
11. Validar `hecate-agent`.
12. Cadastrar primeira impressora.
13. Executar descoberta automática.
14. Associar divisão/política.
15. Testar job retido.
16. Testar release.
17. Testar accounting P&B/colorido.
18. Validar logs e auditoria.
19. Executar checklist de aceite.

## 11. Cadastro de impressora

Entrada mínima:

- nome lógico;
- IP/FQDN;
- localização.

Depois, executar **Detectar**.

O agente tenta obter:

- fabricante;
- modelo;
- serial;
- protocolos;
- capacidades;
- contadores;
- suprimentos;
- status.

O administrador confirma ou corrige os dados detectados.

## 12. Clientes

Clientes Windows e Ubuntu devem apontar para o fluxo controlado do SavaPage.

Não publicar filas físicas CUPS como caminho normal.

A configuração cliente deve ser validada em POC antes de documentação definitiva de rollout.

## 13. Backup

Backup mínimo:

- database HECATE;
- database Keycloak;
- database SavaPage;
- configuração CUPS;
- configuração SavaPage;
- configuração do agente;
- parâmetros do HECATE;
- certificados necessários;
- export de configurações administrativas importantes.

Segredos devem seguir política específica e não ser colocados em repositório Git.

## 14. Restore

O procedimento de restore deve conseguir reconstruir a VM em host novo sem depender de configuração manual obscura.

Teste de restauração deve fazer parte da homologação.

## 15. Atualização

Atualizações devem vir do Nexus institucional.

Princípios:

- pacote versionado;
- rollback documentado;
- migrations versionadas;
- compatibilidade entre HECATE, agente e schema;
- health-check após atualização.

## 16. Operação local

A equipe da OM deve conseguir:

- verificar saúde dos serviços;
- reiniciar serviços autorizados;
- cadastrar impressoras;
- executar descoberta;
- verificar suprimentos;
- consultar logs;
- testar integrações;
- identificar onde a cadeia falhou.

Cadeia de diagnóstico:

```text
HECATE -> SavaPage -> CUPS -> fila -> impressora
```

## 17. Replicação para outras OM

O produto deve minimizar diferenças locais.

Variáveis locais esperadas:

- OM;
- domínio/LDAP;
- Catálogo MB;
- impressoras;
- divisões;
- políticas;
- contratos;
- quotas.

Código, pacotes e arquitetura permanecem padronizados.
