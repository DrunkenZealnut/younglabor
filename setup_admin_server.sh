#!/bin/bash

# HOPEC Admin Server Setup Script
# This script configures XAMPP to serve the admin panel at hopec.local:8012/admin

echo "🚀 HOPEC Admin Server Setup"
echo "=========================="

# Load environment variables
if [ -f ".env" ]; then
    export $(grep -v '^#' .env | xargs)
fi

# Detect XAMPP installation
XAMPP_ROOT=""
POSSIBLE_XAMPP_PATHS=(
    "/Applications/XAMPP"
    "/opt/lampp"
    "C:/xampp"
    "$XAMPP_ROOT_ENV"
)

for path in "${POSSIBLE_XAMPP_PATHS[@]}"; do
    if [ -d "$path" ]; then
        XAMPP_ROOT="$path"
        break
    fi
done

# 1. Check if XAMPP is installed
if [ -z "$XAMPP_ROOT" ]; then
    echo "❌ XAMPP이 설치되지 않았습니다."
    echo "   https://www.apachefriends.org/download.html에서 XAMPP를 설치하세요."
    echo "   또는 XAMPP_ROOT 환경변수를 설정하세요."
    exit 1
fi

echo "✅ XAMPP 설치 확인됨: $XAMPP_ROOT"

# Determine config paths
HTTPD_CONF="$XAMPP_ROOT/etc/httpd.conf"
VHOST_CONF="$XAMPP_ROOT/etc/extra/httpd-vhosts.conf"

# Check for alternative paths
if [ ! -f "$HTTPD_CONF" ] && [ -f "$XAMPP_ROOT/apache/conf/httpd.conf" ]; then
    HTTPD_CONF="$XAMPP_ROOT/apache/conf/httpd.conf"
    VHOST_CONF="$XAMPP_ROOT/apache/conf/extra/httpd-vhosts.conf"
fi

# 2. Backup existing httpd.conf if it exists
if [ -f "$HTTPD_CONF" ]; then
    if [ ! -f "$HTTPD_CONF.backup" ]; then
        echo "🔄 기존 httpd.conf 백업 중..."
        sudo cp "$HTTPD_CONF" "$HTTPD_CONF.backup"
    fi
fi

# 3. Check if port 8012 is already configured
if grep -q "Listen 8012" "$HTTPD_CONF" 2>/dev/null; then
    echo "✅ 포트 8012가 이미 설정되어 있습니다."
else
    echo "🔧 포트 8012 추가 중..."
    echo "Listen 8012" | sudo tee -a "$HTTPD_CONF" >/dev/null
fi

# 4. Configure Virtual Host
echo "🔧 Virtual Host 설정 중..."
VHOST_CONFIG="
##
# HOPEC Local Development Server (Port 8012)
##
<VirtualHost *:8012>
    ServerName hopec.local
    ServerAlias www.hopec.local
    DocumentRoot \"/Users/zealnutkim/Documents/개발/hopec\"
    ErrorLog \"logs/hopec-error_log\"
    CustomLog \"logs/hopec-access_log\" common
    
    <Directory \"/Users/zealnutkim/Documents/개발/hopec\">
        Options Indexes FollowSymLinks Includes ExecCGI
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Default localhost for port 8012
<VirtualHost *:8012>
    ServerName localhost
    DocumentRoot \"/Applications/XAMPP/htdocs\"
    ErrorLog \"logs/localhost-8012-error_log\"
    CustomLog \"logs/localhost-8012-access_log\" common
</VirtualHost>
"

# Check if virtual host already exists
if ! grep -q "HOPEC Local Development Server" /Applications/XAMPP/etc/extra/httpd-vhosts.conf 2>/dev/null; then
    echo "$VHOST_CONFIG" | sudo tee -a /Applications/XAMPP/etc/extra/httpd-vhosts.conf >/dev/null
    echo "✅ Virtual Host 설정 완료"
else
    echo "✅ Virtual Host가 이미 설정되어 있습니다."
fi

# 5. Update hosts file for hopec.local
if ! grep -q "hopec.local" /etc/hosts; then
    echo "🔧 /etc/hosts 파일에 hopec.local 추가 중..."
    echo "127.0.0.1    hopec.local" | sudo tee -a /etc/hosts >/dev/null
    echo "✅ hosts 파일 업데이트 완료"
else
    echo "✅ hopec.local이 이미 hosts 파일에 있습니다."
fi

# 6. Check XAMPP status and restart if needed
echo "🔄 XAMPP 서비스 확인 중..."
if pgrep -f "httpd" > /dev/null; then
    echo "🔄 Apache 재시작 중..."
    sudo /Applications/XAMPP/xamppfiles/bin/apachectl restart
else
    echo "🚀 Apache 시작 중..."
    sudo /Applications/XAMPP/xamppfiles/bin/apachectl start
fi

echo ""
echo "🎉 설정 완료!"
echo "=========================="
echo "이제 다음 주소로 접속하실 수 있습니다:"
echo ""
echo "🌐 메인 사이트: http://hopec.local:8012"
echo "⚙️  관리자 페이지: http://hopec.local:8012/admin"
echo ""
echo "📝 참고사항:"
echo "   - 관리자 페이지는 로그인이 필요할 수 있습니다"
echo "   - 문제가 발생하면 XAMPP를 재시작해보세요"
echo "   - 로그는 /Applications/XAMPP/logs/에서 확인할 수 있습니다"