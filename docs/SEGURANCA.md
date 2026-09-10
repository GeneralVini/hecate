# Segurança HECATE

## 1. Objetivo

A segurança do HECATE deve garantir que o serviço de impressão seja autenticado, autorizado, auditável e operável sem ampliar desnecessariamente privilégios no domínio ou no servidor da OM.

## 2. Princípio de menor privilégio

- Samba AD somente leitura.
- Contas de serviço dedicadas.
- Credenciais separadas por componente.
- HECATE Web sem sudo genérico.
- Operações privilegiadas somente pelo `hecate-agent`.
- Banco de dados com owners separados.

## 3. Samba AD / LDAP

Preferir LDAPS com validação de certificado.

O HECATE e componentes associados não devem:

- criar ou remover usuários;
- alterar memberships;
- alterar GPO;
- alterar OU;
- alterar DNS;
- redefinir senhas;
- usar conta Domain Admin.

## 4. Autenticação web

Keycloak é o componente de SSO/OIDC.

Objetivos:

- não armazenar senha do domínio no HECATE;
- permitir política de sessão centralizada;
- suportar MFA futuramente;
- permitir federação com múltiplas fontes quando necessário.

## 5. PIN de liberação

O PIN pertence ao HECATE e deve:

- ser armazenado com hash forte;
- nunca ser registrado em log;
- possuir controle de tentativas;
- permitir bloqueio temporário;
- permitir reset auditado;
- possuir política de validade configurável.

O PIN é um segundo gesto de autorização do job, não prova presença física junto à impressora.

## 6. Autorização

A decisão de acesso considera:

- usuário autenticado;
- vínculo organizacional;
- política da divisão;
- impressora solicitada;
- autorização excepcional vigente;
- quota disponível;
- estado do contrato/política quando aplicável.

IP não substitui identidade.

## 7. Fluxo de impressão controlado

As estações devem usar o fluxo SavaPage.

As filas físicas CUPS não devem ser publicadas aos usuários comuns.

Acesso direto cliente -> impressora deve ser restringido pela infraestrutura da OM sempre que possível, sem transformar firewall/ACL em componente do produto HECATE.

## 8. hecate-agent

O agente executa operações privilegiadas por lista fechada.

Proibido:

- endpoint de shell genérico;
- execução arbitrária de comandos informados pelo frontend;
- sudo irrestrito para usuário web.

Toda ação sensível deve registrar:

- usuário solicitante;
- operação;
- alvo;
- data/hora;
- resultado;
- justificativa quando aplicável.

## 9. Banco de dados

Usar databases separados:

```text
hecate
savapage
keycloak
```

Cada database deve possuir owner/credencial própria.

Segredos não devem ser versionados no Git.

## 10. SavaPage

Regras:

- sem escrita direta em banco;
- sem manipulação não suportada do spool;
- integração apenas por interfaces homologadas;
- restringir acesso administrativo direto do SavaPage a equipe técnica autorizada.

Administradores funcionais devem operar preferencialmente pelo HECATE.

## 11. Retenção de documentos

Não habilitar arquivamento permanente de documentos impressos como requisito do HECATE.

Conteúdo do job existe apenas pelo tempo necessário ao processamento e retenção.

Após impressão, cancelamento ou expiração, deve ser eliminado conforme mecanismo homologado.

## 12. Logs e auditoria

Preservar logs suficientes para rastreabilidade sem registrar conteúdo sensível desnecessário.

Metadados desejados:

- usuário;
- data/hora;
- documento/job name;
- tamanho;
- páginas;
- P&B/colorida;
- IP/hostname de origem;
- impressora;
- status;
- OM/divisão;
- contrato/quota;
- autorização relacionada.

Logs administrativos devem ser protegidos contra alteração por usuário comum.

## 13. SNMP e impressoras

Preferir SNMPv3.

Fallback SNMPv2c somente leitura pode ser usado quando necessário.

Nunca usar SNMP SET como parte do monitoramento do HECATE.

Credenciais e communities devem ser protegidas como segredo operacional.

## 14. Segredos

Segredos incluem:

- senhas LDAP;
- client secrets OIDC;
- credenciais PostgreSQL;
- communities SNMP;
- credenciais SNMPv3;
- tokens do Catálogo MB;
- chaves internas do agente.

Nunca armazenar esses valores em código-fonte ou documentação versionada.

## 15. Perfis administrativos

Perfis de referência:

### Administrador Técnico

- stack;
- serviços;
- impressoras;
- diagnóstico;
- integrações técnicas.

### Administrador Funcional

- divisões;
- políticas;
- quotas;
- contratos.

### Aprovador

- transferências;
- exceções/autorização.

### Auditor

- leitura de logs, relatórios e trilhas de auditoria.

### Usuário comum

- próprios jobs;
- impressoras disponíveis;
- release;
- solicitação de exceção.

## 16. Continuidade

Como HA não é requisito inicial, segurança operacional inclui capacidade de reconstrução:

- backup de databases;
- export de configuração;
- documentação de instalação;
- imagens/pacotes homologados no Nexus;
- restauração testada periodicamente.
