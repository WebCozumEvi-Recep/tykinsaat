  </div>
</main>
<nav class="tabbar" aria-label="Alt menü"><?php foreach ($__alt as [$__mh,$__mi,$__mt]): ?>
  <a href="<?= $__mh ?>" class="<?= $__cur===$__mh?'aktif':'' ?>" <?= $__mh==='#menu'?'onclick="menuAc();return false"':'' ?>><?= ikon($__mi) ?><?= $__mt ?></a>
<?php endforeach; ?></nav>
<?php if (!empty($fab)): ?><a class="fab" href="<?= e($fab) ?>" aria-label="Hızlı ekle"><?= ikon('plus') ?></a><?php endif; ?>
<script src="assets/app.js"></script>
</body></html>
