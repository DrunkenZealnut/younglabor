<?php

function validateContentInput(array $input, ?callable $hostResolver = null): array
{
    $errors = [];
    foreach ($input as $field => $raw) {
        if (!is_scalar($raw) && $raw !== null) { $errors[$field] = '값을 확인해 주세요.'; $input[$field] = ''; }
    }
    $values = [
        'type' => trim((string)($input['type'] ?? '')),
        'title' => trim((string)($input['title'] ?? '')),
        'slug' => trim((string)($input['slug'] ?? '')),
        'summary' => trim((string)($input['summary'] ?? '')),
        'body' => trim((string)($input['body'] ?? '')),
        'content_date' => trim((string)($input['content_date'] ?? '')),
        'status' => trim((string)($input['status'] ?? 'draft')),
        'outlet' => trim((string)($input['outlet'] ?? '')),
        'external_url' => trim((string)($input['external_url'] ?? '')),
        'resource_category' => trim((string)($input['resource_category'] ?? '')),
        'alt_text' => trim((string)($input['alt_text'] ?? '')),
        'remove_cover' => !empty($input['remove_cover']),
    ];
    $values += ['public_author'=>trim((string)($input['public_author']??'')), 'editorial_category'=>trim((string)($input['editorial_category']??'')), 'work_area_id'=>null, 'program_id'=>null, 'home_featured'=>!empty($input['home_featured'])?1:0];
    foreach (['work_area_id','program_id'] as $field) {
        if (($input[$field]??'') !== '' && $input[$field] !== null) {
            $n=filter_var($input[$field], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
            if ($n===false) $errors[$field]='연결 항목을 확인해 주세요.'; else $values[$field]=$n;
        }
    }
    if (mb_strlen($values['public_author'])>160) $errors['public_author']='작성 주체는 160자 이하로 입력해 주세요.';
    if ($values['type']==='article' && $values['public_author']==='') $errors['public_author']='공개 작성 주체를 입력해 주세요.';
    $categories=['article'=>['story','column','policy'],'notice'=>['notice','recruitment']];
    if (isset($categories[$values['type']]) && !in_array($values['editorial_category'],$categories[$values['type']],true)) $errors['editorial_category']='글 분류를 선택해 주세요.';
    if (!isset($categories[$values['type']])) $values['editorial_category']=null;

    if (!in_array($values['type'], ['activity', 'press', 'resource', 'article', 'notice'], true)) {
        $errors['type'] = '콘텐츠 유형을 선택해 주세요.';
    }
    if ($values['title'] === '' || mb_strlen($values['title'], 'UTF-8') > 160) {
        $errors['title'] = '제목은 1자 이상 160자 이하로 입력해 주세요.';
    }
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $values['slug']) || strlen($values['slug']) > 180) {
        $errors['slug'] = '주소 이름은 영문 소문자, 숫자, 하이픈만 사용할 수 있습니다.';
    }
    if (!contentDateIsValid($values['content_date'])) {
        $errors['content_date'] = '올바른 날짜를 입력해 주세요.';
    }
    if (!in_array($values['status'], ['draft', 'published'], true)) {
        $errors['status'] = '공개 상태를 선택해 주세요.';
    }
    if ($values['summary'] === '' || mb_strlen($values['summary'], 'UTF-8') > 500) {
        $errors['summary'] = '요약은 1자 이상 500자 이하로 입력해 주세요.';
    }

    if (in_array($values['type'], ['activity','article','notice'],true)) {
        if ($values['body'] === '') {
            $errors['body'] = '본문을 입력해 주세요.';
        }
        $values['outlet'] = null;
        $values['external_url'] = null;
        $values['resource_category'] = null;
    } elseif ($values['type'] === 'press') {
        if ($values['outlet'] === '' || mb_strlen($values['outlet'], 'UTF-8') > 160) {
            $errors['outlet'] = '언론사 이름은 1자 이상 160자 이하로 입력해 주세요.';
        }
        if (!contentExternalUrlIsSafe($values['external_url'], $hostResolver)) {
            $errors['external_url'] = '공개 인터넷의 안전한 HTTPS 주소를 입력해 주세요.';
        }
        $values['body'] = null;
        $values['resource_category'] = null;
        $values['alt_text'] = '';
    } elseif ($values['type'] === 'resource') {
        if (!in_array($values['resource_category'], ['education', 'research', 'guide', 'other'], true)) {
            $errors['resource_category'] = '자료 분류를 선택해 주세요.';
        }
        if ($values['external_url'] !== '' && !contentExternalUrlIsSafe($values['external_url'], $hostResolver)) {
            $errors['external_url'] = '공개 인터넷의 안전한 HTTPS 주소를 입력해 주세요.';
        }
        $values['body'] = null;
        $values['outlet'] = null;
        $values['alt_text'] = '';
    }

    foreach (['summary', 'body', 'outlet', 'external_url', 'resource_category'] as $nullable) {
        if ($values[$nullable] === '') {
            $values[$nullable] = null;
        }
    }

    return ['values' => $values, 'errors' => $errors];
}

function contentDateIsValid(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    $problems = DateTimeImmutable::getLastErrors();
    return $parsed !== false
        && ($problems === false || ($problems['warning_count'] === 0 && $problems['error_count'] === 0))
        && $parsed->format('Y-m-d') === $date;
}

function contentExternalUrlIsSafe(string $url, ?callable $hostResolver = null): bool
{
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $parts = parse_url($url);
    if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https') {
        return false;
    }
    if (isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
        return false;
    }
    if (isset($parts['port']) && (int)$parts['port'] !== 443) {
        return false;
    }
    $host = strtolower(rtrim((string)($parts['host'] ?? ''), '.'));
    if (strlen($host) > 2 && $host[0] === '[' && substr($host, -1) === ']') {
        $host = substr($host, 1, -1);
    }
    if ($host === '' || $host === 'localhost' || substr($host, -6) === '.local') {
        return false;
    }

    if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
        return contentAddressIsPublic($host);
    }

    $resolver = $hostResolver ?: 'resolveContentHost';
    try {
        $addresses = $resolver($host);
    } catch (Throwable $error) {
        return false;
    }
    if (!is_array($addresses) || $addresses === []) {
        return false;
    }
    foreach ($addresses as $address) {
        if (!is_string($address) || !contentAddressIsPublic($address)) {
            return false;
        }
    }
    return true;
}

function resolveContentHost(string $host): array
{
    $addresses = [];
    $records = @dns_get_record($host, DNS_A | DNS_AAAA);
    if (!is_array($records)) {
        return [];
    }
    foreach ($records as $record) {
        if (!empty($record['ip'])) {
            $addresses[] = $record['ip'];
        }
        if (!empty($record['ipv6'])) {
            $addresses[] = $record['ipv6'];
        }
    }
    return array_values(array_unique($addresses));
}

function contentAddressIsPublic(string $address): bool
{
    return filter_var(
        $address,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) !== false;
}
