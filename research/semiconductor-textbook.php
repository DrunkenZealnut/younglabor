<?php
// 반도체고등학교 노동안전교과서 제작을 위한 기초연구보고서 소개 페이지 (2026.11)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/PageTracker.php';
PageTracker::track('반도체고 노동안전교과서 기초연구');

$reportPdfPath = 'assets/files/semiconductor-textbook-basic-research-2026.pdf';
$reportPdfFile = __DIR__ . '/../' . $reportPdfPath;
$reportPdfSize = is_file($reportPdfFile) ? round(filesize($reportPdfFile) / 1048576, 1) . 'MB' : '';

$reportFacts = [
    '연구기관' => '청년노동자인권센터',
    '연구책임자' => '김창수 (청년노동자인권센터 대표)',
    '책임연구자' => '박동욱 (한국방송통신대학교 교수)',
    '교정' => '박내현 (청년노동자인권센터 운영위원)',
    '지원' => '아름다운재단',
    '발행' => '2026년 11월 · 72쪽',
];

$reportStats = [
    ['value' => '9.5%', 'label' => '반도체고 전공교과서 9권(2,055쪽)에서 찾은 안전보건 표현 1,207건 가운데, 구체적인 예방조치와 작업방법을 알려 주는 내용의 비율'],
    ['value' => '0건', 'label' => '같은 교과서에 실린 실제 사고 사례. 추락·공정안전관리·직업병·물질안전보건자료(MSDS)라는 말도 한 번도 나오지 않았습니다'],
    ['value' => '39.6%', 'label' => '반도체 공장에서 일하는 직업계고 출신 노동자 48명 가운데 고등학교 때 안전보건교육을 전혀 받지 못했다고 답한 비율'],
    ['value' => '10.4%', 'label' => '같은 응답자 가운데 반도체·전자산업의 유해요인을 배운 적이 있다고 답한 비율'],
];

$reportFindings = [
    ['title' => '반도체고 전공교과서', 'body' => '전공교과서 9권, 2,055쪽에서 사고·부상·질병과 관련된 30개 키워드를 찾았습니다. 26개 키워드에서 1,207건이 나왔지만 \'안전\'(363건), \'보호구\'(103건), \'위험\'(101건)처럼 일반적인 말이 대부분이었습니다. 공정기술 교육과 실제 사고·질병 예방 교육이 이어지지 않았습니다.'],
    ['title' => 'NCS 반도체 교육자료', 'body' => '개발·제조·장비·재료 분야 NCS 교육자료 86종, 9,100쪽을 같은 방법으로 분석했습니다. 현장 직무 중심이라 교과서보다 안전보건 내용은 많았지만, 분야와 교재마다 차이가 컸고 \'안전에 유의한다\', \'보호구를 착용한다\' 같은 선언적인 표현이 상당 부분을 차지했습니다.'],
    ['title' => '사고·질병 사례 교육', 'body' => '반도체 현장에서 실제로 일어난 사고와 직업병을 \'원인 → 노출·사고 과정 → 피해 → 예방대책\' 순서로 배울 수 있는 사례 교육이 부족했습니다. 화학물질 누출·중독, 질식, 화재·폭발, 감전, 끼임, 방사선 노출 사례를 교육 내용과 연결해야 합니다.'],
    ['title' => '학생 대상 안전보건교육의 효과', 'body' => '국내외 문헌은 일터에 들어가기 전부터 안전보건교육을 시작해야 하고, 지식을 외우는 것보다 실제 위험 상황과 예방조치를 연결하는 참여형 교육이 효과적이라고 말합니다. 화학물질 교육도 GHS 표지와 물질안전보건자료(SDS) 확인, 노출 경로, 보호구, 응급대응을 실제 상황과 함께 배워야 합니다.'],
    ['title' => '해외 기술계 고등학교', 'body' => '미국 직업기술교육(CTE)은 산업안전·제조·전자기술 과목과 현장학습에 안전보건을 담고, 일터에 들어가기 전 OSHA 10시간 기초안전교육을 운영합니다. 독일·스위스·오스트리아·스웨덴·덴마크·핀란드·일본은 안전보건을 전공 기술교과와 실습, 기업 현장훈련 안에 녹여 가르칩니다.'],
    ['title' => '반도체 노동자 설문', 'body' => '반도체 공장에서 일하는 직업계고 출신 노동자 48명(대부분 경력 5년 이상, 설비 유지보수 직무)에게 물었습니다. 고교 시절 안전보건교육은 \'전혀 없었다\' 39.6%, \'가끔 있었다\' 37.5%였습니다. 조사 결과, 공정별 위험요인과 화학물질·가스, 장비 안전, 보호구, 사고대응처럼 실제 직무와 이어진 안전보건교육을 일터에 오기 전에 강화해야 한다는 점을 확인했습니다.'],
];

