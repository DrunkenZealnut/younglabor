# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**청년노동자인권센터** (Young Labor Workers' Rights Center) - A PHP web application advocating for young semiconductor industry workers' rights, with an MSDS (Material Safety Data Sheet) chemical safety sub-application.

- **Stack**: Vanilla PHP 8+, HTML5, CSS3, vanilla JavaScript, Apache (XAMPP)
- **No framework, no package manager, no build tools, no database**
- **Production URL**: https://younglabor.kr
- **Local URL**: http://localhost:8080/younglabor

## Running Locally

```bash
# Start Apache via XAMPP
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start

# Main site
open http://localhost:8080/younglabor/

# MSDS sub-app
open http://localhost:8080/younglabor/msds/
```

## Testing

No automated test suite exists. Testing is manual via browser endpoints:

- `/msds/api-check.php` — API configuration status dashboard
- `/msds/api/health.php` — Health check endpoint (JSON)
- `/test-config.php` — Main config loading test
- `/msds/test-msds-api.php` — KOSHA API connectivity test
- `/msds/test-api-key.php` — Vision API key validation
- `/msds/test-post-request.html` — Manual POST form for API testing

```bash
# Quick health check
curl http://localhost:8080/younglabor/msds/api/health.php
```

**Logs**: `/msds/logs/api_YYYY-MM-DD.log`
**Apache logs**: `/Applications/XAMPP/xamppfiles/logs/`

## Architecture

```
younglabor/
├── config.php              # Root config: .env loader, env helpers, theme, site globals
├── index.php               # Main landing page (single-page with hash navigation)
├── .env                    # All secrets, theme colors, site info (NEVER commit)
├── api/
│   └── contact.php         # Contact form POST endpoint
├── includes/
│   └── Mailer.php          # Pure PHP SMTP mailer (no PHPMailer dependency)
└── msds/                   # MSDS sub-application (chemical safety search)
    ├── config.php          # MSDS config: API constants, Vision API setup (loads root config)
    ├── .htaccess           # URL rewriting: hides .php extensions, UTF-8
    ├── index.php           # Chemical search interface
    ├── detail.php          # 16-section MSDS detail view
    ├── MsdsApiClient.php   # KOSHA MSDS API client (XML-based)
    ├── ClaudeVisionClient.php  # Claude Vision API client
    ├── OpenAIVisionClient.php  # OpenAI Vision API client (gpt-4o-mini)
    └── api/
        ├── analyze.php     # Image analysis endpoint (MSDS label → structured data)
        └── health.php      # API health check
```

### Configuration Chain

`msds/config.php` → loads → root `config.php` → loads → `.env`

Root `config.php` provides global helpers: `env()`, `url()`, `isLocal()`, `isProduction()`, `detectEnvironment()`, `getThemeCSSVariables()`. Globals `$theme`, `$site`, `$environment`, `$baseUrl` are used throughout.

### MSDS Sub-App Flow

1. **Search**: User searches by chemical name/CAS/UN/KE/EN number → `MsdsApiClient` queries KOSHA XML API
2. **Image Analysis**: User uploads MSDS label photo → `api/analyze.php` sends to Vision API (Claude or OpenAI, controlled by `VISION_API_PROVIDER` env var) → extracts chemical info → auto-searches KOSHA
3. **Detail View**: `detail.php` shows 16 MSDS sections with print support

### API Response Convention

```json
{"success": true|false, "message": "...", "data": {...}}
```

## Key Conventions

- **Language**: Code comments and documentation are in Korean; class/function names are in English
- **Naming**: Classes=PascalCase, functions=camelCase, constants=UPPER_SNAKE_CASE, CSS classes=kebab-case
- **Environment detection**: Auto-detects local vs production by hostname; override via `APP_ENV` in `.env`
- **URL rewriting**: MSDS sub-app uses `.htaccess` to hide `.php` extensions; `api/` folder is excluded from rewrites
- **Theme**: Colors come from `.env` variables, injected as CSS custom properties (`--color-primary`, etc.)
- **No external PHP dependencies**: Everything is implemented with native PHP (cURL for HTTP, raw sockets for SMTP)

## External APIs

| API | Client Class | Purpose |
|-----|-------------|---------|
| KOSHA MSDS | `msds/MsdsApiClient.php` | Chemical safety data (XML responses) |
| Claude Vision | `msds/ClaudeVisionClient.php` | MSDS label image analysis |
| OpenAI Vision | `msds/OpenAIVisionClient.php` | Alternative image analysis (gpt-4o-mini) |
| Gmail SMTP | `includes/Mailer.php` | Contact form email delivery |

## Important Notes

- `.env` contains all API keys and is in `.gitignore` — never commit it
- `.gitignore` also excludes `test-*.php` and `*-test.php` files
- The `consult/` directory is a placeholder (empty)
- PHP requires `curl` and `openssl` extensions enabled
