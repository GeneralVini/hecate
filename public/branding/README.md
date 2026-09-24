# Assets de identidade visual HECATE

Esta pasta contém somente os assets de produção usados pela interface e pela documentação publicada do HECATE.

Os masters de maior qualidade devem ser preservados em `resources/branding/originals/` e nunca servidos diretamente pelo navegador.

## Arquivos atuais

- `logo-horizontal.png`
- `logo-vertical.png`
- `symbol.png`
- `avatar.png`
- `favicon-16x16.png`
- `favicon-32x32.png`
- `favicon-48x48.png`
- `favicon-180x180.png`
- `favicon-192x192.png`
- `favicon-512x512.png`
- `login-background.jpg`
- `dashboard-background.jpg`
- `hecate-hero.jpg`

## Conceito vigente

A identidade visual atual não apresenta o HECATE como inventário de ferramentas. A leitura deve evidenciar primeiro a **camada de governança**, composta por políticas, papéis, cotas, aprovações, auditoria e indicadores. Os componentes técnicos permanecem visualmente reconhecíveis como mecanismos de implementação.

Os três portais representam os domínios:

- **Identidade** — Keycloak e Samba AD;
- **Controle** — SavaPage e CUPS;
- **Dados** — PostgreSQL e Podman.

A composição institucional usa uma representação original de Hécate como elemento simbólico de passagem controlada, decisão e limiar. A personagem visual do HECATE deve ser tratada como criação própria do projeto, sem reprodução intencional da aparência de pessoa real. O cenário do Rio de Janeiro, com Cristo Redentor e Pão de Açúcar, reforça a identidade visual da solução sem substituir seu caráter técnico e institucional.

## Originais e derivados de produção

A regra é separar fonte editorial de arquivo publicado:

```text
resources/branding/originals/  -> masters preservados
public/branding/               -> derivados otimizados para produção
```

Não aplicar `pngquant`, JPEG recompression ou outro processamento destrutivo diretamente sobre os masters.

Para inicializar os masters com os arquivos atualmente publicados, sem sobrescrever originais já existentes:

```bash
make branding-bootstrap
```

Depois, para gerar os derivados de produção:

```bash
make branding-optimize
```

O pipeline usa:

- ImageMagick para resize, remoção de metadados e JPEG;
- `pngquant` para PNG quando disponível;
- JPEG com qualidade padrão 82 para as artes pictóricas;
- PNG quantizado entre 80 e 95 para avatar, logos, símbolo e favicons.

As qualidades podem ser ajustadas explicitamente, sem alterar o script:

```bash
HECATE_BRANDING_JPEG_QUALITY=84 \
HECATE_BRANDING_PNG_MIN_QUALITY=82 \
HECATE_BRANDING_PNG_MAX_QUALITY=96 \
make branding-optimize
```

O objetivo não é atingir um número rígido, mas evitar servir masters de 2–4 MB quando um derivado visualmente equivalente pode ser entregue com peso muito menor.

## Resolução de URLs

Os arquivos permanecem fisicamente em `public/branding/`, mas a aplicação não deve referenciá-los com caminhos absolutos como `/branding/...`.

O consumo em runtime é centralizado em `BrandingAsset`, com URLs obtidas pelo `AssetManager` do Yii. Essa regra evita dependência de domínio, subdiretório de implantação, `DocumentRoot`, reverse proxy, multisite ou sistema operacional.

Regras:

- PHP deve obter a URL com `AssetManager::getUrl(BrandingAsset::class, $arquivo)`;
- CSS deve receber backgrounds por custom properties injetadas pela view/layout após resolução pelo Yii;
- JavaScript não deve construir base URL de branding manualmente;
- `MainAsset` depende de `BrandingAsset`, mantendo identidade visual separada dos CSS/JS da aplicação;
- novos assets institucionais devem seguir o mesmo mecanismo, sem criar concatenação paralela de caminhos.

## Uso recomendado

### `logo-horizontal.png`

Topbar, login, README, apresentações e cabeçalhos institucionais.

### `logo-vertical.png`

Capas, splash, materiais verticais e páginas institucionais.

### `symbol.png`

Sidebar recolhida, avatar do produto, loader e componentes compactos.

### `avatar.png`

Retrato institucional da personagem, usado principalmente no slide 2 do manual e como referência visual canônica para novas composições.

### Favicons

Usar o tamanho adequado no `<head>`, atalhos e futuro manifest/PWA.

### `login-background.jpg`

Background da tela de autenticação. O formulário permanece HTML/Yii3, nunca desenhado na própria imagem. A composição deve preservar área de escape suficiente para usuário, senha e ação de entrada.

O rodapé institucional é renderizado em HTML/CSS para manter legibilidade e adaptação responsiva:

- estrela dourada;
- `PLATAFORMA INSTITUCIONAL DE GOVERNANÇA E CONTROLE DE IMPRESSÃO`;
- `CTIM - YYYY`;
- `CC(EN) HONORATO` discreto à direita.

### `dashboard-background.jpg`

Aplicar com overlay escuro e baixa interferência visual. O background deve sustentar a identidade sem prejudicar cards, tabelas, alertas ou métricas.

### `hecate-hero.jpg`

Imagem institucional de referência para README, apresentações, documentação visual e onboarding. É a peça que melhor expressa a relação entre governança e os três domínios técnicos.

## Direção visual

A identidade usa:

- azul-marinho profundo;
- dourado;
- branco e tons frios de apoio;
- Hécate;
- chave e tocha;
- lua tríplice;
- caminhos e portais;
- Rio de Janeiro como cenário institucional;
- governança acima dos componentes técnicos.

Evitar:

- tratar o HECATE como mero inventário de ferramentas;
- `APP-PRINT`;
- excesso de fantasia sem relação com a solução;
- excesso de texto nas imagens operacionais;
- formulários de login incorporados ao background;
- efeitos visuais que reduzam a legibilidade da interface;
- usar como referência visual a aparência de pessoa real sem autorização explícita;
- URLs de branding hardcoded em PHP, CSS ou JavaScript.

Consulte `docs/IDENTIDADE-VISUAL.md` para as regras completas de interface e branding.
