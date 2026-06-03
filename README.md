# Meta Tag Optimizer

A PHP-based tool that crawls any URL server-side, extracts the full page content and generates optimized meta tags – either rule-based (no API key required) or powered by your choice of AI provider.

**Live demo:** [ai-ready-check.de](https://ai-ready-check.de) · [soerenmeier.de/webtools/metatag-optimizer/](https://soerenmeier.de/webtools/metatag-optimizer/)  
**Documentation:** [soerenmeier.de/docs/metatag-optimizer/](https://soerenmeier.de/docs/metatag-optimizer/)

---

## Features

- **Server-side URL crawler** – PHP cURL + DOMXPath, no headless browser required
- **7 AI providers** – Anthropic Claude, OpenAI GPT-4o, Google Gemini, Perplexity, xAI Grok, custom OpenAI-compatible endpoint, or rule-based (free, no key)
- **6 output types** – Title, Description, Keywords, Open Graph, Twitter Card, JSON-LD, Robots meta
- **Two input modes** – crawl a URL automatically or enter content manually
- **Rule-based fallback** – if the AI response is unparseable, falls back to rule-based generation
- **Rate limiting** – file-based per IP, configurable window and max requests
- **No framework, no database** – single PHP directory, Composer for autoloading only

---

## Requirements

- PHP 8.1+
- PHP extensions: `curl`, `dom`, `mbstring`
- Composer (for autoloading)

---

## Installation

```bash
git clone https://github.com/soeren777/metatag-optimizer.git
cd metatag-optimizer
composer install
cp config.example.php config.php
```

Edit `config.php` and set your AI provider and API key (or leave as `none` for rule-based generation).

Make the `tmp/` directory writable:
```bash
chmod 755 tmp/
```

---

## Configuration

All settings are in `config.php`:

```php
'ai_provider' => 'none',       // none | anthropic | openai | google | perplexity | grok | ownai
'ai_api_key'  => '',           // your API key
'ai_model'    => '',           // leave empty for provider default
'ai_api_url'  => '',           // only for 'ownai': https://your-endpoint.com/v1/chat/completions
```

### Provider defaults

| Provider | Default model |
|---|---|
| `anthropic` | claude-sonnet-4-5 |
| `openai` | gpt-4o |
| `google` | gemini-1.5-flash |
| `perplexity` | llama-3.1-sonar-large-128k-online |
| `grok` | grok-beta |
| `ownai` | configurable via `ai_model` |

---

## API

The tool exposes a single POST endpoint at `api.php`.

### Crawl a URL

```json
POST api.php
{ "action": "crawl", "url": "https://example.com/page" }
```

Returns extracted page data (H1, H2s, paragraphs, existing meta tags, JSON-LD, body text).

### Generate meta tags

```json
POST api.php
{
  "action": "generate",
  "page": { ... },
  "overrides": { "title": "...", "keywords": "..." }
}
```

Returns all generated meta tags.

### Response format

```json
{ "ok": true, "data": { ... } }
{ "ok": false, "error": "..." }
```

---

## File structure

```
metatag-optimizer/
├── api.php                  # Request handler, rate limiter, CORS
├── config.php               # Provider, API key, model, crawler settings (not in repo)
├── config.example.php       # Config template
├── Crawler.php              # URL fetch + DOM content extraction
├── Generator.php            # Rule-based + AI tag generation
├── providers/
│   ├── ProviderInterface.php
│   ├── ProviderFactory.php
│   ├── BaseHttpProvider.php
│   ├── NoneProvider.php
│   ├── AnthropicProvider.php
│   ├── OpenAiProvider.php
│   ├── GoogleProvider.php
│   └── OtherProviders.php   # Perplexity, Grok, OwnAI
├── index.html               # Frontend (Vanilla JS, no framework)
└── tmp/                     # Rate limit files (needs write permission)
```

---

## CORS

By default `api.php` restricts CORS to the deploying domain. Edit the header in `api.php`:

```php
header('Access-Control-Allow-Origin: https://your-domain.com');
```

---

## Known limitations (v1.0)

- JS-rendered pages are not supported (no headless browser)
- No authentication support for login-protected pages
- Rule-based mode does not generate keywords (requires AI provider)
- Rate limiting is file-based only, no distributed cache

---

## License

MIT License – see [LICENSE](LICENSE)

---

Built by [Sören Meier](https://soerenmeier.de)
