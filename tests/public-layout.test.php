<?php
$header = file_get_contents(__DIR__ . '/../includes/header.php');
$footer = file_get_contents(__DIR__ . '/../includes/footer.php');
foreach (['단체소개','사업소개','청년과노동','게시판'] as $label) {
    if (strpos($header, $label) === false) exit(1);
}
if (substr_count($header, '<main') !== 1) exit(1);
if (strpos($header, '</main>') !== false) exit(1);
if (strpos($footer, '</main>') === false) exit(1);
if (strpos($footer, 'siteSupportPartners()') === false) exit(1);
if (strpos($header, 'aria-expanded="false"') === false) exit(1);
if (strpos($header, "document.documentElement.classList.add('js')") === false) exit(1);
if (strpos($footer, "setAttribute('aria-expanded'") === false) exit(1);
if (strpos($header, 'https://cdn.jsdelivr.net/gh/orioncactus/pretendard') !== false) exit(1);
