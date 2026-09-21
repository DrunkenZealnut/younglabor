<?php
require __DIR__ . '/../includes/ContentValidation.php';
require __DIR__ . '/../includes/ContentPresenter.php';
$bad = validateContentInput(['type'=>'press','title'=>'<img onerror=alert(1)>','slug'=>'Bad Slug','summary'=>'소개','content_date'=>'2026-02-30','status'=>'public','outlet'=>'언론사','external_url'=>'http://127.0.0.1/a']);
foreach (['slug','content_date','status','external_url'] as $field) if (!isset($bad['errors'][$field])) exit(1);
$safe = renderPlainText("첫 줄\n\n<script>alert(1)</script>");
if (strpos($safe, '<script>') !== false || strpos($safe, '&lt;script&gt;') === false) exit(1);
$privateDns = static function (string $host): array { return ['10.0.0.4']; };
$url = validateContentInput(['type'=>'press','title'=>'제목','slug'=>'news','summary'=>'소개','content_date'=>'2026-09-21','status'=>'draft','outlet'=>'언론사','external_url'=>'https://news.example/a'], $privateDns);
if (!isset($url['errors']['external_url'])) exit(1);
$publicDns = static function (string $host): array { return ['93.184.216.34', '2606:2800:220:1:248:1893:25c8:1946']; };
$valid = validateContentInput(['type'=>'press','title'=>'제목','slug'=>'safe-news','summary'=>'소개','content_date'=>'2026-09-21','status'=>'published','outlet'=>'언론사','external_url'=>'https://news.example/article'], $publicDns);
if ($valid['errors'] !== [] || $valid['values']['body'] !== null || externalLinkHost($valid['values']['external_url']) !== 'news.example') exit(1);
foreach (['https://user@news.example/a','https://news.example:444/a','https://news.example/a#fragment','https://localhost/a','https://[::1]/a'] as $unsafe) {
    $result = validateContentInput(['type'=>'press','title'=>'제목','slug'=>'unsafe','summary'=>'소개','content_date'=>'2026-09-21','status'=>'draft','outlet'=>'언론사','external_url'=>$unsafe], $publicDns);
    if (!isset($result['errors']['external_url'])) exit(1);
}
if (contentTypeLabel('activity') !== '활동게시판' || resourceCategoryLabel('guide') !== '안내자료') exit(1);
