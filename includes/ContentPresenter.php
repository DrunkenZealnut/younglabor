<?php

function renderPlainText(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $paragraphs = preg_split('/(?:\r\n|\r|\n){2,}/', trim($escaped));
    if (!$paragraphs || $paragraphs === ['']) {
        return '';
    }
    return implode("\n", array_map(static function (string $paragraph): string {
        return '<p>' . nl2br($paragraph, false) . '</p>';
    }, $paragraphs));
}

function contentTypeLabel(string $type): string
{
    return [
        'activity' => '활동게시판',
        'press' => '언론보도',
        'resource' => '자료실',
    ][$type] ?? $type;
}

function resourceCategoryLabel(string $category): string
{
    return [
        'education' => '교육자료',
        'research' => '연구자료',
        'guide' => '안내자료',
        'other' => '기타',
    ][$category] ?? $category;
}

function externalLinkHost(string $url): string
{
    $host = parse_url($url, PHP_URL_HOST);
    return is_string($host) ? strtolower(rtrim($host, '.')) : '';
}
