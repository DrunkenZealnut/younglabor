<?php
/** 기존 글과 문의를 보존하는 재실행 가능한 추가 이관. */
function renewalTableExists(PDO $pdo, string $table): bool {
 $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?'); $q->execute([$table]); return (bool)$q->fetchColumn();
}
function renewalAddColumn(PDO $pdo,string $table,string $column,string $definition): void {
 $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?'); $q->execute([$table,$column]);
 if (!$q->fetchColumn()) $pdo->exec("ALTER TABLE `$table` ADD `$column` $definition");
}
function migrateRenewal(PDO $pdo): void {
 if (!renewalTableExists($pdo,'content_posts')) $pdo->exec(file_get_contents(__DIR__.'/../database/migrations/20260921_create_managed_content.sql'));
 $pdo->exec("CREATE TABLE IF NOT EXISTS renewal_migrations (name VARCHAR(100) PRIMARY KEY, applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
 $common="id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(180) NOT NULL UNIQUE, title VARCHAR(160) NOT NULL, summary TEXT NOT NULL, body TEXT NULL, status ENUM('draft','published') NOT NULL DEFAULT 'draft', revision INT UNSIGNED NOT NULL DEFAULT 1, home_featured TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
 $tables=[
 'work_areas'=>"$common, audience TEXT NULL, provision TEXT NULL, phase ENUM('preparing','recruiting','active','ended') NOT NULL DEFAULT 'preparing', action_label VARCHAR(100) NULL, action_path VARCHAR(2048) NULL, sort_order INT NOT NULL DEFAULT 0",
 'work_funding'=>"$common, year SMALLINT UNSIGNED NOT NULL UNIQUE, term SMALLINT UNSIGNED NOT NULL DEFAULT 1, sponsor VARCHAR(160) NOT NULL",
 'work_units'=>"$common, funding_id INT UNSIGNED NOT NULL, registered_name VARCHAR(160) NULL, phase ENUM('preparing','recruiting','active','ended') NOT NULL DEFAULT 'preparing', action_label VARCHAR(100) NULL, action_path VARCHAR(2048) NULL, sort_order INT NOT NULL DEFAULT 0, FOREIGN KEY (funding_id) REFERENCES work_funding(id)",
 'work_programs'=>"$common, kind ENUM('course','club') NOT NULL, work_area_id INT UNSIGNED NULL, audience TEXT NULL, venue TEXT NULL, fee VARCHAR(100) NULL, application_url VARCHAR(2048) NULL, recruitment_status ENUM('pending','open','closed') NOT NULL DEFAULT 'pending', recruitment_mode ENUM('dated','rolling') NOT NULL DEFAULT 'dated', deadline_at DATETIME NULL, contact_email VARCHAR(254) NOT NULL, organizers TEXT NULL, credit TEXT NULL, support_details TEXT NULL, FOREIGN KEY (work_area_id) REFERENCES work_areas(id)",
 'work_sessions'=>"id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, program_id INT UNSIGNED NOT NULL, number TINYINT UNSIGNED NOT NULL, starts_at DATETIME NOT NULL, speaker VARCHAR(160) NOT NULL, title VARCHAR(160) NOT NULL, description TEXT NULL, mode ENUM('online','hybrid') NOT NULL, panel TEXT NULL, UNIQUE KEY (program_id,number), FOREIGN KEY (program_id) REFERENCES work_programs(id) ON DELETE CASCADE",
 'work_unit_areas'=>"unit_id INT UNSIGNED NOT NULL, area_id INT UNSIGNED NOT NULL, PRIMARY KEY(unit_id,area_id), FOREIGN KEY(unit_id) REFERENCES work_units(id) ON DELETE CASCADE, FOREIGN KEY(area_id) REFERENCES work_areas(id)"
 ];
 foreach($tables as $table=>$columns) $pdo->exec("CREATE TABLE IF NOT EXISTS $table ($columns) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 $pdo->exec("ALTER TABLE content_posts MODIFY type ENUM('activity','press','resource','article','notice') NOT NULL");
 foreach(['public_author'=>"VARCHAR(160) NULL",'editorial_category'=>"VARCHAR(30) NULL",'work_area_id'=>"INT UNSIGNED NULL",'program_id'=>"INT UNSIGNED NULL",'home_featured'=>"TINYINT(1) NOT NULL DEFAULT 0"] as $column=>$def) renewalAddColumn($pdo,'content_posts',$column,$def);
 if(renewalTableExists($pdo,'inquiries')) renewalAddColumn($pdo,'inquiries','inquiry_type',"VARCHAR(30) NOT NULL DEFAULT 'general'");
 $pdo->exec("INSERT IGNORE INTO renewal_migrations(name) VALUES ('20261011-renewal')");
}
