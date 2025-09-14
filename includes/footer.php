    </div></div></div><!-- #wrapper 닫기 태그들 -->
    
    <?php 
    // Natural Green 테마 푸터 포함
    $naturalGreenFooter = HOPEC_BASE_PATH . '/theme/natural-green/includes/footer.php';
    if (file_exists($naturalGreenFooter)) {
        include $naturalGreenFooter;
    }
    ?>
    
    <script>
    // Lucide 아이콘 초기화
    try {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    } catch(e) {
        console.log('Lucide icons initialization skipped');
    }
    </script>
    
</body>
</html><?php