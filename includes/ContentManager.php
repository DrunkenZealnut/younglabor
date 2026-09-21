<?php

class ContentValidationException extends InvalidArgumentException
{
    private array $errors;
    private array $values;

    public function __construct(array $errors, array $values)
    {
        parent::__construct('Managed content validation failed.');
        $this->errors = $errors;
        $this->values = $values;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function values(): array
    {
        return $this->values;
    }
}

class ContentManager
{
    private PDO $pdo;
    private ContentRepository $repository;
    private ContentStorage $storage;

    public function __construct(PDO $pdo, ContentRepository $repository, ContentStorage $storage)
    {
        $this->pdo = $pdo;
        $this->repository = $repository;
        $this->storage = $storage;
    }

    public function save(array $input, ?array $upload, ?int $expectedRevision): int
    {
        $validation = validateContentInput($input);
        $values = $validation['values'];
        $errors = $validation['errors'];
        $authorId = filter_var($input['author_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($authorId === false) {
            $errors['author_id'] = '작성자 정보가 올바르지 않습니다.';
        }
        $values['author_id'] = $authorId === false ? 0 : (int)$authorId;

        $id = null;
        if (array_key_exists('id', $input)) {
            $id = filter_var($input['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                $errors['id'] = '콘텐츠를 찾을 수 없습니다.';
            } else {
                $values['id'] = (int)$id;
            }
        }
        $existing = $id ? $this->repository->findAdminById((int)$id) : null;
        if ($id && $existing === null) {
            throw new ContentConflictException('Content no longer exists.');
        }
        if ($existing && $existing['type'] !== $values['type']) {
            throw new ContentConflictException('Content type cannot change.');
        }

        $currentFiles = $id ? $this->repository->filesForPost((int)$id) : [];
        $currentByPurpose = [];
        foreach ($currentFiles as $file) {
            $currentByPurpose[$file['purpose']] = $file;
        }
        $hasUpload = $upload !== null && (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($values['type'] === 'activity') {
            $coverRemains = $hasUpload || (!$values['remove_cover'] && isset($currentByPurpose['cover']));
            if ($coverRemains && $values['alt_text'] === '') {
                $errors['alt_text'] = '표지 이미지의 대체 텍스트를 입력해 주세요.';
            }
        }

        if ($values['type'] === 'resource') {
            if ($hasUpload) {
                $values['external_url'] = null;
            } elseif ($values['external_url'] === null && !isset($currentByPurpose['attachment'])) {
                $errors['external_url'] = '첨부파일 또는 HTTPS 주소 중 하나를 입력해 주세요.';
            }
        }

        if ($errors !== []) {
            throw new ContentValidationException($errors, $values);
        }

        $staged = null;
        $stored = null;
        $detached = [];
        try {
            if ($hasUpload) {
                $staged = $this->storage->stageForType($values['type'], $upload, $values['alt_text']);
            }
            $this->pdo->beginTransaction();
            $savedId = $this->repository->save($values, $expectedRevision);

            if ($staged !== null) {
                $stored = $this->storage->promote($staged);
                $old = $this->repository->replaceFile($savedId, $stored);
                if ($old !== []) {
                    $detached[] = $old;
                }
            } elseif ($values['type'] === 'resource' && $values['external_url'] !== null && isset($currentByPurpose['attachment'])) {
                $old = $this->repository->detachFile($savedId, 'attachment');
                if ($old !== []) {
                    $detached[] = $old;
                }
            } elseif ($values['type'] === 'activity' && $values['remove_cover'] && isset($currentByPurpose['cover'])) {
                $old = $this->repository->detachFile($savedId, 'cover');
                if ($old !== []) {
                    $detached[] = $old;
                }
            } elseif ($values['type'] === 'activity' && isset($currentByPurpose['cover'])) {
                $this->repository->updateFileAltText($savedId, 'cover', $values['alt_text']);
            }

            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if ($stored !== null) {
                $this->storage->remove($stored['storage_name']);
            }
            if ($staged !== null) {
                $this->storage->discard($staged);
            }
            throw $error;
        }
        $this->cleanupDetachedFiles($detached);
        return $savedId;
    }

    public function delete(int $id, int $expectedRevision): void
    {
        $detached = [];
        try {
            $this->pdo->beginTransaction();
            $detached = $this->repository->detachFilesForDelete($id);
            $this->repository->delete($id, $expectedRevision);
            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
        $this->cleanupDetachedFiles($detached);
    }

    public function retryPendingCleanup(): int
    {
        $completed = 0;
        foreach ($this->repository->listPendingCleanup(100) as $file) {
            try {
                if ($this->storage->remove((string)$file['storage_name'])) {
                    $this->repository->finishFileCleanup((int)$file['id']);
                    $completed++;
                    continue;
                }
            } catch (Throwable $error) {
                // Keep the detached row queued; log only its numeric identifier.
            }
            if (isset($file['id'])) {
                error_log('Content cleanup pending for file ID ' . (int)$file['id']);
            }
        }
        return $completed;
    }

    private function cleanupDetachedFiles(array $files): void
    {
        foreach ($files as $file) {
            try {
                if ($this->storage->remove((string)$file['storage_name'])) {
                    $this->repository->finishFileCleanup((int)$file['id']);
                    continue;
                }
            } catch (Throwable $error) {
                // Keep the detached row queued; log only its numeric identifier.
            }
            if (isset($file['id'])) {
                error_log('Content cleanup pending for file ID ' . (int)$file['id']);
            }
        }
    }
}
