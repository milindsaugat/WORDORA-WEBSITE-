<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$pageFilter = $_GET['page'] ?? 'home';
$validPages = ['home' => 'Home Page', 'who_we_are' => 'Who We Are', 'services' => 'What We Do / Services', 'contact' => 'Contact Us', 'blog' => 'Blog & Journal'];
if (!array_key_exists($pageFilter, $validPages)) {
    $pageFilter = 'home';
}

$adminTitle = 'Hero Section Manager — ' . $validPages[$pageFilter];

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        Hero::delete($delId);
        flash_set('success', 'Slide deleted successfully.');
        redirect('admin/hero/index.php?page=' . urlencode($pageFilter));
    }
}

// Handle Mode Selector (Tick / Radio Options)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_hero_mode') {
    if (CSRF::verify($_POST['csrf_token'] ?? '')) {
        $selectedMode = $_POST['hero_mode'] ?? 'slider';
        Hero::setHeroMode($pageFilter, $selectedMode);
        if ($pageFilter === 'home') {
            Setting::set('hero_mode', $selectedMode);
        }
        flash_set('success', 'Hero display mode for ' . $validPages[$pageFilter] . ' saved successfully.');
        redirect('admin/hero/index.php?page=' . urlencode($pageFilter));
    }
}

$slides = Hero::getAll($pageFilter);
$currentHeroMode = Hero::getHeroMode($pageFilter);

include ROOT_PATH . '/admin/includes/header.php';
?>

<!-- Multi-Page Hero Navigation Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
  <?php foreach ($validPages as $pKey => $pLabel): ?>
    <?php $isPAct = ($pageFilter === $pKey); ?>
    <a href="<?= url('admin/hero/index.php?page=' . $pKey) ?>" style="padding: 10px 18px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isPAct ? 'background: var(--admin-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--admin-navy); border: 1.5px solid var(--admin-border);' ?>">
      <i class="ri-slideshow-line" style="color: <?= $isPAct ? 'var(--admin-teal)' : 'var(--admin-muted)' ?>;"></i> <?= $pLabel ?> Hero
    </a>
  <?php endforeach; ?>
</div>

<style>
.hero-mode-option-card {
  border: 2px solid #E2E8EE;
  background: #FAF8F5;
  padding: 16px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  transition: all 0.25s ease;
}
.hero-mode-option-card:hover {
  border-color: rgba(74, 139, 140, 0.45);
  background: #FFFFFF;
}
.hero-mode-option-card.is-active,
.hero-mode-option-card:has(input[type="radio"]:checked) {
  border-color: var(--admin-teal) !important;
  background: var(--admin-teal-pale) !important;
  box-shadow: 0 4px 14px rgba(74, 139, 140, 0.15);
}
</style>

<script>
function updateHeroModeCards(radio) {
  document.querySelectorAll('.hero-mode-option-card').forEach(function(card) {
    card.classList.remove('is-active');
  });
  if (radio && radio.checked) {
    var parent = radio.closest('.hero-mode-option-card');
    if (parent) {
      parent.classList.add('is-active');
    }
  }
}
</script>

<!-- 1. Hero Mode Selector (Tick / Radio Module - No Dropdown) -->
<div class="admin-card" style="margin-bottom: 24px;">
  <div class="card-header">
    <div>
      <h2 class="card-title"><i class="ri-checkbox-circle-line"></i> Hero Section Display Mode</h2>
      <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;">Select how the hero section appears on the homepage.</div>
    </div>
  </div>
  <div class="card-body">
    <form method="POST" action="<?= url('admin/hero/index.php') ?>">
      <?= CSRF::field() ?>
      <input type="hidden" name="action" value="save_hero_mode">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
        
        <!-- Option 1: Multi-Slide Carousel (Slider) -->
        <label class="hero-mode-option-card <?= ($currentHeroMode === 'slider') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="slider" <?= ($currentHeroMode === 'slider') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="margin-top: 4px; accent-color: var(--admin-teal);">
          <div>
            <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px;">✨ Multi-Slide Carousel (Slider)</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Smooth Swiper.js fade slider where each slide has its own full background artwork and distinct text.</div>
          </div>
        </label>

        <!-- Option 2: Single Background Image -->
        <label class="hero-mode-option-card <?= ($currentHeroMode === 'single' || $currentHeroMode === 'single_image') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="single" <?= ($currentHeroMode === 'single' || $currentHeroMode === 'single_image') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="margin-top: 4px; accent-color: var(--admin-teal);">
          <div>
            <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px;">🖼️ Single Background Image</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Renders 1 static high-impact background image hero without slide transitions.</div>
          </div>
        </label>

        <!-- Option 3: HTML5 Video Hero -->
        <label class="hero-mode-option-card <?= ($currentHeroMode === 'video') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="video" <?= ($currentHeroMode === 'video') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="margin-top: 4px; accent-color: var(--admin-teal);">
          <div>
            <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px;">🎬 Background Video Hero</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Autoplay looping MP4/WebM video hero with dark overlay.</div>
          </div>
        </label>

      </div>

      <button type="submit" class="btn-adm btn-adm-primary">
        <i class="ri-save-line"></i> Save Display Mode
      </button>
    </form>
  </div>
