# Desenvolvimento HECATE

## 1. Finalidade

Este documento consolida o ambiente de desenvolvimento, o baseline de qualidade e as convenções de documentação de código do HECATE. Ele é a referência prática para desenvolvimento local, hooks Git, CI e revisão técnica.

## 2. Ambiente local

A aplicação web segue o template oficial `yiisoft/app`, com Composer, PostgreSQL e ferramentas de QA versionadas no repositório. `composer.json` declara PHP 8.2–8.5; o CI configura PHP 8.5 e Rector usa regras PHP 8.2. Isso não comprova homologação de toda a faixa: a matriz de versões permanece pendente na EAP.

O ambiente de desenvolvimento Linux suporta duas famílias principais: Debian/Ubuntu e Oracle Linux/RHEL-like. Os scripts de bootstrap detectam a plataforma por `/etc/os-release`, com fallback para a presença de `apt-get` ou `dnf`, e exibem instruções adequadas para a distribuição quando um pré-requisito estiver ausente.

O Lefthook é um executável externo ao Composer e deve estar disponível no `PATH` antes do primeiro `make setup`.

Ubuntu/Kubuntu/Debian:

```bash
curl -1sLf 'https://dl.cloudsmith.io/public/evilmartians/lefthook/setup.deb.sh' | sudo -E bash
sudo apt-get install -y lefthook
lefthook version
```

Oracle Linux/RHEL/Rocky/AlmaLinux/Fedora:

```bash
curl -1sLf 'https://dl.cloudsmith.io/public/evilmartians/lefthook/setup.rpm.sh' | sudo -E bash
sudo dnf install -y lefthook
lefthook version
```

O baseline de segurança local requer também:

```text
Python 3.10+ com módulo venv
Java 17+
curl
sha256sum
```

No Oracle Linux 9, o `python3` do sistema pode permanecer em Python 3.9. O HECATE não substitui, remapeia nem remove esse runtime. Para as ferramentas de segurança, os scripts procuram explicitamente, nesta ordem, `python3.12`, `python3.11`, `python3.10` e `python3`, usando o primeiro interpretador disponível com versão >= 3.10.

Python 3.12 é a opção preferencial em Oracle Linux/RHEL-like quando disponível; Python 3.11 é o fallback operacional. A instalação é paralela ao Python do sistema. Exemplos:

```bash
sudo dnf install -y python3.12 python3.12-pip
# fallback, quando 3.12 não estiver disponível:
sudo dnf install -y python3.11 python3.11-pip
```

Em Debian/Ubuntu, o runtime padrão suportado continua sendo suficiente quando atende ao requisito >= 3.10:

```bash
sudo apt-get install -y python3 python3-venv
```

Os scripts validam também o suporte a `venv` antes de criar o ambiente privado do Semgrep. Durante o setup, o interpretador efetivamente selecionado é exibido, por exemplo:

```text
[INFO] Python selecionado para ferramentas de segurança: python3.12 (3.12.x)
```

Pacotes usuais adicionais por família:

```text
Debian/Ubuntu:          openjdk-17-jre curl coreutils
Oracle/RHEL-like:       java-17-openjdk-headless curl coreutils
```

Esses runtimes são pré-requisitos. Semgrep CE e OWASP ZAP não são instalados globalmente: o `make setup` os prepara em `.tools/` dentro do projeto.

Primeira execução do projeto:

```bash
git clone https://github.com/GeneralVini/hecate.git
cd hecate
make setup
```

O ramo `main` é a linha canônica de desenvolvimento e entrega. Não é necessário trocar para um ramo histórico após o clone.

O `composer.lock` é obrigatório. O bootstrap usa `composer install` e não deve executar `composer update`, `composer require` ou instalar pacotes do sistema de forma implícita.

O `make setup` detecta a família da distribuição; valida PHP, Composer, Git, Lefthook e a disponibilidade de um Python >= 3.10 para as ferramentas de segurança; instala as dependências a partir do lockfile; valida o Composer; confirma ECS, Rector, PHPStan, Psalm e PHPUnit; instala Semgrep CE e OWASP ZAP em `.tools/`; executa `lefthook install` e `lefthook validate`; e roda o baseline de QA e segurança.

