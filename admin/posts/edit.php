<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$post = null;

if ($id) {
    $post = Post::getById($id);
    if (!$post) {
        flash_set('error', 'Article not found.');
        redirect('admin/posts/index.php');
    }
}

$adminTitle = $id ? 'Edit Article' : 'Write New Article';
$categories = Category::getAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';

        if (empty($title)) {
            $error = 'Article title is required.';
        } elseif (empty($content)) {
            $error = 'Article content cannot be empty.';
        } else {
            if (empty($slug)) {
                $slug = slugify($title);
            } else {
                $slug = slugify($slug);
            }

            $existingImg = $_POST['existing_featured_img'] ?? ($post['featured_img'] ?? '');
            $featuredImg = $existingImg;

            // Handle featured image upload / removal (Priority: New Upload > Remove > Existing)
            if (isset($_FILES['featured_img_file']) && $_FILES['featured_img_file']['error'] === UPLOAD_ERR_OK) {
                $uploader = new Upload('blog');
                $uploadRes = $uploader->handle($_FILES['featured_img_file']);
                if ($uploadRes['success']) {
                    if (!empty($existingImg) && $existingImg !== $uploadRes['path']) {
                        delete_uploaded_file($existingImg);
                    }
                    $featuredImg = $uploadRes['path'];
                } else {
                    $error = 'Image upload error: ' . $uploadRes['msg'];
                }
            } elseif (!empty($_POST['remove_featured_img']) && $_POST['remove_featured_img'] === '1') {
                delete_uploaded_file($existingImg);
                $featuredImg = '';
            }

            if (empty($error)) {
                $data = [
                    'title'        => $title,
                    'slug'         => $slug,
                    'excerpt'      => trim($_POST['excerpt'] ?? ''),
                    'content'      => $content,
                    'featured_img' => $featuredImg,
                    'category_id'  => (int)($_POST['category_id'] ?? 0),
                    'author_id'    => (int)(Auth::user('id') ?? 1),
                    'status'       => $_POST['status'] ?? 'draft',
                    'read_time'    => max(1, (int)($_POST['read_time'] ?? read_time($content))),
                    'meta_title'   => trim($_POST['meta_title'] ?? ''),
                    'meta_desc'    => trim($_POST['meta_desc'] ?? ''),
                    'meta_keywords'=> trim($_POST['meta_keywords'] ?? ''),
                ];

                Post::save($data, $id);
                flash_set('success', $id ? 'Article updated successfully!' : 'Article published successfully!');
                redirect('admin/posts/index.php');
            }
        }
    }
}

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h2 class="card-title"><?= $id ? 'Edit Article: ' . e($post['title']) : 'Write New Article' ?></h2>
    <a href="<?= url('admin/posts/index.php') ?>" class="btn-adm btn-adm-outline btn-adm-sm">
      <i class="ri-arrow-left-line"></i> Back to Articles
    </a>
  </div>

  <div class="card-body">
    <?php if ($error): ?>
      <div style="margin-bottom: 20px; padding: 12px; border-radius: 6px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
        <i class="ri-error-warning-line"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="<?= $id ? url('admin/posts/edit.php?id=' . $id) : url('admin/posts/edit.php') ?>">
      <?= CSRF::field() ?>
      <input type="hidden" name="existing_featured_img" value="<?= e($post['featured_img'] ?? '') ?>">

      <div class="form-grid">
        <div class="form-field full">
          <label class="field-label" for="postTitle">Article Title *</label>
          <input type="text" id="postTitle" name="title" class="field-input" required placeholder="e.g. The Anatomy of a High-Converting Brand Narrative" value="<?= e($_POST['title'] ?? $post['title'] ?? '') ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="postSlug">URL Slug (leave blank to auto-generate)</label>
          <input type="text" id="postSlug" name="slug" class="field-input" placeholder="anatomy-of-high-converting-brand-narrative" value="<?= e($_POST['slug'] ?? $post['slug'] ?? '') ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="postCategory" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Category</span>
            <a href="<?= url('admin/pages/blog.php?tab=sec03') ?>" target="_blank" style="font-size: 11px; color: var(--admin-teal); font-weight: 700; text-decoration: none;">
              <i class="ri-add-line"></i> Manage Categories
            </a>
          </label>
          <select id="postCategory" name="category_id" class="field-select">
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? $post['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-field full">
          <label class="field-label" for="postExcerpt">Short Excerpt / Summary</label>
          <textarea id="postExcerpt" name="excerpt" class="field-textarea" style="min-height: 70px;" placeholder="A brief summary displayed on the blog archive and search snippets..."><?= e($_POST['excerpt'] ?? $post['excerpt'] ?? '') ?></textarea>
        </div>

        <!-- Rich WYSIWYG Content Field -->
        <div class="form-field full">
          <label class="field-label" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="ri-edit-2-line" style="color: var(--admin-teal);"></i> Article Body Content (Rich WYSIWYG Editor) *</span>
            <span style="font-size: 11px; font-weight: normal; color: var(--admin-muted);">Matching Website Fonts: Inter • Playfair Display • DM Sans • JetBrains Mono</span>
          </label>
          
          <!-- Hidden Input for Form Submission -->
          <input type="hidden" id="postContent" name="content" value="<?= htmlspecialchars($_POST['content'] ?? $post['content'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
          
          <!-- Quill Editor Container -->
          <div style="background: #FFFFFF; border-radius: 8px; overflow: hidden; border: 1.5px solid var(--admin-border); margin-top: 6px;">
            <div id="quillToolbar"></div>
            <div id="quillEditor" style="min-height: 380px; font-size: 15px; line-height: 1.75; font-family: 'Inter', sans-serif;">
              <?= $_POST['content'] ?? $post['content'] ?? '' ?>
            </div>
          </div>
          <div class="field-help" style="margin-top: 8px;">
            Use the rich toolbar above for Headings (Playfair Display), Body text (Inter), Code (JetBrains Mono), Bold, Italic, Bullet Lists, Blockquotes, Hyperlinks, and Media.
          </div>
        </div>

        <!-- Featured Image (File Upload Card) -->
        <div class="form-field full" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border);">
          <label class="field-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--admin-navy);">
            <i class="ri-image-2-line" style="color: var(--admin-teal);"></i> Featured Article Image
          </label>
          
          <?php if (!empty($post['featured_img'])): ?>
            <div id="preview_post_img" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
              <img src="<?= media_url($post['featured_img']) ?>" alt="Current featured image" style="max-height: 70px; border-radius: 4px; border: 1px solid var(--admin-border);">
              <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 600; color: var(--admin-navy);">Active Featured Image</div>
                <div style="font-size: 11px; color: var(--admin-muted); word-break: break-all;"><?= e($post['featured_img']) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_featured_img', 'preview_post_img')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                <i class="ri-delete-bin-line"></i> Remove Image
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_featured_img" id="remove_featured_img" value="0">

          <label class="field-label" style="font-size: 12px;">Upload New Image (PNG, JPG, WebP)</label>
          <input type="file" name="featured_img_file" class="field-input" accept="image/*">
        </div>

        <!-- Publication Settings -->
        <div class="form-field">
          <label class="field-label" for="postStatus">Publishing Status</label>
          <select id="postStatus" name="status" class="field-select">
            <option value="published" <?= ($_POST['status'] ?? $post['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published (Live)</option>
            <option value="draft" <?= ($_POST['status'] ?? $post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Hidden)</option>
          </select>
        </div>

        <div class="form-field">
          <label class="field-label" for="postReadTime">Estimated Read Time (Minutes)</label>
          <input type="number" id="postReadTime" name="read_time" class="field-input" min="1" max="60" value="<?= (int)($_POST['read_time'] ?? $post['read_time'] ?? 5) ?>">
        </div>

        <!-- SEO Metadata Box -->
        <div class="form-field full" style="background: #F8FAFC; padding: 16px; border-radius: 8px; border: 1px solid var(--admin-border); margin-top: 8px;">
          <h3 style="font-size: 13px; font-weight: 700; color: var(--admin-navy); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-search-eye-line" style="color: var(--admin-teal);"></i> Search Engine Optimization (SEO)
          </h3>
          <div class="form-grid">
            <div class="form-field">
              <label class="field-label" for="metaTitle">SEO Meta Title</label>
              <input type="text" id="metaTitle" name="meta_title" class="field-input" placeholder="e.g. Master Guide: Brand Copywriting in 2026" value="<?= e($_POST['meta_title'] ?? $post['meta_title'] ?? '') ?>">
              <div class="field-help">Recommended length: 50–60 characters.</div>
            </div>

            <div class="form-field">
              <label class="field-label" for="metaDesc">SEO Meta Description</label>
              <input type="text" id="metaDesc" name="meta_desc" class="field-input" placeholder="e.g. Learn how leading brands build narratives that convert..." value="<?= e($_POST['meta_desc'] ?? $post['meta_desc'] ?? '') ?>">
              <div class="field-help">Recommended length: 140–160 characters.</div>
            </div>

            <div class="form-field full">
              <label class="field-label" for="metaKeywords">SEO Focus Keywords (Comma-Separated)</label>
              <input type="text" id="metaKeywords" name="meta_keywords" class="field-input" placeholder="e.g. Brand Copywriting, B2B Content Strategy, Conversion Storytelling" value="<?= e($_POST['meta_keywords'] ?? $post['meta_keywords'] ?? '') ?>">
              <div class="field-help">Separate focus keyword phrases with commas.</div>
            </div>
          </div>
        </div>
      </div>

      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--admin-border); display: flex; gap: 12px;">
        <button type="submit" class="btn-adm btn-adm-primary">
          <i class="ri-save-line"></i> <?= $id ? 'Update Article' : 'Publish Article' ?>
        </button>
        <a href="<?= url('admin/posts/index.php') ?>" class="btn-adm btn-adm-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<!-- Google Fonts for WYSIWYG Editor -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">

<!-- Quill.js WYSIWYG Rich Editor Styles & Scripts -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<style>
/* Quill Toolbar & Container Styling */
.ql-toolbar.ql-snow {
  background: #FAF8F5;
  border: 1.5px dashed rgba(74, 139, 140, 0.35) !important;
  border-bottom: 1.5px solid var(--admin-border) !important;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  padding: 10px 14px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 10px;
}
.ql-toolbar.ql-snow .ql-formats {
  margin-right: 0 !important;
  display: inline-flex;
  align-items: center;
  gap: 2px;
}
.ql-snow .ql-picker.ql-font {
  width: 175px !important;
}
.ql-snow .ql-picker.ql-header {
  width: 115px !important;
}
.ql-snow .ql-picker-label {
  display: flex !important;
  align-items: center !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  padding-right: 22px !important;
  font-size: 13px !important;
}
.ql-snow .ql-picker-label::before {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}
.ql-snow .ql-picker-options {
  min-width: 190px !important;
  border-radius: 8px !important;
  box-shadow: 0 10px 25px rgba(15, 30, 54, 0.12) !important;
  border: 1px solid rgba(74, 139, 140, 0.25) !important;
  padding: 6px 0 !important;
}
.ql-snow .ql-picker-item {
  padding: 6px 12px !important;
  font-size: 13px !important;
}

.ql-container.ql-snow {
  border: 1.5px dashed rgba(74, 139, 140, 0.35) !important;
  border-top: none !important;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
  font-family: 'Inter', sans-serif;
  background: #FFFFFF;
}
.ql-editor {
  min-height: 380px;
  font-size: 15px;
  line-height: 1.8;
  color: var(--admin-navy);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
.ql-editor h1, .ql-editor h2, .ql-editor h3, .ql-editor h4 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--admin-navy);
  margin-top: 1.3em;
  margin-bottom: 0.5em;
  font-weight: 700;
}
.ql-editor blockquote {
  border-left: 4px solid var(--admin-teal);
  padding: 12px 18px;
  color: #475569;
  font-style: italic;
  background: rgba(74, 139, 140, 0.06);
  border-radius: 0 8px 8px 0;
}
.ql-editor pre.ql-syntax {
  background: #0F1E36;
  color: #F8FAFC;
  border-radius: 8px;
  padding: 14px;
  font-family: 'JetBrains Mono', monospace;
}
.ql-editor code {
  font-family: 'JetBrains Mono', monospace;
  background: #F1F5F9;
  color: var(--admin-teal);
  padding: 2px 6px;
  border-radius: 4px;
}

