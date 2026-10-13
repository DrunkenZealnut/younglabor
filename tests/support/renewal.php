<?php
function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function renewalTestPdo(): PDO {
 $name=getenv('TEST_DB_NAME') ?: 'younglabor_renewal_test';
 if (!preg_match('/^[a-z0-9_]+_test$/', $name)) throw new RuntimeException('Only _test databases permitted');
 $dsn='mysql:host=127.0.0.1;port='.(getenv('TEST_DB_PORT') ?: '33317').';dbname='.$name.';charset=utf8mb4';
 return new PDO($dsn,'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
