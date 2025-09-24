#!/bin/bash

# 희망씨 웹사이트 XAMPP 서버 시작 스크립트

echo "🚀 희망씨 웹사이트 XAMPP 서버를 시작합니다..."

# XAMPP 설치 확인
if [ ! -d "/Applications/XAMPP" ]; then
    echo "❌ XAMPP가 설치되어 있지 않습니다."
    echo "   https://www.apachefriends.org/download.html 에서 XAMPP를 다운로드하여 설치하세요."
    exit 1
fi

echo "✅ XAMPP 설치 확인됨"

# VirtualHost 설정 확인
VHOST_FILE="/Applications/XAMPP/etc/extra/httpd-vhosts.conf"
if ! grep -q "hopec.local" "$VHOST_FILE" 2>/dev/null; then
    echo "⚠️  VirtualHost 설정이 필요합니다."
    echo "   XAMPP_SETUP_GUIDE.md 파일을 참조하여 설정을 완료하세요."
    echo ""
fi

# Apache 상태 확인
if pgrep -f "/Applications/XAMPP/bin/httpd" > /dev/null; then
    echo "✅ Apache가 이미 실행 중입니다."
else
    echo "🔄 Apache를 시작합니다..."
    sudo /Applications/XAMPP/bin/httpd -D FOREGROUND &
    sleep 3
fi

# MySQL 상태 확인
if pgrep -f "/Applications/XAMPP/bin/mysqld" > /dev/null; then
    echo "✅ MySQL이 이미 실행 중입니다."
else
    echo "🔄 MySQL을 시작합니다..."
    sudo /Applications/XAMPP/bin/mysql.server start
    sleep 2
fi

echo ""
echo "📍 접속 주소:"
echo "   - 메인 사이트: http://hopec.local:8012"
echo "   - XAMPP 대시보드: http://localhost:8012"
echo "   - phpMyAdmin: http://localhost/phpmyadmin (기본 포트 80)"
echo "   - 데이터베이스 관리: http://hopec.local:8012/db_manager.php"
echo ""
echo "💡 관리자 비밀번호: hopec2024!"
echo ""
echo "🔧 XAMPP Control Panel을 열려면:"
echo "   open /Applications/XAMPP/manager-osx.app"
echo ""
echo "📚 설정 가이드: XAMPP_SETUP_GUIDE.md 참조"