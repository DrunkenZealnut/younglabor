<?php

function deploymentPathIsUnder(string $path, string $root): bool
{
    $normalizedPath = trim(str_replace('\\', '/', $path), '/');
    $normalizedRoot = trim(str_replace('\\', '/', $root), '/');
    return $normalizedRoot !== ''
        && ($normalizedPath === $normalizedRoot || strpos($normalizedPath, $normalizedRoot . '/') === 0);
}

function deploymentPathIsExcluded(string $path, array $roots): bool
{
    $normalizedPath = trim(str_replace('\\', '/', $path), '/');
    foreach ($roots as $root) {
        $normalizedRoot = trim(str_replace('\\', '/', (string)$root), '/');
        if (strpbrk($normalizedRoot, '*?[') !== false) {
            if (fnmatch($normalizedRoot, $normalizedPath, FNM_PATHNAME)) {
                return true;
            }
            continue;
        }
        if (deploymentPathIsUnder($normalizedPath, $normalizedRoot)) {
            return true;
        }
    }
    return false;
}

function deploymentResultIsSuccessful(array $copyErrors, array $pruneErrors): bool
{
    return count($copyErrors) === 0 && count($pruneErrors) === 0;
}

function deploymentPathIsProtectedProductionData(string $path): bool
{
    foreach (['backup', 'dbeditor', 'data/file', 'uploads', 'wp-content/uploads'] as $root) {
        if (deploymentPathIsUnder($path, $root)) {
            return true;
        }
    }
    return false;
}
