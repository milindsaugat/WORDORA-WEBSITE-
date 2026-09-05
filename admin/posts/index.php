<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$adminTitle = 'Blog Articles';

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        Post::delete($delId);
        flash_set('success', 'Article deleted successfully.');
        redirect('admin/posts/index.php');
    }
}

$posts = Post::getAll();

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h2 class="card-title"><i class="ri-article-line"></i> All Blog Articles (<?= count($posts) ?>)</h2>
    <a href="<?= url('admin/posts/edit.php') ?>" class="btn-adm btn-adm-primary btn-adm-sm">
      <i class="ri-add-line"></i> Write New Article
    </a>
  </div>

  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 60px;">Image</th>
          <th>Article Title</th>
          <th>Category</th>
          <th>Status</th>
          <th>Read Time</th>
          <th>Views</th>
          <th>Published Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($posts)): ?>
          <?php foreach ($posts as $post): ?>
          <tr>
            <td>
              <?php if (!empty($post['featured_img'])): ?>
                <img src="<?= media_url($post['featured_img']) ?>" alt="" style="width: 48px; height: 34px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border);">
              <?php else: ?>
                <div style="width: 48px; height: 34px; background: var(--admin-teal-pale); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal);">
                  <i class="ri-file-text-line"></i>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-weight: 700; color: var(--admin-navy);">
                <a href="<?= url('admin/posts/edit.php?id=' . $post['id']) ?>" style="color: var(--admin-navy);">
                  <?= e($post['title']) ?>
                </a>
              </div>
              <div style="font-size: 11px; color: var(--admin-muted);">
                <code>/blog/<?= e($post['slug']) ?></code>
              </div>
            </td>
            <td>
              <span class="badge-status" style="background: var(--admin-teal-pale); color: var(--admin-navy);">
                <?= e($post['category_name'] ?? 'Uncategorized') ?>
              </span>
            </td>
            <td>
              <span class="badge-status badge-<?= e($post['status']) ?>">
                <?= ucfirst($post['status']) ?>
              </span>
            </td>
            <td><?= (int)$post['read_time'] ?> min</td>
            <td><?= (int)$post['views'] ?></td>
            <td style="font-size: 12px; color: var(--admin-muted);">
              <?= date('M d, Y', strtotime($post['created_at'])) ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <div class="table-actions" style="justify-content: flex-end;">
                <a href="<?= url('blog/' . e($post['slug'])) ?>" target="_blank" class="btn-adm-action" style="background: #FFF; border: 1.5px solid #CBD5E1; color: var(--admin-navy);" title="View Live">
                  <i class="ri-eye-line"></i>
                </a>
                <a href="<?= url('admin/posts/edit.php?id=' . $post['id']) ?>" class="btn-adm-action btn-adm-edit" title="Edit">
                  <i class="ri-edit-line"></i> <span>Edit</span>
                </a>
                <a href="<?= url('admin/posts/index.php?delete=' . $post['id']) ?>" class="btn-adm-action btn-adm-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this article?')">
                  <i class="ri-delete-bin-line"></i> <span>Delete</span>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align: center; color: var(--admin-muted); padding: 36px;">
              No articles found. Click "Write New Article" to publish your first post.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>
