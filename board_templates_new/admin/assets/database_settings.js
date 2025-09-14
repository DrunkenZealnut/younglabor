/**
 * Board Templates 데이터베이스 설정 페이지 JavaScript
 * 
 * 기능:
 * - 데이터베이스 연결 테스트
 * - 실시간 설정 검증
 * - 테이블명 자동 생성
 * - 설정 가져오기/내보내기
 * - 시스템 상태 모니터링
 */

class DatabaseSettingsManager {
    constructor() {
        this.init();
    }

    /**
     * 초기화
     */
    init() {
        this.bindEvents();
        this.initializeUI();
        this.loadSystemStatus();
        this.startPeriodicCheck();
    }

    /**
     * 이벤트 바인딩
     */
    bindEvents() {
        // 데이터베이스 연결 테스트
        const testConnectionBtn = document.getElementById('testConnectionBtn');
        if (testConnectionBtn) {
            testConnectionBtn.addEventListener('click', () => this.testDatabaseConnection());
        }

        // 비밀번호 표시/숨김
        const togglePasswordBtn = document.getElementById('togglePassword');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', () => this.togglePassword());
        }

        // 테이블 접두사 미리보기
        const tablePrefixInput = document.getElementById('table_prefix');
        if (tablePrefixInput) {
            tablePrefixInput.addEventListener('input', () => this.updateTablePreview());
        }

        // 테이블명 자동 생성
        const generateTablesBtn = document.getElementById('generateTablesBtn');
        if (generateTablesBtn) {
            generateTablesBtn.addEventListener('click', () => this.generateTableNames());
        }

        // 테이블 존재 확인
        const validateTablesBtn = document.getElementById('validateTablesBtn');
        if (validateTablesBtn) {
            validateTablesBtn.addEventListener('click', () => this.validateTables());
        }

        // 설정 내보내기/가져오기
        const exportConfigBtn = document.getElementById('exportConfigBtn');
        if (exportConfigBtn) {
            exportConfigBtn.addEventListener('click', () => this.exportConfiguration());
        }

        const importConfigBtn = document.getElementById('importConfigBtn');
        if (importConfigBtn) {
            importConfigBtn.addEventListener('click', () => this.importConfiguration());
        }

        // 설정 백업
        const backupConfigBtn = document.getElementById('backupConfigBtn');
        if (backupConfigBtn) {
            backupConfigBtn.addEventListener('click', () => this.backupConfiguration());
        }

        // 로그 보기
        const viewLogsBtn = document.getElementById('viewLogsBtn');
        if (viewLogsBtn) {
            viewLogsBtn.addEventListener('click', () => this.viewLogs());
        }

        // 파일 업로드 처리
        const configFileInput = document.getElementById('configFileInput');
        if (configFileInput) {
            configFileInput.addEventListener('change', (e) => this.handleFileImport(e));
        }

