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
- credencial/token do Catálogo MB mantido no HECATE Master;
- eventual credencial do phpIPAM mantida no HECATE Master;
- credenciais M2M de cada instalação;
- chaves internas do agente.

Credenciais de integrações corporativas do Master não devem ser distribuídas às instalações locais.

Nunca armazenar esses valores em código-fonte ou documentação versionada.

## 15. Perfis administrativos

Necessidades atuais:

- **Administrador geral do HECATE:** dashboards, CRUDs, configurações e gestão global da instância local, com concessão explícita; não implica privilégio de sistema operacional ou de outras OMs.
- **Gestor de contrato de impressão:** leitura predominante de contratos, consumo, franquias, custos, impressoras vinculadas e indicadores, limitada aos contratos autorizados. Escritas eventuais exigem permissão própria; leitura não concede administração.
- **HECATE Master:** consumidor federado de agregados e intermediador das integrações institucionais previstas, não usuário administrativo local; ver seção 17.

Os perfis funcionais abaixo continuam como referência de responsabilidades e segregação. Não são substituídos pelos três cenários acima nem devem virar roles obrigatórias sem necessidade:

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

## 17. Segurança da federação e bootstrap

### 17.1. Princípios

O pacote genérico não deve conter `OM_ID`, credencial permanente do Master, client secret global, token do Catálogo MB ou credencial compartilhada entre OM. A identidade federada é estabelecida após a instalação.

A conexão parte da OM para o Master. O Master vincula cada `installation_uuid` à OM confirmada no bootstrap e deve manter identidade/credencial própria por instalação para a operação federada posterior. O mecanismo M2M definitivo permanece sujeito a homologação, preferencialmente OAuth2 Client Credentials ou equivalente compatível com Keycloak. mTLS permanece opção futura condicionada à necessidade operacional.

Usar TLS com validação de certificado. Restringir a configuração de destino a administradores autorizados e validar URL/destinos permitidos, inclusive redirecionamentos, para evitar SSRF e envio a destinatário indevido. Segredos ficam em armazenamento operacional protegido, fora de pacote, Git e logs; registro, rotação, revogação e sincronizações devem ser auditáveis sem expor credenciais.

### 17.2. Identificação da OM no bootstrap

Durante a instalação inicial, o HECATE Local informa e valida o domínio Samba AD da OM. O domínio serve como referência inicial de descoberta e não deve ser tratado como identidade oficial por si só.

O HECATE Master consulta o Catálogo MB, que permanece como fonte autoritativa dos dados institucionais, para obter e confirmar código, indicativo, nome e demais informações necessárias. O operador confirma a OM identificada antes da associação definitiva da instalação.

CatalogoMB é consumido exclusivamente pelo Master. O HECATE Local não recebe endpoint, token ou credencial da API corporativa.

Divergências entre o domínio informado, os dados retornados pelo Catálogo MB ou outras fontes auxiliares não devem ser corrigidas automaticamente pelo HECATE. Devem impedir vínculo automático ou exigir verificação administrativa.

### 17.3. Premissa de segurança do bootstrap

O bootstrap ocorre em ambiente institucional controlado e considera como premissa a cooperação entre as OM. Na primeira versão, não é requisito proteger o registro contra tentativa deliberada de personificação de outra OM por agente interno.

A combinação entre domínio AD validado, identificação oficial pelo Catálogo MB e confirmação pelo operador é considerada suficiente para o bootstrap inicial. Essa premissa é específica do processo inicial de registro e não elimina a necessidade de autenticação M2M própria para as sincronizações posteriores entre HECATE Local e Master.

### 17.4. phpIPAM opcional

Quando houver integração disponível e autorizada com o phpIPAM, o Master poderá verificar de forma complementar a compatibilidade do endereço de origem da solicitação com as redes associadas à OM.

Essa verificação:

- é opcional;
- não é requisito para o bootstrap;
- não constitui autenticação isoladamente;
- deve utilizar o endereço observado pelo Master ou por infraestrutura intermediária previamente confiável;
- nunca deve confiar em um IP declarado pelo cliente como prova.

O phpIPAM, caso integrado, também é consumido exclusivamente pelo Master.

### 17.5. Cache institucional

O cache do Catálogo MB no Master é técnico, somente leitura e descartável. Não é fonte autoritativa e não deve possuir mecanismos de edição dos dados recebidos.

Falha de atualização não deve apagar o último snapshot válido. O estado de sincronização deve permitir distinguir dados atuais, desatualizados e falha de refresh. Correções de dados institucionais devem ocorrer no Catálogo MB.

### 17.6. Dados federados de governança

Enviar apenas métricas e dimensões aprovadas no contrato. Conteúdo de documentos, nomes de jobs, identidades individuais, credenciais e cópia das trilhas operacionais não integram o envio de governança. Os metadados da seção 12 são locais, com retenção e acesso controlados; não autorizam exportação ao Master. Definir granularidade que evite identificação indireta em grupos pequenos.

O receptor limita tamanho e frequência dos envios, valida versão e vínculo instância/OM e trata reenvios sem duplicação. Permissões federadas não concedem leitura irrestrita nem comandos sobre a OM. Retenção dos agregados e acesso dos usuários centrais devem ser definidos antes da homologação.

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

Ele executa, nessa ordem, `composer audit`, Psalm com `--taint-analysis` e Semgrep CE. A combinação `composer audit` + Psalm Taint é o núcleo inicial por oferecer cobertura adicional com pouca complexidade operacional; Semgrep amplia a detecção de padrões inseguros e permite regras específicas do HECATE.

O gate completo de desenvolvimento é:

```bash
composer check
```

`composer check` executa `composer qa` seguido de `composer security`. A mesma composição deve ser repetida no CI.

OWASP ZAP não integra o `composer check`, pois depende da aplicação em execução. O comando `composer security:dast` é destinado exclusivamente a ambiente local/de teste autorizado; o wrapper versionado restringe o alvo automatizado a `localhost`/`127.0.0.1`.

Semgrep CE e OWASP ZAP são instalados em `.tools/` pelo `make setup`, sem Docker e sem instalação global obrigatória. Semgrep é mantido em virtualenv Python próprio. O pacote Linux do ZAP é baixado em versão fixada e validado por SHA-256 antes da extração. O diretório `.tools/` não é versionado.

Achado de scanner não deve ser silenciado apenas para liberar o pipeline. Falso positivo deve ser analisado e, se necessário, tratado de forma localizada e documentável. Regras globais ou baselines amplos não devem esconder vulnerabilidades reais.
