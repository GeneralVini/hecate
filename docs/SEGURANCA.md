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
- **Semgrep Community Edition:** SAST complementar e regras locais versionadas em `security/semgrep-rules/hecate.yml`;
- **OWASP ZAP:** DAST separado para aplicação em execução.

O comando padrão de segurança estática é:

```bash
composer security
```

Ele executa, nessa ordem:

```text
Composer Audit
-> Psalm Taint
-> validação das regras Semgrep
-> testes positivos/negativos das regras
-> scan Semgrep do código HECATE
```

Os comandos de diagnóstico isolado são:

```bash
composer security:semgrep:validate
composer security:semgrep:test
composer security:semgrep
```

A validação deve ocorrer antes dos testes para impedir que uma regra PHP inválida chegue ao `semgrep --test`. Regras e fixtures ficam em árvores paralelas e com o mesmo basename:

```text
security/semgrep-rules/hecate.yml
security/semgrep-tests/hecate.php
```

No uso cotidiano, não executar toda a cadeia manualmente em sequência. O gate completo é:

```bash
composer check
```

`composer check` executa `composer qa` seguido de `composer security`; portanto já inclui a validação, os testes e o scan Semgrep. A mesma composição deve ser repetida no CI.

OWASP ZAP não integra o `composer check`, pois depende da aplicação em execução. O comando `composer security:dast` é destinado exclusivamente a ambiente local/de teste autorizado; o wrapper versionado restringe o alvo automatizado a `localhost`/`127.0.0.1`.

Semgrep CE e OWASP ZAP são instalados em `.tools/` pelo `make setup`, sem Docker e sem instalação global obrigatória. Semgrep é mantido em virtualenv Python próprio no ambiente local. Os wrappers também aceitam executável Semgrep disponibilizado pelo `PATH` no CI; isso não altera a versão homologada pelo workflow. O pacote Linux do ZAP é baixado em versão fixada e validado por SHA-256 antes da extração. O diretório `.tools/` não é versionado.

### 18.1. Classificação de findings Semgrep

As severidades locais têm significado operacional explícito:

```text
ERROR    finding de alto sinal; bloqueia o gate e exige correção ou análise técnica
WARNING  hotspot para revisão; não equivale por si só a vulnerabilidade e não bloqueia o gate
```

O wrapper `scripts/semgrep-scan.sh` executa o Semgrep em JSON, separa findings bloqueantes de hotspots e apresenta resumo com regra, arquivo, linha, motivo e trecho afetado. Erro interno, erro de parsing ou falha do mecanismo é tratado separadamente de finding de segurança e também invalida o gate, pois o scan não pode ser considerado confiável.

A cobertura também é parte do resultado. O scan usa alvos explícitos `src`, `config` e `public`, inclui arquivos ainda não rastreados pelo Git com `--no-git-ignore` e exclui deliberadamente `public/assets/**` por ser conteúdo gerado em runtime. Skips reportados pelo Semgrep são divididos em exclusões por política e skips inesperados. Exclusão deliberada não degrada o resultado; skip inesperado torna a cobertura parcial e invalida o gate até que a causa seja entendida ou a política explícita seja ajustada.

Um finding não deve ser declarado vulnerabilidade confirmada sem contexto. Em especial, regras de taint procuram fluxo de fonte não confiável para sink perigoso; regras `WARNING` marcam construções que merecem revisão humana. Achado de scanner não deve ser silenciado apenas para liberar o pipeline. Falso positivo deve ser reduzido refinando a regra e adicionando fixture negativa correspondente, em vez de criar suppressions globais.

### 18.2. Sources Yii3/PSR-7

O HECATE recebe entrada web prioritariamente pela infraestrutura Yii3/PSR-7, e as regras locais precisam representar esse fluxo real. Conforme o contrato analisado, são consideradas sources relevantes:

```text
$request->getQueryParams()
$request->getParsedBody()
$request->getHeaderLine(...)
$request->getUploadedFiles()
```

As superglobais PHP continuam cobertas quando aparecerem, mas não são a única representação de entrada HTTP. As regras de taint devem acompanhar dados PSR-7 até sinks de SQL, HTML, filesystem, URL externa e headers.

Para SQL, a presença de dado vindo do request no valor SQL passado a `createCommand()`, `query()` ou equivalente é finding bloqueante. SQL fixo com parâmetros/bindings permanece padrão seguro; identificadores dinâmicos, quando inevitáveis, exigem allowlist.

Open redirect e header injection são tratados separadamente. `Location` controlado externamente é risco de redirect; valores de outros headers derivados da requisição exigem validação específica e proteção contra CR/LF. Redirect produzido por `UrlGeneratorInterface` para rota interna conhecida deve permanecer coberto como caso negativo, não como vulnerabilidade.

URLs controladas pela requisição que alcançam `curl_init()`, `CURLOPT_URL`, `file_get_contents()` ou `fopen()` entram no contrato SSRF. Caminhos controlados externamente que alcançam includes ou filesystem entram no contrato de path traversal/LFI.

Uso de MD5/SHA-1 sobre variáveis semanticamente ligadas a senha, PIN ou credencial é hotspot (`WARNING`), porque o HECATE exige hash forte para PIN e não deve usar hashes criptograficamente fracos para autenticadores.

## 19. Modelo de proteção por sink

Validação de entrada e proteção de saída são responsabilidades diferentes. O HECATE não deve tentar tornar toda entrada genericamente "sanitizada". O controle deve ser aplicado conforme o destino do dado:

```text
Entrada HTTP  -> validar tipo, formato, tamanho e regra de domínio
HTML          -> escaping contextual; preferir helpers Yii/yiisoft-html
SQL           -> parâmetros/bindings; allowlist para tabela/coluna/ordenação
Shell         -> proibido na aplicação web; operações fechadas via hecate-agent
URL externa   -> validar esquema/host/destino; bloquear SSRF e redirecionamentos indevidos
Filesystem    -> base path controlado, normalização e allowlist quando aplicável
Headers       -> validar semântica e rejeitar CR/LF; Location externo exige allowlist/rota segura
Logs          -> excluir senha, token, cookie, Authorization, PIN e conteúdo sensível
```

Views PHP não devem assumir escaping automático. Valores não confiáveis enviados para HTML devem ser codificados conforme o contexto. APIs que desativem encoding, como `encode(false)` ou `NoEncode`, são hotspots e exigem origem confiável e justificativa localizada.

CSRF deve ser tratado principalmente pela infraestrutura/middleware Yii3 e não por verificações ad hoc espalhadas nas Actions. Operações mutáveis não devem ser expostas por GET. Bypass de proteção CSRF em código de produção exige revisão de segurança.

O baseline local do Semgrep prioriza padrões de alto sinal e usa taint mode onde o risco depende de fluxo de dados HTTP — inclusive PSR-7 — para sinks de SQL injection, XSS, SSRF, path traversal/LFI, redirecionamento e header injection. Regras diretas continuam cobrindo execução de shell/código dinâmico, desserialização insegura, construção dinâmica de SQL, logging de segredos, hash fraco de credenciais e hotspots de saída/debug. Toda alteração material de regra deve preservar uma fixture positiva (`ruleid`) e uma negativa (`ok`) pertinente.

O caso seguro `dirname(__DIR__)` usado para formar caminho local de bootstrap é explicitamente coberto como regressão negativa e não deve ser confundido com entrada controlada pelo usuário. Da mesma forma, redirect gerado por `UrlGeneratorInterface` para rota interna conhecida deve permanecer como fixture negativa de open redirect.

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
