# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### XAMPP Environment
The project runs on XAMPP (Apache, MySQL, PHP):
```bash
# Start/stop XAMPP MySQL and Apache services
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start
sudo /Applications/XAMPP/xamppfiles/bin/apachectl stop
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server stop

# Check service status
lsof -i :80    # Apache
lsof -i :3306  # MySQL
```

### Tailwind CSS Build Commands
```bash
# Build Tailwind CSS for development
npm run build

# Build with watch mode for development
npm run build-watch

# Build optimized for production
npm run build-production
```

### Server Setup
```bash
# Start local PHP server (alternative to XAMPP Apache)
./start_server.sh  # Runs on port 8013

# Quick XAMPP startup
./start_xampp.sh
```

### Database Operations
```bash
# Access MySQL command line
/Applications/XAMPP/xamppfiles/bin/mysql -u root -p

# Import database schema
mysql -u root -p kcsvictory < project_default_schema.sql
```

## System Architecture

### Project Structure
This is a PHP-based content management system for a youth labor rights organization website ("청년노동자인권센터"). The codebase follows a hybrid architecture:

1. **Legacy PHP Structure**: Traditional PHP with includes, config files, and direct database queries
2. **Modern Components**: Environment-based configuration, Tailwind CSS optimization, admin MVC framework

### Key Architectural Components

#### Configuration System
- **Environment Variables**: Uses `.env` files for configuration (loaded by `bootstrap/app.php`)
- **Config Hierarchy**: 
  - `config/database.php` - Database connections and table prefixes
  - `config/app.php` - Application settings
  - `includes/config_loader.php` - Environment variable loading
- **Theme System**: Natural Green theme with customizable colors via CSS variables

#### Database Architecture
- **Primary Database**: `kcsvictory` (MariaDB/MySQL)
- **Table Structure**: Mixed legacy board system with modern unified tables
  - Legacy tables: Individual tables per board type (`notices`, `press`, `gallery`, etc.)
  - Modern tables: Unified `posts` table with `board_type` field, centralized `comments` table
- **Table Prefixes**: Configurable via `DB_PREFIX` environment variable

#### Admin System
- **Location**: `/admin/` directory contains complete MVC-based admin system
- **MVC Framework**: Custom implementation in `/admin/mvc/`
  - Controllers in `/admin/mvc/controllers/`
  - Models in `/admin/mvc/models/`
  - Views in `/admin/mvc/views/`
- **Features**: User management, content management, theme settings, popup management
- **Installation**: Automated setup wizard with database schema creation

#### Frontend Architecture
- **Theme Engine**: Located in `/theme/natural-green/`
- **CSS Framework**: Tailwind CSS with custom configuration and safelist
- **Asset Management**: Optimized CSS generation with Tailwind build process
- **Responsive Design**: Mobile-first approach with breakpoint-specific styling

### Database Schema Highlights

#### Core Content Tables
- `posts`: Unified post table with `board_type` field for different content types
- `comments`: Centralized comment system with hierarchical threading
- `boards`: Board configuration and metadata
- `menu`: Hierarchical navigation menu system

#### Admin & Settings Tables
- `admin_user`: Admin authentication and permissions
- `site_settings`: Key-value configuration storage
- `theme_presets`: Theme color schemes
- `popup_settings`: Site-wide popup management

#### Legacy Integration
- Individual board tables (`notices`, `press`, `gallery`, etc.) exist for backward compatibility
- Board templates system in `/board_templates/` provides abstraction layer
- Migration system gradually moves from legacy to unified structure

### Request Flow
1. **Entry Point**: `index.php` handles routing and URL rewriting
2. **Bootstrap**: `bootstrap/app.php` loads environment and configuration
3. **Theme Loading**: Natural Green theme system provides styling and layout
4. **Content Rendering**: Page-specific PHP files render content with shared header/footer
5. **Admin Interface**: Separate MVC system handles administrative functions

### Development Patterns
- **Configuration**: Always use environment variables via `env()` helper function
- **Database Access**: Use PDO with prepared statements, apply table prefixes via `table()` helper
- **Styling**: Use Tailwind CSS classes, reference custom color scheme variables
- **Security**: CSRF protection, input sanitization, session management built-in
- **File Organization**: Modular structure with clear separation of concerns

## Testing & Validation

### CSS/Theme Testing
```bash
# Test theme compilation
php test-optimization.php

# Validate theme settings
php verify_colors.php
php verify_fixed_colors.php
```

### Database Testing
```bash
# Check database structure
php admin/database_structure_check.php

# Validate admin system
php admin/validate_mvc.php
```

## Important Notes

### Environment Setup
- Copy `.env.example` to `.env` and configure database credentials
- Database name is typically `kcsvictory`
- Default admin credentials are generated during setup

### Color System
The project uses a sophisticated color management system:
- Base colors defined in CSS variables
- Theme presets stored in database
- Tailwind config extends with custom color palette
- Color overrides can be applied dynamically

### Security Considerations
- Admin system has role-based permissions
- SQL injection protection via PDO prepared statements
- XSS protection via input sanitization
- CSRF tokens for form submissions
- Session-based authentication

### File Upload Handling
- Centralized upload system in `/includes/upload_helpers.php`
- Image processing and optimization built-in
- Security validation for file types and sizes

This codebase represents a mature PHP application with both legacy and modern architectural elements, focused on content management for a Korean labor rights organization.