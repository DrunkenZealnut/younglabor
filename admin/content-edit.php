<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/ContentRepository.php';
require_once __DIR__ . '/../includes/ContentValidation.php';
require_once __DIR__ . '/../includes/ContentPresenter.php';
require_once __DIR__ . '/../includes/ContentStorage.php';
require_once __DIR__ . '/../includes/ContentManager.php';

$db = Database::getInstance()->getConnection();
$repository = new ContentRepository($db);
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$existing = $id === false || $id === null ? null : $repository->findAdminById((int)$id);
if ($id && $existing === null) {
    http_response_code(404);
    adminHeader();
    echo '<div class="card">콘텐츠를 찾을 수 없습니다.</div>';
    adminFooter();
    exit;
}

$files = $existing ? $repository->filesForPost((int)$existing['id']) : [];
$fileByPurpose = [];
foreach ($files as $file) $fileByPurpose[$file['purpose']] = $file;
$values = $existing ?: [
    'type'=>'activity', 'title'=>'', 'slug'=>'', 'summary'=>'', 'body'=>'',
    'content_date'=>date('Y-m-d'), 'status'=>'draft', 'outlet'=>'',
    'external_url'=>'', 'resource_category'=>'education', 'revision'=>null,
];
$values['alt_text'] = (string)($fileByPurpose['cover']['alt_text'] ?? '');
$values['remove_cover'] = false;
$errors = [];
$generalError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $generalError = '요청을 확인할 수 없습니다. 다시 시도해 주세요.';
    } else {
        $submittedType = $existing ? (string)$existing['type'] : (string)($_POST['type'] ?? '');
        $input = [
            'type'=>$submittedType,
            'title'=>$_POST['title'] ?? '',
            'slug'=>$_POST['slug'] ?? '',
            'summary'=>$_POST['summary'] ?? '',
            'body'=>$_POST['body'] ?? '',
            'content_date'=>$_POST['content_date'] ?? '',
            'status'=>$_POST['status'] ?? '',
            'outlet'=>$_POST['outlet'] ?? '',
            'external_url'=>$_POST['external_url'] ?? '',
            'resource_category'=>$_POST['resource_category'] ?? '',
            'alt_text'=>$_POST['alt_text'] ?? '',
            'remove_cover'=>isset($_POST['remove_cover']),
            'author_id'=>$adminUser['id'],
        ];
        if ($existing) $input['id'] = (int)$existing['id'];
        $expectedRevision = $existing ? filter_var($_POST['expected_revision'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) : null;
        $upload = $_FILES['upload'] ?? null;
        try {
            if ($existing && $expectedRevision === false) throw new ContentConflictException('Invalid revision.');
            $storage = new ContentStorage(contentStoragePath());
            $manager = new ContentManager($db, $repository, $storage);
            $savedId = $manager->save($input, $upload, $existing ? (int)$expectedRevision : null);
            $_SESSION['content_flash'] = ['type'=>'success', 'message'=>$existing ? '콘텐츠를 수정했습니다.' : '콘텐츠를 만들었습니다.'];
            header('Location: ' . url('admin/content-edit.php?id=' . $savedId));
            exit;
        } catch (ContentValidationException $error) {
            $errors = $error->errors();
            $values = array_replace($values, $error->values());
        } catch (ContentConflictException $error) {
            $generalError = '다른 변경과 충돌했습니다. 목록에서 다시 열어 주세요.';
            $values = array_replace($values, $input);
        } catch (ContentUploadException $error) {
            $generalError = '파일을 확인하지 못했습니다. 형식과 크기를 확인해 주세요.';
            $values = array_replace($values, $input);
        } catch (Throwable $error) {
            $generalError = '저장하지 못했습니다. 잠시 후 다시 시도해 주세요.';
            $values = array_replace($values, $input);
        }
    }
}