        // 폼 검증
        this.bindFormValidation();
    }

    /**
     * UI 초기화
     */
    initializeUI() {
        // 테이블 접두사 미리보기 업데이트
        this.updateTablePreview();
        
        // 툴팁 초기화
        this.initializeTooltips();
        
        // 진행률 표시바 숨김
        this.hideAllProgressBars();
    }

    /**
     * 데이터베이스 연결 테스트
     */
    async testDatabaseConnection() {
        const btn = document.getElementById('testConnectionBtn');
        const spinner = btn.querySelector('.spinner-border');
        const statusDiv = document.getElementById('connectionStatus');
        
        // UI 상태 변경
        btn.disabled = true;
        spinner.classList.remove('d-none');
        this.updateConnectionStatus('testing', '연결 테스트 중...');

        try {
            const formData = new FormData();
            formData.append('action', 'test_connection');
            formData.append('db_host', document.getElementById('db_host').value);
            formData.append('db_port', document.getElementById('db_port').value);
            formData.append('db_name', document.getElementById('db_name').value);
            formData.append('db_user', document.getElementById('db_user').value);
            formData.append('db_password', document.getElementById('db_password').value);
            formData.append('db_charset', document.getElementById('db_charset').value);

            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            // 응답이 JSON인지 확인
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('서버에서 올바르지 않은 응답을 받았습니다.');
            }

            const result = await response.json();

            if (result.success) {
                this.updateConnectionStatus('connected', '연결 성공');
                this.showAlert('success', `연결 테스트 성공: ${result.message}`);
                
                if (result.details) {
                    console.log('Database Details:', result.details);
                }
            } else {
                this.updateConnectionStatus('disconnected', '연결 실패');
                this.showAlert('error', `연결 테스트 실패: ${result.message}`);
            }
        } catch (error) {
            this.updateConnectionStatus('disconnected', '연결 오류');
            this.showAlert('error', `연결 테스트 중 오류 발생: ${error.message}`);
        } finally {
            // UI 상태 복원
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    /**
     * 연결 상태 업데이트
     */
    updateConnectionStatus(status, message) {
        const statusDiv = document.getElementById('connectionStatus');
        if (!statusDiv) return;

        statusDiv.className = `connection-status ${status}`;
        statusDiv.innerHTML = `
            <i class="bi bi-circle-fill"></i>
            <span>${message}</span>
        `;
    }

    /**
     * 비밀번호 표시/숨김 토글
     */
    togglePassword() {
        const passwordInput = document.getElementById('db_password');
        const toggleBtn = document.getElementById('togglePassword');
        const icon = toggleBtn.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    /**
     * 테이블 접두사 미리보기 업데이트
     */
    updateTablePreview() {
        const prefixInput = document.getElementById('table_prefix');
        const previewDiv = document.getElementById('tablePreview');
        
        if (!prefixInput || !previewDiv) return;

        const prefix = prefixInput.value || 'bt_';
        const sampleTables = ['posts', 'categories', 'attachments', 'comments'];
        
        const preview = sampleTables.map(table => `${prefix}${table}`).join(', ');
        previewDiv.textContent = preview;
    }

    /**
     * 테이블명 자동 생성
     */
    generateTableNames() {
        const prefix = document.getElementById('table_prefix').value || 'bt_';
        const tableNames = ['posts', 'categories', 'attachments', 'comments'];

        tableNames.forEach(tableName => {
            const input = document.getElementById(`table_${tableName}`);
            if (input) {
                input.value = prefix + tableName;
            }
        });

        // 사용자 및 게시판 테이블은 기본값 유지 또는 프로젝트별 설정
        const userTableInput = document.getElementById('table_users');
        const boardTableInput = document.getElementById('table_boards');
        
        if (userTableInput && !userTableInput.value) {
            userTableInput.value = 'edu_users'; // UDONG 프로젝트 기본값
        }
        
        if (boardTableInput && !boardTableInput.value) {
            boardTableInput.value = 'labor_rights_boards'; // UDONG 프로젝트 기본값
        }

        this.showAlert('info', '테이블명이 자동으로 생성되었습니다.');
    }

    /**
     * 테이블 존재 확인
     */
    async validateTables() {
        const btn = document.getElementById('validateTablesBtn');
        
        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>확인 중...';

            const response = await fetch('api/validate_tables.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'validate_tables'
                })
            });

            const result = await response.json();

            if (result.success) {
                this.displayTableValidationResults(result.tables);
                this.showAlert('success', result.message);
            } else {
                this.showAlert('error', `테이블 확인 실패: ${result.message}`);
            }
        } catch (error) {
            this.showAlert('error', `테이블 확인 중 오류 발생: ${error.message}`);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-2"></i>테이블 존재 확인';
        }
    }

    /**
     * 테이블 검증 결과 표시
     */
    displayTableValidationResults(tables) {
        let resultsHtml = '<div class="mt-3"><h6>테이블 존재 확인 결과</h6><div class="list-group">';
        
        Object.entries(tables).forEach(([key, table]) => {
            const statusClass = table.exists ? 'success' : 'danger';
            const statusIcon = table.exists ? 'check-circle' : 'x-circle';
            
            resultsHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${table.table_name}</strong>
                        <small class="text-muted d-block">${key}</small>
                    </div>
                    <span class="badge bg-${statusClass}">
                        <i class="bi bi-${statusIcon} me-1"></i>
                        ${table.status}
                    </span>
                </div>
            `;
        });
        
        resultsHtml += '</div></div>';
        
        // 결과를 적절한 위치에 표시
        const tablesForm = document.getElementById('tablesForm');
        const existingResults = tablesForm.querySelector('.validation-results');
        
        if (existingResults) {
            existingResults.remove();
        }
        
        const resultsDiv = document.createElement('div');
        resultsDiv.className = 'validation-results';
        resultsDiv.innerHTML = resultsHtml;
        
        tablesForm.appendChild(resultsDiv);
    }

    /**
     * 설정 내보내기
     */
    async exportConfiguration() {
        try {
            const response = await fetch('api/export_config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'export_config'
                })
            });

            const result = await response.json();

            if (result.success) {
                // 파일 다운로드 트리거
                this.downloadFile(result.data, result.filename, 'application/json');
                this.showAlert('success', '설정을 내보냈습니다.');
            } else {
                this.showAlert('error', `설정 내보내기 실패: ${result.message}`);
            }
        } catch (error) {
            this.showAlert('error', `설정 내보내기 중 오류 발생: ${error.message}`);
        }
    }

    /**
     * 설정 가져오기 트리거
     */
    importConfiguration() {
        const fileInput = document.getElementById('configFileInput');
        fileInput.click();
    }

    /**
     * 파일 가져오기 처리
     */
    async handleFileImport(event) {
        const file = event.target.files[0];
        if (!file) return;

        try {
            const fileContent = await this.readFileAsText(file);
            
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'import_config',
                    config_data: fileContent
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', '설정을 가져왔습니다. 페이지를 새로고침합니다.');
                setTimeout(() => location.reload(), 2000);
            } else {
                this.showAlert('error', `설정 가져오기 실패: ${result.message}`);
            }
        } catch (error) {
            this.showAlert('error', `파일 처리 중 오류 발생: ${error.message}`);
        }
    }

    /**
     * 설정 백업
     */
    async backupConfiguration() {
        const btn = document.getElementById('backupConfigBtn');
        
        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>백업 중...';

            const response = await fetch('api/backup_config.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'backup_config'
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', `백업 완료: ${result.backup_file}`);
            } else {
                this.showAlert('error', `백업 실패: ${result.message}`);
            }
        } catch (error) {
            this.showAlert('error', `백업 중 오류 발생: ${error.message}`);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-plus me-2"></i>설정 백업';
        }
    }

    /**
     * 시스템 상태 로드
     */
    async loadSystemStatus() {
        const statusContainer = document.getElementById('systemStatus');
        
        try {
            const response = await fetch('api/system_status.php');
            const result = await response.json();

            if (result.success) {
                this.displaySystemStatus(result.status);
            } else {
                statusContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            시스템 상태를 불러올 수 없습니다: ${result.message}
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            statusContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        시스템 상태 확인 중 오류 발생: ${error.message}
                    </div>
                </div>
            `;
        }
    }

    /**
     * 시스템 상태 표시
     */
    displaySystemStatus(status) {
        const statusContainer = document.getElementById('systemStatus');
        let html = '';

        Object.entries(status.checks).forEach(([key, check]) => {
            const badgeClass = check.status === 'ok' ? 'success' : 
                             check.status === 'warning' ? 'warning' : 'danger';
            const icon = check.status === 'ok' ? 'check-circle' : 
                        check.status === 'warning' ? 'exclamation-triangle' : 'x-circle';

            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title mb-1">${check.name}</h6>
                                    <p class="card-text small mb-0">${check.message}</p>
                                    ${check.path ? `<small class="text-muted">${check.path}</small>` : ''}
                                </div>
                                <span class="badge bg-${badgeClass}">
                                    <i class="bi bi-${icon}"></i>
                                </span>
                            </div>
                            ${check.details ? this.formatStatusDetails(check.details) : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        statusContainer.innerHTML = html;
    }

    /**
     * 상태 세부사항 포맷팅
     */
    formatStatusDetails(details) {
        if (typeof details !== 'object') return '';
        
        let html = '<div class="mt-2 pt-2 border-top"><small>';
        
        Object.entries(details).forEach(([key, value]) => {
            html += `<div><strong>${key}:</strong> ${value}</div>`;
        });
        
        html += '</small></div>';
        return html;
    }

    /**
     * 주기적 상태 확인 시작
     */
    startPeriodicCheck() {
        // 5분마다 시스템 상태 확인
        setInterval(() => {
            this.loadSystemStatus();
        }, 5 * 60 * 1000);
    }

    /**
     * 로그 보기
     */
    viewLogs() {
        // 새 창에서 로그 뷰어 열기
        window.open('log_viewer.php', 'logViewer', 'width=800,height=600,scrollbars=yes');
    }

    /**
     * 폼 검증 바인딩
     */
    bindFormValidation() {
        // 실시간 검증
        const requiredFields = ['db_host', 'db_name', 'db_user'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', () => this.validateField(field));
                field.addEventListener('input', () => this.clearFieldError(field));
            }
        });
    }

    /**
     * 필드 검증
     */
    validateField(field) {
        const value = field.value.trim();
        const isValid = value.length > 0;
        
        this.setFieldValidation(field, isValid, isValid ? '' : '이 필드는 필수입니다.');
        return isValid;
    }

    /**
     * 필드 검증 상태 설정
     */
    setFieldValidation(field, isValid, message) {
        const feedback = field.parentNode.querySelector('.invalid-feedback') || 
                        this.createFeedbackElement(field.parentNode);
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            feedback.style.display = 'none';
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
    }

    /**
     * 필드 오류 상태 클리어
     */
    clearFieldError(field) {
        field.classList.remove('is-invalid', 'is-valid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
    }

    /**
     * 피드백 엘리먼트 생성
     */
    createFeedbackElement(parent) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        parent.appendChild(feedback);
        return feedback;
    }

    /**
     * 파일을 텍스트로 읽기
     */
    readFileAsText(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = e => reject(e);
            reader.readAsText(file);
        });
    }

    /**
     * 파일 다운로드
     */
    downloadFile(content, filename, contentType) {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /**
     * 알림 표시
     */
    showAlert(type, message, duration = 5000) {
        // 기존 알림 제거
        const existingAlert = document.querySelector('.alert.auto-dismiss');
        if (existingAlert) {
            existingAlert.remove();
        }

        // 새 알림 생성
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show auto-dismiss`;
        alertDiv.innerHTML = `
            <i class="bi bi-${this.getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // 컨테이너 상단에 추가
        const container = document.querySelector('.container');
        const firstChild = container.firstElementChild;
        container.insertBefore(alertDiv, firstChild);

        // 자동 제거
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, duration);
    }

    /**
     * 알림 아이콘 가져오기
     */
    getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-triangle', 
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * 진행률 표시바 숨김
     */
    hideAllProgressBars() {
        const spinners = document.querySelectorAll('.spinner-border');
        spinners.forEach(spinner => {
            if (!spinner.classList.contains('d-none')) {
                spinner.classList.add('d-none');
            }
        });
    }

    /**
     * 툴팁 초기화
     */
    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * 전체 초기화 확인
 */
function confirmReset() {
    if (confirm('모든 설정을 기본값으로 초기화하시겠습니까?\n현재 설정은 자동으로 백업됩니다.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="reset_defaults">
            <input type="hidden" name="active_tab" value="validation">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * 고급 설정 기본값 복원
 */
function resetAdvancedSettings() {
    if (confirm('고급 설정을 기본값으로 복원하시겠습니까?')) {
        // 기본값으로 복원
        document.getElementById('upload_path').value = '../uploads';
        document.getElementById('upload_url').value = '/uploads';
        document.getElementById('max_file_size').value = '5';
        document.getElementById('allowed_extensions').value = 'jpg, jpeg, png, gif, pdf, doc, docx, hwp';
        document.getElementById('session_name').value = 'PHPSESSID';
        document.getElementById('csrf_token_name').value = 'csrf_token';
        document.getElementById('login_required').checked = true;
        document.getElementById('admin_required').checked = false;
        document.getElementById('download_permission').checked = true;
    }
}

// DOM 로드 완료 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    new DatabaseSettingsManager();
});