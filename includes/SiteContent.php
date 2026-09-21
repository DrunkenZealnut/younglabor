<?php

function siteWorkAreas(): array
{
    return [
        [
            'key' => 'field',
            'title' => '현장 조직',
            'summary' => '학교 방문, 교사 만남, 캠페인과 동아리로 관계를 만듭니다.',
            'purpose' => '청년이 일을 시작하기 전부터 안전을 말할 사람과 연결합니다.',
            'activities' => ['반도체고 방문과 교사 만남', '커피차 캠페인', '청소년 노동안전 동아리'],
            'href' => 'activity',
            'link_label' => '현장 활동 보기',
        ],
        [
            'key' => 'education',
            'title' => '교육',
            'summary' => '일을 시작하기 전에 위험을 알고 질문할 수 있게 합니다.',
            'purpose' => '안전 지식을 권리를 지키는 말과 행동으로 이어 줍니다.',
            'activities' => ['일하는 열아홉 강좌', '반도체 산업과 유해인자 교육', '교사와 함께 만드는 수업 자료'],
            'href' => 'resources',
            'link_label' => '교육 자료 보기',
        ],
        [
            'key' => 'tools',
            'title' => '안전 도구',
            'summary' => '학교와 일터에서 필요한 안전·노동 정보를 쉽게 찾게 합니다.',
            'purpose' => '복잡한 안전 정보와 노동 기준을 시민이 직접 확인할 수 있게 합니다.',
            'activities' => ['SafeFactory 산업안전 학습 도구', '기초 노동상담과 임금계산', 'MSDS와 안전 자료 탐색'],
            'href' => 'tools',
            'link_label' => '안전 도구 보기',
        ],
        [
            'key' => 'research',
            'title' => '연구',
            'summary' => '현장 경험을 근거로 만들고 교육과 제도 개선에 연결합니다.',
            'purpose' => '학교와 제조업 현장의 문제를 기록하고 바꿀 근거를 쌓습니다.',
            'activities' => ['반도체고·산업 기초연구', '현장 경험과 사례 기록', '교육·제도 개선 제안'],
            'href' => 'resources',
            'link_label' => '연구 자료 보기',
        ],
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

function siteImpactStats(): array
{
    return [
        ['value' => '6곳', 'label' => '반도체고 현장 접촉'],
        ['value' => '4회', 'label' => '일하는 열아홉 강좌'],
    ];
}

function siteSupportPartners(): array
{
    return [[
        'name' => '아름다운재단',
        'relationship' => '지원',
        'program' => '2025 공익단체 인큐베이팅 지원사업',
    ]];
}
