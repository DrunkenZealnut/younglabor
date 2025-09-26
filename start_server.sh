#!/bin/bash

# 희망씨 웹사이트 로컬 서버 시작 스크립트

echo "🚀 희망씨 웹사이트 로컬 서버를 시작합니다..."

# MySQL 서비스 상태 확인 및 시작
echo "📊 MySQL 서비스 확인 중..."
if ! brew services list | grep -q "mysql@8.0.*started"; then
    echo "🔄 MySQL 서비스를 시작합니다..."
    brew services start mysql@8.0
    sleep 3
else
    echo "✅ MySQL이 이미 실행 중입니다."
fi

# younglabor 프로젝트 디렉토리로 설정
younglabor_DIR="/Users/zealnutkim/Documents/개발/younglabor"
echo "📁 웹 루트 디렉토리: $younglabor_DIR"

# PHP 내장 서버 시작
echo "🌐 PHP 내장 서버를 시작합니다..."
echo "📍 접속 주소:"
echo "   - 메인 사이트: http://younglabor.local:8013"
echo "   - 대체 주소: http://localhost:8013"
echo "   - 데이터베이스 관리: http://younglabor.local:8013/db_manager.php"
echo ""
echo "💡 관리자 비밀번호: younglabor2024!"
echo ""
echo "🛑 서버를 중지하려면 Ctrl+C를 누르세요."
echo ""

# PHP 서버 시작
php -S 0.0.0.0:8013 -t "$younglabor_DIR"