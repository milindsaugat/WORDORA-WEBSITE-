<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$adminTitle = 'Service Detail Studio';

if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if (CSRF::verify($_GET['csrf'] ?? '')) {
        Service::delete($delId);
        flash_set('success', 'Service detail page deleted successfully.');
    } else {
        flash_set('error', 'Security token invalid.');
    }
    redirect('admin/services/index.php');
}

$services = Service::getAll();
$devServicesEnabled = (setting('home_sec3c_enabled', '1') !== '0');

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="admin-card">
  <?php if (!$devServicesEnabled): ?>
  <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 12px; font-size: 13px; background: #FEF2F2; color: #991B1B; border: 1.5px solid #FECACA; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <i class="ri-eye-off-line" style="font-size: 22px; color: #DC2626;"></i>
      <div>
        <strong>7 Development &amp; Design Services are Turned OFF:</strong> They are currently hidden across the website, SEO Meta manager, and Navbar.
        <div style="font-size: 12px; color: #7F1D1D; margin-top: 2px;">
          To re-enable them, visit <a href="<?= url('admin/pages/home.php?tab=sec03c') ?>" style="color: #991B1B; font-weight: 700; text-decoration: underline;">Homepage Section 3 / 3C</a> and switch the Master Toggle to ON.
        </div>
      </div>
    </div>
    <span style="font-size: 11px; font-weight: 800; background: #DC2626; color: #FFF; padding: 4px 10px; border-radius: 6px;">7 SERVICES DISABLED</span>
  </div>
  <?php endif; ?>

  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h2 class="card-title" style="font-size: 20px; font-weight: 700; color: var(--admin-navy); margin: 0;">
        <i class="ri-file-list-3-line" style="color: var(--admin-teal);"></i> Service Detail Studio (<?= count($services) ?>)
      </h2>
      <p style="font-size: 13px; color: var(--admin-muted); margin-top: 4px;">
        Select any service below to customize its dedicated editorial detail page, Single Image hero cover, narrative, 3 impact pillars, 4 personas, 4 framework steps, 5 FAQs, and CTA signature.
      </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
      <a href="<?= url('services.php') ?>" target="_blank" class="btn-adm btn-adm-outline">
        <i class="ri-external-link-line"></i> View Live Services Matrix
      </a>
      <a href="<?= url('admin/services/edit.php?id=new') ?>" class="btn-adm btn-adm-primary">
        <i class="ri-add-line"></i> Add New Service Page
      </a>
    </div>
  </div>

  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 50px;">Icon</th>
          <th style="width: 80px;">Artwork</th>
          <th>Service Title &amp; Slug</th>
          <th>Hero Headline &amp; Tag</th>
          <th style="width: 120px;">Metrics Impact</th>
          <th style="width: 80px;">Sort</th>
          <th style="width: 90px;">Status</th>
          <th style="width: 170px; text-align: right;">Studio Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($services)): ?>
        <tr>
          <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">
            No services found. Click "Add New Service Page" above to create one.
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($services as $srv): ?>
          <tr>
            <td>
              <div style="width: 38px; height: 38px; border-radius: 8px; background: var(--admin-teal-pale); color: var(--admin-teal); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="<?= e($srv['icon'] ?: 'ri-quill-pen-line') ?>"></i>
              </div>
            </td>
            <td>
              <?php if (!empty($srv['image_path'])): ?>
                <img src="<?= media_url($srv['image_path']) ?>" alt="" style="width: 60px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border); background: #FAF8F5;">
              <?php else: ?>
                <div style="width: 60px; height: 42px; background: #FAF8F5; border: 1px dashed var(--admin-border); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--admin-muted); font-size: 11px; font-weight: 600;">
                  Default
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px;"><?= e($srv['title']) ?></div>
              <div style="font-size: 11px; color: var(--admin-muted); font-family: monospace; margin-top: 2px;">
                <code>/service/<?= e($srv['slug']) ?></code>
              </div>
            </td>
            <td>
              <div style="font-size: 11px; color: var(--admin-teal); font-weight: 700; font-family: monospace; text-transform: uppercase;">
                <?= e($srv['tag'] ?: 'Core Capability') ?>
              </div>
              <div style="font-size: 12px; color: var(--admin-navy); font-weight: 600; margin-top: 2px; max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?= e($srv['hero_headline'] ?: $srv['description']) ?>
              </div>
            </td>
            <td>
              <?php if (!empty($srv['metrics_val'])): ?>
                <div style="font-size: 12px; color: var(--admin-navy); font-weight: 700;">
                  <i class="ri-line-chart-line" style="color: var(--admin-teal);"></i> <?= e($srv['metrics_val']) ?>
                </div>
                <div style="font-size: 11px; color: var(--admin-muted);"><?= e($srv['metrics_lbl']) ?></div>
              <?php else: ?>
                <span style="color: var(--admin-muted);">—</span>
              <?php endif; ?>
            </td>
            <td style="font-weight: 600;"><?= (int)$srv['sort_order'] ?></td>
            <td>
              <?php 
              $isDev = (int)$srv['id'] > 7;
              if ($isDev && !$devServicesEnabled): ?>
                <span class="badge" style="background: #FEE2E2; color: #991B1B; font-size: 11px;" title="Turned OFF via Homepage Section 3/3C Master Toggle">Hidden (OFF)</span>
              <?php elseif ($srv['is_active']): ?>
                <span class="badge badge-teal" style="font-size: 11px;">Active</span>
              <?php else: ?>
                <span class="badge" style="background: #E2E8F0; color: #64748B; font-size: 11px;">Inactive</span>
              <?php endif; ?>
            </td>
            <td style="text-align: right;">
              <div class="table-actions" style="justify-content: flex-end;">
                <?php if ($isDev && !$devServicesEnabled): ?>
                  <span class="btn-adm-action" style="background: #F8FAFC; border: 1px dashed #CBD5E1; color: #94A3B8; cursor: not-allowed; opacity: 0.85;" title="This service is turned OFF via Homepage Section 3/3C Master Toggle. Turn toggle ON to edit or view.">
                    <i class="ri-lock-2-line"></i> <span>Studio Locked (OFF)</span>
                  </span>
                <?php else: ?>
                  <a href="<?= url('service/' . urlencode($srv['slug'])) ?>" target="_blank" class="btn-adm-action" style="background: #FFF; border: 1.5px solid #CBD5E1; color: var(--admin-navy);" title="View Live Detail Page">
                    <i class="ri-external-link-line"></i> <span>View Live</span>
                  </a>
                  <a href="<?= url('admin/services/edit.php?id=' . $srv['id']) ?>" class="btn-adm-action btn-adm-edit" title="Edit Service Detail Studio">
                    <i class="ri-edit-line"></i> <span>Edit Studio</span>
                  </a>
                <?php endif; ?>
                <a href="<?= url('admin/services/index.php?delete=' . $srv['id'] . '&csrf=' . CSRF::token()) ?>" class="btn-adm-action btn-adm-delete" onclick="return confirm('Are you sure you want to delete this service page?');" title="Delete Service">
                  <i class="ri-delete-bin-line"></i> <span>Delete</span>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>
