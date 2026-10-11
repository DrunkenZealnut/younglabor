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
        'activity' => '활동 소식',
        'press' => '언론보도',
        'resource' => '자료실',
        'article' => '청년과노동', 'notice' => '공지·모집',
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

function contentImageSrcset(array $file): string
{
    $id = (int)($file['file_id'] ?? 0);
    $width = (int)($file['file_width'] ?? 0);
    if ($id < 1 || $width < 1) {
        return '';
    }
    $sources = [];
    foreach ([480, 960] as $variantWidth) {
        if ($variantWidth < $width) {
            $sources[] = url('media/' . $id . '?w=' . $variantWidth) . ' ' . $variantWidth . 'w';
        }
    }
    $sources[] = url('media/' . $id) . ' ' . $width . 'w';
    return implode(', ', $sources);
}

function publicPostPath(array $post): string
{
    $slug=$post['slug'];
    return match($post['type']) {
        'article'=>'youth-labor/'.$slug,
        'notice'=>'board/notice--'.$slug,
        'press'=>'board/press--'.$slug,
        'activity'=>'board/'.$slug,
        'resource'=>'resources/'.$slug,
        default=>throw new InvalidArgumentException('Unknown content type')
    };
}
function editorialCategoryLabel(string $category): string {
    return ['story'=>'당사자 이야기','column'=>'칼럼','policy'=>'정책·제도 제안','notice'=>'공지','recruitment'=>'모집','activity'=>'활동 소식','press'=>'언론 소식'][$category]??$category;
}