Os scripts `scripts/bootstrap.sh` e `scripts/install-security-tools.sh` suportam Debian-like e Oracle Linux/RHEL-like. Eles não executam `apt-get`, `dnf` ou scripts de repositório automaticamente. Quando faltar uma dependência do sistema, informam o comando adequado e encerram para que a instalação seja uma decisão explícita do administrador.

O Semgrep é instalado em virtualenv Python próprio e versionado pelo script de bootstrap. O virtualenv é criado com o interpretador compatível selecionado, sem alterar o Python padrão do sistema. O OWASP ZAP usa o pacote Linux oficial em versão fixada; o arquivo baixado é validado por SHA-256 antes da extração. Docker não faz parte desse fluxo.

### 2.1. Alteração de dependências de desenvolvimento

`composer update` é operação de manutenção do conjunto de dependências e não faz parte da instalação normal de uma máquina nova. Quando `composer.json` for alterado intencionalmente, o responsável pela mudança deve atualizar e versionar o lockfile antes de considerar a alteração concluída.

O lockfile atual já inclui Rector, ECS, PHPStan e Psalm do baseline adotado. Novas instalações usam `composer install`; não repetir a atualização de transição. Para manutenção deliberada, atualizar apenas as dependências afetadas, revisar o lockfile e executar `composer validate --no-interaction` e `composer check` antes de versionar a mudança.

Semgrep e ZAP não pertencem ao `composer.lock`; suas versões ficam fixadas em `scripts/install-security-tools.sh`.

### 2.2. Banco e execução local

Variáveis locais de referência para PostgreSQL:

```bash
export HECATE_DB_HOST=127.0.0.1
export HECATE_DB_PORT=5432
export HECATE_DB_NAME=hecate
export HECATE_DB_USER=hecate
export HECATE_DB_PASSWORD='senha'
```

Depois de configurar um banco de desenvolvimento:

```bash
./yii migrate:up
APP_ENV=dev APP_DEBUG=1 composer serve
```

Esses comandos não são procedimento de implantação em produção; consultar [IMPLANTACAO.md](IMPLANTACAO.md).

## 3. Estrutura Yii3

Código novo deve respeitar a estrutura Yii3 adotada no projeto:

```text
assets/
config/
public/
src/
tests/
runtime/
yii
```

Endpoints web são actions/handlers invocáveis em `src/Web`, com dependências fornecidas por DI. Não usar convenções Yii2 como referência arquitetural para código novo.

A tecnologia do frontend não está fechada. Views nativas do Yii3 podem ser usadas quando forem adequadas, mas o backend deve permanecer apto a servir um frontend separado por API sem transportar regras de domínio para a camada de apresentação.

Organizar por responsabilidade/módulo quando isso melhorar coesão. Não criar camadas DDD vazias por convenção.

## 4. Baseline de qualidade e segurança

O baseline oficial é:

```text
Lefthook         hooks Git e orquestração local
Rector           refatoração automática homologada
ECS              coding standard e autofix
PHPStan          análise estática principal
Psalm            análise estática complementar
PHPUnit          testes
Composer Audit   vulnerabilidades conhecidas em dependências
Psalm Taint      fluxo de dados não confiável até sinks
Semgrep CE       SAST complementar e regras locais testadas
OWASP ZAP        DAST separado para aplicação em execução
```

Validação de qualidade:

```bash
composer qa
```

Validação de segurança estática/dependências:

```bash
composer security
```

Gate completo:

```bash
composer check
```

Autocorreção determinística:

```bash
composer fix
```

Comandos individuais:

```bash
composer lint
composer rector
composer stan
composer psalm
composer psalm:taint
composer test
composer security:dependencies
composer security:semgrep:validate
composer security:semgrep:test
composer security:semgrep
```

O fluxo Semgrep é deliberadamente sequencial:

```text
security/semgrep-rules/hecate.yml
        ↓
security:semgrep:validate
        ↓
security/semgrep-tests/hecate.php
        ↓
security:semgrep:test
        ↓
security:semgrep
```

A validação impede que regras com erro de parsing cheguem ao runner de testes. Regras e fixtures usam o mesmo basename e árvores paralelas, conforme o mecanismo de testes do Semgrep. Cada alteração material de regra deve manter exemplo positivo (`ruleid`) e negativo (`ok`) pertinente.

