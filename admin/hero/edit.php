<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$slide = null;

$returnTo = $_GET['return_to'] ?? $_POST['return_to'] ?? url('admin/pages/home.php?tab=sec01');

if ($id) {
    $slide = Hero::getById($id);
    if (!$slide) {
        flash_set('error', 'Slide not found.');
        redirect($returnTo);
    }
}

$adminTitle = $id ? 'Edit Hero Slide' : 'Add New Hero Slide';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $error = 'Main Heading is required.';
        } else {
            $existingMedia = $_POST['existing_media_url'] ?? ($slide['media_url'] ?? '');
            $existingVideo = $_POST['existing_video_url'] ?? ($slide['video_url'] ?? '');
            $mediaUrl = $existingMedia;
            $videoUrl = $existingVideo;

            // Handle Image Upload / Removal (Priority: New Upload > Remove > Existing)
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $uploader = new Upload('hero');
                $uploadRes = $uploader->handle($_FILES['media_file'], false);
                if ($uploadRes['success']) {
                    if (!empty($existingMedia) && $existingMedia !== $uploadRes['path']) {
                        delete_uploaded_file($existingMedia);
                    }
                    $mediaUrl = $uploadRes['path'];
                } else {
                    $error = 'Image upload error: ' . $uploadRes['msg'];
                }
            } elseif (!empty($_POST['remove_media']) && $_POST['remove_media'] === '1') {
                delete_uploaded_file($existingMedia);
                $mediaUrl = '';
            }

            // Handle Video Upload / URL / Removal (Priority: Remove > New Upload > Direct URL > Existing)
            if (!empty($_POST['remove_video']) && $_POST['remove_video'] === '1') {
                delete_uploaded_file($existingVideo);
                $videoUrl = '';
            } elseif (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $uploader = new Upload('hero');
                $uploadRes = $uploader->handle($_FILES['video_file'], true);
                if ($uploadRes['success']) {
                    if (!empty($existingVideo) && $existingVideo !== $uploadRes['path']) {
                        delete_uploaded_file($existingVideo);
                    }
                    $videoUrl = $uploadRes['path'];
                } else {
                    $error = 'Video upload error: ' . $uploadRes['msg'];
                }
            } elseif (isset($_POST['video_url_text'])) {
                $newVideoUrl = trim($_POST['video_url_text']);
                if ($newVideoUrl !== $existingVideo && !empty($existingVideo)) {
                    delete_uploaded_file($existingVideo);
                }
                $videoUrl = $newVideoUrl;
            }

            if (empty($error)) {
                $slidePage = $_POST['page'] ?? $_GET['page'] ?? ($slide['page'] ?? 'home');
                $data = [
                    'page'                  => $slidePage,
                    'banner_type'           => !empty($videoUrl) ? 'video' : 'slider',
                    'eyebrow'               => trim($_POST['eyebrow'] ?? ''),
                    'title'                 => $title,
                    'subtitle'              => trim($_POST['subtitle'] ?? ''),
                    'media_url'             => $mediaUrl,
                    'video_url'             => $videoUrl,
                    'button_primary_text'   => trim($_POST['button_primary_text'] ?? 'Explore Our Work'),
                    'button_primary_url'    => trim($_POST['button_primary_url'] ?? 'services.php'),
                    'button_secondary_text' => trim($_POST['button_secondary_text'] ?? 'Start a Conversation'),
                    'button_secondary_url'  => trim($_POST['button_secondary_url'] ?? 'contact.php'),
                    'sort_order'            => (int)($_POST['sort_order'] ?? 0),
                    'is_active'             => isset($_POST['is_active']) ? 1 : 0,
                ];

                Hero::save($data, $id);
                flash_set('success', $id ? 'Slide updated successfully!' : 'Slide created successfully!');
                redirect($returnTo);
            }
        }
    }
}

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="admin-card" style="max-width: 860px; margin: 0 auto;">
  <div class="card-header">
    <h2 class="card-title"><?= $id ? 'Edit Hero Slide' : 'Add New Hero Slide' ?></h2>
    <a href="<?= e($returnTo) ?>" class="btn-adm btn-adm-outline btn-adm-sm">
      <i class="ri-arrow-left-line"></i> Back to Homepage Studio
    </a>
  </div>

  <div class="card-body">
    <?php if ($error): ?>
      <div style="margin-bottom: 20px; padding: 12px; border-radius: 6px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
        <i class="ri-error-warning-line"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="<?= $id ? url('admin/hero/edit.php?id=' . $id) : url('admin/hero/edit.php') ?>">
      <?= CSRF::field() ?>
      <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
      <input type="hidden" name="page" value="<?= e($_GET['page'] ?? $_POST['page'] ?? ($slide['page'] ?? 'home')) ?>">
      <input type="hidden" name="existing_media_url" value="<?= e($slide['media_url'] ?? '') ?>">
      <input type="hidden" name="existing_video_url" value="<?= e($slide['video_url'] ?? '') ?>">

      <div class="form-grid">
        <!-- Sub-Heading (Eyebrow) -->
        <div class="form-field full">
          <label class="field-label" for="slideEyebrow">Sub-Heading (Eyebrow Tag)</label>
          <input type="text" id="slideEyebrow" name="eyebrow" class="field-input" placeholder="e.g. EDITORIAL CONTENT & COPYWRITING STUDIO" value="<?= e($_POST['eyebrow'] ?? $slide['eyebrow'] ?? '') ?>">
        </div>

        <!-- Main Heading -->
        <div class="form-field full">
          <label class="field-label" for="slideTitle">Main Heading *</label>
          <input type="text" id="slideTitle" name="title" class="field-input" required placeholder="e.g. Words That Work. Stories That Sell." value="<?= e($_POST['title'] ?? $slide['title'] ?? '') ?>">
        </div>

        <!-- Description Paragraph -->
        <div class="form-field full">
          <label class="field-label" for="slideSubtitle">Description Paragraph</label>
          <textarea id="slideSubtitle" name="subtitle" class="field-textarea" style="min-height: 85px;" placeholder="We turn research, ideas and brand thinking into content people remember — and businesses can grow with."><?= e($_POST['subtitle'] ?? $slide['subtitle'] ?? '') ?></textarea>
        </div>

        <!-- Primary Button -->
        <div class="form-field">
          <label class="field-label" for="btn1Text">Primary Button Text</label>
          <input type="text" id="btn1Text" name="button_primary_text" class="field-input" placeholder="Explore Our Work" value="<?= e($_POST['button_primary_text'] ?? $slide['button_primary_text'] ?? 'Explore Our Work') ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="btn1Url">Primary Button URL</label>
          <input type="text" id="btn1Url" name="button_primary_url" class="field-input" placeholder="services.php" value="<?= e($_POST['button_primary_url'] ?? $slide['button_primary_url'] ?? 'services.php') ?>">
        </div>

        <!-- Secondary Button -->
        <div class="form-field">
          <label class="field-label" for="btn2Text">Secondary Button Text</label>
          <input type="text" id="btn2Text" name="button_secondary_text" class="field-input" placeholder="Start a Conversation" value="<?= e($_POST['button_secondary_text'] ?? $slide['button_secondary_text'] ?? 'Start a Conversation') ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="btn2Url">Secondary Button URL</label>
          <input type="text" id="btn2Url" name="button_secondary_url" class="field-input" placeholder="contact.php" value="<?= e($_POST['button_secondary_url'] ?? $slide['button_secondary_url'] ?? 'contact.php') ?>">
        </div>
        
        <!-- Slide Background Artwork Image (File Upload Card) -->
        <div class="form-field full" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border); margin-top: 6px;">
          <h3 style="font-size: 13px; font-weight: 700; color: var(--admin-navy); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-image-2-line" style="color: var(--admin-teal);"></i> Slide Background Image
          </h3>

          <?php if (!empty($slide['media_url'])): ?>
            <div id="preview_hero_media" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
              <img src="<?= media_url($slide['media_url']) ?>" alt="Current image" style="max-height: 70px; border-radius: 4px; border: 1px solid var(--admin-border);">
              <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 600; color: var(--admin-navy);">Active Background Image</div>
                <div style="font-size: 11px; color: var(--admin-muted); word-break: break-all;"><?= e($slide['media_url']) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_media', 'preview_hero_media')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                <i class="ri-delete-bin-line"></i> Remove Image
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_media" id="remove_media" value="0">

          <label class="field-label" style="font-size: 12px;">Upload New Background Image (PNG, JPG, WebP)</label>
          <input type="file" name="media_file" class="field-input" accept="image/*">
        </div>

        <!-- Video Hero Option (Upload or Direct URL) -->
        <div class="form-field full" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border); margin-top: 6px;">
          <h3 style="font-size: 13px; font-weight: 700; color: var(--admin-navy); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-video-line" style="color: var(--admin-teal);"></i> Background Video Hero (Upload File or Enter Direct URL)
          </h3>

          <?php if (!empty($slide['video_url'])): ?>
            <div id="preview_hero_video" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 12px 14px; border-radius: 6px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
              <div style="width: 80px; height: 50px; background: #0F1E36; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal); font-size: 24px; overflow: hidden; flex-shrink: 0;">
                <video src="<?= media_url($slide['video_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;" muted autoplay loop playsinline></video>
              </div>
              <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px; font-weight: 600; color: var(--admin-navy);">Active Video File / URL</div>
                <div style="font-size: 11px; color: var(--admin-teal); word-break: break-all;"><?= e($slide['video_url']) ?></div>
              </div>
              <button type="button" onclick="document.getElementById('remove_video').value='1'; document.getElementById('preview_hero_video').style.display='none'; document.querySelector('input[name=video_url_text]').value='';" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                <i class="ri-delete-bin-line"></i> Remove Video
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_video" id="remove_video" value="0">

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 10px;">
            <div>
              <label class="field-label" style="font-size: 12px;"><i class="ri-upload-cloud-2-line"></i> Option 1: Upload Video File (MP4, WebM)</label>
              <input type="file" name="video_file" class="field-input" accept="video/mp4,video/webm">
              <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Max 50MB MP4/WebM video clip.</small>
            </div>
            <div>
              <label class="field-label" style="font-size: 12px;"><i class="ri-link"></i> Option 2: Or Paste Direct Video URL</label>
              <input type="text" name="video_url_text" class="field-input" placeholder="https://domain.com/video.mp4 or /uploads/..." value="<?= e($slide['video_url'] ?? '') ?>">
              <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">External CDN or hosted video URL.</small>
            </div>
          </div>
        </div>

        <!-- Sort Order & Status -->
        <div class="form-field">
          <label class="field-label" for="slideSort">Sort Order</label>
          <input type="number" id="slideSort" name="sort_order" class="field-input" value="<?= (int)($_POST['sort_order'] ?? $slide['sort_order'] ?? 0) ?>">
        </div>

        <div class="form-field" style="display: flex; align-items: center; gap: 8px; margin-top: 28px;">
          <input type="checkbox" id="slideActive" name="is_active" value="1" <?= (!isset($slide) || !empty($slide['is_active'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--admin-teal);">
          <label for="slideActive" style="font-weight: 600; color: var(--admin-navy); cursor: pointer;">Active on Website</label>
        </div>
      </div>

      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--admin-border); display: flex; gap: 12px;">
        <button type="submit" class="btn-adm btn-adm-primary">
          <i class="ri-save-line"></i> <?= $id ? 'Update Slide' : 'Save Slide' ?>
        </button>
        <a href="<?= url('admin/hero/index.php') ?>" class="btn-adm btn-adm-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
function instantRemoveMedia(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  if (input) input.value = '1';
  if (preview) {
    preview.style.opacity = '0';
    preview.style.transform = 'translateY(-6px)';
    setTimeout(() => { preview.remove(); }, 250);
  }
}
</script>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>