/* Custom Font Whitelist CSS */
.ql-font-inter { font-family: 'Inter', sans-serif !important; }
.ql-font-playfair { font-family: 'Playfair Display', Georgia, serif !important; }
.ql-font-dmsans { font-family: 'DM Sans', sans-serif !important; }
.ql-font-jetbrains { font-family: 'JetBrains Mono', monospace !important; }
.ql-font-georgia { font-family: Georgia, serif !important; }

/* Custom Font Dropdown Labels */
.ql-snow .ql-picker.ql-font .ql-picker-label::before,
.ql-snow .ql-picker.ql-font .ql-picker-item::before,
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="inter"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="inter"]::before {
  content: 'Inter (Body)';
  font-family: 'Inter', sans-serif;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="playfair"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="playfair"]::before {
  content: 'Playfair (Headings)';
  font-family: 'Playfair Display', Georgia, serif;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="dmsans"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="dmsans"]::before {
  content: 'DM Sans (Clean)';
  font-family: 'DM Sans', sans-serif;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="jetbrains"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="jetbrains"]::before {
  content: 'JetBrains Mono (Code)';
  font-family: 'JetBrains Mono', monospace;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before {
  content: 'Georgia (Editorial)';
  font-family: Georgia, serif;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Register Website Fonts with Quill Font Attributor
  const Font = Quill.import('formats/font');
  Font.whitelist = ['inter', 'playfair', 'dmsans', 'jetbrains', 'georgia'];
  Quill.register(Font, true);

  const toolbarOptions = [
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [{ 'font': ['inter', 'playfair', 'dmsans', 'jetbrains', 'georgia'] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'script': 'sub'}, { 'script': 'super' }],
    ['blockquote', 'code-block'],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    [{ 'indent': '-1'}, { 'indent': '+1' }],
    [{ 'direction': 'rtl' }],
    [{ 'align': [] }],
    ['link', 'image', 'video'],
    ['clean']
  ];

  const quill = new Quill('#quillEditor', {
    modules: {
      toolbar: toolbarOptions
    },
    theme: 'snow',
    placeholder: 'Write your full authoritative article here... format headings with Playfair Display, body with Inter, add quotes, code blocks, links, and media seamlessly.'
  });

  // Sync Quill HTML content to hidden input before form submit
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function() {
      const html = quill.root.innerHTML;
      document.getElementById('postContent').value = (html === '<p><br></p>') ? '' : html;
    });
  }
});

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
