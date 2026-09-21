<?php
$root = sys_get_temp_dir() . '/younglabor-content-' . bin2hex(random_bytes(4));
putenv('CONTENT_STORAGE_PATH=' . $root);
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/ContentStorage.php';
putenv('CONTENT_STORAGE_PATH=' . __DIR__);
try { contentStoragePath(); exit(1); } catch (RuntimeException $expected) {}
putenv('CONTENT_STORAGE_PATH=' . $root);
$image = $root . '-source.png';
$canvas = imagecreatetruecolor(2000, 1000);
imagepng($canvas, $image); imagedestroy($canvas);
$storage = new ContentStorage(contentStoragePath(), static function (string $from, string $to): bool { return rename($from, $to); });
$cover = $storage->stageCover(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$image,'size'=>filesize($image),'name'=>'cover.png'], '현장 사진');
if ($cover['mime'] !== 'image/webp' || $cover['width'] !== 1600 || $cover['height'] !== 800) exit(1);
if (array_keys($cover['variants'] ?? []) !== [480, 960]) exit(1);
$storedCover = $storage->promote($cover);
foreach ([480, 960] as $variantWidth) {
    $variantPath = $storage->pathForVariant($storedCover['storage_name'], $variantWidth);
    if ($variantPath === null || !is_file($variantPath)) exit(1);
    $variantSize = getimagesize($variantPath);
    if ($variantSize === false || $variantSize[0] !== $variantWidth) exit(1);
}
$missingVariant = $storage->pathForVariant($storedCover['storage_name'], 480);
unlink($missingVariant);
$recreatedVariant = $storage->ensureVariant($storedCover['storage_name'], 480);
if ($recreatedVariant !== $missingVariant || !is_file($recreatedVariant) || getimagesize($recreatedVariant)[0] !== 480) exit(1);
unlink($missingVariant);
mkdir($missingVariant, 0700);
ob_start();
$failedVariant = $storage->ensureVariant($storedCover['storage_name'], 480);
$warningOutput = ob_get_clean();
if ($failedVariant !== null || $warningOutput !== '') exit(1);
rmdir($missingVariant);
if (!$storage->remove($storedCover['storage_name'])) exit(1);
foreach ([480, 960] as $variantWidth) {
    if (is_file((string)$storage->pathForVariant($storedCover['storage_name'], $variantWidth))) exit(1);
}
$script = $root . '-bad.pdf'; file_put_contents($script, "<?php echo 1;");
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$script,'size'=>filesize($script),'name'=>'bad.pdf']); exit(1); } catch (ContentUploadException $expected) {}
$archive = $root . '-bad.zip';
$zip = new ZipArchive(); $zip->open($archive, ZipArchive::CREATE); $zip->addFromString('../escape.php', '<?php'); $zip->close();
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$archive,'size'=>filesize($archive),'name'=>'bad.zip']); exit(1); } catch (ContentUploadException $expected) {}
$many = $root . '-many.zip';
$zip = new ZipArchive(); $zip->open($many, ZipArchive::CREATE);
for ($i = 0; $i < 1001; $i++) $zip->addFromString('safe/' . $i . '.txt', 'x');
$zip->close();
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$many,'size'=>filesize($many),'name'=>'many.zip']); exit(1); } catch (ContentUploadException $expected) {}
$pdf = $root . '-good.pdf'; file_put_contents($pdf, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");
$stagedPdf = $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$pdf,'size'=>filesize($pdf),'name'=>'guide.pdf']);
$storedPdf = $storage->promote($stagedPdf);
if ($storedPdf['mime'] !== 'application/pdf' || !is_file($storage->pathFor($storedPdf['storage_name']))) exit(1);
if (!$storage->remove($storedPdf['storage_name'])) exit(1);
$safeZip = $root . '-safe.zip';
$zip = new ZipArchive(); $zip->open($safeZip, ZipArchive::CREATE); $zip->addFromString('docs/readme.txt', 'safe'); $zip->close();
$stagedZip = $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$safeZip,'size'=>filesize($safeZip),'name'=>'archive.zip']);
$storage->discard($stagedZip);
$fakeDocx = $root . '-fake.docx';
$zip = new ZipArchive(); $zip->open($fakeDocx, ZipArchive::CREATE); $zip->addFromString('[Content_Types].xml', '<Types/>'); $zip->close();
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$fakeDocx,'size'=>filesize($fakeDocx),'name'=>'fake.docx']); exit(1); } catch (ContentUploadException $expected) {}
$scriptZip = $root . '-script.zip';
$zip = new ZipArchive(); $zip->open($scriptZip, ZipArchive::CREATE); $zip->addFromString('assets/run.js', 'alert(1)'); $zip->close();
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$scriptZip,'size'=>filesize($scriptZip),'name'=>'script.zip']); exit(1); } catch (ContentUploadException $expected) {}
foreach (glob($root . '*') ?: [] as $path) {
    if (is_file($path)) unlink($path);
}
foreach (glob($root . '/.staging/*') ?: [] as $path) if (is_file($path)) unlink($path);
if (is_dir($root . '/.staging')) rmdir($root . '/.staging');
if (is_dir($root)) rmdir($root);
