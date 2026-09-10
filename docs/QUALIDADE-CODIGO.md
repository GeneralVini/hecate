# Qualidade, Compliance e Padrões de Código — HECATE

## 1. Finalidade

Este documento define os padrões mínimos de qualidade de código e os controles de compliance técnico do HECATE. O objetivo é impedir que o produto evolua apenas por validação funcional, sem critérios objetivos de qualidade, manutenibilidade, segurança e aderência a padrões PHP.

A qualidade deve ser verificável no pipeline de CI e não depender apenas de revisão manual.

---

## 2. Padrões PHP adotados

### Obrigatórios

- **PSR-1 — Basic Coding Standard**: regras básicas de arquivos, namespaces, classes, constantes e métodos.
- **PSR-4 — Autoloading Standard**: autoload via Composer e namespaces coerentes com o código do projeto.
- **PSR-12 — Extended Coding Style**: padrão principal de estilo e formatação do código PHP.

O PSR-12 substitui o PSR-2 e deve ser tratado como baseline mínimo do projeto.

### Evolução recomendada

O projeto deve acompanhar o **PER Coding Style** da PHP-FIG, adotando regras compatíveis com a versão mínima de PHP homologada para o HECATE e com Yii2.

### PSRs aplicáveis por integração

- **PSR-3 — Logger Interface**: recomendado em componentes próprios que precisem expor logging interoperável. Não é obrigatório substituir o mecanismo nativo de log do Yii2.
- **PSR-11 — Container Interface**: apenas onde houver necessidade real de interoperabilidade. Não criar container paralelo ao Yii2.
- **PSR-6 / PSR-16 — Cache**: opcionais em bibliotecas ou adapters que precisem interoperar com caches externos.
- **PSR-18 — HTTP Client**: útil em clientes reutilizáveis de APIs externas quando houver benefício de interoperabilidade.

### PSRs que não devem ser impostos ao núcleo Yii2 sem necessidade

PSR-7 e PSR-15 não devem ser introduzidos apenas para declarar conformidade, pois alterariam o modelo HTTP/middleware natural do Yii2 e aumentariam complexidade sem ganho direto para o HECATE.

**Decisão:** para o código próprio do HECATE, o conjunto obrigatório é **PSR-1 + PSR-4 + PSR-12**, com acompanhamento do PER Coding Style e adoção seletiva dos demais PSRs em interfaces de integração.

---

## 3. Lint e estilo

Todo código PHP deve passar por verificação automática antes de merge.

### PHP syntax lint

Executar `php -l` nos arquivos PHP modificados ou em todo o código próprio do projeto. Falhas de sintaxe bloqueiam o pipeline.

### PHP_CodeSniffer

Utilizar **PHP_CodeSniffer (PHPCS)** com ruleset baseado em PSR-12.

Objetivos:

- identificar violações de estilo;
- garantir consistência de indentação, imports, espaços e estrutura;
- impedir divergência progressiva entre módulos;
- permitir correção automática com PHPCBF quando segura.

Comandos de referência:

```bash
vendor/bin/phpcs
vendor/bin/phpcbf
```

O ruleset deve excluir apenas dependências, artefatos gerados e runtime. Código próprio não deve ser ignorado para contornar violações.

---

## 4. Análise estática com PHPStan

PHPStan é obrigatório no pipeline.

### Estratégia de níveis

1. iniciar com **nível 8** como gate mínimo;
2. corrigir tipagem e modelagem até atingir nível 9;
3. atingir **nível 10 / max** como objetivo de maturidade;
4. nunca reduzir o nível para aprovar uma entrega.

### Integração com Yii2

Utilizar extensão de PHPStan compatível com a versão homologada de Yii2/PHPStan para melhorar inferência de ActiveRecord, ActiveQuery, `Yii::$app`, componentes do service locator e propriedades/métodos mágicos do framework.

### Baseline

Um baseline pode ser usado temporariamente em código legado ou em fase de elevação de nível, mas não pode esconder erros novos, crescer sem justificativa nem substituir correção de código novo.

**Gate:** nenhuma nova violação PHPStan no código alterado e análise global aprovada no nível mínimo homologado.

---

## 5. SonarQube

O HECATE deve possuir análise contínua no SonarQube institucional quando disponível.

A análise deve abranger, no mínimo:

- bugs/reliability;
- vulnerabilidades/security;
- security hotspots;
- maintainability/code smells;
- duplicação de código;
- complexidade;
- cobertura de testes;
- dívida técnica.

### Quality Gate

O merge para `main` deve ser bloqueado quando o Quality Gate falhar.

Política recomendada para código novo:

- zero vulnerabilidades novas críticas/altas;
- zero bugs novos críticos/altos;
- security hotspots revisados;
- nenhuma piora deliberada de maintainability sem justificativa;
- duplicação sob limite institucional definido;
- cobertura mínima para código crítico;
- nenhuma issue `BLOCKER` ou `CRITICAL` aberta em código novo.

---

## 6. Componentes visuais reutilizáveis

O HECATE deve evitar repetição de marcação, regras visuais e comportamento JavaScript entre páginas.

### Widgets Yii2

Usar `widgets/` do padrão Yii2 Basic para componentes efetivamente reutilizáveis e com comportamento próprio. Exemplos esperados:

- `FlashAlert` para mensagens de sucesso, aviso, erro e informação;
- GridView institucional para padronizar cabeçalho, ações, paginação, vazio, filtros e responsividade;
- badges de estado (`OK`, `ATENÇÃO`, `INDISPONÍVEL`, `EXPIRADO`);
- cards de saúde do stack;
- indicador de cota P&B/colorida;
- indicador de suprimentos;
- componente de confirmação para ações sensíveis;
- componentes de filtros recorrentes;
- breadcrumbs quando houver comportamento adicional ao padrão.

Não criar widget para cada trecho de HTML. Se for apenas marcação reutilizável e sem comportamento, preferir partial em `views/`.

**Regra prática:** um padrão visual/comportamental repetido em dois ou mais pontos, ou que represente regra institucional de UX, deve ser candidato a componente reutilizável.

### GridView

Evitar configurar manualmente o mesmo `GridView` em dezenas de telas. Padronizar, por composição ou configuração reutilizável:

- tabela responsiva;
- estado vazio;
- coluna de ações;
- formatação de datas/status;
- paginação;
- filtros;
- mensagens e tooltips;
- acessibilidade básica;
- aparência coerente com HECATE.

A abstração não deve esconder a API do Yii2 nem dificultar customizações legítimas da tela.

---

## 7. Política de CSS e JavaScript

Não criar arquivos CSS ou JavaScript específicos para cada página por padrão.

### Regra geral

- concentrar estilo global e componentes no `AppAsset` e folhas de estilo compartilhadas;
- concentrar comportamentos reutilizáveis em JavaScript comum da aplicação;
- utilizar classes utilitárias do Bootstrap 5 antes de criar CSS novo;
- criar estilos por componente reutilizável, não por URL/tela;
- evitar CSS inline;
- evitar JavaScript inline;
- evitar `style=` e handlers HTML como `onclick=`;
- usar `data-*` para parametrizar comportamento compartilhado quando apropriado.

### Exceção

Arquivo CSS/JS específico de página somente é aceitável quando a tela possui comportamento realmente exclusivo, de porte suficiente para justificar isolamento e que não faça sentido como componente reutilizável. A exceção deve ser clara em code review.

Pequenas inicializações contextuais podem usar mecanismos de registro do Yii2 quando isso for mais simples, mas não devem concentrar lógica de negócio nem transformar as views em scripts extensos.

---

## 8. Segurança de código e sanitização

Segurança é requisito de entrega, não atividade posterior. O HECATE deve adotar validação de entrada, escaping de saída, autorização no servidor e APIs seguras do framework.

### 8.1. XSS

- todo dado não confiável exibido como texto deve ser escapado;
- em Yii2, preferir `Html::encode()` e helpers que façam escaping corretamente;
- não renderizar entrada do usuário como HTML por conveniência;
- quando HTML rico for requisito real, usar whitelist/sanitização apropriada, como `HtmlPurifier`, com configuração restritiva;
- evitar concatenar dados dinâmicos diretamente em atributos HTML ou JavaScript;
- valores inseridos em JS devem ser serializados de forma segura, nunca concatenados manualmente;
- adotar Content Security Policy compatível com a aplicação como defesa adicional, sem tratá-la como substituta do escaping.

**Princípio:** validar entrada não substitui escaping de saída. Cada contexto de saída deve ser tratado corretamente.

### 8.2. SQL Injection

- utilizar ActiveRecord, Query Builder ou comandos parametrizados do Yii2;
- nunca concatenar valores de requisição em SQL;
- em SQL nativo, usar parâmetros/bind values;
- nomes dinâmicos de tabela, coluna e ordenação devem vir de allowlist, pois parâmetros SQL não protegem identificadores;
- filtros de GridView/SearchModel devem mapear apenas atributos permitidos;
- nunca aceitar expressão SQL arbitrária da interface ou API.

### 8.3. Mass assignment e validação de modelos

