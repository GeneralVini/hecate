# Masters de branding

Esta pasta preserva os arquivos fonte de maior qualidade usados para gerar os assets publicados em `public/branding/`.

## Regra

- Não usar arquivos desta pasta diretamente na interface web.
- Não executar compressão destrutiva sobre os masters.
- `public/branding/` contém somente derivados preparados para produção.
- Ao receber uma nova arte, substituir primeiro o master correspondente nesta pasta e depois executar `make branding-optimize`.

## Nomes esperados

O pipeline aceita PNG, JPEG ou WebP como fonte para as três artes principais, mantendo o nome-base:

- `hecate-hero.*`
- `login-background.*`
- `dashboard-background.*`

Os demais masters usam os nomes atuais:

- `avatar.png`
- `logo-horizontal.png`
- `logo-vertical.png`
- `symbol.png`
- `favicon-16x16.png`
- `favicon-32x32.png`
- `favicon-48x48.png`
- `favicon-180x180.png`
- `favicon-192x192.png`
- `favicon-512x512.png`

## Inicialização

Para preservar como baseline os assets que já estão em `public/branding/`:

```bash
make branding-bootstrap
```

Esse bootstrap nunca sobrescreve um master já existente. Quando houver um original de maior qualidade, substitua manualmente apenas o arquivo correspondente em `resources/branding/originals/`.

Depois gere os derivados de produção:

```bash
make branding-optimize
```
