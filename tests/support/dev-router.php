<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$prefix='/younglabor';
if(!str_starts_with($path,$prefix)){http_response_code(404);exit;}
$relative=substr($path,strlen($prefix));$root=dirname(__DIR__,2);
if(preg_match('~(?:^|/)\.|^/(?:includes|docs|tests|scripts|database|config\.php)~',$relative)){http_response_code(403);exit;}
$rules=['~^/work/?$~'=>'work/index.php','~^/work/areas/([a-z0-9-]+)/?$~'=>'work/area.php','~^/work/programs/([a-z0-9-]+)/?$~'=>'work/program.php','~^/work/incubating/(\d{4})/?$~'=>'work/incubating.php','~^/youth-labor/?$~'=>'youth-labor/index.php','~^/youth-labor/([a-z0-9-]+)/?$~'=>'youth-labor/view.php','~^/board/?$~'=>'board/index.php','~^/board/([a-z0-9-]+)/?$~'=>'board/view.php','~^/activity/([a-z0-9-]+)/?$~'=>'activity/view.php','~^/resources/([a-z0-9-]+)/?$~'=>'resources/view.php','~^/media/(\d+)/?$~'=>'media.php','~^/downloads/(\d+)/?$~'=>'download.php'];
$file=null;foreach($rules as $pattern=>$target)if(preg_match($pattern,$relative,$m)){$file=$target;if(isset($m[1]))$_GET[str_contains($target,'incubating')?'year':(str_contains($target,'media')||str_contains($target,'download')?'id':'slug')]=$m[1];break;}
if(!$file){$clean=ltrim($relative,'/');if($clean==='' || is_dir($root.'/'.$clean))$file=rtrim($clean,'/').($clean===''?'index.php':'/index.php');elseif(is_file($root.'/'.$clean))$file=$clean;else $file=$clean.'.php';}
if(!is_file($root.'/'.$file)){http_response_code(404);echo 'Not found';exit;}
if(!str_ends_with($file,'.php')){$types=['css'=>'text/css','js'=>'application/javascript','png'=>'image/png','svg'=>'image/svg+xml','webp'=>'image/webp'];header('Content-Type: '.($types[pathinfo($file,PATHINFO_EXTENSION)]??'application/octet-stream'));readfile($root.'/'.$file);exit;}
$_SERVER['SCRIPT_NAME']='/younglabor/'.$file;require $root.'/'.$file;