- definir `rules()` e cenários explicitamente;
- revisar atributos `safe`;
- não carregar indiscriminadamente payload em modelos administrativos;
- campos de privilégios, aprovação, saldo, owner, OM, divisão e status não devem ser controláveis pelo cliente quando forem derivados do contexto/autorização;
- validar tipo, tamanho, formato, enumeração e faixa, inclusive em APIs internas.

### 8.4. CSRF

- manter proteção CSRF do Yii2 habilitada em operações web autenticadas;
- não desabilitar CSRF globalmente para resolver integração;
- endpoints de integração devem possuir mecanismo próprio adequado de autenticação e integridade, separado da interface web;
- ações mutáveis devem respeitar métodos HTTP apropriados e `VerbFilter`.

### 8.5. Autorização, IDOR/BOLA e segregação de OM

- autenticação não implica autorização;
- toda consulta/ação sensível deve verificar permissão no servidor;
- nunca confiar que esconder botão na interface impede acesso;
- validar ownership de jobs antes de visualizar, cancelar ou liberar;
- validar escopo de OM/divisão em toda operação administrativa;
- IDs recebidos do cliente não podem ser usados sem verificação de autorização sobre o objeto correspondente;
- perfis Técnico, Funcional, Aprovador e Auditor devem possuir menor privilégio necessário.

### 8.6. Command injection e `hecate-agent`

Esse risco é especialmente crítico no HECATE.

- o PHP não deve executar shell arbitrário;
- não usar `exec`, `shell_exec`, `system`, `passthru` ou equivalentes com dados de usuário;
- HECATE Web deve solicitar ao `hecate-agent` apenas operações de uma lista fechada;
- argumentos devem possuir schema, tipo e allowlist;
- nomes de serviços, filas, interfaces e operações não devem ser transformados diretamente em comandos shell;
- o agent deve chamar APIs/bibliotecas ou executar comandos previamente definidos, com argumentos validados;
- toda operação privilegiada deve ser auditada.

### 8.7. SSRF

Como o HECATE consulta impressoras, EWS, IPP, Catálogo MB e outros endpoints, SSRF deve ser tratado explicitamente.

- validar IP/FQDN antes de conexões iniciadas pelo servidor;
- impedir uso do recurso de descoberta como proxy para destinos arbitrários;
- aplicar allowlist/faixas institucionais quando a implantação permitir;
- bloquear esquemas inesperados (`file:`, `gopher:`, etc.);
- controlar redirects HTTP;
- definir timeouts e limites de resposta;
- não permitir que URL fornecida por usuário determine livremente acesso a serviços locais sensíveis.

### 8.8. Path traversal e arquivos

- não construir caminhos de filesystem diretamente a partir de entrada do usuário;
- normalizar e validar nomes de arquivos;
- evitar upload de arquivos no MVP salvo requisito explícito;
- se upload for introduzido, validar tipo real, extensão, tamanho, destino, permissões e impedir execução;
- conteúdo temporário de impressão pertence ao fluxo SavaPage e não deve virar armazenamento documental do HECATE.

### 8.9. Secrets e credenciais

- nunca versionar senha, token, secret OIDC, community SNMP ou chave privada;
- usar variáveis de ambiente/secret store homologado;
- não expor secrets em exceptions, logs, dumps ou dashboard;
- aplicar rotação quando suportada;
- contas LDAP devem permanecer read-only e com privilégio mínimo.

### 8.10. Sessão e autenticação

- cookies de sessão com `Secure`, `HttpOnly` e política `SameSite` compatível;
- TLS obrigatório em produção;
- regenerar sessão nos eventos de autenticação apropriados;
- respeitar expiração e logout do Keycloak;
- PIN do HECATE deve ser armazenado com hash forte e nunca logado;
- aplicar rate limiting/bloqueio nas tentativas de PIN e operações de autenticação local aplicáveis.

### 8.11. Headers e navegador

Definir e homologar, no proxy/web server ou aplicação, headers compatíveis com o sistema, incluindo:

- Content-Security-Policy;
- X-Content-Type-Options;
- Referrer-Policy;
- política de frame/frame-ancestors;
- HSTS quando HTTPS institucional estiver consolidado.

### 8.12. Logs e dados sensíveis

- não registrar senha, token, cookie, PIN, conteúdo de documentos ou payload sensível integral;
- mascarar campos sensíveis;
- evitar log injection, normalizando dados livres quando necessário;
- separar log operacional de trilha de auditoria;
- registrar identidade, ação, objeto, resultado e timestamp em operações administrativas relevantes.

---

## 9. Regras de arquitetura e manutenibilidade

Além dos PSRs:

- preferir `declare(strict_types=1);` no código próprio quando compatível;
- utilizar tipos nativos de parâmetro, retorno e propriedades sempre que possível;
- evitar `mixed` sem necessidade;
- evitar arrays sem shape conhecido nas fronteiras de integração;
- controllers devem permanecer pequenos;
- regras de negócio críticas não devem ficar em views;
- views não devem consultar banco ou serviços externos diretamente;
- models/SearchModels devem concentrar validação e consulta compatíveis com o padrão Yii2;
- evitar helpers globais com estado;
- não criar classes, diretórios ou camadas arquiteturais apenas para reproduzir padrões de outros frameworks;
- extrair reutilização somente quando houver ganho claro de coesão, teste ou segurança;
- toda ação privilegiada deve ser auditável.

---

## 10. Testes automatizados

O pipeline deve evoluir para incluir:

- testes unitários;
- testes de integração;
- testes de migrations;
- testes de autorização;
- testes de reserva/consumo de cotas;
- testes dos adapters SavaPage, Catálogo MB e `hecate-agent`;
- testes dos fluxos críticos de liberação de impressão;
- testes negativos de autorização e validação;
- testes de regressão para vulnerabilidades corrigidas.

Fluxos de segurança, autorização, cotas, transferência e liberação devem possuir cobertura superior ao restante da aplicação.

---

## 11. Dependency e supply-chain compliance

Obrigatório:

- `composer validate`;
- `composer audit`;
- `composer.lock` versionado;
- versões de dependências controladas;
- atualização deliberada e testada;
- proibição de dependências abandonadas sem justificativa formal;
- análise de vulnerabilidades antes do release;
- origem e versão rastreáveis de imagens OCI e pacotes RPM distribuídos via Nexus.

Dependências frontend também devem ser inventariadas e fixadas por versão quando aplicável.

---

## 12. Revisão de segurança

Code review deve procurar explicitamente, quando aplicável:

- XSS;
- SQL Injection;
- command injection;
- SSRF;
- path traversal;
- CSRF;
- IDOR/BOLA;
- broken access control;
- mass assignment;
- exposição de secrets;
- logging de dados sensíveis;
- validação insuficiente de integrações externas;
- race conditions em cotas/reservas/aprovações;
- ausência de auditoria em operação sensível.

A revisão deve considerar OWASP Top 10 e, para APIs, OWASP API Security Top 10 como referências de ameaça, adaptadas ao contexto do HECATE.

---

## 13. Pipeline mínimo de CI

```text
composer validate
      ↓
composer install
      ↓
php -l
      ↓
PHPCS / PSR-12
      ↓
PHPStan
      ↓
testes automatizados
      ↓
composer audit
      ↓
SonarQube / SAST
      ↓
Quality Gate
      ↓
merge/release
```

Qualquer etapa obrigatória com falha deve bloquear merge/release.

---

## 14. Definition of Done técnica

Uma entrega de código somente pode ser considerada concluída quando:

- [ ] segue PSR-1, PSR-4 e PSR-12;
- [ ] passa em `php -l`;
- [ ] passa no PHPCS;
- [ ] passa no PHPStan no nível mínimo homologado;
- [ ] não adiciona suppressions/baselines sem justificativa;
- [ ] possui testes adequados ao risco da alteração;
- [ ] `composer validate` passa;
- [ ] `composer audit` não apresenta vulnerabilidade não aceita formalmente;
- [ ] SonarQube/Quality Gate está aprovado quando disponível;
- [ ] entradas são validadas e saídas não confiáveis são escapadas conforme contexto;
- [ ] queries dinâmicas estão parametrizadas/allowlisted;
- [ ] autorização de objeto e escopo foi validada no servidor;
- [ ] não há comando shell construído com entrada não confiável;
- [ ] não introduz segredos ou dados sensíveis no repositório/logs;
- [ ] CSS/JS específico de página só existe com justificativa;
- [ ] padrão visual/comportamental repetido foi avaliado para widget/partial compartilhado;
- [ ] documentação técnica foi atualizada quando necessário;
- [ ] revisão de código foi realizada antes do merge.

---

## 15. Meta de maturidade para 1.0

A versão 1.0 do HECATE deve ser entregue com:

- PSR-12 aplicado automaticamente;
- PHPStan mínimo nível 8, com plano de elevação para 10/max;
- lint de sintaxe obrigatório;
- biblioteca mínima de widgets/componentes institucionais reutilizáveis;
- política de assets compartilhados aplicada;
- testes automatizados dos fluxos críticos;
- testes negativos para controles de autorização e validação;
- Composer audit;
- SonarQube/SAST integrado ao CI;
- Quality Gate bloqueante;
- zero segredo versionado;
- revisão explícita de XSS, SQLi, SSRF, command injection, CSRF e broken access control;
- critérios de qualidade reproduzíveis por qualquer pipeline institucional.