$flash = $_SESSION['content_flash'] ?? null;
unset($_SESSION['content_flash']);
adminHeader();
?>
<style>
.content-form{max-width:900px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.form-field{margin-bottom:18px}.form-field.full{grid-column:1/-1}.form-field label{display:block;font-size:13px;font-weight:700;margin-bottom:7px}.form-field input,.form-field select,.form-field textarea{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}.form-field textarea{min-height:120px;resize:vertical}.field-error{color:#dc2626;font-size:12px;margin-top:5px}.field-help{color:#64748b;font-size:12px;margin-top:5px}.type-section[hidden]{display:none}@media(max-width:768px){.form-grid{grid-template-columns:1fr}}
</style>
<div class="main-header"><h1><?php echo $existing ? '콘텐츠 수정' : '새 콘텐츠'; ?></h1><a class="btn btn-outline" href="<?php echo e(url('admin/content.php')); ?>">목록</a></div>
<?php if ($flash): ?><div class="card" style="margin-bottom:16px;border-left:4px solid #22c55e"><?php echo e((string)$flash['message']); ?></div><?php endif; ?>
<?php if ($generalError): ?><div class="card" style="margin-bottom:16px;border-left:4px solid #ef4444"><?php echo e($generalError); ?></div><?php endif; ?>

<form class="card content-form" method="post" enctype="multipart/form-data">
    <?php echo csrfField(); ?>
    <?php if ($existing): ?><input type="hidden" name="expected_revision" value="<?php echo (int)$existing['revision']; ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-field">
            <label for="type">유형</label>
            <?php if ($existing): ?>
                <input type="text" value="<?php echo e(contentTypeLabel((string)$existing['type'])); ?>" disabled>
            <?php else: ?>
                <select id="type" name="type" onchange="toggleContentFields()">
                    <?php foreach (['activity','press','resource'] as $option): ?><option value="<?php echo $option; ?>"<?php echo ($values['type'] ?? '') === $option ? ' selected' : ''; ?>><?php echo e(contentTypeLabel($option)); ?></option><?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if (isset($errors['type'])): ?><div class="field-error"><?php echo e($errors['type']); ?></div><?php endif; ?>
        </div>
        <div class="form-field"><label for="status">상태</label><select id="status" name="status"><option value="draft"<?php echo ($values['status'] ?? '') === 'draft' ? ' selected' : ''; ?>>임시저장</option><option value="published"<?php echo ($values['status'] ?? '') === 'published' ? ' selected' : ''; ?>>공개</option></select></div>
        <div class="form-field full"><label for="title">제목</label><input id="title" name="title" maxlength="160" required value="<?php echo e((string)($values['title'] ?? '')); ?>"><?php if (isset($errors['title'])): ?><div class="field-error"><?php echo e($errors['title']); ?></div><?php endif; ?></div>
        <div class="form-field"><label for="slug">주소 이름</label><input id="slug" name="slug" maxlength="180" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" value="<?php echo e((string)($values['slug'] ?? '')); ?>"><div class="field-help">영문 소문자, 숫자, 하이픈만 사용합니다.</div><?php if (isset($errors['slug'])): ?><div class="field-error"><?php echo e($errors['slug']); ?></div><?php endif; ?></div>
        <div class="form-field"><label for="content_date">기준일</label><input id="content_date" type="date" name="content_date" required value="<?php echo e((string)($values['content_date'] ?? '')); ?>"><?php if (isset($errors['content_date'])): ?><div class="field-error"><?php echo e($errors['content_date']); ?></div><?php endif; ?></div>
        <div class="form-field full"><label for="summary">요약/설명</label><textarea id="summary" name="summary" maxlength="500" required><?php echo e((string)($values['summary'] ?? '')); ?></textarea><?php if (isset($errors['summary'])): ?><div class="field-error"><?php echo e($errors['summary']); ?></div><?php endif; ?></div>
    </div>

    <div class="type-section" data-type="activity">
        <div class="form-field"><label for="body">활동 내용</label><textarea id="body" name="body" rows="12"><?php echo e((string)($values['body'] ?? '')); ?></textarea><?php if (isset($errors['body'])): ?><div class="field-error"><?php echo e($errors['body']); ?></div><?php endif; ?></div>
        <div class="form-field"><label for="activity-upload">표지 이미지</label><input id="activity-upload" type="file" name="upload" accept="image/jpeg,image/png,image/webp"><div class="field-help">JPEG, PNG, WebP · 최대 5 MiB. 식별 가능한 미성년자의 얼굴은 필요한 동의를 확인한 경우에만 업로드하세요.</div></div>
        <div class="form-field"><label for="alt_text">대체 텍스트</label><input id="alt_text" name="alt_text" maxlength="255" value="<?php echo e((string)($values['alt_text'] ?? '')); ?>"><?php if (isset($errors['alt_text'])): ?><div class="field-error"><?php echo e($errors['alt_text']); ?></div><?php endif; ?></div>
        <?php if (isset($fileByPurpose['cover'])): ?><label><input type="checkbox" name="remove_cover" value="1"<?php echo !empty($values['remove_cover']) ? ' checked' : ''; ?>> 현재 표지 이미지 제거</label><?php endif; ?>
    </div>

    <div class="type-section" data-type="press">
        <div class="form-field"><label for="outlet">언론사</label><input id="outlet" name="outlet" maxlength="160" value="<?php echo e((string)($values['outlet'] ?? '')); ?>"><?php if (isset($errors['outlet'])): ?><div class="field-error"><?php echo e($errors['outlet']); ?></div><?php endif; ?></div>
        <div class="form-field"><label for="press-url">원문 HTTPS 주소</label><input id="press-url" type="url" name="external_url" value="<?php echo e((string)($values['external_url'] ?? '')); ?>"><?php if (isset($errors['external_url'])): ?><div class="field-error"><?php echo e($errors['external_url']); ?></div><?php endif; ?></div>
    </div>

    <div class="type-section" data-type="resource">
        <div class="form-field"><label for="resource_category">자료 분류</label><select id="resource_category" name="resource_category"><?php foreach (['education','research','guide','other'] as $category): ?><option value="<?php echo $category; ?>"<?php echo ($values['resource_category'] ?? '') === $category ? ' selected' : ''; ?>><?php echo e(resourceCategoryLabel($category)); ?></option><?php endforeach; ?></select><?php if (isset($errors['resource_category'])): ?><div class="field-error"><?php echo e($errors['resource_category']); ?></div><?php endif; ?></div>
        <div class="form-field"><label for="resource-upload">첨부파일</label><input id="resource-upload" type="file" name="upload" accept=".pdf,.hwp,.hwpx,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"><div class="field-help">최대 20 MiB. 새 파일을 올리면 HTTPS 주소는 비워집니다.<?php echo isset($fileByPurpose['attachment']) ? ' 현재 첨부파일이 등록되어 있습니다.' : ''; ?></div></div>
        <div class="form-field"><label for="resource-url">외부 HTTPS 주소</label><input id="resource-url" type="url" name="external_url" value="<?php echo e((string)($values['external_url'] ?? '')); ?>"><div class="field-help">첨부파일과 외부 주소 중 하나만 사용합니다.</div><?php if (isset($errors['external_url'])): ?><div class="field-error"><?php echo e($errors['external_url']); ?></div><?php endif; ?></div>
    </div>

    <div style="display:flex;gap:10px;margin-top:24px"><button class="btn btn-primary" type="submit">저장</button><a class="btn btn-outline" href="<?php echo e(url('admin/content.php')); ?>">취소</a></div>
</form>
<script>
function toggleContentFields() {
    const fixedType = <?php echo json_encode($existing['type'] ?? null); ?>;
    const selected = fixedType || document.getElementById('type').value;
    document.querySelectorAll('.type-section').forEach(section => { section.hidden = section.dataset.type !== selected; });
    document.querySelectorAll('.type-section input, .type-section textarea, .type-section select').forEach(field => { field.disabled = field.closest('.type-section').hidden; });
}
toggleContentFields();
</script>
<?php adminFooter(); ?>