Na triagem local, `ERROR` representa finding de alto sinal e bloqueia o gate. `WARNING` representa hotspot para revisão e não é tratado automaticamente como vulnerabilidade confirmada nem bloqueia o gate. O wrapper de scan apresenta um resumo com regra, arquivo, linha, motivo, trecho afetado e separação entre erro do scanner, bloqueante e hotspot.

O DAST fica separado porque exige aplicação em execução:

```bash
HECATE_ZAP_TARGET=http://127.0.0.1:8080 composer security:dast
```

O wrapper do projeto aceita apenas `localhost` ou `127.0.0.1`. Outros ambientes exigem procedimento de segurança específico e autorização explícita; não ampliar essa restrição no script de desenvolvimento apenas por conveniência.

Também deve permanecer válido:

```bash
composer validate --no-interaction
```

`composer fix` pode alterar código com Rector e ECS. A alteração deve ser revisada antes do commit. PHPStan, Psalm, Semgrep, Composer Audit e PHPUnit não são tratados como ferramentas genéricas de autofix: falhas sem correção determinística exigem decisão técnica.

O nível do PHPStan não deve ser reduzido para aprovar uma alteração. Suppressions e baselines amplos não devem ser usados para esconder erros reais.

A primeira combinação de segurança priorizada é **Composer Audit + Psalm Taint Analysis**, porque acrescenta análise de dependências e fluxo de dados com pouca complexidade operacional. Semgrep CE complementa essa camada com padrões e regras específicas do HECATE. Regras Semgrep de fluxo usam taint mode quando fonte e sink são relevantes para diferenciar código dinâmico legítimo de entrada controlada externamente. ZAP fica fora do gate cotidiano porque sua análise depende de uma instância executável.

SonarQube é opcional e futuro; não é requisito para desenvolvimento local ou pipeline básico. Nexus permanece repositório/distribuição de artefatos e não substitui o scanner de código ou o CI.

## 5. Hooks Git

A configuração versionada está em `lefthook.yml`.

No `pre-commit`, arquivos PHP staged passam sequencialmente por Rector e ECS com aplicação de correções seguras, e arquivos alterados são novamente adicionados ao stage pelo Lefthook.

No `pre-push`, as verificações de QA continuam sendo executadas conforme `lefthook.yml`. O CI é o gate autoritativo e executa `composer check`, incluindo as verificações de segurança estática e dependências.

Instalação ou reinstalação dos hooks:

```bash
lefthook install
lefthook validate
```

Execução manual:

```bash
lefthook run pre-commit
lefthook run pre-push
```

Se já existir um hook Git no repositório local, o Lefthook pode preservá-lo com sufixo `.old` antes de instalar o hook gerenciado. Isso é esperado e deve ser revisado apenas se houver lógica local que precise ser incorporada ao fluxo versionado.

Hooks locais aumentam feedback rápido, mas não substituem CI. A validação do servidor deve repetir `composer check`.

## 6. Rector e ECS

`rector.php` define somente transformações compatíveis com a versão mínima de PHP homologada pelo projeto. Não habilitar conjuntos de migração para uma versão superior ao requisito mínimo sem atualizar previamente a matriz de compatibilidade.

`ecs.php` é a fonte do coding standard PHP e substitui a configuração direta de PHPCS/PHPCBF. O baseline inicial é PSR-12, com evolução controlada conforme necessidade real do projeto.

Não duplicar regras equivalentes em ferramentas diferentes sem motivo concreto.

## 7. Padrões PHP

São adotados, conforme aplicável:

- PSR-1, PSR-4 e PSR-12;
- PSR-7 e PSR-17 para HTTP;
- PSR-11 para container;
- PSR-15 para middleware.

Preferir tipos nativos, `declare(strict_types=1);`, dependências explícitas via DI e código com baixo acoplamento.

ActiveRecord não é modelo compartilhado da aplicação. Seu uso novo deve ser pontual, restrito à infraestrutura e justificado. Para persistência e consultas, preferir SQL explícito e parametrizado via Yii DB quando isso tornar a intenção mais clara.

Não introduzir `GenericRepository`, `BaseRepository`, `BaseService`, service locator global ou abstrações genéricas sem necessidade concreta.

