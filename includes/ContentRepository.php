<?php

class ContentConflictException extends RuntimeException {}
class ContentSlugConflictException extends ContentConflictException {}

class ContentRepository
{
    private const TYPES = ['activity', 'press', 'resource'];
    private const PURPOSES = ['cover', 'attachment'];

    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listPublished(string $type, int $limit, int $offset): array
    {
        $this->assertType($type);
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $sql = $this->publicSelect() . " WHERE p.type = :type AND p.status = 'published'"
            . " ORDER BY p.content_date DESC, p.id DESC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll();
    }

    public function countPublished(string $type): int
    {
        $this->assertType($type);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM content_posts p WHERE p.type = :type AND p.status = 'published'");
        $stmt->execute([':type' => $type]);
        return (int)$stmt->fetchColumn();
    }

    public function findPublishedBySlug(string $type, string $slug): ?array
    {
        $this->assertType($type);
        $stmt = $this->pdo->prepare($this->publicSelect() . " WHERE p.type = :type AND p.slug = :slug AND p.status = 'published' LIMIT 1");
        $stmt->execute([':type' => $type, ':slug' => $slug]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        if ($type === 'resource') {
            $file = $this->findPostFileForPublic((int)$row['id'], 'attachment');
            foreach (['file_id', 'file_purpose', 'file_original_name', 'file_mime', 'file_byte_size', 'file_width', 'file_height', 'file_alt_text'] as $key) {
                $row[$key] = $file[$key] ?? null;
            }
        }
        return $row;
    }

    public function latestPublished(string $type, int $limit): array
    {
        return $this->listPublished($type, $limit, 0);
    }

    public function findPublishedFile(int $id, string $purpose): ?array
    {
        $this->assertPurpose($purpose);
        if ($id < 1) {
            return null;
        }
        $stmt = $this->pdo->prepare(
            "SELECT f.*, p.type, p.slug, p.status"
            . " FROM content_files f JOIN content_posts p ON p.id = f.post_id"
            . " WHERE f.id = :id AND f.purpose = :purpose AND f.cleanup_pending = 0"
            . " AND p.status = 'published' LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':purpose' => $purpose]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function listAdmin(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminFilters($filters);
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $stmt = $this->pdo->prepare(
            "SELECT p.* FROM content_posts p {$where}"
            . " ORDER BY p.content_date DESC, p.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAdmin(array $filters): int
    {
        [$where, $params] = $this->adminFilters($filters);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM content_posts p {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findAdminById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM content_posts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function save(array $post, ?int $expectedRevision): int
    {
        $type = (string)($post['type'] ?? '');
        $this->assertType($type);
        $isUpdate = array_key_exists('id', $post);

        if (!$isUpdate) {
            if ($expectedRevision !== null) {
                throw new ContentConflictException('Unexpected revision for new content.');
            }
            return $this->insert($post);
        }

        $id = filter_var($post['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || $expectedRevision === null || $expectedRevision < 1) {
            throw new ContentConflictException('Invalid content revision.');
        }
        $current = $this->findAdminById((int)$id);
        if ($current === null || $current['type'] !== $type) {
            throw new ContentConflictException('Content changed or no longer exists.');
        }

        $sql = "UPDATE content_posts SET title = :title, slug = :slug, summary = :summary, body = :body,"
            . " content_date = :content_date, status = :status, outlet = :outlet, external_url = :external_url,"
            . " resource_category = :resource_category, author_id = :author_id, revision = revision + 1,"
            . " published_at = CASE WHEN :status_publish = 'published' AND published_at IS NULL THEN CURRENT_TIMESTAMP ELSE published_at END"
            . " WHERE id = :id AND revision = :expected_revision";
        $params = $this->postParams($post);
        unset($params[':type']);
        $params[':status_publish'] = $params[':status'];
        $params[':id'] = (int)$id;
        $params[':expected_revision'] = $expectedRevision;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $error) {
            $this->translateConflict($error);
            throw $error;
        }
        if ($stmt->rowCount() !== 1) {
            throw new ContentConflictException('Content changed while it was being edited.');
        }
        return (int)$id;
    }

    public function replaceFile(int $postId, array $file): array
    {
        $purpose = (string)($file['purpose'] ?? '');
        $this->assertPurpose($purpose);
        $detached = $this->detachFile($postId, $purpose);
        $sql = "INSERT INTO content_files"
            . " (post_id, purpose, storage_name, original_name, mime, byte_size, sha256, width, height, alt_text, cleanup_pending)"
            . " VALUES (:post_id, :purpose, :storage_name, :original_name, :mime, :byte_size, :sha256, :width, :height, :alt_text, 0)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':purpose' => $purpose,
            ':storage_name' => $file['storage_name'],
            ':original_name' => $file['original_name'],
            ':mime' => $file['mime'],
            ':byte_size' => $file['byte_size'],
            ':sha256' => $file['sha256'],
            ':width' => $file['width'] ?? null,
            ':height' => $file['height'] ?? null,
            ':alt_text' => $file['alt_text'] ?? null,
        ]);
        return $detached;
    }

    public function filesForPost(int $postId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM content_files WHERE post_id = :post_id AND cleanup_pending = 0 ORDER BY id');
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function updateFileAltText(int $postId, string $purpose, string $altText): void
    {
        $this->assertPurpose($purpose);
        $stmt = $this->pdo->prepare(
            'UPDATE content_files SET alt_text = :alt_text WHERE post_id = :post_id AND purpose = :purpose AND cleanup_pending = 0'
        );
        $stmt->execute([':alt_text' => $altText, ':post_id' => $postId, ':purpose' => $purpose]);
        if ($stmt->rowCount() > 1) {
            throw new RuntimeException('More than one managed file was updated.');
        }
    }

    public function detachFile(int $postId, string $purpose): array
    {
        $this->assertPurpose($purpose);
        $stmt = $this->pdo->prepare('SELECT * FROM content_files WHERE post_id = :post_id AND purpose = :purpose AND cleanup_pending = 0 LIMIT 1');
        $stmt->execute([':post_id' => $postId, ':purpose' => $purpose]);
        $row = $stmt->fetch();
        if ($row === false) {
            return [];
        }
        $update = $this->pdo->prepare('UPDATE content_files SET post_id = NULL, cleanup_pending = 1 WHERE id = :id');
        $update->execute([':id' => $row['id']]);
        $row['post_id'] = null;
        $row['cleanup_pending'] = 1;
        return $row;
    }

    public function detachFilesForDelete(int $postId): array
    {
        $rows = $this->filesForPost($postId);
        if ($rows === []) {
            return [];
        }
        $stmt = $this->pdo->prepare('UPDATE content_files SET post_id = NULL, cleanup_pending = 1 WHERE post_id = :post_id');
        $stmt->execute([':post_id' => $postId]);
        foreach ($rows as &$row) {
            $row['post_id'] = null;
            $row['cleanup_pending'] = 1;
        }
        unset($row);
        return $rows;
    }

    public function listPendingCleanup(int $limit): array
    {
        $limit = max(1, min(500, $limit));
        return $this->pdo->query("SELECT * FROM content_files WHERE post_id IS NULL AND cleanup_pending = 1 ORDER BY id LIMIT {$limit}")->fetchAll();
    }

    public function finishFileCleanup(int $fileId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM content_files WHERE id = :id AND post_id IS NULL AND cleanup_pending = 1');
        $stmt->execute([':id' => $fileId]);
    }

    public function delete(int $id, int $expectedRevision): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM content_posts WHERE id = :id AND revision = :revision');
        $stmt->execute([':id' => $id, ':revision' => $expectedRevision]);
        if ($stmt->rowCount() !== 1) {
            throw new ContentConflictException('Content changed while it was being deleted.');
        }
    }

    private function insert(array $post): int
    {
        $sql = "INSERT INTO content_posts"
            . " (type, title, slug, summary, body, content_date, status, outlet, external_url, resource_category, author_id, published_at)"
            . " VALUES (:type, :title, :slug, :summary, :body, :content_date, :status, :outlet, :external_url, :resource_category, :author_id,"
            . " CASE WHEN :status_publish = 'published' THEN CURRENT_TIMESTAMP ELSE NULL END)";
        $params = $this->postParams($post);
        $params[':status_publish'] = $params[':status'];
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $error) {
            $this->translateConflict($error);
            throw $error;
        }
        return (int)$this->pdo->lastInsertId();
    }

    private function postParams(array $post): array
    {
        return [
            ':type' => (string)$post['type'],
            ':title' => (string)($post['title'] ?? ''),
            ':slug' => (string)($post['slug'] ?? ''),
            ':summary' => $post['summary'] ?? null,
            ':body' => $post['body'] ?? null,
            ':content_date' => (string)($post['content_date'] ?? ''),
            ':status' => (string)($post['status'] ?? 'draft'),
            ':outlet' => $post['outlet'] ?? null,
            ':external_url' => $post['external_url'] ?? null,
            ':resource_category' => $post['resource_category'] ?? null,
            ':author_id' => (int)($post['author_id'] ?? 0),
        ];
    }

    private function publicSelect(): string
    {
        return "SELECT p.*, f.id AS file_id, f.purpose AS file_purpose, f.original_name AS file_original_name,"
            . " f.mime AS file_mime, f.byte_size AS file_byte_size, f.width AS file_width, f.height AS file_height,"
            . " f.alt_text AS file_alt_text"
            . " FROM content_posts p LEFT JOIN content_files f"
            . " ON f.post_id = p.id AND f.purpose = 'cover' AND f.cleanup_pending = 0";
    }

    private function findPostFileForPublic(int $postId, string $purpose): ?array
    {
        $this->assertPurpose($purpose);
        $stmt = $this->pdo->prepare(
            "SELECT f.id AS file_id, f.purpose AS file_purpose, f.original_name AS file_original_name,"
            . " f.mime AS file_mime, f.byte_size AS file_byte_size, f.width AS file_width, f.height AS file_height,"
            . " f.alt_text AS file_alt_text FROM content_files f"
            . " WHERE f.post_id = :post_id AND f.purpose = :purpose AND f.cleanup_pending = 0 LIMIT 1"
        );
        $stmt->execute([':post_id' => $postId, ':purpose' => $purpose]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    private function adminFilters(array $filters): array
    {
        $conditions = [];
        $params = [];
        $type = (string)($filters['type'] ?? '');
        if ($type !== '') {
            $this->assertType($type);
            $conditions[] = 'p.type = :type';
            $params[':type'] = $type;
        }
        $status = (string)($filters['status'] ?? '');
        if ($status !== '') {
            if (!in_array($status, ['draft', 'published'], true)) {
                throw new InvalidArgumentException('Unknown content status.');
            }
            $conditions[] = 'p.status = :status';
            $params[':status'] = $status;
        }
        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(p.title LIKE :search OR p.summary LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        return [$conditions ? 'WHERE ' . implode(' AND ', $conditions) : '', $params];
    }

    private function assertType(string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Unknown content type.');
        }
    }

    private function assertPurpose(string $purpose): void
    {
        if (!in_array($purpose, self::PURPOSES, true)) {
            throw new InvalidArgumentException('Unknown file purpose.');
        }
    }

    private function translateConflict(PDOException $error): void
    {
        if ($error->getCode() === '23000') {
            throw new ContentSlugConflictException('Content slug already exists.', 0, $error);
        }
    }
}
