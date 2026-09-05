<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$adminTitle = 'Contact Leads & Inquiries';

// Handle Status Change
if (isset($_GET['mark_read'])) {
    $leadId = (int)$_GET['mark_read'];
    Contact::markRead($leadId);
    flash_set('success', 'Lead marked as read.');
    redirect('admin/leads/index.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $db = DB::getInstance();
    $db->prepare("DELETE FROM contacts WHERE id = ?")->execute([$delId]);
    flash_set('success', 'Lead deleted successfully.');
    redirect('admin/leads/index.php');
}

$statusFilter = trim($_GET['status'] ?? '');
$leads = Contact::getAll($statusFilter);

include ROOT_PATH . '/admin/includes/header.php';
?>

<!-- Filter Bar -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
  <div style="display: flex; gap: 8px;">
    <a href="<?= url('admin/leads/index.php') ?>" class="btn-adm <?= empty($statusFilter) ? 'btn-adm-primary' : 'btn-adm-outline' ?> btn-adm-sm">
      All Leads (<?= Contact::countAll() ?>)
    </a>
    <a href="<?= url('admin/leads/index.php?status=unread') ?>" class="btn-adm <?= $statusFilter === 'unread' ? 'btn-adm-primary' : 'btn-adm-outline' ?> btn-adm-sm">
      Unread (<?= Contact::countByStatus('unread') ?>)
    </a>
    <a href="<?= url('admin/leads/index.php?status=read') ?>" class="btn-adm <?= $statusFilter === 'read' ? 'btn-adm-primary' : 'btn-adm-outline' ?> btn-adm-sm">
      Read (<?= Contact::countByStatus('read') ?>)
    </a>
  </div>
</div>

<div class="admin-card">
  <div class="card-header">
    <h2 class="card-title"><i class="ri-mail-line"></i> Inquiries (<?= count($leads) ?>)</h2>
  </div>

  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Client Name</th>
          <th>Email</th>
          <th>Company</th>
          <th>Service Required</th>
          <th>Budget</th>
          <th>Message</th>
          <th>Status</th>
          <th>Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($leads)): ?>
          <?php foreach ($leads as $lead): ?>
          <tr style="<?= $lead['status'] === 'unread' ? 'background: #FFFBF5; font-weight: 500;' : '' ?>">
            <td style="font-weight: 700; color: var(--admin-navy);"><?= e($lead['name']) ?></td>
            <td>
              <a href="mailto:<?= e($lead['email']) ?>?subject=Re:%20Inquiry%20from%20WORDORA" style="color: var(--admin-teal);">
                <?= e($lead['email']) ?>
              </a>
            </td>
            <td><?= e($lead['company'] ?: '—') ?></td>
            <td>
              <span class="badge-status" style="background: var(--admin-teal-pale); color: var(--admin-navy);">
                <?= e($lead['service'] ?: 'General') ?>
              </span>
            </td>
            <td><?= e($lead['budget'] ?: '—') ?></td>
            <td>
              <div style="max-width: 260px; font-size: 12px; color: var(--admin-muted); line-height: 1.4;" title="<?= e($lead['message']) ?>">
                <?= e(truncate($lead['message'], 75)) ?>
              </div>
            </td>
            <td>
              <span class="badge-status badge-<?= e($lead['status']) ?>">
                <?= ucfirst($lead['status']) ?>
              </span>
            </td>
            <td style="font-size: 12px; color: var(--admin-muted); white-space: nowrap;">
              <?= date('M d, Y H:i', strtotime($lead['submitted_at'])) ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <div class="table-actions" style="justify-content: flex-end;">
                <?php if ($lead['status'] === 'unread'): ?>
                  <a href="<?= url('admin/leads/index.php?mark_read=' . $lead['id']) ?>" class="btn-adm-action" style="background: #FFF; border: 1.5px solid #CBD5E1; color: var(--admin-navy);" title="Mark as Read">
                    <i class="ri-check-line"></i> <span>Read</span>
                  </a>
                <?php endif; ?>
                <a href="mailto:<?= e($lead['email']) ?>?subject=Re:%20Inquiry%20from%20WORDORA" class="btn-adm-action btn-adm-edit" title="Reply via Email">
                  <i class="ri-reply-line"></i> <span>Reply</span>
                </a>
                <a href="<?= url('admin/leads/index.php?delete=' . $lead['id']) ?>" class="btn-adm-action btn-adm-delete" title="Delete" onclick="return confirm('Delete this inquiry?')">
                  <i class="ri-delete-bin-line"></i> <span>Delete</span>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; color: var(--admin-muted); padding: 36px;">
              No inquiries found in this view.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>
