<?php
$flash = flash_message();
if ($flash['message']): ?>
<div class="flash-toast flash-<?= e($flash['type']) ?>" id="flashToast">
    <?= e($flash['message']) ?>
</div>
<script>
    setTimeout(() => {
        const t = document.getElementById('flashToast');
        if (t) t.classList.add('flash-hide');
    }, 3000);
</script>
<?php endif; ?>
