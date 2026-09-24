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

Para liberação de impressão, a decisão de acesso considera:

- usuário autenticado;
- vínculo organizacional;
- política da divisão;
- impressora solicitada;
- autorização excepcional vigente;
- quota disponível;
- estado do contrato/política quando aplicável.

IP não substitui identidade.

Para leituras, aplicar `Request -> RBAC/Permission -> resolução de escopo autorizado -> Query -> SQL restrito`. Autorizar o endpoint não autoriza todos os registros. Escopo é calculado no servidor a partir de identidade e vínculos confiáveis, nunca ampliado por parâmetros enviados pelo cliente.

Queries expostas a usuários devem exigir o escopo resolvido, sem fallback implícito para todos os dados. Escopo ausente é negado; escopo vazio não produz acesso global. Acesso global local deve ser uma concessão explícita. Centralizar a resolução e tornar seu uso obrigatório nas entradas de leitura evita depender de filtros lembrados manualmente. Filtros de tela apenas restringem o escopo e devem ser aplicados também a totais, exportações e agregações antes da paginação.

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
- credencial/token do Catálogo MB mantido na instalação da OM;
- chaves internas do agente.

Cada OM deve usar credencial própria e privilégio mínimo para a integração institucional. Segredos não devem ser incluídos no pacote genérico.

Nunca armazenar esses valores em código-fonte ou documentação versionada.

## 15. Perfis administrativos

Necessidades atuais:

- **Administrador geral do HECATE:** dashboards, CRUDs, configurações e gestão global da instância local, com concessão explícita; não implica privilégio de sistema operacional ou de outras OMs.
- **Gestor de contrato de impressão:** leitura predominante de contratos, consumo, franquias, custos, impressoras vinculadas e indicadores, limitada aos contratos autorizados. Escritas eventuais exigem permissão própria; leitura não concede administração.

Os perfis funcionais abaixo continuam como referência de responsabilidades e segregação. Não substituem os cenários acima nem devem virar roles obrigatórias sem necessidade:

### Administrador Técnico

- stack;
- serviços;
- impressoras;
- diagnóstico;
- integrações técnicas.

### Administrador Funcional

- políticas;
- quotas;
- contratos;
- configurações de negócio próprias do HECATE.

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

## 17. Segurança da integração institucional

### 17.1. Princípios

O pacote genérico não deve conter `OM_ID`, token do Catálogo MB nem credencial compartilhada entre OM. Identidade institucional e credenciais próprias são configuradas após a instalação.

Usar TLS com validação de certificado nas consultas ao Catálogo MB. Restringir a configuração de destino a administradores autorizados e validar URL/destinos permitidos, inclusive redirecionamentos, para evitar SSRF e envio a destinatário indevido. Segredos ficam em armazenamento operacional protegido, fora de pacote, Git e logs; rotação e sincronizações devem ser auditáveis sem expor credenciais.

### 17.2. Identificação da OM no bootstrap

Durante a instalação inicial, o HECATE valida o domínio Samba AD da OM. O domínio serve como referência inicial e não deve ser tratado como identidade oficial por si só.

O HECATE da OM consulta diretamente o Catálogo MB, fonte autoritativa dos dados institucionais, para obter e confirmar código, indicativo, nome e demais informações necessárias. O operador confirma a OM identificada antes do vínculo local.

Endpoint e credencial da API são protegidos na instalação da OM, com acesso restrito e sem exposição em tela ou log.

Divergências entre o domínio informado, os dados retornados pelo Catálogo MB ou outras fontes auxiliares não devem ser corrigidas automaticamente pelo HECATE. Devem impedir vínculo automático ou exigir verificação administrativa.

### 17.3. Premissa de segurança do bootstrap

A identificação inicial ocorre em ambiente institucional controlado.

A combinação entre domínio AD validado, identificação oficial pelo Catálogo MB e confirmação pelo operador fundamenta o vínculo inicial. Consultas posteriores à API usam a credencial própria da OM.

### 17.4. Cache institucional

O cache do Catálogo MB na instalação da OM é técnico, somente leitura e descartável. Não é fonte autoritativa e não deve possuir mecanismos de edição dos dados recebidos.

Falha de atualização não deve apagar o último snapshot válido. O estado de sincronização deve permitir distinguir dados atuais, desatualizados e falha de refresh. Correções de dados institucionais devem ocorrer no Catálogo MB.

