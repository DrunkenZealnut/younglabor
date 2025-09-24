/**
 * Board Templates Summernote Emoji 플러그인
 * Phase 2: 이모지 선택기 기능
 */

(function() {
    'use strict';
    
    function waitForBase(callback) {
        if (window.BoardTemplatesPluginBase && window.btRegisterPlugin) {
            callback();
        } else {
            setTimeout(() => waitForBase(callback), 100);
        }
    }
    
    waitForBase(function() {
        btRegisterPlugin('emoji', {
            langPath: 'special.emoji',
            
            initialize: function(context) {
                this.context = context;
                this.log('Emoji 플러그인 초기화');
                
                this.emojiCategories = {
                    'people': {
                        name: '사람',
                        icon: '😀',
                        emojis: [
                            '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃',
                            '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙',
                            '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔',
                            '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥',
                            '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧',
                            '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓', '🧐'
                        ]
                    },
                    'nature': {
                        name: '자연',
                        icon: '🐶',
                        emojis: [
                            '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯',
                            '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒',
                            '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇',
                            '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜',
                            '🦟', '🦗', '🕷️', '🕸️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕',
                            '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳'
                        ]
                    },
                    'food': {
                        name: '음식',
                        icon: '🍎',
                        emojis: [
                            '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒',
                            '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬',
                            '🥒', '🌶️', '🌽', '🥕', '🥔', '🍠', '🥐', '🍞', '🥖', '🥨',
                            '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖',
                            '🌭', '🍔', '🍟', '🍕', '🥪', '🥙', '🌮', '🌯', '🥗', '🥘',
                            '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🍤', '🍙', '🍚'
                        ]
                    },
                    'activities': {
                        name: '활동',
                        icon: '⚽',
                        emojis: [
                            '⚽', '🏀', '🏈', '⚾', '🎾', '🏐', '🏉', '🥏', '🎱', '🏓',
                            '🏸', '🥍', '🏒', '🏑', '🥎', '🏏', '⛳', '🏹', '🎣', '🥊',
                            '🥋', '🎽', '⛸️', '🛷', '🛹', '🎿', '⛷️', '🏂', '🏋️‍♀️', '🏋️‍♂️',
                            '🤼‍♀️', '🤼‍♂️', '🤸‍♀️', '🤸‍♂️', '⛹️‍♀️', '⛹️‍♂️', '🤺', '🤾‍♀️', '🤾‍♂️', '🏌️‍♀️',
                            '🏌️‍♂️', '🏇', '🧘‍♀️', '🧘‍♂️', '🏄‍♀️', '🏄‍♂️', '🏊‍♀️', '🏊‍♂️', '🤽‍♀️', '🤽‍♂️',
                            '🚣‍♀️', '🚣‍♂️', '🧗‍♀️', '🧗‍♂️', '🚵‍♀️', '🚵‍♂️', '🚴‍♀️', '🚴‍♂️', '🏆', '🥇'
                        ]
                    },
                    'travel': {
                        name: '여행',
                        icon: '🚗',
                        emojis: [
                            '🚗', '🚕', '🚙', '🚌', '🚎', '🏎️', '🚓', '🚑', '🚒', '🚐',
                            '🛻', '🚚', '🚛', '🚜', '🏍️', '🛴', '🚲', '🛵', '🚁', '✈️',
                            '🛩️', '🛫', '🛬', '💺', '🚀', '🛰️', '🚢', '⛵', '🛥️', '🚤',
                            '⛴️', '🛳️', '🚂', '🚃', '🚄', '🚅', '🚆', '🚇', '🚈', '🚉',
                            '🚊', '🚝', '🚞', '🚋', '🚌', '🚍', '🎡', '🎢', '🎠', '🏗️',
                            '🌁', '🗼', '🏭', '⛲', '🎡', '🎢', '🏰', '🏯', '🏟️', '🎪'
                        ]
                    },
                    'objects': {
                        name: '사물',
                        icon: '💻',
                        emojis: [
                            '💻', '🖥️', '🖨️', '⌨️', '🖱️', '🖲️', '💽', '💾', '💿', '📀',
                            '📱', '📞', '☎️', '📟', '📠', '📺', '📻', '⏰', '🕐', '⏱️',
                            '⏲️', '⏱️', '🕰️', '📡', '🔋', '🔌', '💡', '🔦', '🕯️', '🧯',
                            '🛢️', '💸', '💵', '💴', '💶', '💷', '💰', '💳', '🧾', '💎',
                            '⚖️', '🧰', '🔧', '🔨', '⚒️', '🛠️', '⛏️', '🔩', '⚙️', '🧱',
                            '⛓️', '🧲', '🔫', '💣', '🧨', '🔪', '🗡️', '⚔️', '🛡️', '🚬'
                        ]
                    },
                    'symbols': {
                        name: '기호',
                        icon: '❤️',
                        emojis: [
                            '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔',
                            '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '♥️',
                            '💢', '💥', '💫', '💦', '💨', '🕳️', '💣', '💤', '👋', '🤚',
                            '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙',
                            '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '👊', '✊',
                            '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅'
                        ]
                    },
                    'flags': {
                        name: '국기',
                        icon: '🇰🇷',
                        emojis: [
                            '🇰🇷', '🇺🇸', '🇯🇵', '🇨🇳', '🇬🇧', '🇫🇷', '🇩🇪', '🇮🇹', '🇪🇸', '🇷🇺',
                            '🇨🇦', '🇦🇺', '🇧🇷', '🇮🇳', '🇲🇽', '🇹🇷', '🇸🇦', '🇿🇦', '🇪🇬', '🇳🇬',
                            '🇦🇷', '🇨🇱', '🇵🇪', '🇨🇴', '🇻🇪', '🇺🇾', '🇵🇾', '🇪🇨', '🇧🇴', '🇬🇾',
                            '🇸🇷', '🇫🇬', '🇬🇫', '🇧🇶', '🇨🇼', '🇸🇽', '🇦🇼', '🇹🇹', '🇯🇲', '🇭🇹'
                        ]
                    }
                };
                
                this.recentEmojis = this.loadRecentEmojis();
                this.searchResults = [];
            },
            
            createButton: function(context) {
                const self = this;
                return {
                    tooltip: this.getTooltip(context, 'Emoji (Ctrl+Shift+E)'),
                    click: function() {
                        self.showEmojiPicker(context);
                    }
                };
            },
            
            loadRecentEmojis: function() {
                try {
                    const recent = localStorage.getItem('bt-recent-emojis');
                    return recent ? JSON.parse(recent) : ['😀', '👍', '❤️', '😂', '🎉'];
                } catch (e) {
                    return ['😀', '👍', '❤️', '😂', '🎉'];
                }
            },
            
            saveRecentEmojis: function() {
                try {
                    localStorage.setItem('bt-recent-emojis', JSON.stringify(this.recentEmojis));
                } catch (e) {
                    // localStorage 사용 불가
                }
            },
            
            addRecentEmoji: function(emoji) {
                // 이미 있으면 제거
                const index = this.recentEmojis.indexOf(emoji);
                if (index > -1) {
                    this.recentEmojis.splice(index, 1);
                }
                
                // 맨 앞에 추가
                this.recentEmojis.unshift(emoji);
                
                // 최대 20개까지만 유지
                if (this.recentEmojis.length > 20) {
                    this.recentEmojis.pop();
                }
                
                this.saveRecentEmojis();
            },
            
            showEmojiPicker: function(context) {
                const self = this;
                
                const categoryTabs = Object.entries(this.emojiCategories).map(([id, category]) => `
                    <button class="bt-emoji-category-tab" data-category="${id}" title="${category.name}">
                        <span class="bt-category-icon">${category.icon}</span>
                        <span class="bt-category-name">${category.name}</span>
                    </button>
                `).join('');
                
                const pickerHtml = `
                    <div class="bt-emoji-picker-overlay" onclick="btCloseEmojiPicker()">
                        <div class="bt-emoji-picker" onclick="event.stopPropagation()">
                            <div class="bt-emoji-header">
                                <div class="bt-emoji-search">
                                    <input type="text" class="bt-emoji-search-input" placeholder="이모지 검색..." />
                                    <button class="bt-search-clear" style="display: none;">&times;</button>
                                </div>
                                <button class="bt-emoji-close" onclick="btCloseEmojiPicker()">&times;</button>
                            </div>
                            
                            <div class="bt-emoji-categories">
                                <button class="bt-emoji-category-tab active" data-category="recent" title="최근 사용">
                                    <span class="bt-category-icon">🕐</span>
                                    <span class="bt-category-name">최근</span>
                                </button>
                                ${categoryTabs}
                            </div>
                            
                            <div class="bt-emoji-content">
                                <div class="bt-emoji-grid recent-emojis active">
                                    ${this.renderEmojiGrid(this.recentEmojis)}
                                </div>
                                ${Object.entries(this.emojiCategories).map(([id, category]) => `
                                    <div class="bt-emoji-grid category-${id}" data-category="${id}">
                                        ${this.renderEmojiGrid(category.emojis)}
                                    </div>
                                `).join('')}
                                <div class="bt-emoji-grid search-results">
                                    <!-- 검색 결과가 여기에 표시됩니다 -->
                                </div>
                            </div>
                            
                            <div class="bt-emoji-info">
                                <span class="bt-emoji-preview"></span>
                                <span class="bt-emoji-name"></span>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(pickerHtml);
                this.attachPickerEvents();
            },
            
            renderEmojiGrid: function(emojis) {
                return emojis.map(emoji => `
                    <button class="bt-emoji-item" data-emoji="${emoji}" title="${this.getEmojiName(emoji)}">
                        ${emoji}
                    </button>
                `).join('');
            },
            
            getEmojiName: function(emoji) {
                // 이모지 이름 매핑 (간단한 버전)
                const emojiNames = {
                    '😀': '웃는얼굴',
                    '😃': '큰웃음',
                    '😄': '활짝웃는얼굴',
                    '😁': '이빨보이는웃음',
                    '😆': '웃는눈웃음',
                    '😅': '식은땀웃음',
                    '🤣': '바닥구르는웃음',
                    '😂': '기쁨의눈물',
                    '👍': '좋아요',
                    '👎': '싫어요',
                    '❤️': '빨간하트',
                    '💙': '파란하트',
                    '💚': '초록하트',
                    '💛': '노란하트',
                    '💜': '보라하트',
                    '🎉': '축하',
                    '🎊': '색종이',
                    '🔥': '불',
                    '⭐': '별',
                    '✨': '반짝임',
                    '🇰🇷': '한국국기',
                    '🇺🇸': '미국국기',
                    '🇯🇵': '일본국기'
                };
                
                return emojiNames[emoji] || emoji;
            },
            
            attachPickerEvents: function() {
                const self = this;
                const $picker = $('.bt-emoji-picker');
                
                // 카테고리 탭 클릭
                $picker.find('.bt-emoji-category-tab').on('click', function() {
                    const category = $(this).data('category');
                    
                    // 탭 활성화
                    $picker.find('.bt-emoji-category-tab').removeClass('active');
                    $(this).addClass('active');
                    
                    // 그리드 표시
                    $picker.find('.bt-emoji-grid').removeClass('active');
                    if (category === 'recent') {
                        $picker.find('.recent-emojis').addClass('active');
                    } else {
                        $picker.find(`.category-${category}`).addClass('active');
                    }
                    
                    // 검색 결과 숨기기
                    $picker.find('.search-results').removeClass('active');
                    $picker.find('.bt-emoji-search-input').val('');
                    $picker.find('.bt-search-clear').hide();
                });
                
                // 이모지 클릭
                $picker.on('click', '.bt-emoji-item', function() {
                    const emoji = $(this).data('emoji');
                    self.insertEmoji(emoji);
                    btCloseEmojiPicker();
                });
                
                // 이모지 호버
                $picker.on('mouseenter', '.bt-emoji-item', function() {
                    const emoji = $(this).data('emoji');
                    const name = self.getEmojiName(emoji);
                    
                    $picker.find('.bt-emoji-preview').text(emoji);
                    $picker.find('.bt-emoji-name').text(name);
                });
                
                $picker.on('mouseleave', '.bt-emoji-item', function() {
                    $picker.find('.bt-emoji-preview').text('');
                    $picker.find('.bt-emoji-name').text('');
                });
                
                // 검색 기능
                $picker.find('.bt-emoji-search-input').on('input', function() {
                    const query = $(this).val().toLowerCase().trim();
                    const $clearBtn = $picker.find('.bt-search-clear');
                    
                    if (query) {
                        $clearBtn.show();
                        self.searchEmojis(query);
                    } else {
                        $clearBtn.hide();
                        $picker.find('.search-results').removeClass('active');
                        // 현재 활성 카테고리 복원
                        const activeCategory = $picker.find('.bt-emoji-category-tab.active').data('category');
                        if (activeCategory === 'recent') {
                            $picker.find('.recent-emojis').addClass('active');
                        } else {
                            $picker.find(`.category-${activeCategory}`).addClass('active');
                        }
                    }
                });
                
                // 검색 지우기 버튼
                $picker.find('.bt-search-clear').on('click', function() {
                    $picker.find('.bt-emoji-search-input').val('').trigger('input');
                });
                
                // 키보드 단축키
                $picker.find('.bt-emoji-search-input').on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        btCloseEmojiPicker();
                    }
                });
            },
            
            searchEmojis: function(query) {
                const $picker = $('.bt-emoji-picker');
                const $searchResults = $picker.find('.search-results');
                
                // 모든 이모지에서 검색
                const allEmojis = [];
                Object.values(this.emojiCategories).forEach(category => {
                    allEmojis.push(...category.emojis);
                });
                
                // 검색어와 이모지 이름 매칭
                const results = allEmojis.filter(emoji => {
                    const name = this.getEmojiName(emoji).toLowerCase();
                    return name.includes(query) || emoji.includes(query);
                });
                
                // 검색 결과 표시
                if (results.length > 0) {
                    $searchResults.html(this.renderEmojiGrid(results)).addClass('active');
                    $picker.find('.bt-emoji-grid').not('.search-results').removeClass('active');
                } else {
                    $searchResults.html('<div class="bt-no-results">검색 결과가 없습니다.</div>').addClass('active');
                    $picker.find('.bt-emoji-grid').not('.search-results').removeClass('active');
                }
            },
            
            insertEmoji: function(emoji) {
                this.context.invoke('editor.insertText', emoji);
                this.addRecentEmoji(emoji);
                this.log(`이모지 삽입됨: ${emoji}`);
            },
            
            getCSS: function(context) {
                const theme = this.getTheme(context);
                
                return `
                    /* 이모지 피커 오버레이 */
                    .bt-emoji-picker-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.5);
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    /* 이모지 피커 */
                    .bt-emoji-picker {
                        width: 400px;
                        height: 500px;
                        background: ${theme.backgroundColor || '#ffffff'};
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 12px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                        display: flex;
                        flex-direction: column;
                        overflow: hidden;
                    }
                    
                    /* 헤더 */
                    .bt-emoji-header {
                        display: flex;
                        align-items: center;
                        padding: 15px;
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        background: ${theme.headerBackground || '#f8fafc'};
                    }
                    
                    .bt-emoji-search {
                        flex: 1;
                        position: relative;
                    }
                    
                    .bt-emoji-search-input {
                        width: 100%;
                        padding: 8px 30px 8px 12px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 20px;
                        font-size: 14px;
                        outline: none;
                    }
                    
                    .bt-emoji-search-input:focus {
                        border-color: ${theme.primary || '#3b82f6'};
                    }
                    
                    .bt-search-clear {
                        position: absolute;
                        right: 8px;
                        top: 50%;
                        transform: translateY(-50%);
                        background: none;
                        border: none;
                        font-size: 18px;
                        cursor: pointer;
                        color: ${theme.textSecondary || '#64748b'};
                        width: 20px;
                        height: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    .bt-emoji-close {
                        background: none;
                        border: none;
                        font-size: 20px;
                        cursor: pointer;
                        color: ${theme.textSecondary || '#64748b'};
                        margin-left: 10px;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 50%;
                        transition: background-color 0.2s ease;
                    }
                    
                    .bt-emoji-close:hover {
                        background: ${theme.hoverBackground || '#f1f5f9'};
                    }
                    
                    /* 카테고리 탭 */
                    .bt-emoji-categories {
                        display: flex;
                        background: ${theme.backgroundColor || '#ffffff'};
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        overflow-x: auto;
                        scrollbar-width: none;
                    }
                    
                    .bt-emoji-categories::-webkit-scrollbar {
                        display: none;
                    }
                    
                    .bt-emoji-category-tab {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        padding: 8px 12px;
                        background: none;
                        border: none;
                        border-bottom: 2px solid transparent;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        min-width: 60px;
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    .bt-emoji-category-tab:hover {
                        background: ${theme.hoverBackground || '#f8fafc'};
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-emoji-category-tab.active {
                        color: ${theme.primary || '#3b82f6'};
                        border-bottom-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.activeBackground || '#eff6ff'};
                    }
                    
                    .bt-category-icon {
                        font-size: 18px;
                        margin-bottom: 2px;
                    }
                    
                    .bt-category-name {
                        font-size: 10px;
                        font-weight: 500;
                    }
                    
                    /* 이모지 콘텐츠 */
                    .bt-emoji-content {
                        flex: 1;
                        position: relative;
                        overflow: hidden;
                    }
                    
                    .bt-emoji-grid {
                        display: none;
                        grid-template-columns: repeat(8, 1fr);
                        gap: 5px;
                        padding: 15px;
                        height: 100%;
                        overflow-y: auto;
                    }
                    
                    .bt-emoji-grid.active {
                        display: grid;
                    }
                    
                    .bt-emoji-item {
                        background: none;
                        border: none;
                        border-radius: 8px;
                        padding: 8px;
                        cursor: pointer;
                        font-size: 20px;
                        transition: all 0.2s ease;
                        aspect-ratio: 1;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    .bt-emoji-item:hover {
                        background: ${theme.hoverBackground || '#f1f5f9'};
                        transform: scale(1.2);
                    }
                    
                    .bt-emoji-item:active {
                        transform: scale(0.95);
                    }
                    
                    /* 검색 결과 없음 */
                    .bt-no-results {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100%;
                        color: ${theme.textSecondary || '#64748b'};
                        font-style: italic;
                    }
                    
                    /* 이모지 정보 */
                    .bt-emoji-info {
                        display: flex;
                        align-items: center;
                        padding: 10px 15px;
                        border-top: 1px solid ${theme.borderColor || '#e2e8f0'};
                        background: ${theme.headerBackground || '#f8fafc'};
                        min-height: 40px;
                    }
                    
                    .bt-emoji-preview {
                        font-size: 24px;
                        margin-right: 10px;
                        min-width: 30px;
                    }
                    
                    .bt-emoji-name {
                        font-size: 14px;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-weight: 500;
                    }
                    
                    /* 반응형 디자인 */
                    @media (max-width: 480px) {
                        .bt-emoji-picker {
                            width: 95vw;
                            height: 80vh;
                            max-width: 400px;
                        }
                        
                        .bt-emoji-grid {
                            grid-template-columns: repeat(6, 1fr);
                        }
                        
                        .bt-category-name {
                            font-size: 9px;
                        }
                        
                        .bt-emoji-item {
                            font-size: 18px;
                            padding: 6px;
                        }
                    }
                    
                    /* 스크롤바 스타일 */
                    .bt-emoji-grid::-webkit-scrollbar {
                        width: 6px;
                    }
                    
                    .bt-emoji-grid::-webkit-scrollbar-track {
                        background: ${theme.scrollTrack || '#f1f5f9'};
                        border-radius: 3px;
                    }
                    
                    .bt-emoji-grid::-webkit-scrollbar-thumb {
                        background: ${theme.scrollThumb || '#cbd5e1'};
                        border-radius: 3px;
                    }
                    
                    .bt-emoji-grid::-webkit-scrollbar-thumb:hover {
                        background: ${theme.scrollThumbHover || '#94a3b8'};
                    }
                    
                    /* 애니메이션 */
                    .bt-emoji-picker {
                        animation: btEmojiPickerShow 0.2s ease-out;
                    }
                    
                    @keyframes btEmojiPickerShow {
                        from {
                            opacity: 0;
                            transform: scale(0.9);
                        }
                        to {
                            opacity: 1;
                            transform: scale(1);
                        }
                    }
                `;
            },
            
            attachEvents: function(context) {
                const self = this;
                
                // 키보드 단축키
                $(document).on('keydown', function(e) {
                    if (e.ctrlKey && e.shiftKey && e.key === 'E') {
                        e.preventDefault();
                        self.showEmojiPicker(context);
                    }
                });
            },
            
            cleanup: function(context) {
                $('.bt-emoji-picker-overlay').remove();
                this.log('Emoji 플러그인 정리 완료');
            }
        });
    });
    
    // 전역 이모지 함수들
    window.btCloseEmojiPicker = function() {
        $('.bt-emoji-picker-overlay').remove();
    };
    
    window.btInsertEmoji = function(emoji) {
        // 현재 활성화된 Summernote 에디터에 이모지 삽입
        const $activeEditor = $('.note-editable:focus');
        if ($activeEditor.length) {
            $activeEditor.focus();
            document.execCommand('insertText', false, emoji);
        }
        btCloseEmojiPicker();
    };
    
    window.btGetRecentEmojis = function() {
        try {
            const recent = localStorage.getItem('bt-recent-emojis');
            return recent ? JSON.parse(recent) : ['😀', '👍', '❤️', '😂', '🎉'];
        } catch (e) {
            return ['😀', '👍', '❤️', '😂', '🎉'];
        }
    };
    
})();