<?php
require __DIR__.'/renewal.php';require __DIR__.'/../../includes/RenewalMigration.php';require __DIR__.'/../../includes/RenewalSeed.php';
$p=renewalTestPdo();
$p->exec("CREATE TABLE IF NOT EXISTS admin_user (id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50),password_hash VARCHAR(255),name VARCHAR(100),role VARCHAR(20) DEFAULT 'admin',status VARCHAR(20) DEFAULT 'active',is_active TINYINT DEFAULT 1,last_login DATETIME NULL,login_attempts INT DEFAULT 0,locked_until DATETIME NULL)");
$p->exec("CREATE TABLE IF NOT EXISTS inquiries(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,email VARCHAR(255) NOT NULL,message TEXT NOT NULL,status VARCHAR(20) DEFAULT 'new',admin_reply TEXT NULL,replied_at DATETIME NULL,replied_by VARCHAR(50) NULL,ip_address VARCHAR(45),user_agent TEXT,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$p->exec("CREATE TABLE IF NOT EXISTS younglabor_visitor_log(id INT AUTO_INCREMENT PRIMARY KEY,ip_address VARCHAR(45),user_agent TEXT,visit_date DATE,visit_time DATETIME DEFAULT CURRENT_TIMESTAMP,page_url VARCHAR(500),referrer VARCHAR(500))");
$p->exec("DELETE FROM admin_user WHERE username='renewal-test'");$q=$p->prepare('INSERT INTO admin_user(username,password_hash,name) VALUES (?,?,?)');$q->execute(['renewal-test',password_hash('local-test-password',PASSWORD_DEFAULT),'로컬 테스트']);
migrateRenewal($p);seedRenewal($p);echo "Test fixtures ready\n";
