# Desenvolvimento HECATE

## 1. Finalidade

Este documento consolida o ambiente de desenvolvimento, o baseline de qualidade e as convenções de documentação de código do HECATE. Ele é a referência prática para desenvolvimento local, hooks Git, CI e revisão técnica.

## 2. Ambiente local

A aplicação web segue o template oficial `yiisoft/app`, com Composer, PostgreSQL e ferramentas de QA versionadas no repositório. `composer.json` declara PHP 8.2–8.5; o CI configura PHP 8.5 e Rector usa regras PHP 8.2. Isso não comprova homologação de toda a faixa: a matriz de versões permanece pendente na EAP.

O Lefthook é um executável externo ao Composer e deve estar disponível no `PATH` antes do primeiro `make setup`. Em Ubuntu/Kubuntu/Debian:

```bash
curl -1sLf 'https://dl.cloudsmith.io/public/evilmartians/lefthook/setup.deb.sh' | sudo -E bash
sudo apt install lefthook
lefthook version
```

Primeira execução do projeto:

```bash
git clone https://github.com/GeneralVini/hecate.git
cd hecate
git switch yii3
make setup
```

O `composer.lock` é obrigatório. O bootstrap usa `composer install` e não deve executar `composer update`, `composer require` ou instalar pacotes do sistema de forma implícita.

O `make setup` valida PHP, Composer, Git e Lefthook; instala as dependências a partir do lockfile; valida o Composer; confirma ECS, Rector, PHPStan, Psalm e PHPUnit; executa `lefthook install` e `lefthook validate`; e roda o baseline de QA. Se o Lefthook estiver ausente, o bootstrap mostra os comandos de instalação para Ubuntu/Kubuntu/Debian e encerra sem alterar o sistema.

### 2.1. Alteração de dependências de desenvolvimento

`composer update` é operação de manutenção do conjunto de dependências e não faz parte da instalação normal de uma máquina nova. Quando `composer.json` for alterado intencionalmente, o responsável pela mudança deve atualizar e versionar o lockfile antes de considerar a alteração concluída.

O lockfile atual já inclui Rector, ECS e PHPStan do baseline adotado. Novas instalações usam `composer install`; não repetir a atualização de transição. Para manutenção deliberada, atualizar apenas as dependências afetadas, revisar o lockfile e executar `composer validate --no-interaction` e `composer qa` antes de versionar a mudança.

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

## 4. Baseline de qualidade

O baseline oficial é:

```text
Lefthook     hooks Git e orquestração local
Rector       refatoração automática homologada
ECS          coding standard e autofix
PHPStan      análise estática principal
Psalm        análise estática complementar
PHPUnit      testes
```

Validação integral:

```bash
composer qa
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
composer test
```

Também deve permanecer válido:

```bash
composer validate --no-interaction
```

`composer fix` pode alterar código com Rector e ECS. A alteração deve ser revisada antes do commit. PHPStan, Psalm e PHPUnit não são tratados como ferramentas genéricas de autofix: falhas sem correção determinística exigem decisão técnica.

O nível do PHPStan não deve ser reduzido para aprovar uma alteração. Suppressions e baselines amplos não devem ser usados para esconder erros reais.

SonarQube é opcional e futuro; não é requisito para desenvolvimento local ou pipeline básico.

## 5. Hooks Git

A configuração versionada está em `lefthook.yml`.

No `pre-commit`, arquivos PHP staged passam sequencialmente por Rector e ECS com aplicação de correções seguras, e arquivos alterados são novamente adicionados ao stage pelo Lefthook.

No `pre-push`, as verificações são executadas em paralelo:

```text
ECS
Rector dry-run
PHPStan
Psalm
PHPUnit
```

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

Hooks locais aumentam feedback rápido, mas não substituem CI. A validação do servidor deve repetir `composer qa`.

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

Contratos de apresentação e federação evoluem separadamente; DTO de tela não é automaticamente payload do Master. Consultar o [contrato federado](ARQUITETURA.md#132-contrato-de-sincronização) antes de implementar sincronização, sem antecipar buses ou repositories genéricos.

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

## 10. PHPDoc, JSDoc e comentários

Tipos e nomes devem explicar o óbvio. PHPDoc e JSDoc devem acrescentar informação que a assinatura não expressa, como array shapes, generics, efeitos colaterais, exceções relevantes, contratos de integrações, transações, concorrência e formatos de dados externos.

Não adicionar PHPDoc redundante a toda classe ou método. Comentários inline devem explicar o motivo de uma implementação, não apenas traduzir o código. Código comentado não deve permanecer no repositório.

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

Fluxo correspondente:

```text
editar código
    ↓
composer fix
    ↓
git add .
    ↓
commit
    ↓
pre-commit: Rector + ECS
    ↓
pre-push: QA completo
    ↓
CI: composer qa
    ↓
merge
```

Código gerado ou assistido por IA passa pelos mesmos checks do código escrito manualmente.

## 13. Critério de conclusão

Antes de considerar uma alteração tecnicamente apta:

- `composer.json` e `composer.lock` devem estar sincronizados;
- `composer qa` deve estar aprovado;
- hooks Lefthook devem estar instaláveis e validáveis a partir da configuração versionada;
- não devem existir suppressions adicionadas apenas para contornar erros reais;
- migrations e integrações alteradas devem ser testadas quando aplicável;
- controles de segurança pertinentes devem ter sido considerados;
- o CI deve permanecer verde.
