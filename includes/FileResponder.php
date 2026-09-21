<?php

class InvalidRangeException extends InvalidArgumentException {}

function parseSingleByteRange(?string $header, int $size): ?array
{
    if ($header === null || trim($header) === '') {
        return null;
    }
    if ($size < 1 || strpos($header, ',') !== false || !preg_match('/^bytes=(\d*)-(\d*)$/', trim($header), $matches)) {
        throw new InvalidRangeException('Invalid byte range.');
    }
    $startText = $matches[1];
    $endText = $matches[2];
    if ($startText === '' && $endText === '') {
        throw new InvalidRangeException('Invalid byte range.');
    }
    if ($startText === '') {
        $suffix = (int)$endText;
        if ($suffix < 1) {
            throw new InvalidRangeException('Invalid byte range.');
        }
        return [max(0, $size - $suffix), $size - 1];
    }
    $start = (int)$startText;
    if ($start >= $size) {
        throw new InvalidRangeException('Range starts beyond the file.');
    }
    $end = $endText === '' ? $size - 1 : min((int)$endText, $size - 1);
    if ($end < $start) {
        throw new InvalidRangeException('Range end precedes its start.');
    }
    return [$start, $end];
}

function safeDownloadName(string $name): string
{
    $name = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $name) ?? '';
    $name = str_replace(['/', '\\', ':', '"', "'", '<', '>', '|'], ' ', $name);
    $name = ltrim($name, ". \t\n\r\0\x0B");
    $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
    $name = mb_substr($name, 0, 180, 'UTF-8');
    return $name !== '' ? $name : 'download';
}

function streamContentFile(array $file, string $absolutePath, bool $attachment): void
{
    $handle = @fopen($absolutePath, 'rb');
    if ($handle === false) {
        http_response_code(404);
        return;
    }
    $stat = @fstat($handle);
    $size = is_array($stat) ? ($stat['size'] ?? false) : false;
    if ($size === false || $size < 1) {
        fclose($handle);
        http_response_code(404);
        return;
    }
    $etag = '"' . (string)$file['sha256'] . '"';
    header('ETag: ' . $etag);
    header('X-Content-Type-Options: nosniff');
    if (fileResponderEtagMatches((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''), $etag)) {
        fclose($handle);
        http_response_code(304);
        return;
    }

    $mime = $attachment ? (string)$file['mime'] : 'image/webp';
    header('Content-Type: ' . $mime);
    $start = 0;
    $end = $size - 1;
    if ($attachment) {
        $safeName = safeDownloadName((string)$file['original_name']);
        $fallback = preg_replace('/[^\x20-\x7E]/', '_', $safeName) ?: 'download';
        $fallback = str_replace(['"', '\\'], '_', $fallback);
        header("Content-Disposition: attachment; filename=\"{$fallback}\"; filename*=UTF-8''" . rawurlencode($safeName));
        header('Accept-Ranges: bytes');
        try {
            $range = parseSingleByteRange($_SERVER['HTTP_RANGE'] ?? null, $size);
        } catch (InvalidRangeException $error) {
            fclose($handle);
            http_response_code(416);
            header('Content-Range: bytes */' . $size);
            header('Content-Length: 0');
            return;
        }
        if ($range !== null) {
            [$start, $end] = $range;
            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }
    } else {
        header('Cache-Control: public, max-age=31536000, immutable');
    }
    $length = $end - $start + 1;
    header('Content-Length: ' . $length);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
        fclose($handle);
        return;
    }
    if ($start > 0) {
        fseek($handle, $start);
    }
    $remaining = $length;
    while ($remaining > 0 && !feof($handle)) {
        $chunk = fread($handle, min(65536, $remaining));
        if ($chunk === false || $chunk === '') {
            break;
        }
        echo $chunk;
        $remaining -= strlen($chunk);
        if (function_exists('fastcgi_finish_request')) {
            flush();
        }
    }
    fclose($handle);
}

function fileResponderEtagMatches(string $header, string $etag): bool
{
    foreach (explode(',', $header) as $candidate) {
        $candidate = trim($candidate);
        if ($candidate === '*' || $candidate === $etag || $candidate === 'W/' . $etag) {
            return true;
        }
    }
    return false;
}