Para novas leituras e escritas, seguir os [fluxos de arquitetura](ARQUITETURA.md#61-caminhos-de-escrita-e-leitura). Queries recebem escopo autorizado obrigatório e retornam projeções específicas; não aceitar um escopo ausente como acesso global. Não usar a permissão de release como substituta genérica da permissão de consulta contratual. Testar limites de acesso também nos agregados e exportações.

DTOs de tela não devem ser usados automaticamente como contratos de integração com AD ou Catálogo MB. Consultar as [fronteiras de integração](ARQUITETURA.md#8-integrações) antes de implementar sincronização, sem antecipar buses ou repositories genéricos.

## 8. Segurança de código

Toda alteração deve considerar os controles aplicáveis de autenticação, autorização, CSRF, XSS, SQL injection, SSRF, command injection, privilege escalation, path traversal, sessão, secrets e auditoria.

Regras mínimas:

- nunca concatenar entrada do usuário diretamente em SQL;
- validar identificadores dinâmicos por allowlist;
- escapar saída conforme o contexto;
- validar autorização no servidor, inclusive escopo de OM, divisão, impressora, job e cota;
- não executar shell arbitrário pelo PHP;
- operações privilegiadas devem passar pelo `hecate-agent` com ações fechadas;
- não registrar senha, token, cookie, PIN ou conteúdo de documentos;
- manter secrets fora do código e da documentação versionada.

As ferramentas não substituem revisão de arquitetura ou testes. Composer Audit cobre advisories de dependências; Psalm Taint acompanha fluxo de dados; Semgrep aplica regras locais versionadas em `security/semgrep-rules/hecate.yml`; ZAP observa a aplicação em execução. Achados devem ser investigados e corrigidos ou justificados de forma localizada, sem suppressions globais destinadas apenas a liberar pipeline.

O caso seguro de bootstrap baseado em `dirname(__DIR__)` deve permanecer coberto por fixture negativa para impedir regressão do falso positivo de LFI/path traversal já identificado.

## 9. Frontend e reutilização

Reutilizar layouts, componentes, templates, alerts, grids, badges, cards, filtros e assets compartilhados quando houver duplicação real ou regra institucional de UX.

Se o frontend permanecer em Yii3, evitar por padrão CSS e JavaScript específicos por página. Se houver frontend separado, aplicar os mesmos princípios de reutilização e separação de responsabilidades na stack escolhida.

### 9.1. Padrão institucional para telas CRUD

Telas administrativas de CRUD devem, por padrão, seguir o mesmo comportamento já adotado em **Locais** e **Impressoras**. Exceções precisam ter motivo funcional claro.

O padrão é:

- breadcrumb dinâmico com `HECATE / seção / recurso`, sem repetir título grande dentro da área de conteúdo;
- grid zebrado e compacto;
- paginação server-side de 10 registros por página;
- total e faixa de registros exibidos no rodapé;
- filtros acima do cabeçalho, com busca textual automática a partir de 3 caracteres e debounce;
- filtros booleanos/select aplicados imediatamente;
- ordenação server-side por clique no cabeçalho, alternando `ASC` e `DESC` e preservando filtros;
- estados booleanos apresentados com badge visual, não como texto cru `Sim/Não`;
- ações representadas por ícones coerentes com Bootstrap (`primary`, `secondary`, `warning`, `danger`);
- cadastro aberto em modal;
- edição utilizando o mesmo modal, populado com os dados do registro;
- exclusão lógica quando o domínio exigir preservação histórica;
- mensagens pós-operação em toast flutuante discreto, temporário e sem deslocar o layout;
- operações bem-sucedidas usando POST/Redirect/GET para evitar reenvio acidental;
- CSS e JavaScript compartilhados em assets reutilizáveis; não criar um arquivo por página quando o comportamento puder ser comum.

Queries de grid devem executar filtros, ordenação, contagem e paginação no banco. Valores de filtro continuam parametrizados; nomes de coluna/direção de ordenação só podem entrar no SQL após validação por allowlist.

Novas telas de CRUD devem partir desse padrão antes de criar variações próprias.

### 9.2. Assets institucionais e BrandingAsset

Os assets oficiais de identidade ficam em `public/branding/`, mas seus caminhos públicos são resolvidos pelo Yii por meio de `BrandingAsset`.

Regras de implementação:

- obter URLs em PHP por `AssetManager::getUrl(BrandingAsset::class, $arquivo)`;
- não hardcodear `/branding/...`, domínio, porta, subdiretório, `DocumentRoot` ou caminho dependente do SO;
- não duplicar concatenação de `baseUrl` em helpers ou variáveis específicas por arquivo;
- fornecer backgrounds ao CSS por custom properties injetadas pelo layout/view após resolução pelo Yii;
- JavaScript deve receber uma URL já resolvida quando precisar de um asset e não deve reconstruir a base pública;
- manter `MainAsset` para CSS/JavaScript da aplicação e `BrandingAsset` para identidade visual.

Ao revisar uma alteração, pesquisar referências diretas a `branding/`, nomes de backgrounds, logos e favicons para evitar regressão para paths absolutos.

## 10. PHPDoc, JSDoc, CSSDoc e comentários

Tipos e nomes devem explicar o óbvio. PHPDoc e JSDoc devem acrescentar informação que a assinatura não expressa, como array shapes, generics, efeitos colaterais, exceções relevantes, contratos de integrações, transações, concorrência e formatos de dados externos.

CSSDoc deve documentar contratos de custom properties, dependências entre layout e stylesheet, requisitos de acessibilidade/responsividade ou decisões cuja razão não seja evidente apenas pelo seletor. No branding, documentar que as custom properties de background são fornecidas pelo Yii/`BrandingAsset` e não devem receber caminhos de deployment no CSS.

Não adicionar PHPDoc, JSDoc ou CSSDoc redundante apenas para repetir nomes de classes, métodos, funções ou seletores. Comentários inline devem explicar o motivo de uma implementação, não apenas traduzir o código. Código comentado não deve permanecer no repositório.

## 11. Testes

A suíte deve evoluir além de smoke tests e cobrir, conforme risco:

- regras de domínio;
- casos de uso;
- PostgreSQL e migrations;
- autorização;
- cotas e concorrência;
- integrações externas;
- fluxo de liberação e accounting.

Prefira testar fronteiras reais quando isso aumentar confiança. Evitar mocks excessivos.

## 12. Fluxo de trabalho

Comando operacional padrão para aplicar as autocorreções determinísticas, adicionar novamente ao stage os arquivos eventualmente alterados, criar o commit e enviar a branch:

```bash
composer fix && git add . && git commit -m "feat: descrição da alteração" && git push
```

O segundo `git add .` é intencional: `composer fix` pode modificar arquivos com Rector e ECS. Assim, as correções automáticas entram no mesmo commit e não permanecem pendentes no VS Code após a operação.

Antes do commit/push, quando se deseja executar o gate integral manualmente:

```bash
git pull --rebase origin main && composer check
```

Não é necessário executar `security:semgrep:validate`, `security:semgrep:test`, `security:semgrep`, `security` e `check` em sequência: `composer check` já percorre toda a cadeia uma vez. Os comandos individuais existem para diagnóstico focado.

Fluxo correspondente:

```text
editar código
    ↓
composer fix
    ↓
composer check
    ↓
git add .
    ↓
commit
    ↓
pre-commit: Rector + ECS
    ↓
pre-push: QA local
    ↓
CI: composer check
    ↓
merge
```

Código gerado ou assistido por IA passa pelos mesmos checks do código escrito manualmente.

## 13. Critério de conclusão

Antes de considerar uma alteração tecnicamente apta:

- `composer.json` e `composer.lock` devem estar sincronizados;
- `composer qa` deve estar aprovado;
- `composer security` deve estar aprovado;
- regras Semgrep devem passar por `security:semgrep:validate` e `security:semgrep:test`;
- findings `ERROR` não resolvidos não podem permanecer no gate;
- hotspots `WARNING` devem ser revisados conforme contexto, sem serem chamados automaticamente de vulnerabilidade;
- `composer check` deve ser reproduzível localmente e no CI;
- hooks Lefthook devem estar instaláveis e validáveis a partir da configuração versionada;
- não devem existir suppressions adicionadas apenas para contornar erros reais;
- migrations e integrações alteradas devem ser testadas quando aplicável;
- controles de segurança pertinentes devem ter sido considerados;
- assets institucionais não devem conter URLs de deployment hardcoded;
- o CI deve permanecer verde.