</div>

<!-- 2. Hero Slides List -->
<div class="admin-card">
  <div class="card-header">
    <div>
      <h2 class="card-title"><i class="ri-slideshow-line"></i> Manage Hero Slides (<?= count($slides) ?>)</h2>
      <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;">
        Each slide sets its own Background Image/Video, Heading, Sub-heading, Description, and Buttons.
      </div>
    </div>
    <a href="<?= url('admin/hero/edit.php?page=' . urlencode($pageFilter) . '&return_to=' . urlencode('admin/hero/index.php?page=' . $pageFilter)) ?>" class="btn-adm btn-adm-primary">
      <i class="ri-add-line"></i> Add New Slide
    </a>
  </div>

  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 80px;">Background</th>
          <th>Sub-Heading & Main Heading</th>
          <th>Buttons</th>
          <th>Sort</th>
          <th>Status</th>
          <th style="text-align: right; width: 140px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($slides)): ?>
          <?php foreach ($slides as $slide): ?>
          <tr>
            <td>
              <?php if (!empty($slide['media_url'])): ?>
                <img src="<?= media_url($slide['media_url']) ?>" alt="" style="width: 64px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);">
              <?php elseif (!empty($slide['video_url'])): ?>
                <div style="width: 64px; height: 42px; background: #0F1E36; color: #4A8B8C; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                  <i class="ri-video-fill"></i>
                </div>
              <?php else: ?>
                <div style="width: 64px; height: 42px; background: #F1F5F9; color: #94A3B8; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                  <i class="ri-image-line"></i>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($slide['eyebrow'])): ?>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--admin-teal); font-weight: 700; letter-spacing: 0.05em;"><?= e($slide['eyebrow']) ?></div>
              <?php endif; ?>
              <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px; margin-top: 2px;"><?= e($slide['title']) ?></div>
              <?php if (!empty($slide['subtitle'])): ?>
                <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;"><?= e(truncate($slide['subtitle'], 65)) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-size: 12px;"><strong>Primary:</strong> <?= e($slide['button_primary_text'] ?: 'None') ?></div>
              <?php if (!empty($slide['button_secondary_text'])): ?>
                <div style="font-size: 12px; color: var(--admin-muted);"><strong>Secondary:</strong> <?= e($slide['button_secondary_text']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= (int)$slide['sort_order'] ?></td>
            <td>
              <span class="badge-status badge-<?= $slide['is_active'] ? 'active' : 'inactive' ?>">
                <?= $slide['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td style="text-align: right;">
              <div class="table-actions" style="justify-content: flex-end;">
                <a href="<?= url('admin/hero/edit.php?id=' . $slide['id'] . '&page=' . urlencode($pageFilter) . '&return_to=' . urlencode('admin/hero/index.php?page=' . $pageFilter)) ?>" class="btn-adm-action btn-adm-edit" title="Edit">
                  <i class="ri-edit-line"></i> <span>Edit</span>
                </a>
                <a href="<?= url('admin/hero/index.php?page=' . urlencode($pageFilter) . '&delete=' . $slide['id']) ?>" class="btn-adm-action btn-adm-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this slide?')">
                  <i class="ri-delete-bin-line"></i> <span>Delete</span>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 40px;">
              <i class="ri-slideshow-line" style="font-size: 32px; color: var(--admin-teal); display: block; margin-bottom: 8px;"></i>
              No hero slides found for <?= $validPages[$pageFilter] ?>. Click <strong>"Add New Slide"</strong> above to create one.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>