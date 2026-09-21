<?php

function loadHomeContent(callable $repositoryFactory): array
{
    try {
        $repository = $repositoryFactory();
        return [
            'activity' => $repository->latestPublished('activity', 3),
            'press' => $repository->latestPublished('press', 3),
            'unavailable' => false,
        ];
    } catch (Throwable $error) {
        error_log('Home content unavailable');
        return ['activity' => [], 'press' => [], 'unavailable' => true];
    }
}
