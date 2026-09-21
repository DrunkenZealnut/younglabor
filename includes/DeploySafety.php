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
    foreach ($roots as $root) {
        if (deploymentPathIsUnder($path, (string)$root)) {
            return true;
        }
    }
    return false;
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

