<?php if (!defined('_HOPEC_')) exit; ?>

    </div>
  </div>
  
  </div>
  <!-- } 콘텐츠 끝 -->

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
  try {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  } catch(e) {}
  </script>
  
  <?php if(defined('G5_DEVICE_BUTTON_DISPLAY') && G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { /* 장치 전환 버튼 자리 */ } ?>
  <?php if (!empty($config['cf_analytics'])) { echo $config['cf_analytics']; } ?>

  </body>
</html>