$reportChapters = [
    '반도체산업의 가치와 사회적 역할',
    '반도체산업의 주요 생산품',
    '반도체산업과 산업 안전보건',
    '산업재해와 직업병 이해',
    '반도체산업 제조공정 이해',
    '반도체산업 작업환경과 클린룸',
    '반도체산업 공정의 위험 요인',
    '반도체산업 공정 주요 화학물질과 건강 영향',
    '화학물질 안전관리(GHS, MSDS)',
    '반도체산업 사고·부상 예방',
    '반도체산업의 주요 공정 설비 안전과 위험관리',
    '반도체산업 공정에서 응급 상황과 사고대응',
    '개인보호구와 작업자 보호',
    '사고·부상과 질병 예방을 위한 국가의 법령, 기업의 책임과 노동자의 권리와 의무',
    '미래 반도체산업과 안전보건 문화',
];

$reportNextSteps = [
    '15장 목차(안)를 바탕으로 실제 교재 내용과 학습활동을 개발합니다.',
    '공정마다 \'작업내용 → 위험요인 → 노출·사고 과정 → 건강영향·부상 → 예방조치 → 비상대응\'으로 이어지는 학습 구조를 만듭니다.',
    '국내 반도체 현장에서 실제로 일어난 사고와 직업병을 학생 눈높이에 맞는 사례학습으로 만듭니다.',
    'GHS·SDS, 위험성평가, LOTO(잠금·표지), 인터록, 보호구, 응급대응을 학생이 직접 확인하고 판단하는 활동으로 만듭니다.',
    '반도체 공정 전문가와 반도체고 교사가 함께 쓰고, 감수와 학생 적용 평가로 검증합니다.',
];

$currentPage = 'resources';
$pageTitle = '반도체고등학교 노동안전교과서 기초연구 - ' . $site['name'];
$pageDescription = '반도체고 전공교과서와 NCS 교육자료의 안전보건 내용을 분석하고, 반도체고 안전보건 교과서 15장 목차(안)를 제안한 기초연구 보고서입니다.';
$pageUrl = url('research/semiconductor-textbook');
require_once __DIR__ . '/../includes/header.php';

function reportEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<section class="page-header report-header">
    <div class="container">
        <p class="eyebrow">연구 보고서 · 2026.11</p>
        <h1>반도체고 교과서에<br>안전은 얼마나 담겨 있을까요?</h1>
        <p class="report-lead">『반도체고등학교 노동안전교과서 제작을 위한 기초연구보고서』는 반도체고 전공교과서와 NCS 교육자료를 분석하고, 해외 사례와 반도체 노동자의 경험을 모아 반도체고 학생을 위한 안전보건 교과서의 방향과 목차(안)를 제안합니다.</p>
        <div class="hero-actions">
            <a class="btn-cta" href="<?php echo reportEscape(assetUrl($reportPdfPath)); ?>" download="반도체고등학교_노동안전교과서_기초연구보고서_2026.pdf">보고서 PDF 내려받기<?php echo $reportPdfSize !== '' ? ' (' . reportEscape($reportPdfSize) . ')' : ''; ?> <span aria-hidden="true">↓</span></a>
            <a class="arrow-link" href="#findings">주요 결과 먼저 보기 ↓</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split-intro">
        <h2 class="section-title">왜 이 연구를 했나요</h2>
        <div class="report-prose">
            <p class="statement">반도체 공장은 '클린룸'이지만, 그 '깨끗함'은 제품을 위한 것이지 노동자의 건강을 뜻하지 않습니다.</p>
            <p>반도체 공정에서는 수많은 화학물질과 특수가스, 고전압·진공·자동화 설비를 다룹니다. 2007년 삼성전자 반도체 공장 노동자의 백혈병 사망 이후 직업병 문제가 사회적으로 드러났고, 2018년 이산화탄소 누출로 하청 노동자가 숨졌으며, 2024년 엑스선 검사장비 방사선 노출, 2025년 가스 누출 사고가 이어졌습니다.</p>
            <p>반도체고를 졸업한 많은 학생이 공정 운영자와 장비 기술직으로 이 현장에 들어갑니다. 일터에 가기 전, 학교에서 무엇을 배우고 있는지 살펴보고 무엇을 더 배워야 하는지 정리하려고 이 연구를 시작했습니다.</p>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-heading"><div><p class="eyebrow">숫자로 본 결과</p><h2>위험은 구체적인데,<br>교과서는 막연합니다.</h2></div></div>
        <div class="report-stats">
            <?php foreach ($reportStats as $stat): ?>
            <div class="report-stat">
                <strong><?php echo reportEscape($stat['value']); ?></strong>
                <p><?php echo reportEscape($stat['label']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt" id="findings">
    <div class="container">
        <div class="section-heading"><div><p class="eyebrow">주요 연구결과</p><h2>여섯 갈래로 살펴본 것</h2></div></div>
        <ol class="report-findings">
            <?php foreach ($reportFindings as $index => $finding): ?>
            <li>
                <span class="report-number"><?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?></span>
                <h3><?php echo reportEscape($finding['title']); ?></h3>
                <p><?php echo reportEscape($finding['body']); ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <div>
            <p class="eyebrow">제안</p>
            <h2 class="section-title-lg">반도체고 안전보건 교과서 15장 목차(안)</h2>
            <p class="report-note">반도체산업과 작업환경에서 시작해 공정, 위험요인, 화학물질, 사고 예방, 응급대응, 보호구, 법과 권리, 안전보건 문화로 이어지는 흐름입니다. 공정별 위험요인, 직업병 예방, 위험성평가, LOTO, 실제 사고 사례처럼 지금 교재에 빠진 내용을 채우고, 학생이 위험을 스스로 알아보고 예방행동을 실천하도록 하는 데 초점을 맞췄습니다.</p>
        </div>
        <ol class="report-chapters">
            <?php foreach ($reportChapters as $index => $chapter): ?>
            <li><span>제<?php echo $index + 1; ?>장</span><?php echo reportEscape($chapter); ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section-alt">
    <div class="container split-intro">
        <h2 class="section-title">결론과 다음 과제</h2>
        <div class="report-prose">
            <p class="statement">지금 교과서와 NCS 자료에도 안전이라는 말은 있지만, 공정별 사고·질병 위험을 구체적인 예방조치와 이어 주는 교육은 부족합니다.</p>
            <p>반도체산업에 들어갈 학생이 위험을 알아보고 스스로 사고와 질병을 예방할 수 있도록, 반도체 기술교육과 안전보건교육을 하나로 묶은 전문 교재가 필요합니다. 보고서는 후속 교재 개발에서 다음을 제안합니다.</p>
            <ul class="plain-list">
                <?php foreach ($reportNextSteps as $step): ?>
                <li><?php echo reportEscape($step); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section report-download">
    <div class="container report-download-layout">
        <div>
            <p class="eyebrow">보고서 전문</p>
            <h2>반도체고등학교 노동안전교과서<br class="desktop-break"> 제작을 위한 기초연구보고서</h2>
            <p>교육과 연구에 활용하거나 인용할 때는 출처를 밝혀 주세요.</p>
            <a class="btn-cta" href="<?php echo reportEscape(assetUrl($reportPdfPath)); ?>" download="반도체고등학교_노동안전교과서_기초연구보고서_2026.pdf">PDF 내려받기<?php echo $reportPdfSize !== '' ? ' (' . reportEscape($reportPdfSize) . ')' : ''; ?> <span aria-hidden="true">↓</span></a>
            <p class="report-contact">교재 개발 협력이나 학교 교육 문의는 <a href="mailto:admin@younglabor.kr">admin@younglabor.kr</a>로 보내 주세요.</p>
        </div>
        <dl class="program-facts">
            <?php foreach ($reportFacts as $label => $value): ?>
            <div><dt><?php echo reportEscape($label); ?></dt><dd><?php echo reportEscape($value); ?></dd></div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
