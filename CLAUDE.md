# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**청년노동자인권센터** (Young Labor Workers' Rights Center) - 반도체산업 청년노동자 권리 옹호를 위한 PHP 웹 애플리케이션. 단체 홍보 멀티페이지, 청소년노동안전동아리 신청, 관리자 패널을 포함.

- **Stack**: Vanilla PHP 8+, MySQL (MariaDB), HTML5, CSS3, vanilla JavaScript, Apache (XAMPP)
- **No framework, no package manager, no build tools**
- **Database**: MySQL — admin 패널, 동아리 신청, 문의 관리, 방문자 통계에 사용
- **Production URL**: https://younglabor.kr (카페24 서버)
- **Local URL**: http://localhost:8080/younglabor

## Running Locally

```bash
# Start Apache + MySQL via XAMPP
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start

# Main site
open http://localhost:8080/younglabor/

# Admin panel
open http://localhost:8080/younglabor/admin/

# Committee application
open http://localhost:8080/younglabor/committee/
```

## Testing

No automated test suite exists. Testing is manual via browser:

- `/test-config.php` — Main config loading test
- `/admin/setup.php` — 최초 관리자 계정 생성 (admin_user 테이블이 비어있을 때만 접근 가능)
- `/admin/` — Admin login and dashboard
- `/committee/` — Committee application form

**Apache logs**: `/Applications/XAMPP/xamppfiles/logs/`

## Architecture

```text
younglabor/
├── config.php              # Root config: multi-env .env loader, helpers, theme, site globals
├── .htaccess               # URL rewrite: extensionless URLs (about → about.php)
├── index.php               # Main landing page (문의 폼 포함)
├── about.php               # 단체소개
├── activities.php          # 사업소개
├── news.php                # 소식
├── .env / .env.local / .env.production   # Secrets/theme/site — NEVER commit
├── api/
│   ├── contact.php         # Contact form POST endpoint
│   ├── committee.php       # Committee application API
│   └── deploy.php          # GitHub webhook auto-deploy (HMAC verified)
├── includes/
│   ├── header.php          # Shared page header + nav (set $currentPage before include)
│   ├── footer.php          # Shared page footer
│   ├── Database.php        # MySQL singleton connection
│   ├── Mailer.php          # Pure PHP SMTP mailer (no PHPMailer dependency)
│   └── PageTracker.php     # Page visit tracker
├── committee/
│   └── index.php           # 청소년노동안전동아리 application form
├── admin/                  # Admin panel (session auth)
│   ├── config.php          # Admin config: session settings, constants
│   ├── auth.php            # Auth middleware (session check, timeout, CSRF)
│   ├── login.php / logout.php
│   ├── setup.php           # 최초 관리자 계정 생성
│   ├── dashboard.php       # Overview dashboard
│   ├── committee.php       # Committee application management
│   ├── contacts.php        # Contact inquiry management
│   ├── statistics.php      # Visitor statistics
│   ├── helpers.php         # Admin utility functions
│   └── api/                # committee-action.php, contact-action.php, stats-data.php
├── data/file/notices/      # Uploaded notice attachments
├── assets/                 # css/, images/, js/
├── admin-panel-migration.sql   # Admin panel DB migration
└── DEPLOYMENT.md           # Webhook deployment guide
```

### Shared Layout Pattern (멀티페이지)

Top-level content pages (`index.php`, `about.php`, `activities.php`, `news.php`) all follow the same skeleton:

```php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/PageTracker.php';
$currentPage = 'about';                            // drives nav active state
require_once __DIR__ . '/includes/header.php';
// ... page content ...
require_once __DIR__ . '/includes/footer.php';
```

Nav (in `includes/header.php`): 단체소개(`about`), 사업소개(`activities`), 소식(`news`), 동아리 신청(`committee`). Fonts: Pretendard via CDN.

### Configuration Chain

Root `config.php` loads env in override order: `.env` (공통) → then `.env.local` **or** `.env.production` (환경별, hostname으로 자동 감지). 환경별 파일이 공통 값을 오버라이드.

Provides global helpers: `env()`, `url()`, `isLocal()`, `isProduction()`, `detectEnvironment()`, `getThemeCSSVariables()`. Globals `$theme`, `$site`, `$environment`, `$baseUrl` are used throughout. `url()` builds paths relative to the detected base URL — always use it for internal links/assets.

### URL Rewriting (`.htaccess`)

- Extensionless URLs: `/about` → `about.php` (existing-file fallback)

### API Response Convention

```json
{"success": true|false, "message": "...", "data": {...}}
```

## Key Conventions

- **Language**: Code comments and documentation are in Korean; class/function names are in English
- **Naming**: Classes=PascalCase, functions=camelCase, constants=UPPER_SNAKE_CASE, CSS classes=kebab-case
- **Shared layout**: New content pages must set `$currentPage` and include `header.php`/`footer.php` rather than emitting their own `<html>` shell
- **Environment detection**: Auto-detects local vs production by hostname; override via `APP_ENV` in `.env`
- **Theme**: Colors come from `.env` variables, injected as CSS custom properties (`--color-primary`, etc.)
- **No external PHP dependencies**: Everything is implemented with native PHP (cURL for HTTP, raw sockets for SMTP)

## Deployment

GitHub Actions (`.github/workflows/deploy.yml`) → `main` push → HMAC-signed webhook → `api/deploy.php` on the 카페24 server downloads the repo ZIP and overwrites files. See `DEPLOYMENT.md` for the full flow, excluded files, and troubleshooting.

- Secret: `DEPLOY_SECRET` must match between GitHub Secrets and server `.env`
- Deploy excludes `.env*`, `.git`, `.github`, `CLAUDE.md`, `.claude`, `deploy.log`
- Status: `gh run list --limit 5`; re-run: `gh run rerun <run-id>`

## External APIs

| API | Client Class | Purpose |
|-----|-------------|---------|
| Gmail SMTP | `includes/Mailer.php` | Contact form email delivery |

## Important Notes

- `.env`, `.env.local`, `.env.production` contain all secrets and are in `.gitignore` — never commit them
- `.gitignore` also excludes `test-*.php` and `*-test.php` files
- PHP requires `curl` and `openssl` extensions enabled
- Admin panel uses session-based auth with brute-force protection and CSRF tokens

## Skill routing

When the user's request matches an available skill, ALWAYS invoke it using the Skill
tool as your FIRST action. Do NOT answer directly, do NOT use other tools first.
The skill has specialized workflows that produce better results than ad-hoc answers.

Key routing rules:
- Product ideas, "is this worth building", brainstorming → invoke office-hours
- Bugs, errors, "why is this broken", 500 errors → invoke investigate
- Ship, deploy, push, create PR → invoke ship
- QA, test the site, find bugs → invoke qa
- Code review, check my diff → invoke review
- Update docs after shipping → invoke document-release
- Weekly retro → invoke retro
- Design system, brand → invoke design-consultation
- Visual audit, design polish → invoke design-review
- Architecture review → invoke plan-eng-review
- Save progress, checkpoint, resume → invoke checkpoint
- Code quality, health check → invoke health
