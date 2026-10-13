<?php

function siteWorkAreas(): array
{
    return [
        ['key'=>'education','title'=>'안전교육·교과서','summary'=>'처음 일하는 사람의 안전을 배우다','href'=>'work/areas/education','link_label'=>'자세히 보기'],
        ['key'=>'tools','title'=>'온라인 도구','summary'=>'일터에서 권리와 안전을 챙기는 도구','href'=>'work/areas/tools','link_label'=>'자세히 보기'],
        ['key'=>'community','title'=>'연결·모임','summary'=>'고민을 함께 풀 사람을 만나다','href'=>'work/areas/community','link_label'=>'자세히 보기'],
        ['key'=>'voice','title'=>'목소리·정책','summary'=>'보이지 않는 노동을 사회의 의제로','href'=>'work/areas/voice','link_label'=>'자세히 보기'],
    ];
}

function siteTools(): array
{
    return [
        [
            'name' => 'SafeFactory',
            'status' => '제작 중',
            'url' => 'https://safefactory.kr/',
            'domain' => 'safefactory.kr',
            'tagline' => '반도체와 산업안전을 질문하고 배우는 AI 학습 플랫폼',
            'audience' => '반도체 분야 특성화고 학생과 교사, 산업안전을 배우려는 청년',
            'description' => 'NCS 반도체 자료, 안전교육 자료, KOSHA 가이드와 MSDS를 한곳에서 검색하고 질문할 수 있습니다.',
            'cta' => 'SafeFactory 살펴보기',
        ],
        [
            'name' => '기초 노동상담',
            'status' => '제작 중',
            'url' => 'https://laborconsult.vercel.app/',
            'domain' => 'laborconsult.vercel.app',
            'tagline' => '법령과 판례의 근거를 함께 보여주는 AI 노동상담·임금계산 도구',
            'audience' => '기초 노동 문제를 확인하려는 청년노동자와 시민',
            'description' => '근로기준법, 판례와 행정해석을 근거로 상담 정보를 제공하고 28종의 임금 계산을 돕습니다.',
            'cta' => '기초 노동상담 사용해보기',
        ],
    ];
}

function siteSupportPartners(): array
{
    return [[
        'name' => '아름다운재단',
        'relationship' => '지원',
        'program' => '공익단체 인큐베이팅 지원사업',
    ]];
}

function siteClubRecruitment(?callable $repositoryFactory = null): array
{
    require_once __DIR__.'/Database.php';require_once __DIR__.'/WorkRepository.php';
    try {
        $repository=$repositoryFactory ? $repositoryFactory() : new WorkRepository(Database::getInstance()->getConnection());
        $p=$repository->findProgram('vibecoding-club');
        if($p)return $p+['name'=>$p['title'],'apply_url'=>$p['application_url'],'apply_domain'=>parse_url($p['application_url']??'',PHP_URL_HOST)?:''];
    }catch(Throwable $e){}
    return ['name'=>'바이브코딩동아리','status'=>'draft','recruitment_status'=>'pending','recruitment_mode'=>'rolling','deadline_at'=>null,'apply_url'=>'','apply_domain'=>'','application_url'=>''];
}
function clubRecruitmentIsOpen(?DateTimeImmutable $now=null,?array $club=null): bool
{
    require_once __DIR__.'/WorkPresenter.php';
    return programAcceptsApplications($club??siteClubRecruitment(),$now);
}
