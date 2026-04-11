# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**청년노동자인권센터** (Young Labor Workers' Rights Center) - 반도체산업 청년노동자 권리 옹호를 위한 PHP 웹 애플리케이션. 단체 홍보, 청소년노동안전동아리 신청, 관리자 패널을 포함.

- **Stack**: Vanilla PHP 8+, MySQL (MariaDB), HTML5, CSS3, vanilla JavaScript, Apache (XAMPP)
- **No framework, no package manager, no build tools**
- **Database**: MySQL — admin 패널, 동아리 신청, 문의 관리, 방문자 통계에 사용
- **Production URL**: https://younglabor.kr
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
- `/admin/` — Admin login and dashboard
- `/committee/` — Committee application form

**Apache logs**: `/Applications/XAMPP/xamppfiles/logs/`

## Architecture

```
younglabor/
├── config.php              # Root config: .env loader, env helpers, theme, site globals
├── index.php               # Main landing page
├── .env                    # All secrets, theme colors, site info (NEVER commit)
├── api/
│   ├── contact.php         # Contact form POST endpoint
│   ├── deploy.php          # GitHub webhook auto-deploy
│   └── committee.php       # Committee application API
├── includes/
│   ├── Database.php        # MySQL singleton connection
│   ├── Mailer.php          # Pure PHP SMTP mailer (no PHPMailer dependency)
│   └── PageTracker.php     # Page visit tracker
├── committee/
│   └── index.php           # 청소년노동안전동아리 application form
├── admin/                  # Admin panel (dashboard, committee/contact management, stats)
│   ├── config.php          # Admin config: session settings, constants
│   ├── auth.php            # Auth middleware (session check, timeout, CSRF)
│   ├── login.php           # Login page with brute-force protection
│   ├── dashboard.php       # Overview dashboard
│   ├── committee.php       # Committee application management
│   ├── contacts.php        # Contact inquiry management
│   ├── statistics.php      # Visitor statistics
│   ├── helpers.php         # Admin utility functions
│   └── api/                # Admin API endpoints
└── assets/                 # Static assets (images, etc.)
```

### Configuration Chain

Root `config.php` → loads → `.env` → detects environment (local/production)

Root `config.php` provides global helpers: `env()`, `url()`, `isLocal()`, `isProduction()`, `detectEnvironment()`, `getThemeCSSVariables()`. Globals `$theme`, `$site`, `$environment`, `$baseUrl` are used throughout.

### API Response Convention

```json
{"success": true|false, "message": "...", "data": {...}}
```

## Key Conventions

- **Language**: Code comments and documentation are in Korean; class/function names are in English
- **Naming**: Classes=PascalCase, functions=camelCase, constants=UPPER_SNAKE_CASE, CSS classes=kebab-case
- **Environment detection**: Auto-detects local vs production by hostname; override via `APP_ENV` in `.env`
- **Theme**: Colors come from `.env` variables, injected as CSS custom properties (`--color-primary`, etc.)
- **No external PHP dependencies**: Everything is implemented with native PHP (cURL for HTTP, raw sockets for SMTP)
- **Deployment**: GitHub Actions webhook → `api/deploy.php` downloads ZIP and copies files

## External APIs

| API | Client Class | Purpose |
|-----|-------------|---------|
| Gmail SMTP | `includes/Mailer.php` | Contact form email delivery |

## Important Notes

- `.env` contains all API keys and is in `.gitignore` — never commit it
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