### 17.5. Usuários e grupos do AD

Consultar usuários e grupos por LDAPS com conta de leitura e escopo mínimo. Dados do Catálogo MB ajudam a correlacionar lotação e estrutura institucional; inconsistências exigem revisão humana. O HECATE não modifica usuários, grupos ou vínculos no Samba AD automaticamente.

## 18. Segurança no desenvolvimento

O baseline de segurança de código é composto por ferramentas livres/gratuitas e executadas nativamente no ambiente de desenvolvimento:

- **PHPStan:** análise estática principal de tipos e inconsistências;
- **Composer Audit:** SCA de dependências e advisories conhecidos a partir do lockfile;
- **Psalm Taint Analysis:** rastreamento de dados não confiáveis entre sources e sinks;
- **Semgrep Community Edition:** SAST complementar e regras locais versionadas em `security/semgrep.yml`;
- **OWASP ZAP:** DAST separado para aplicação em execução.

O comando padrão de segurança estática é:

```bash
composer security
```

Ele executa, nessa ordem, `composer audit`, Psalm com `--taint-analysis`, os testes das regras locais Semgrep e o scan Semgrep CE. A validação das regras pode ser executada isoladamente com:

```bash
composer security:semgrep:test
```

O gate completo de desenvolvimento é:

```bash
composer check
```

`composer check` executa `composer qa` seguido de `composer security`. A mesma composição deve ser repetida no CI.

OWASP ZAP não integra o `composer check`, pois depende da aplicação em execução. O comando `composer security:dast` é destinado exclusivamente a ambiente local/de teste autorizado; o wrapper versionado restringe o alvo automatizado a `localhost`/`127.0.0.1`.

Semgrep CE e OWASP ZAP são instalados em `.tools/` pelo `make setup`, sem Docker e sem instalação global obrigatória. Semgrep é mantido em virtualenv Python próprio. O pacote Linux do ZAP é baixado em versão fixada e validado por SHA-256 antes da extração. O diretório `.tools/` não é versionado.

Achado de scanner não deve ser silenciado apenas para liberar o pipeline. Falso positivo deve ser analisado e, se necessário, tratado de forma localizada e documentável. Regras globais ou baselines amplos não devem esconder vulnerabilidades reais.

## 19. Modelo de proteção por sink

Validação de entrada e proteção de saída são responsabilidades diferentes. O HECATE não deve tentar tornar toda entrada genericamente "sanitizada". O controle deve ser aplicado conforme o destino do dado:

```text
Entrada HTTP  -> validar tipo, formato, tamanho e regra de domínio
HTML          -> escaping contextual; preferir helpers Yii/yiisoft-html
SQL           -> parâmetros/bindings; allowlist para tabela/coluna/ordenação
Shell         -> proibido na aplicação web; operações fechadas via hecate-agent
URL externa   -> validar esquema/host/destino; bloquear SSRF e redirecionamentos indevidos
Filesystem    -> base path controlado, normalização e allowlist quando aplicável
Logs          -> excluir senha, token, cookie, Authorization, PIN e conteúdo sensível
```

Views PHP não devem assumir escaping automático. Valores não confiáveis enviados para HTML devem ser codificados conforme o contexto. APIs que desativem encoding, como `encode(false)` ou `NoEncode`, são hotspots e exigem origem confiável e justificativa localizada.

CSRF deve ser tratado principalmente pela infraestrutura/middleware Yii3 e não por verificações ad hoc espalhadas nas Actions. Operações mutáveis não devem ser expostas por GET. Bypass de proteção CSRF em código de produção exige revisão de segurança.

O baseline local do Semgrep cobre padrões de alto sinal para execução de shell, desserialização insegura, construção dinâmica de SQL, inclusão dinâmica, path traversal, SSRF, headers/redirecionamentos, hotspots de XSS, exposição direta de superglobais, logging de segredos e saída de debug. As fixtures ficam em `security/semgrep-tests/` e devem acompanhar qualquer nova regra ou alteração material de uma regra existente.

Semgrep não substitui Psalm Taint, testes, middleware do Yii3 ou DAST. A defesa esperada é em camadas:

```text
Yii3 / código seguro
        +
Psalm Taint
        +
Semgrep HECATE
        +
PHPUnit / testes de integração
        +
OWASP ZAP em ambiente autorizado
```
