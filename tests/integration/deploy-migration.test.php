<?php
require __DIR__.'/../support/renewal.php';
require __DIR__.'/../../includes/DeployMigration.php';
// 이관 전 상태를 재현하려면 빈 DB가 필요하므로 공용 테스트 DB 대신 전용 _test DB를 만들고 지운다
$name='younglabor_deploy_test';
$server=new PDO('mysql:host=127.0.0.1;port='.(getenv('TEST_DB_PORT') ?: '33317').';charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$server->exec("DROP DATABASE IF EXISTS $name");$server->exec("CREATE DATABASE $name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
putenv("TEST_DB_NAME=$name");
try {
 $p=renewalTestPdo();
 $p->exec("CREATE TABLE inquiries(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,email VARCHAR(255) NOT NULL,message TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 $p->exec("INSERT INTO inquiries(name,email,message) VALUES ('기존','old@example.com','보존할 문의')");
 $before=deployMigrationStatus($p);
 check($before['pending']===true,'empty DB must be pending');
 check(!in_array(true,$before['tables'],true),'status must not create tables');
 check($before['counts']['work_areas']===null && $before['applied']===[],'status reports missing tables');
 check($before['columns']===['inquiries.inquiry_type'=>false],'status reports missing inquiry_type used by the contact form');
 check(deployMigrationStatus($p)['tables']===$before['tables'],'status is read-only');
 $after=deployMigrationApply($p);
 check($after['pending']===false,'apply must finish migration');
 check(!in_array(false,$after['tables'],true) && $after['columns']===['inquiries.inquiry_type'=>true],'all renewal tables and columns created');
 check($after['applied']===['20261011-renewal'],'migration recorded');
 check($after['counts']===['work_areas'=>4,'work_programs'=>2,'work_sessions'=>4],'initial content seeded');
 check($p->query("SELECT message FROM inquiries WHERE email='old@example.com'")->fetchColumn()==='보존할 문의','existing inquiries preserved');
 $p->exec("UPDATE work_areas SET summary='관리자 수정' WHERE slug='education'");
 check(deployMigrationApply($p)['counts']===$after['counts'],'apply is idempotent');
 check($p->query("SELECT summary FROM work_areas WHERE slug='education'")->fetchColumn()==='관리자 수정','re-apply keeps admin edits');
 // apply가 중간에 멈춘 상태: 테이블은 있고 이관 기록이 없거나, 기록은 있고 테이블이 빠진 경우 모두 pending
 $p->exec("DELETE FROM renewal_migrations");
 $partial=deployMigrationStatus($p);
 check($partial['pending']===true && !in_array(false,$partial['tables'],true),'missing marker keeps pending');
 check(deployMigrationApply($p)['pending']===false,'re-apply records marker');
 $p->exec("DROP TABLE work_unit_areas");
 check(deployMigrationStatus($p)['pending']===true,'missing table keeps pending');
 check(deployMigrationApply($p)['pending']===false,'re-apply restores missing table');
 echo "PASS deploy migration status/apply on empty DB\n";
} finally {
 $server->exec("DROP DATABASE IF EXISTS $name");
}
