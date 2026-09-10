# Ambiente de Desenvolvimento — HECATE

## 1. IDE padrão

O ambiente de desenvolvimento de referência do HECATE é o **Visual Studio Code**.

O objetivo é que o IDE apresente o mais cedo possível os mesmos problemas que bloqueariam o pipeline de CI, reduzindo retrabalho e evitando divergência entre desenvolvimento local e validação de merge/release.

A experiência local deve privilegiar diagnóstico contínuo, correção na causa e prevenção de regressões.

---

## 2. Diagnósticos esperados no VS Code

O VS Code deve ser configurado para apresentar, no painel **Problems** e sempre que possível diretamente no editor, os diagnósticos relevantes do projeto.

Devem ser considerados, no mínimo:

- erros de sintaxe PHP;
- diagnósticos do PHPStan;
- violações de estilo/PHPCS;
- problemas detectados pelo SonarQube for IDE/SonarLint;
- erros de JavaScript/JSON/CSS;
- warnings de tipagem e símbolos não resolvidos do analisador PHP;
- problemas de testes quando executados por task ou integração apropriada.

O editor não substitui o pipeline. O CI continua sendo a fonte final de validação para merge e release.

---

## 3. Regra para código novo e código gerado

Todo código novo, inclusive código produzido por geração automática, assistentes de programação ou ferramentas de IA, deve ser tratado como código de produção.

Antes de considerar uma alteração concluída, o desenvolvedor deve:

1. revisar os diagnostics apresentados pelo VS Code;
2. executar lint/PHPCS/PHPStan/testes aplicáveis;
3. revisar issues do Sonar relacionadas ao código alterado;
4. corrigir a causa dos problemas encontrados;
5. confirmar que a alteração não introduziu novas violações.

Ferramentas de geração de código ou assistência por IA devem receber os padrões do HECATE como restrições de implementação e devem produzir código compatível com PSR-12, PHPStan, regras de segurança e convenções Yii2 do projeto.

---

## 4. Não silenciar ferramentas para aprovar código

Quando PHPStan, PHPCS, SonarQube, lint ou testes apontarem problema, a ação padrão é corrigir o código.

Não utilizar como atalho:

- `@phpstan-ignore-*`;
- `// phpcs:ignore` ou exclusões amplas de ruleset;
- `NOSONAR`;
- desativação de regra do Sonar;
- redução do nível do PHPStan;
- expansão artificial de baseline;
- casts ou verificações redundantes apenas para esconder diagnóstico;
- `@` para suprimir erro PHP;
- `eslint-disable` genérico quando houver JavaScript lint configurado;
- exclusão de arquivos próprios do escopo das ferramentas.

Uma supressão somente é aceitável quando houver falso positivo ou limitação técnica comprovada. Nesse caso, deve ser:

- específica;
- localizada;
- comentada com justificativa objetiva;
- revisada em code review;
- removível quando a limitação deixar de existir.

---

## 5. PHP no editor

O VS Code deve possuir suporte a análise PHP capaz de identificar, ainda durante a edição:

- classes e namespaces incorretos;
- imports ausentes ou inválidos;
- chamadas para símbolos inexistentes;
- incompatibilidades evidentes de tipos;
- assinaturas incorretas;
- problemas em PHPDoc;
- referências inconsistentes com Composer/PSR-4.

O Composer deve permanecer como fonte do autoload real. O IDE não deve depender de mapeamentos manuais que contradigam `composer.json`.

---

## 6. PHPStan no fluxo local

PHPStan deve fazer parte do ciclo normal de desenvolvimento e não apenas do CI.

O desenvolvedor deve conseguir executar facilmente a análise do projeto ou do código alterado a partir do terminal integrado ou de tasks do VS Code.

A configuração local deve utilizar exatamente o mesmo arquivo e o mesmo nível homologado usados no pipeline.

**Regra:** não manter uma configuração "mais permissiva" apenas para desenvolvimento local.

