<?php
require __DIR__.'/support/renewal.php';
$base=getenv('SITE_BASE_URL')?:'http://127.0.0.1:18763/younglabor';$html=file_get_contents($base.'/club');
check(str_contains($html,'상시모집'),'club always recruiting');
foreach(['10월 15일','9월 23일','Lovable','러버블','Claude','GPT'] as $old)check(!str_contains($html,$old),'outdated condition removed: '.$old);
check(str_contains($html,'바이브코딩도구'),'generic tool name');check(str_contains($html,'https://zealot-survey.vercel.app/RJXag60aMfMT'),'correct club form');
echo "PASS club ongoing recruitment, generic tools, application\n";
