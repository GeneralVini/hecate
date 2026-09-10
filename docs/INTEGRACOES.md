# Integrações HECATE

## 1. Princípios gerais

As integrações do HECATE devem ser desacopladas o suficiente para permitir homologação e evolução sem quebrar o núcleo do produto.

Regras obrigatórias:

- não escrever diretamente no banco do SavaPage;
- não alterar objetos do Samba AD;
- não depender de IP para identificar usuário ou divisão;
- preferir protocolos e interfaces documentadas;
- registrar falhas de integração e última sincronização;
- degradar telemetria sem bloquear impressão quando possível.

## 2. Samba AD / LDAP

O Samba AD é a fonte institucional de identidade.

### Uso permitido

- consulta de usuários;
- consulta de grupos;
- consulta de memberships;
- autenticação via Keycloak/federação;
- sincronização necessária ao SavaPage.

### Uso proibido

- criar ou alterar usuários;
- alterar grupos;
- alterar OU;
- alterar GPO;
- alterar DNS;
- redefinir senha;
- usar conta administrativa ampla sem necessidade.

### Segurança

- preferir LDAPS;
- validar CA/cadeia de confiança;
- usar conta de serviço com privilégio mínimo;
- preferir contas distintas para Keycloak e SavaPage.

Exemplos de nomes técnicos:

```text
svc-hecate-keycloak
svc-hecate-savapage
```

## 3. Keycloak

O Keycloak centraliza autenticação do portal HECATE.

Responsabilidades:

- OIDC;
- SSO;
- federação LDAP/AD;
- sessão web;
- base para MFA futuro.

O HECATE deve confiar nos claims homologados pelo Keycloak e não armazenar senha do domínio.

## 4. Catálogo MB

O Catálogo MB complementa a identidade com atributos organizacionais.

Dados de interesse:

- nome;
- posto/graduação;
- telefone;
- função;
- departamento/divisão;
- OM;
- outros atributos disponíveis na API e necessários à governança.

### Regra de precedência

```text
Samba AD = identidade
Catálogo MB = organização/função preferencial
HECATE = política e override controlado
```

### Cache e indisponibilidade

O HECATE deve:

- manter cache controlado;
- registrar timestamp da última sincronização;
- sinalizar divergências;
- sinalizar dados ausentes;
- evitar apagar vínculo válido apenas porque a API ficou temporariamente indisponível;
- permitir override administrativo auditado quando necessário.

### Override local

Deve registrar:

- valor anterior;
- valor aplicado;
- motivo;
- administrador;
- data/hora;
- validade opcional.

## 5. SavaPage

O SavaPage é o motor de impressão e accounting.

### Integrações desejadas

- usuários/grupos externos;
- ACL de impressoras;
- internal groups quando necessário;
- jobs retidos;
- accounting;
- consulta de metadados;
- liberação de job;
- cancelamento/expiração quando suportado;
- relatórios/contadores necessários ao HECATE.

### Regra de implementação

A integração deve ficar encapsulada em código compatível com a estrutura padrão do Yii2 Basic, evitando espalhar dependência do SavaPage pelo sistema.

Ordem de preferência:

1. CLI/documentação estável;
2. API oficial documentada;
3. JSON-RPC/XML-RPC documentado;
4. REST apenas onde homologado;
5. configuração suportada pelo produto.

Nunca:

- escrever diretamente nas tabelas do SavaPage;
- alterar arquivos internos de spool de forma não suportada;
- automatizar a interface web como mecanismo de produção.

### Liberação de job

O fluxo funcional está definido, mas o método exato de release de um job já retido precisa ser homologado em POC com a interface oficial adequada.

## 6. CUPS

O CUPS é interno ao fluxo.

Responsabilidades:

- fila física;
- driver/PPD quando necessário;
- spool;
- transporte ao equipamento.

As filas físicas não devem ser expostas aos clientes comuns, pois criariam bypass do SavaPage/HECATE.

## 7. hecate-agent

O agente é a ponte privilegiada entre o portal e o sistema operacional.

Ações previstas:

```text
health
service-status
service-start
service-stop
service-restart
ldap-test
catalog-test
db-test
printer-test
printer-discover
printer-supplies
collect-logs
cups-diagnostic
savapage-diagnostic
podman-status
```

Nomes finais podem mudar, mas o princípio é fixo: **ações fechadas e auditáveis**.

Preferir Unix socket local entre HECATE Web e agente.

## 8. Descoberta de impressoras

O agente deve tentar automaticamente:

1. ICMP/TCP/connectividade básica, quando permitido;
2. IPP/IPPS;
3. SNMPv3;
4. SNMPv2c read-only como fallback;
5. portas 631/9100/515 quando relevantes;
6. EWS/API HTTP/HTTPS;
7. parser por fabricante/modelo;
8. entrada manual.

### Dados desejados

- fabricante;
- modelo;
- número de série;
- cor/P&B;
- duplex;
- A3/A4 e demais formatos;
- protocolo de impressão;
- status;
- contador;
- suprimentos;
- firmware, quando disponível.

Cada valor deve registrar sua origem.

Exemplo:

```text
modelo: IPP
serial: SNMP
suprimentos: EWS
status: SNMP
```

## 9. SNMP

Preferência:

```text
SNMPv3 -> SNMPv2c read-only
```

Regras:

- nunca SNMP SET;
- credenciais/comunidades protegidas;
- Printer-MIB quando disponível;
- adaptar OIDs específicos só quando necessário.

Suprimentos devem ser normalizados com:

- tipo;
- descrição;
- cor;
- nível atual;
- capacidade máxima;
- percentual;
- fonte;
- timestamp.

## 10. EWS/API de fabricante

Fallback para equipamentos sem SNMP suficiente.

Ordem de preferência:

1. endpoint estruturado JSON/XML;
2. HTML/DOM estável;
3. parser específico por fabricante/modelo;
4. browser headless somente como último recurso futuro.

OCR/screenshot não é mecanismo de v1.

Adapters conceituais:

```text
generic-ipp
generic-snmp
hp-ews
epson-ews
xerox-ews
ricoh-ews
generic-http
```

## 11. PostgreSQL

Uma instância pode atender os componentes da OM, mas com isolamento lógico:

```text
DB hecate    -> owner hecate
DB savapage  -> owner savapage
DB keycloak  -> owner keycloak
```

Backups e restore devem considerar cada database separadamente.

## 12. Nexus Repository

O Nexus é central e institucional, não instalado em cada OM.

Hospeda:

- RPMs;
- repositórios DNF/Yum;
- imagens OCI;
- artefatos homologados;
- eventualmente arquivos raw necessários à distribuição.

Não participa do caminho de impressão em tempo real.

## 13. Clientes Windows e Ubuntu

A meta é que ambos enviem jobs ao caminho controlado do SavaPage.

A POC deve validar:

- identidade correta do usuário;
- nome do documento;
- IP/hostname de origem;
- páginas;
- P&B/colorida;
- comportamento sem prompts redundantes;
- impossibilidade prática de bypass pela fila física publicada.

## 14. Falhas e observabilidade

Cada integração deve expor no HECATE:

- estado atual;
- última verificação;
- última sincronização;
- mensagem de erro resumida;
- detalhes técnicos sob perfil autorizado;
- ação de teste/diagnóstico quando aplicável.
