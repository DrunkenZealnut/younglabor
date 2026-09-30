<?php
require_once __DIR__ . '/../includes/SiteContent.php';
date_default_timezone_set('UTC');

function assertClub(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$club = siteClubRecruitment();
assertClub($club['name'] === '바이브코딩동아리', '동아리 명칭이 다릅니다.');
assertClub($club['apply_url'] === 'https://zealot-survey.vercel.app/RJXag60aMfMT', '신청서 URL이 다릅니다.');
assertClub(parse_url($club['apply_url'], PHP_URL_SCHEME) === 'https', '신청서 링크는 HTTPS여야 합니다.');
assertClub(parse_url($club['apply_url'], PHP_URL_HOST) === $club['apply_domain'], '표시 도메인이 링크 호스트와 다릅니다.');
assertClub($club['deadline'] === '2026-10-15', '모집 마감일이 다릅니다.');
assertClub(clubDeadlineLabel() === '10월 15일', '마감일 표기가 다릅니다.');

$seoul = new DateTimeZone('Asia/Seoul');
$utc = new DateTimeZone('UTC');
assertClub(clubRecruitmentIsOpen(new DateTimeImmutable('2026-09-28 09:00:00', $seoul)), '모집 기간에는 모집 중이어야 합니다.');
assertClub(clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-15 23:59:59', $seoul)), '마감일 23:59:59에는 모집 중이어야 합니다.');
assertClub(!clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-16 00:00:00', $seoul)), '마감 다음 날 0시에는 마감이어야 합니다.');
assertClub(clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-15 14:59:59', $utc)), 'UTC로 주어져도 서울 기준으로 판정해야 합니다.');
assertClub(!clubRecruitmentIsOpen(new DateTimeImmutable('2026-10-15 15:00:00', $utc)), 'UTC 15시는 서울 16일 0시이므로 마감이어야 합니다.');
clubRecruitmentIsOpen();