Quando houver suporte do plugin/extensão escolhida, os diagnostics do PHPStan devem aparecer no painel Problems e nas linhas correspondentes do editor.

---

## 7. PHPCS e formatação

PHPCS deve apontar violações PSR-12 durante o desenvolvimento.

A correção automática com PHPCBF pode ser usada para regras mecânicas e seguras, mas não deve ser confundida com revisão de código.

Recomenda-se formatar no save apenas quando o formatador utilizado estiver alinhado com o ruleset do projeto e não produzir alterações incompatíveis com PHPCS.

---

## 8. SonarQube no IDE

Deve ser utilizada integração do **SonarQube for IDE** com o VS Code sempre que o servidor institucional estiver disponível.

Preferir modo conectado ao SonarQube institucional para que regras, Quality Profile e issues locais sejam coerentes com o pipeline.

O desenvolvedor deve tratar issues de segurança e reliability no momento da implementação, em vez de aguardar análise posterior do CI.

Um problema apontado localmente pelo Sonar não deve ser silenciado apenas para limpar o editor. Se houver falso positivo, o tratamento deve seguir o processo de revisão definido para o projeto.

---

## 9. JavaScript, CSS e arquivos auxiliares

Para JavaScript reutilizável:

- utilizar JSDoc conforme `docs/DOCUMENTACAO-CODIGO.md`;
- manter lint configurado quando a base JavaScript justificar;
- evitar `eval`, `new Function`, handlers inline e uso inseguro de `innerHTML`;
- tratar diagnostics de JavaScript como parte da qualidade da entrega.

CSS deve permanecer centralizado em assets/componentes compartilhados. Warnings de sintaxe e problemas estruturais apontados pelo editor devem ser corrigidos antes do merge.

---

## 10. VS Code e componentes reutilizáveis

O IDE deve favorecer navegação e reutilização do código já existente antes da criação de novos componentes.

Antes de criar um novo GridView especializado, FlashAlert, badge, card, formatter, helper ou comportamento JavaScript, verificar os componentes já disponíveis em `widgets/`, `views/` compartilhadas e assets comuns.

A criação automática de código não deve gerar variantes duplicadas de componentes já padronizados.

---

## 11. Fluxo recomendado de desenvolvimento

```text
editar código
    ↓
VS Code Problems
    ├─ analisador PHP
    ├─ PHPStan
    ├─ PHPCS/lint
    └─ SonarQube for IDE
    ↓
corrigir a causa
    ↓
executar checks locais
    ↓
testes
    ↓
commit / push
    ↓
CI + SonarQube Quality Gate
    ↓
merge
```

O objetivo é que o pipeline confirme a qualidade já observada localmente, e não seja a primeira ferramenta a descobrir problemas básicos.

---

## 12. Configuração versionada

Configurações do VS Code que representem decisões do projeto podem ser versionadas em `.vscode/`, especialmente:

- extensões recomendadas;
- opções de lint/análise;
- exclusões coerentes com o projeto;
- tasks para verificações locais;
- comportamento de formatação compartilhado.

Configurações pessoais de tema, fonte, atalhos e preferências sem impacto no produto não devem ser versionadas.

A configuração versionada não pode conter caminhos absolutos de uma estação, tokens, credenciais ou qualquer segredo.

---

## 13. Critério de conclusão local

Antes de abrir ou atualizar um PR, espera-se que:

- [ ] não existam erros de sintaxe no código alterado;
- [ ] não existam novas violações PHPCS;
- [ ] não existam novas violações PHPStan;
- [ ] issues Sonar relevantes tenham sido corrigidas ou justificadas formalmente;
- [ ] testes aplicáveis estejam aprovados;
- [ ] não tenham sido introduzidas suppressions para contornar as ferramentas;
- [ ] PHPDoc/JSDoc esteja adequado aos contratos novos ou alterados;
- [ ] componentes reutilizáveis existentes tenham sido considerados antes de criar novos arquivos/classes;
- [ ] código gerado ou assistido por IA tenha passado pelos mesmos checks do código escrito manualmente.
