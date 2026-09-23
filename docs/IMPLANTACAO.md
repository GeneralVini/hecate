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
- endpoint institucional do Catálogo MB e credencial própria da OM, quando não fornecidos por configuração segura.

No fluxo normal, o técnico não deve precisar informar o código institucional da OM: o domínio AD serve de referência inicial e a identidade oficial é confirmada diretamente no Catálogo MB, com revisão pelo operador. Segredos não devem ser gravados em pacote ou logs.

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
9. Configurar acesso direto ao Catálogo MB e confirmar a OM identificada.
10. Obter o snapshot institucional inicial da OM.
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

A identidade institucional da OM e sua estrutura organizacional devem ser obtidas do Catálogo MB, sem recadastro paralelo.

Código, pacotes e arquitetura permanecem padronizados.

## 18. Identificação e sincronização institucional

Nexus distribui/versiona o software genérico. Cada instalação identifica sua própria OM e consulta diretamente o Catálogo MB.

### 18.1. Identificação inicial

1. Validar domínio Samba AD, DNS e LDAPS da OM.
2. Configurar endpoint, TLS e credencial própria para consulta ao Catálogo MB.
3. Consultar a identidade oficial da OM e os dados organizacionais necessários.
4. Apresentar o resultado ao operador; divergências exigem verificação antes do vínculo.
5. Gravar o snapshot institucional local somente leitura.

O domínio AD é referência inicial, não identidade oficial. Clonar uma VM para outra OM exige nova configuração institucional e remoção dos dados e segredos da OM anterior.

### 18.2. Cache e atualização do Catálogo MB

O cache local pode ser persistido em PostgreSQL, preferencialmente como snapshot JSONB quando não houver necessidade de normalizar o domínio do Catálogo MB. Não é uma segunda fonte de verdade e não oferece CRUD dos dados recebidos.

O mesmo caso de uso atende à sincronização automática diária e à ação administrativa **Sincronizar agora**. O HECATE registra versão/hash, `last_success_at` e estado do refresh. Falha de atualização preserva o último snapshot válido e indica dados desatualizados. A integração deve definir timeouts e retomada sem bloquear impressão e administração locais.

Dados atualizados do Catálogo MB permitem correlacionar a estrutura institucional com usuários e grupos consultados em leitura no Samba AD. Divergências não autorizam alterações automáticas no domínio.

### 18.3. Operação e recuperação

O painel operacional deve mostrar OM identificada, domínio AD, última sincronização institucional, versão/hash do snapshot e erro acionável sem expor segredos. Backup/restore deve considerar snapshot, configuração e credenciais protegidas. Atualizações devem preservar a compatibilidade dos dados locais e permitir rotação da credencial da integração.

## 19. HECATE Demo

O **HECATE Demo** é uma variante instalável permanente para demonstração e treinamento, não um modo temporário a ser removido após o desenvolvimento.

A edição é habilitada por configuração:

```env
HECATE_EDITION=demo
HECATE_DEMO_DCTIM_DB_NAME=hecate_demo_dctim
HECATE_DEMO_CTIM_DB_NAME=hecate_demo_ctim
```

A edição normal usa `HECATE_EDITION=local` ou a ausência da variável e continua conectada ao database `HECATE_DB_NAME` da OM.

### 19.1. Isolamento dos cenários

Os cenários demonstrativos representam duas instalações locais independentes:

```text
hecate_demo_dctim -> DCTIM -> contrato por franquia
hecate_demo_ctim  -> CTIM  -> contrato por consumo
```

Não existe multi-OM no HECATE Local. A seleção DCTIM/CTIM é exclusiva da edição demo e deve ser apresentada ao usuário como **Cenário de demonstração**, não como troca normal de OM.

### 19.2. Cenários iniciais

DCTIM:

- franquia mensal de 5.000 páginas P&B;
- franquia mensal de 1.000 páginas coloridas;
- cobrança adicional quando a franquia for ultrapassada;
- seed determinística em `demo/seed-dctim.sql`.

CTIM:

- contrato por consumo;
- preço fixo por página P&B;
- preço fixo por página colorida;
- seed determinística em `demo/seed-ctim.sql`.

Os valores financeiros são fictícios e devem ser identificados como dados demonstrativos.

### 19.3. Reset dos bancos demo

Os bancos demonstrativos são descartáveis. Para recriá-los integralmente durante o desenvolvimento:

```bash
bash tools/reset-hecate-demo.sh
```

O script atua somente sobre os databases configurados como DCTIM e CTIM da edição demo, reaplica as migrations normais do produto e recarrega os seeds. O database normal `hecate` não deve ser removido nem alterado por esse procedimento.

O usuário PostgreSQL empregado no reset precisa ter permissão para criar/remover os databases demo.

### 19.4. Evolução dos dashboards

Dashboards da edição demo devem consumir as mesmas queries/read models que a instalação real. Não criar tabelas exclusivas de demonstração ou números hardcoded para simular jobs, usuários, militares, telemetria ou custos ainda não modelados no domínio.

Quando o schema funcional correspondente existir, os seeds demo devem ser ampliados para cobrir histórico determinístico de 12 meses e filtros de hoje, 7 dias, 30 dias, 3, 6, 9 e 12 meses.
