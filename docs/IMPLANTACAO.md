# Implantação HECATE

## 1. Objetivo

Este documento define o modelo técnico de implantação do HECATE em uma OM, priorizando simplicidade, repetibilidade e operação local.

Os pacotes e procedimentos abaixo são o modelo previsto; disponibilidade e homologação devem ser verificadas na [EAP](EAP.md).

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

Componentes definidos para Podman no modelo inicial:

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

- domínio Samba AD da OM;
- servidor LDAP/LDAPS, quando a descoberta automática não for suficiente;
- base DN, quando não puder ser descoberta;
- conta de serviço;
- CA/certificados necessários;
- parâmetros de TLS local;
- parâmetros de banco quando não automatizáveis.

O setup **não deve solicitar** endpoint ou credencial do Catálogo MB na OM. Essa integração pertence exclusivamente ao HECATE Master. No fluxo normal, também não deve exigir que o técnico informe código institucional da OM: o domínio AD é usado como referência de descoberta e a identidade oficial é confirmada via Master/Catálogo MB.

Configurações de negócio não pertencem ao `hecate-setup`.

Devem ser feitas no frontend:

- locais físicos de impressão;
- impressoras;
- políticas;
- contratos;
- quotas;
- transferências;
- aprovações.

Dados institucionais sincronizados do Catálogo MB, como identificação da OM e estrutura organizacional, são somente leitura no HECATE e devem ser corrigidos na fonte oficial.

## 10. Sequência de implantação

1. Provisionar VM Oracle Linux homologada.
2. Configurar acesso aos repositórios institucionais/Nexus.
3. Instalar pacote `hecate`.
4. Executar `hecate-setup`.
5. Informar e validar o domínio Samba AD da OM.
6. Validar PostgreSQL.
7. Validar Keycloak.
8. Validar LDAP/LDAPS.
9. Registrar a instalação no HECATE Master e confirmar a OM identificada via Catálogo MB.
10. Receber o snapshot institucional inicial da OM.
11. Validar SavaPage.
12. Validar CUPS.
13. Validar `hecate-agent`.
14. Cadastrar primeira impressora.
15. Executar descoberta automática.
16. Associar local/política conforme necessário.
17. Testar job retido.
18. Testar release.
19. Testar accounting P&B/colorido.
20. Validar logs e auditoria.
21. Executar checklist de aceite.

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

- domínio/LDAP da OM;
- impressoras;
- locais físicos;
- políticas;
- contratos;
- quotas.

A identidade institucional da OM e sua estrutura organizacional devem ser obtidas por sincronização com o Master, não recadastradas em cada instalação.

Código, pacotes e arquitetura permanecem padronizados.

## 18. Enrollment e operação federada

Nexus distribui/versiona o software genérico; não registra a identidade federada. O Master é responsável pelo registro e associação da instância à OM, conforme [DECISOES.md](DECISOES.md#21-federação-por-agregados-e-identidade-por-instância) e [DECISOES.md](DECISOES.md#22-bootstrap-institucional-e-catálogo-mb).

### 18.1. Bootstrap inicial

Fluxo previsto após a instalação:

```text
HECATE Local
    |
    | informa domínio AD
    v
valida DNS/AD localmente
    |
    v
HECATE Master
    |
    | usa o domínio como referência de descoberta
    v
Catálogo MB
    |
    | retorna identidade oficial e estrutura necessária
    v
Master apresenta a OM encontrada
    |
    | operador confirma
    v
Master registra installation_uuid <-> OM
    |
    v
Master entrega snapshot institucional inicial
    |
    v
HECATE Local conclui o bootstrap
```

O domínio AD é referência inicial, não identidade oficial. O vínculo definitivo usa os dados institucionais retornados pelo Catálogo MB e confirmação do operador.

O pacote/instalador deve gerar um `installation_uuid` local e estável para a instalação. Clonar uma VM para outra OM exige novo bootstrap e nova identidade de instalação.

Configuração conceitual; estas variáveis ainda não são consumidas pela aplicação:

```env
HECATE_MASTER_URL=https://hecate-master.exemplo.mil.br
HECATE_FEDERATION_ENABLED=true
```

A URL do Master deve vir da configuração institucional/pacote sempre que possível, evitando digitação desnecessária durante a instalação.

### 18.2. Catálogo MB e cache do Master

Somente o HECATE Master consome a API do Catálogo MB. As instalações locais não conhecem endpoint, token ou detalhes da API da DAdM.

Quando uma OM é solicitada pela primeira vez:

1. o Master procura snapshot da OM em seu cache técnico;
2. se não houver snapshot válido, consulta o Catálogo MB;
3. armazena a resposta como cache somente leitura e descartável;
4. registra a OM como federada quando o bootstrap é confirmado;
5. retorna ao HECATE Local os dados institucionais necessários.

O cache pode ser persistido em PostgreSQL, preferencialmente como snapshot JSONB quando não houver necessidade de normalizar o domínio do Catálogo MB. Ele não é uma segunda fonte de verdade e não deve oferecer CRUD dos dados recebidos.

Apenas OM com HECATE registrado entram no refresh periódico. Não há necessidade de sincronizar preventivamente todas as OM existentes no Catálogo MB.

Falha de atualização não deve apagar o último snapshot válido. O Master deve preservar `last_success_at`, estado do refresh e informação suficiente para indicar que os dados estão desatualizados.

### 18.3. Sincronização posterior

O Master verifica periodicamente os dados institucionais das OM federadas e atualiza seus snapshots quando necessário. O HECATE Local consulta exclusivamente o Master.

O mesmo caso de uso deve atender:

- sincronização automática diária;
- ação administrativa **Sincronizar agora**.

Quando houver versionamento/hash do snapshot, o Local deve primeiro comparar sua versão com a do Master e baixar o conteúdo apenas quando houver alteração.

O `hecate-agent` pode executar heartbeat e sincronização periódica Local -> Master. Falha ou desativação da federação não deve bloquear impressão e administração locais.

### 18.4. phpIPAM opcional

Caso exista acesso autorizado ao phpIPAM, o Master poderá verificar de forma complementar se o IP de origem da solicitação é compatível com as redes associadas à OM.

Essa integração:

- é opcional;
- não é requisito para concluir o bootstrap;
- não constitui autenticação isoladamente;
- deve usar o IP observado pelo Master ou por infraestrutura intermediária previamente confiável, nunca um IP declarado pelo cliente como prova.

O HECATE Local não deve receber credencial ou integração direta com o phpIPAM.

### 18.5. Operação federada

A conexão parte da OM para o Master. Homologar DNS, TLS, proxy/saída de rede e sincronismo de relógio conforme o ambiente; não exigir abertura de conexão de entrada do Master na OM.

A operação deve mostrar, conforme o fluxo aplicável:

- OM vinculada;
- domínio AD;
- última sincronização institucional;
- versão/hash do snapshot;
- última sincronização federada de métricas;
- atraso acumulado;
- erro acionável sem secrets.

Manter reenvio controlado após falhas e estado persistente suficiente para retomada. Confirmar recebimento antes de considerar o envio federado concluído e tratar correções conforme o contrato de [ARQUITETURA.md](ARQUITETURA.md#132-contrato-de-sincronização).

Backup/restore deve considerar identidade da instalação, credenciais protegidas e estado de sincronização. Homologar recuperação da mesma instância, rotação/revogação da credencial M2M e prevenção de envios concorrentes por cópias restauradas. Atualizações precisam preservar compatibilidade do contrato federado além dos schemas locais.
