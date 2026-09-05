<?php
/**
 * WORDORA — Reusable Homepage Live Visual Section Editor Component
 * Included in admin/pages/home.php AND admin/index.php
 */

$editorError = '';
$activeTab = $_GET['tab'] ?? 'sec01';
$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');

// Handle Delete Slide
if (isset($_GET['delete_slide'])) {
    $delId = (int)$_GET['delete_slide'];
    if ($delId > 0) {
        Hero::delete($delId);
        flash_set('success', 'Hero slide deleted successfully.');
        redirect($currentUrl . '?tab=sec01');
    }
}

// Handle POST Save for all sections
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_editor_submit'])) {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $editorError = 'Security token expired. Please try again.';
    } else {
        $uploader = new Upload('home');
        $tab = $_POST['tab'] ?? 'sec01';
        $activeTab = $tab;

        // Section 01: Hero Banner, Opacity & Video (Upload + URL)
        if ($tab === 'sec01') {
            if (isset($_POST['hero_mode'])) {
                Setting::set('hero_mode', $_POST['hero_mode']);
            }
            if (isset($_POST['hero_overlay_opacity'])) {
                Setting::set('hero_overlay_opacity', (string)max(0, min(100, (int)$_POST['hero_overlay_opacity'])));
            }

            $existingHeroVideo = setting('hero_video_url', '');
            $heroVideo = $existingHeroVideo;

            if (!empty($_POST['remove_hero_video']) && $_POST['remove_hero_video'] === '1') {
                delete_uploaded_file($existingHeroVideo);
                $heroVideo = '';
            } elseif (isset($_FILES['hero_video_file']) && $_FILES['hero_video_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['hero_video_file'], true);
                if ($up['success']) { 
                    if (!empty($existingHeroVideo) && $existingHeroVideo !== $up['path']) {
                        delete_uploaded_file($existingHeroVideo);
                    }
                    $heroVideo = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (isset($_POST['hero_video_url'])) {
                $newVideoUrl = trim($_POST['hero_video_url']);
                if ($newVideoUrl !== $existingHeroVideo && !empty($existingHeroVideo)) {
                    delete_uploaded_file($existingHeroVideo);
                }
                $heroVideo = $newVideoUrl;
            }

            if (!$editorError) {
                Setting::set('hero_video_url', $heroVideo);
            }
        }

        // Section 02: The Editorial Standard
        if ($tab === 'sec02') {
            $sec2Art = setting('home_sec2_artwork', '/img/service treasure.png');
            if (isset($_FILES['home_sec2_artwork_file']) && $_FILES['home_sec2_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['home_sec2_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec2Art) && $sec2Art !== $up['path']) {
                        delete_uploaded_file($sec2Art);
                    }
                    $sec2Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec2_artwork']) && $_POST['remove_sec2_artwork'] === '1') {
                delete_uploaded_file($sec2Art);
                $sec2Art = '/img/service treasure.png';
            }

            if (!$editorError) {
                Setting::set('home_sec2_badge', trim($_POST['home_sec2_badge'] ?? ''));
                Setting::set('home_sec2_title', trim($_POST['home_sec2_title'] ?? ''));
                Setting::set('home_sec2_p1', trim($_POST['home_sec2_p1'] ?? ''));
                Setting::set('home_sec2_quote', trim($_POST['home_sec2_quote'] ?? ''));
                Setting::set('home_sec2_p2', trim($_POST['home_sec2_p2'] ?? ''));
                Setting::set('home_sec2_btn1_text', trim($_POST['home_sec2_btn1_text'] ?? ''));
                Setting::set('home_sec2_btn1_url', trim($_POST['home_sec2_btn1_url'] ?? ''));
                Setting::set('home_sec2_btn2_text', trim($_POST['home_sec2_btn2_text'] ?? ''));
                Setting::set('home_sec2_btn2_url', trim($_POST['home_sec2_btn2_url'] ?? ''));
                Setting::set('home_sec2_artwork', $sec2Art);
                Setting::set('home_sec2_artwork_tag', trim($_POST['home_sec2_artwork_tag'] ?? ''));
            }
        }

        // Section 03: What We Do (Bento Tiles & Marquee Stream)
        if ($tab === 'sec03') {
            $marqueeBg = setting('marquee_bg_image', '/img/papaer banner.png');
            if (isset($_FILES['marquee_bg_file']) && $_FILES['marquee_bg_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['marquee_bg_file']);
                if ($up['success']) { 
                    if (!empty($marqueeBg) && $marqueeBg !== $up['path']) {
                        delete_uploaded_file($marqueeBg);
                    }
                    $marqueeBg = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_marquee_bg']) && $_POST['remove_marquee_bg'] === '1') {
                delete_uploaded_file($marqueeBg);
                $marqueeBg = '/img/papaer banner.png';
            }

            // Process Bento Tiles
            $bentoTiles = [];
            if (!empty($_POST['bento']) && is_array($_POST['bento'])) {
                foreach ($_POST['bento'] as $b) {
                    $bUrl = trim($b['btn_url'] ?? '');
                    if (preg_match('~^/?service-detail(?:\.php)?\?slug=([a-zA-Z0-9\-]+)$~i', $bUrl, $bm)) {
                        $bUrl = 'service/' . $bm[1];
                    }
                    $b2Url = trim($b['btn2_url'] ?? '');
                    if (preg_match('~^/?service-detail(?:\.php)?\?slug=([a-zA-Z0-9\-]+)$~i', $b2Url, $bm2)) {
                        $b2Url = 'service/' . $bm2[1];
                    }

                    $bentoTiles[] = [
                        'badge'    => trim($b['badge'] ?? ''),
                        'icon'     => trim($b['icon'] ?? ''),
                        'title'    => trim($b['title'] ?? ''),
                        'desc'     => trim($b['desc'] ?? ''),
                        'tags'     => trim($b['tags'] ?? ''),
                        'btn_text' => trim($b['btn_text'] ?? ''),
                        'btn_url'  => $bUrl,
                        'btn2_text'=> trim($b['btn2_text'] ?? ''),
                        'btn2_url' => $b2Url,
                    ];
                }
            }

            // Process Marquee Stream Rows
            $marqueeRows = [
                'row1' => trim($_POST['marquee_row1'] ?? ''),
                'row2' => trim($_POST['marquee_row2'] ?? ''),
                'row3' => trim($_POST['marquee_row3'] ?? ''),
            ];

            if (!$editorError) {
                Setting::set('home_sec3_label', trim($_POST['home_sec3_label'] ?? ''));
                Setting::set('home_sec3_title', trim($_POST['home_sec3_title'] ?? ''));
                Setting::set('home_sec3_desc', trim($_POST['home_sec3_desc'] ?? ''));
                Setting::set('home_sec3_marquee_label', trim($_POST['home_sec3_marquee_label'] ?? ''));
                Setting::set('home_sec3_marquee_title', trim($_POST['home_sec3_marquee_title'] ?? ''));
                Setting::set('marquee_bg_image', $marqueeBg);
                if (!empty($bentoTiles)) {
                    Setting::set('home_sec3_bento', json_encode($bentoTiles, JSON_UNESCAPED_UNICODE));
                }
                Setting::set('home_sec3_marquee_rows', json_encode($marqueeRows, JSON_UNESCAPED_UNICODE));

                // Synchronized Master Toggle for 7 Dev Services from Section 03
                if (isset($_POST['home_sec3c_master_toggle_present'])) {
                    Setting::set('home_sec3c_enabled', (!empty($_POST['home_sec3c_enabled']) && $_POST['home_sec3c_enabled'] !== '0') ? '1' : '0');
                }
            }
        }

        // Section 03C: Other / Dev Services Bento
        if ($tab === 'sec03c') {
            $devTiles = [];
            if (!empty($_POST['dev_bento']) && is_array($_POST['dev_bento'])) {
                foreach ($_POST['dev_bento'] as $dItem) {
                    $dUrl = trim($dItem['btn_url'] ?? '');
                    if (preg_match('~^/?service-detail(?:\.php)?\?slug=([a-zA-Z0-9\-]+)$~i', $dUrl, $dm)) {
                        $dUrl = 'service/' . $dm[1];
                    }
                    $devTiles[] = [
                        'badge'    => trim($dItem['badge'] ?? ''),
                        'icon'     => trim($dItem['icon'] ?? 'ri-code-box-line'),
                        'title'    => trim($dItem['title'] ?? ''),
                        'desc'     => trim($dItem['desc'] ?? ''),
                        'btn_text' => trim($dItem['btn_text'] ?? ''),
                        'btn_url'  => $dUrl,
                    ];
                }
            }

            if (!$editorError) {
                Setting::set('home_sec3c_enabled', (!empty($_POST['home_sec3c_enabled']) && $_POST['home_sec3c_enabled'] !== '0') ? '1' : '0');
                Setting::set('home_sec3c_label', trim($_POST['home_sec3c_label'] ?? ''));
                Setting::set('home_sec3c_title', trim($_POST['home_sec3c_title'] ?? ''));
                Setting::set('home_sec3c_desc', trim($_POST['home_sec3c_desc'] ?? ''));
                if (!empty($devTiles)) {
                    Setting::set('home_sec3c_bento', json_encode($devTiles, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 04: Why WORDORA
        if ($tab === 'sec04') {
            $sec4Art = setting('home_sec4_artwork', '/img/why choose us.png');
            if (isset($_FILES['home_sec4_artwork_file']) && $_FILES['home_sec4_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['home_sec4_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec4Art) && $sec4Art !== $up['path']) {
                        delete_uploaded_file($sec4Art);
                    }
                    $sec4Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec4_artwork']) && $_POST['remove_sec4_artwork'] === '1') {
                delete_uploaded_file($sec4Art);
                $sec4Art = '/img/why choose us.png';
            }

            // Cards array
            $whyCards = [];
            if (!empty($_POST['why_cards']) && is_array($_POST['why_cards'])) {
                foreach ($_POST['why_cards'] as $c) {
                    if (!empty($c['title'])) {
                        $whyCards[] = [
                            'icon'  => trim($c['icon'] ?? 'ri-quill-pen-line'),
                            'title' => trim($c['title'] ?? ''),
                            'desc'  => trim($c['desc'] ?? '')
                        ];
                    }
                }
            }

            // Stats array
            $stats = [];
            if (!empty($_POST['stats']) && is_array($_POST['stats'])) {
                foreach ($_POST['stats'] as $s) {
                    if (!empty($s['label'])) {
                        $stats[] = [
                            'count'  => trim($s['count'] ?? '0'),
                            'suffix' => trim($s['suffix'] ?? '+'),
                            'label'  => trim($s['label'] ?? '')
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('home_sec4_label', trim($_POST['home_sec4_label'] ?? ''));
                Setting::set('home_sec4_title', trim($_POST['home_sec4_title'] ?? ''));
                Setting::set('home_sec4_desc', trim($_POST['home_sec4_desc'] ?? ''));
                Setting::set('home_sec4_quote', trim($_POST['home_sec4_quote'] ?? ''));
                Setting::set('home_sec4_btn_text', trim($_POST['home_sec4_btn_text'] ?? ''));
                Setting::set('home_sec4_btn_url', trim($_POST['home_sec4_btn_url'] ?? ''));
                Setting::set('home_sec4_artwork', $sec4Art);
                if (!empty($whyCards)) {
                    Setting::set('home_sec4_cards', json_encode($whyCards, JSON_UNESCAPED_UNICODE));
                }
                if (!empty($stats)) {
                    Setting::set('home_sec4_stats', json_encode($stats, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 05: Who We Write For (Industry Matrix)
        if ($tab === 'sec05') {
            $sec5Art = setting('home_sec5_artwork', '/img/industry.png');
            if (isset($_FILES['home_sec5_artwork_file']) && $_FILES['home_sec5_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['home_sec5_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec5Art) && $sec5Art !== $up['path']) {
                        delete_uploaded_file($sec5Art);
                    }
                    $sec5Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec5_artwork']) && $_POST['remove_sec5_artwork'] === '1') {
                delete_uploaded_file($sec5Art);
                $sec5Art = '/img/industry.png';
            }

            $slides = [];
            if (!empty($_POST['slides']) && is_array($_POST['slides'])) {
                foreach ($_POST['slides'] as $idx => $sl) {
                    if (!empty($sl['title'])) {
                        $img = $sl['img'] ?? '/img/case study.png';
                        if (isset($_FILES['slide_img_' . $idx]) && $_FILES['slide_img_' . $idx]['error'] === UPLOAD_ERR_OK) {
                            $up = $uploader->handle($_FILES['slide_img_' . $idx]);
                            if ($up['success']) { 
                                if (!empty($img) && $img !== $up['path']) {
                                    delete_uploaded_file($img);
                                }
                                $img = $up['path']; 
                            }
                        }
                        $slides[] = [
                            'badge'     => trim($sl['badge'] ?? ''),
                            'title'     => trim($sl['title'] ?? ''),
                            'desc'      => trim($sl['desc'] ?? ''),
                            'm1_val'    => trim($sl['m1_val'] ?? ''),
                            'm1_lbl'    => trim($sl['m1_lbl'] ?? ''),
                            'm2_val'    => trim($sl['m2_val'] ?? ''),
                            'm2_lbl'    => trim($sl['m2_lbl'] ?? ''),
                            'btn1_text' => trim($sl['btn1_text'] ?? ''),
                            'btn1_url'  => trim($sl['btn1_url'] ?? ''),
                            'btn2_text' => trim($sl['btn2_text'] ?? ''),
                            'btn2_url'  => trim($sl['btn2_url'] ?? ''),
                            'media_tag' => trim($sl['media_tag'] ?? 'Client Success Study'),
                            'img'       => $img
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('home_sec5_label', trim($_POST['home_sec5_label'] ?? ''));
                Setting::set('home_sec5_title', trim($_POST['home_sec5_title'] ?? ''));
                Setting::set('home_sec5_desc', trim($_POST['home_sec5_desc'] ?? ''));
                Setting::set('home_sec5_artwork', $sec5Art);
                if (!empty($slides)) {
                    Setting::set('home_sec5_slides', json_encode($slides, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 06: How We Work (Process)
        if ($tab === 'sec06') {
            $sec6Art = setting('home_sec6_artwork', '/img/process.png');
            if (isset($_FILES['home_sec6_artwork_file']) && $_FILES['home_sec6_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['home_sec6_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec6Art) && $sec6Art !== $up['path']) {
                        delete_uploaded_file($sec6Art);
                    }
                    $sec6Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec6_artwork']) && $_POST['remove_sec6_artwork'] === '1') {
                delete_uploaded_file($sec6Art);
                $sec6Art = '/img/process.png';
            }

            $steps = [];
            if (!empty($_POST['steps']) && is_array($_POST['steps'])) {
                foreach ($_POST['steps'] as $st) {
                    if (!empty($st['title'])) {
                        $steps[] = [
                            'step_num' => trim($st['step_num'] ?? 'STEP 01'),
                            'title'    => trim($st['title'] ?? ''),
                            'desc'     => trim($st['desc'] ?? '')
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('home_sec6_label', trim($_POST['home_sec6_label'] ?? ''));
                Setting::set('home_sec6_title', trim($_POST['home_sec6_title'] ?? ''));
                Setting::set('home_sec6_desc', trim($_POST['home_sec6_desc'] ?? ''));
                Setting::set('home_sec6_artwork', $sec6Art);
                Setting::set('home_sec6_flow_tag', trim($_POST['home_sec6_flow_tag'] ?? ''));
                if (!empty($steps)) {
                    Setting::set('home_sec6_steps', json_encode($steps, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 07: Proof & Client Stories
        if ($tab === 'sec07') {
            $testis = [];
            if (!empty($_POST['testimonials']) && is_array($_POST['testimonials'])) {
                foreach ($_POST['testimonials'] as $idx => $t) {
                    if (!empty($t['author_name'])) {
                        $avatarImg = trim($t['avatar_img'] ?? '');
                        if (!empty($t['remove_avatar']) && $t['remove_avatar'] === '1') {
                            $avatarImg = '';
                        }
                        if (!empty($_FILES['testimonials']['name'][$idx]['avatar_file'])) {
                            try {
                                $fileArray = [
                                    'name'     => $_FILES['testimonials']['name'][$idx]['avatar_file'],
                                    'type'     => $_FILES['testimonials']['type'][$idx]['avatar_file'],
                                    'tmp_name' => $_FILES['testimonials']['tmp_name'][$idx]['avatar_file'],
                                    'error'    => $_FILES['testimonials']['error'][$idx]['avatar_file'],
                                    'size'     => $_FILES['testimonials']['size'][$idx]['avatar_file'],
                                ];
                                if ($fileArray['error'] === UPLOAD_ERR_OK) {
                                    $avatarImg = Upload::image($fileArray, 'testimonials');
                                }
                            } catch (\Exception $e) {
                                // keep existing
                            }
                        }

                        $testis[] = [
                            'quote'        => trim($t['quote'] ?? ''),
                            'author_name'  => trim($t['author_name'] ?? ''),
                            'author_role'  => trim($t['author_role'] ?? ''),
                            'author_badge' => trim($t['author_badge'] ?? 'Verified Client'),
                            'initials'     => trim($t['initials'] ?? 'PS'),
                            'avatar_bg'    => trim($t['avatar_bg'] ?? 'var(--color-navy)'),
                            'avatar_img'   => $avatarImg,
                            'stars'        => (int)($t['stars'] ?? 5)
                        ];
                    }
                }
            }

            $metrics = [];
            if (!empty($_POST['metrics']) && is_array($_POST['metrics'])) {
                foreach ($_POST['metrics'] as $m) {
                    if (!empty($m['label'])) {
                        $metrics[] = [
                            'num'   => trim($m['num'] ?? ''),
                            'label' => trim($m['label'] ?? '')
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('home_sec7_label', trim($_POST['home_sec7_label'] ?? ''));
                Setting::set('home_sec7_title', trim($_POST['home_sec7_title'] ?? ''));
                if (!empty($testis)) {
                    Setting::set('home_sec7_testimonials', json_encode($testis, JSON_UNESCAPED_UNICODE));
                }
                if (!empty($metrics)) {
                    Setting::set('home_sec7_metrics', json_encode($metrics, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 08: Editorial Desk
        if ($tab === 'sec08') {
            Setting::set('home_sec8_label', trim($_POST['home_sec8_label'] ?? ''));
            Setting::set('home_sec8_title', trim($_POST['home_sec8_title'] ?? ''));
            Setting::set('home_sec8_btn_text', trim($_POST['home_sec8_btn_text'] ?? ''));
            Setting::set('home_sec8_btn_url', trim($_POST['home_sec8_btn_url'] ?? ''));
            Setting::set('home_sec8_count', (string)max(1, min(12, (int)($_POST['home_sec8_count'] ?? 3))));
        }

        // Section 09: Start A Conversation (CTA)
        if ($tab === 'sec09') {
            $sec9Art = setting('home_sec9_artwork', '/img/cta 1.png');
            if (isset($_FILES['home_sec9_artwork_file']) && $_FILES['home_sec9_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['home_sec9_artwork_file']);
                if ($up['success']) { $sec9Art = $up['path']; } else { $editorError = $up['msg']; }
            } elseif (!empty($_POST['remove_sec9_artwork']) && $_POST['remove_sec9_artwork'] === '1') {
                $sec9Art = '/img/cta 1.png';
            }

            if (!$editorError) {
                Setting::set('home_sec9_badge', trim($_POST['home_sec9_badge'] ?? ''));
                Setting::set('home_sec9_title', trim($_POST['home_sec9_title'] ?? ''));
                Setting::set('home_sec9_desc', trim($_POST['home_sec9_desc'] ?? ''));
                Setting::set('home_sec9_btn1_text', trim($_POST['home_sec9_btn1_text'] ?? ''));
                Setting::set('home_sec9_btn1_url', trim($_POST['home_sec9_btn1_url'] ?? ''));
                Setting::set('home_sec9_btn2_text', trim($_POST['home_sec9_btn2_text'] ?? ''));
                Setting::set('home_sec9_btn2_url', trim($_POST['home_sec9_btn2_url'] ?? ''));
                Setting::set('home_sec9_pill1', trim($_POST['home_sec9_pill1'] ?? ''));
                Setting::set('home_sec9_pill2', trim($_POST['home_sec9_pill2'] ?? ''));
                Setting::set('home_sec9_pill3', trim($_POST['home_sec9_pill3'] ?? ''));
                Setting::set('home_sec9_artwork', $sec9Art);
            }
        }

        if (!$editorError) {
            flash_set('success', 'Homepage section saved successfully!');
            redirect($currentUrl . '?tab=' . $activeTab);
        }
    }
}

// Decode Data Arrays
$whyCards = json_decode(setting('home_sec4_cards', '[]'), true) ?: [];
$stats = json_decode(setting('home_sec4_stats', '[]'), true) ?: [];
$industrySlides = json_decode(setting('home_sec5_slides', '[]'), true) ?: [];
$processSteps = json_decode(setting('home_sec6_steps', '[]'), true) ?: [];
$testimonials = json_decode(setting('home_sec7_testimonials', '[]'), true) ?: [];
$resultMetrics = json_decode(setting('home_sec7_metrics', '[]'), true) ?: [];
$bentoTiles = json_decode(setting('home_sec3_bento', '[]'), true) ?: [];
$marqueeRows = json_decode(setting('home_sec3_marquee_rows', '[]'), true) ?: [];
$devBentoTiles = json_decode(setting('home_sec3c_bento', '[]'), true) ?: [];
if (empty($devBentoTiles)) {
    $devBentoTiles = [
        [
            'badge' => 'High Impact & Search', 'icon' => 'ri-search-eye-line',
            'title' => 'Technical SEO',
            'desc' => 'Advanced technical SEO, Core Web Vitals optimization, schema markup, and data-driven organic growth strategies.',
            'btn_text' => 'Explore SEO', 'btn_url' => 'service/seo'
        ],
        [
            'badge' => 'Scalable Systems', 'icon' => 'ri-code-box-line',
            'title' => 'Web App Development',
            'desc' => 'Custom, high-performance web applications built for speed, security, and scalable enterprise growth.',
            'btn_text' => 'Explore Web Apps', 'btn_url' => 'service/web-development'
        ],
        [
            'badge' => 'iOS & Android', 'icon' => 'ri-smartphone-line',
            'title' => 'Mobile App Development',
            'desc' => 'Native and cross-platform mobile experiences designed to engage users and drive retention.',
            'btn_text' => 'Explore Mobile Apps', 'btn_url' => 'service/mobile-development'
        ],
        [
            'badge' => 'Digital Storefronts', 'icon' => 'ri-macbook-line',
            'title' => 'Website Designing and Development',
            'desc' => 'Stunning, conversion-optimized websites that blend immersive design with flawless functionality.',
            'btn_text' => 'Explore Website Design', 'btn_url' => 'service/website-design'
        ],
        [
            'badge' => 'Future Ready', 'icon' => 'ri-robot-2-line',
            'title' => 'AI Development',
            'desc' => 'Intelligent AI integrations, machine learning models, and automated workflows that modernize operations.',
            'btn_text' => 'Explore AI Solutions', 'btn_url' => 'service/ai-development'
        ],
        [
            'badge' => 'Core Logic', 'icon' => 'ri-code-s-slash-line',
            'title' => 'Software Development',
            'desc' => 'End-to-end software solutions designed to solve complex business challenges with robust architectures.',
            'btn_text' => 'Explore Software', 'btn_url' => 'service/software-development'
        ],
        [
            'badge' => 'User Centric', 'icon' => 'ri-paint-brush-line',
            'title' => 'UI/UX Design',
            'desc' => 'Intuitive, user-centered interfaces and experiences that delight users and streamline interactions.',
            'btn_text' => 'Explore UI/UX', 'btn_url' => 'service/ui-ux-design'
        ]
    ];
}

// Fetch Hero slides for Section 01
$heroSlidesList = [];
try {
    $heroSlidesList = Hero::getAll();
} catch (Exception $e) {}
?>

<!-- Google Fonts for Real Visual Match -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Playfair+Display:ital,wght@0,500;0,700;0,800;1,500;1,700&display=swap" rel="stylesheet">

<style>
:root {
  --wdr-navy: #1B2A4A;
  --wdr-deep-navy: #0F1E36;
  --wdr-teal: #4A8B8C;
  --wdr-teal-light: #6BA8A9;
  --wdr-teal-pale: #D4EAEA;
  --wdr-canvas: #FAF8F5;
  --wdr-white: #FFFFFF;
  --wdr-font-display: 'Playfair Display', Georgia, serif;
  --wdr-font-body: 'Inter', sans-serif;
  --wdr-font-mono: 'JetBrains Mono', monospace;
}

.visual-studio-card {
  background: var(--wdr-canvas);
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 28px;
  position: relative;
}

.visual-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(74, 139, 140, 0.12);
  color: var(--wdr-teal);
  border: 1px dashed var(--wdr-teal);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.visual-label-upper {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--wdr-teal);
  display: block;
  margin-bottom: 8px;
}

.visual-display-heading {
  font-family: var(--wdr-font-display);
  font-size: 28px;
  font-weight: 700;
  color: var(--wdr-navy);
  line-height: 1.25;
  margin: 12px 0 16px;
}

.visual-quote-box {
  border-left: 3.5px solid var(--wdr-teal);
  background: #FFFFFF;
  padding: 16px 20px;
  border-radius: 0 12px 12px 0;
  margin: 18px 0;
  font-family: var(--wdr-font-display);
  font-style: italic;
  font-size: 17px;
  color: var(--wdr-deep-navy);
  box-shadow: 0 4px 14px rgba(15,30,54,0.04);
}

.visual-media-frame {
  background: var(--wdr-white);
  padding: 24px;
  border: 1.5px dashed rgba(74, 139, 140, 0.45);
  border-radius: 24px;
  box-shadow: 0 8px 24px rgba(15, 30, 54, 0.06);
  position: relative;
  text-align: center;
}

.visual-media-tag {
  position: absolute;
  top: -12px;
  right: 20px;
  background: var(--wdr-navy);
  color: #FFF;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
}

.visual-feature-card {
  background: #FFFFFF;
  border: 1px solid #E2E8EE;
  border-radius: 16px;
  padding: 22px;
  box-shadow: 0 4px 12px rgba(15, 30, 54, 0.03);
  transition: all 0.2s ease;
}
.visual-feature-card:hover {
  border-color: var(--wdr-teal);
  box-shadow: 0 8px 20px rgba(74, 139, 140, 0.12);
}

.visual-feature-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(74, 139, 140, 0.12);
  color: var(--wdr-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  margin-bottom: 14px;
}

.visual-stat-card {
  background: #FFFFFF;
  border: 1.5px dashed rgba(74, 139, 140, 0.35);
  border-radius: 16px;
  padding: 20px;
  text-align: center;
}
.visual-stat-num {
  font-family: var(--wdr-font-display);
  font-size: 34px;
  font-weight: 800;
  color: var(--wdr-navy);
  line-height: 1;
}
.visual-stat-line {
  width: 36px;
  height: 2px;
  background: var(--wdr-teal);
  margin: 10px auto;
}

.visual-testi-card {
  background: #FFFFFF;
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  border-radius: 20px;
  padding: 28px;
  position: relative;
}
.visual-quote-mark {
  font-family: var(--wdr-font-display);
  font-size: 52px;
  color: var(--wdr-teal);
  line-height: 0.8;
  display: block;
  margin-bottom: 8px;
}

.visual-dark-cta {
  background: var(--wdr-deep-navy);
  color: #FFFFFF;
  border-radius: 24px;
  padding: 40px;
  border: 1.5px dashed rgba(74, 139, 140, 0.45);
}

.visual-input-styled {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px dashed rgba(74, 139, 140, 0.35);
  border-radius: 8px;
  background: #FFFFFF;
  font-size: 13.5px;
  color: var(--wdr-deep-navy);
  font-family: inherit;
  transition: all 0.2s ease;
}
.visual-input-styled:focus {
  outline: none;
  border-color: var(--wdr-teal);
  border-style: solid;
  box-shadow: 0 0 0 3px rgba(74, 139, 140, 0.15);
}

.visual-input-dark {
  background: rgba(255,255,255,0.08);
  border: 1px dashed rgba(255,255,255,0.3);
  color: #FFFFFF;
}
.visual-input-dark:focus {
  border-color: var(--wdr-teal-light);
  background: rgba(255,255,255,0.14);
}
.hero-mode-option-card {
  border: 2px solid #E2E8EE;
  background: #FAF8F5;
  padding: 18px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  gap: 12px;
  transition: all 0.25s ease;
}
.hero-mode-option-card:hover {
  border-color: rgba(74, 139, 140, 0.45);
  background: #FFFFFF;
}
.hero-mode-option-card.is-active,
.hero-mode-option-card:has(input[type="radio"]:checked) {
  border-color: var(--wdr-teal) !important;
  background: rgba(74, 139, 140, 0.10) !important;
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

<!-- Studio Header & Live Preview Link -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-family: var(--wdr-font-display); font-size: 24px; font-weight: 700; color: var(--admin-navy); margin: 0;">
      <i class="ri-quill-pen-line" style="color: var(--admin-teal);"></i> Homepage Live Visual Studio
    </h2>
    <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
      Frontend Homepage ke sabhi 9 sections (01 to 09) ka complete content, media aur design editor.
    </p>
  </div>
  <div style="display: flex; gap: 10px;">
    <a href="<?= url('/') ?>" target="_blank" class="btn-adm btn-adm-outline">
      <i class="ri-external-link-line"></i> View Live Homepage
    </a>
  </div>
</div>

<?php if ($editorError): ?>
  <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
    <i class="ri-error-warning-line"></i> <?= e($editorError) ?>
  </div>
<?php endif; ?>

<!-- Section Navigation Tabs -->
<div class="section-nav-scroll" style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 10px;">
  <?php
  $tabs = [
      'sec01' => ['num' => '01', 'name' => 'Hero Banner', 'icon' => 'ri-slideshow-line'],
      'sec02' => ['num' => '02', 'name' => 'Editorial Standard', 'icon' => 'ri-quill-pen-line'],
      'sec03' => ['num' => '03', 'name' => 'What We Do & Bento', 'icon' => 'ri-apps-line'],
      'sec03c' => ['num' => '03C', 'name' => 'Other / Dev Services' . (setting('home_sec3c_enabled', '1') === '0' ? ' (OFF)' : ''), 'icon' => 'ri-code-box-line'],
      'sec04' => ['num' => '04', 'name' => 'Why WORDORA', 'icon' => 'ri-shield-star-line'],
      'sec05' => ['num' => '05', 'name' => 'Who We Write For', 'icon' => 'ri-building-line'],
      'sec06' => ['num' => '06', 'name' => 'Our Process', 'icon' => 'ri-route-line'],
      'sec07' => ['num' => '07', 'name' => 'Client Proof', 'icon' => 'ri-chat-quote-line'],
      'sec08' => ['num' => '08', 'name' => 'Editorial Desk', 'icon' => 'ri-article-line'],
      'sec09' => ['num' => '09', 'name' => 'Signature CTA', 'icon' => 'ri-sparkling-fill'],
  ];
  foreach ($tabs as $k => $t):
      $isAct = ($activeTab === $k);
  ?>
  <a href="<?= $currentUrl ?>?tab=<?= $k ?>" style="padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isAct ? 'background: var(--admin-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--admin-navy); border: 1.5px solid var(--admin-border);' ?>">
    <span style="display: inline-block; width: 22px; height: 22px; border-radius: 6px; background: <?= $isAct ? 'var(--admin-teal)' : 'var(--admin-teal-pale)' ?>; color: <?= $isAct ? '#FFF' : 'var(--admin-teal)' ?>; font-size: 11px; font-weight: 800; line-height: 22px; text-align: center;"><?= $t['num'] ?></span>
    <i class="<?= $t['icon'] ?>"></i> <?= $t['name'] ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Master Switch Toggle Styles & Instant AJAX Controller -->
<style>
@keyframes wdrSpin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.wdr-toggle-widget {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: #FFFFFF;
  padding: 6px 16px 6px 12px;
  border-radius: 50px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  user-select: none;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.wdr-toggle-widget:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(0,0,0,0.1) !important;
}
.wdr-toggle-switch {
  position: relative;
  display: inline-block;
  width: 56px;
  height: 30px;
  margin: 0;
  cursor: pointer;
  flex-shrink: 0;
}
.wdr-toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}
.wdr-toggle-track {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  border-radius: 34px;
  cursor: pointer;
  transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
}
.wdr-toggle-knob {
  position: absolute;
  top: 4px;
  left: 4px;
  width: 22px;
  height: 22px;
  background-color: #FFFFFF;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(0,0,0,0.25);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  display: flex;
  align-items: center;
  justify-content: center;
}
.wdr-master-switch-card {
  transition: background-color 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease;
}
.wdr-master-switch-card .wdr-status-badge {
  transition: background-color 0.3s ease, color 0.3s ease;
}
.wdr-master-switch-card .wdr-icon-box {
  transition: background-color 0.3s ease, color 0.3s ease;
}
</style>
<script>
function handleWdrToggleClick(widget) {
  const cb = widget.querySelector('.wdr-sec3c-toggle-input');
  if (cb) {
    cb.checked = !cb.checked;
    handleWdrToggleChange(cb.checked);
  }
}

async function handleWdrToggleChange(isChecked) {
  applyWdrToggleUI(isChecked);
  
  document.querySelectorAll('.wdr-toggle-spinner').forEach(el => el.style.display = 'inline-block');

  try {
    const csrfInput = document.querySelector('input[name="csrf_token"]');
    const csrfToken = csrfInput ? csrfInput.value : '';

    const formData = new FormData();
    formData.append('ajax_action', 'toggle_sec3c');
    formData.append('state', isChecked ? '1' : '0');
    formData.append('csrf_token', csrfToken);

    const response = await fetch(window.location.href, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });

    const data = await response.json();
    if (data && data.success) {
      showWdrToast(
        isChecked 
          ? '⚡ Master Switch is ON! 7 Dev Services are now active across the website.' 
          : '○ Master Switch is OFF! 7 Dev Services are hidden across the website.',
        'success'
      );
    } else {
      throw new Error((data && data.error) ? data.error : 'Failed to update switch');
    }
  } catch (err) {
    console.error('Toggle Error:', err);
    applyWdrToggleUI(!isChecked);
    showWdrToast('Error saving switch: ' + err.message, 'error');
  } finally {
    document.querySelectorAll('.wdr-toggle-spinner').forEach(el => el.style.display = 'none');
  }
}

function applyWdrToggleUI(isActive) {
  document.querySelectorAll('.wdr-sec3c-toggle-input').forEach(cb => cb.checked = isActive);
  document.querySelectorAll('.wdr-sec3c-hidden-val').forEach(input => input.value = isActive ? '1' : '0');

  document.querySelectorAll('.wdr-toggle-track').forEach(track => {
    track.style.backgroundColor = isActive ? '#10B981' : '#CBD5E1';
  });
  document.querySelectorAll('.wdr-toggle-knob').forEach(knob => {
    knob.style.transform = isActive ? 'translateX(26px)' : 'translateX(0px)';
  });
  document.querySelectorAll('.wdr-toggle-knob-icon').forEach(icon => {
    icon.className = 'wdr-toggle-knob-icon ' + (isActive ? 'ri-check-line' : 'ri-close-line');
    icon.style.color = isActive ? '#10B981' : '#94A3B8';
  });

  document.querySelectorAll('.wdr-toggle-widget').forEach(widget => {
    widget.style.borderColor = isActive ? '#86EFAC' : '#FECACA';
  });
  document.querySelectorAll('.wdr-toggle-label-val').forEach(lbl => {
    lbl.textContent = isActive ? 'ON' : 'OFF';
    lbl.style.color = isActive ? '#166534' : '#991B1B';
  });

  document.querySelectorAll('.wdr-master-switch-card').forEach(card => {
    card.style.background = isActive ? '#F0FDF4' : '#FEF2F2';
    card.style.borderColor = isActive ? '#86EFAC' : '#FECACA';
  });

  document.querySelectorAll('.wdr-master-switch-card .wdr-icon-box').forEach(box => {
    box.style.background = isActive ? '#DCFCE7' : '#FEE2E2';
    box.style.color = isActive ? '#166534' : '#991B1B';
    const icon = box.querySelector('i');
    if (icon) {
      icon.className = isActive ? 'ri-shield-check-fill' : 'ri-eye-off-fill';
    }
  });

  const isSec03 = document.getElementById('wdr_sec3_banner');
  if (isSec03) {
    const badge = isSec03.querySelector('.wdr-status-badge');
    if (badge) {
      badge.textContent = isActive ? '● LIVE & ACTIVE' : '○ HIDDEN ACROSS SITE';
      badge.style.background = isActive ? '#166534' : '#991B1B';
    }
    const desc = isSec03.querySelector('.wdr-status-desc');
    if (desc) {
      desc.textContent = isActive 
        ? 'Visible in Navbar (2-column), Homepage Section 3C, Services Page, and Service Detail pages.' 
        : 'Turned OFF: Navbar has reverted to original 1-column layout, and all 7 dev services are completely hidden across the site.';
    }
  }

  const isSec03c = document.getElementById('wdr_sec3c_banner');
  if (isSec03c) {
    const badge = isSec03c.querySelector('.wdr-status-badge');
    if (badge) {
      badge.textContent = isActive ? '● ACTIVE (SHOWN ACROSS SITE)' : '○ DISABLED (HIDDEN ACROSS SITE)';
      badge.style.background = isActive ? '#166534' : '#991B1B';
    }
    const desc = isSec03c.querySelector('.wdr-status-desc');
    if (desc) {
      desc.innerHTML = isActive 
        ? '<strong>Status: LIVE.</strong> These 7 services are visible in Navbar (2 columns), Homepage Section 3C bento, Services page matrix, and their detail pages are active.' 
        : '<strong>Status: HIDDEN.</strong> All 7 development services are completely hidden across the entire website. Navbar has reverted to its original 1-column layout, and detail page links redirect.';
    }
  }
}

function showWdrToast(msg, type = 'success') {
  let toast = document.getElementById('wdr_live_toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'wdr_live_toast';
    toast.style.cssText = 'position: fixed; bottom: 28px; right: 28px; z-index: 999999; display: flex; align-items: center; gap: 12px; padding: 14px 22px; border-radius: 12px; font-weight: 700; font-size: 13.5px; box-shadow: 0 12px 30px rgba(0,0,0,0.2); transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); opacity: 0; transform: translateY(20px); pointer-events: none;';
    document.body.appendChild(toast);
  }
  
  const isOk = (type === 'success');
  toast.style.background = isOk ? '#0F172A' : (type === 'info' ? '#1E293B' : '#991B1B');
  toast.style.color = '#FFFFFF';
  toast.style.border = isOk ? '1.5px solid #10B981' : (type === 'info' ? '1.5px solid #38BDF8' : '1.5px solid #EF4444');
  toast.innerHTML = `<i class="${isOk ? 'ri-checkbox-circle-fill' : (type === 'info' ? 'ri-loader-4-line' : 'ri-error-warning-fill')}" style="font-size: 20px; color: ${isOk ? '#34D399' : (type === 'info' ? '#38BDF8' : '#FCA5A5')};"></i> <span>${msg}</span>`;
  
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';
  
  clearTimeout(window._wdrToastTimeout);
  if (type !== 'info') {
    window._wdrToastTimeout = setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
    }, 2800);
  }
}
</script>


<!-- ═══════════════════════════════════════════
     TAB 01: HERO SECTION & SLIDES
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec01'): ?>
<div class="visual-studio-card">
  <div style="margin-bottom: 20px;">
    <span class="visual-badge"><i class="ri-slideshow-line"></i> SECTION 01 — THE EDITORIAL COVER</span>
    <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Hero Presentation &amp; Slide Carousel Manager</h2>
    <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Full-bleed carousel slides, background videos, overlay opacity, sort order, and slide copy.</p>
  </div>

  <form method="POST" action="<?= $currentUrl ?>?tab=sec01" enctype="multipart/form-data">
    <?= CSRF::field() ?>
    <input type="hidden" name="section_editor_submit" value="1">
    <input type="hidden" name="tab" value="sec01">

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper">Active Hero Display Mode</label>
      <?php $curMode = setting('hero_mode', 'slider'); ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <label class="hero-mode-option-card <?= ($curMode === 'slider') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="slider" <?= ($curMode === 'slider') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">✨ Multi-Slide Carousel (Slider)</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Smooth Swiper.js fade slider where each slide has its own full background artwork and distinct text.</div>
          </div>
        </label>

        <label class="hero-mode-option-card <?= ($curMode === 'single' || $curMode === 'single_image') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="single" <?= ($curMode === 'single' || $curMode === 'single_image') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">🖼️ Single Background Image</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Renders 1 static high-impact background image hero without slide transitions.</div>
          </div>
        </label>

        <label class="hero-mode-option-card <?= ($curMode === 'video') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="video" <?= ($curMode === 'video') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">🎬 Background Video Hero</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Autoplay looping MP4/WebM video hero with dark overlay.</div>
          </div>
        </label>

      </div>

      <!-- Video Hero Configuration Box -->
      <?php $curHeroVideo = setting('hero_video_url', ''); ?>
      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <label class="visual-label-upper"><i class="ri-video-line"></i> Background Video Configuration (Upload File or Enter Direct URL)</label>

        <?php if (!empty($curHeroVideo)): ?>
          <div id="preview_home_hero_video" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
            <div style="width: 80px; height: 50px; background: #0F1E36; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal); font-size: 24px; overflow: hidden; flex-shrink: 0;">
              <video src="<?= media_url($curHeroVideo) ?>" style="width: 100%; height: 100%; object-fit: cover;" muted autoplay loop playsinline></video>
            </div>
            <div style="flex: 1; min-width: 0;">
              <div style="font-size: 13px; font-weight: 700; color: var(--admin-navy);">Active Video Source</div>
              <div style="font-size: 11px; color: var(--admin-teal); word-break: break-all;"><?= e($curHeroVideo) ?></div>
            </div>
            <button type="button" onclick="document.getElementById('remove_hero_video').value='1'; document.getElementById('preview_home_hero_video').style.display='none'; document.querySelector('input[name=hero_video_url]').value='';" class="btn-adm-action btn-adm-delete" style="font-size: 11px; padding: 6px 12px;">
              <i class="ri-delete-bin-line"></i> Remove Video
            </button>
          </div>
        <?php endif; ?>
        <input type="hidden" name="remove_hero_video" id="remove_hero_video" value="0">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 6px;"><i class="ri-upload-cloud-2-line"></i> Option 1: Upload Video File (MP4, WebM)</label>
            <input type="file" name="hero_video_file" class="visual-input-styled" accept="video/mp4,video/webm">
            <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Max 50MB MP4/WebM video.</small>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 6px;"><i class="ri-link"></i> Option 2: Or Paste Direct Video URL</label>
            <input type="text" name="hero_video_url" class="visual-input-styled" value="<?= e($curHeroVideo) ?>" placeholder="/uploads/hero.mp4 or https://...">
            <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">External CDN or hosted video URL.</small>
          </div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
        <div>
          <label class="visual-label-upper">Hero Text Side Overlay Opacity (0% to 100%)</label>
          <input type="number" name="hero_overlay_opacity" class="visual-input-styled" min="0" max="100" value="<?= e(setting('hero_overlay_opacity', '75')) ?>" style="max-width: 140px;">
          <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Controls readability of text over high-contrast images without affecting artwork transparency.</small>
        </div>
      </div>

      <div style="margin-top: 16px;">
        <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Display Mode &amp; Video</button>
      </div>
    </div>
  </form>

  <!-- Full Hero Slides List (Exact Match from admin/hero/index.php) -->
  <div class="admin-card" style="margin-bottom: 0; background: #FFFFFF; border-radius: 16px; border: 1.5px dashed rgba(74, 139, 140, 0.4);">
    <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--admin-border);">
      <div>
        <h2 class="card-title" style="font-size: 18px; font-weight: 700; color: var(--admin-navy); margin: 0;">
          <i class="ri-slideshow-line" style="color: var(--admin-teal);"></i> Manage Hero Slides (<?= count($heroSlidesList) ?>)
        </h2>
        <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;">
          Each slide sets its own Background Image/Video, Heading, Sub-heading, Description, and Buttons.
        </div>
      </div>
      <a href="<?= url('admin/hero/edit.php?return_to=' . urlencode($currentUrl . '?tab=sec01')) ?>" class="btn-adm btn-adm-primary">
        <i class="ri-add-line"></i> Add New Slide
      </a>
    </div>

    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 80px;">Background</th>
            <th>Sub-Heading &amp; Main Heading</th>
            <th>Buttons</th>
            <th>Sort</th>
            <th>Status</th>
            <th style="text-align: right; width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($heroSlidesList)): ?>
            <?php foreach ($heroSlidesList as $slide): ?>
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
              <td>
                <span class="badge" style="background: #F1F5F9; color: var(--admin-navy); font-weight: 600; font-size: 12px; padding: 2px 8px; border-radius: 4px;"><?= (int)$slide['sort_order'] ?></span>
              </td>
              <td>
                <span class="badge-status badge-<?= $slide['is_active'] ? 'active' : 'inactive' ?>">
                  <?= $slide['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                </span>
              </td>
              <td style="text-align: right;">
                <div class="table-actions" style="justify-content: flex-end;">
                  <a href="<?= url('admin/hero/edit.php?id=' . $slide['id'] . '&page=home&return_to=' . urlencode($currentUrl . '?tab=sec01')) ?>" class="btn-adm-action btn-adm-edit" title="Edit Slide">
                    <i class="ri-edit-line"></i> <span>Edit</span>
                  </a>
                  <a href="<?= $currentUrl ?>?tab=sec01&delete_slide=<?= $slide['id'] ?>" class="btn-adm-action btn-adm-delete" onclick="return confirm('Are you sure you want to delete this slide?')" title="Delete Slide">
                    <i class="ri-delete-bin-line"></i> <span>Delete</span>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 30px;">
                No hero slides found. Click "+ Add New Slide" to create your first slide.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 02: THE EDITORIAL STANDARD
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec02'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec02" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec02">

  <div class="visual-studio-card">
    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      
      <!-- Left Column: Content Form -->
      <div>
        <div style="margin-bottom: 12px;">
          <label class="visual-label-upper">Section Badge Text</label>
          <input type="text" name="home_sec2_badge" class="visual-input-styled" value="<?= e(setting('home_sec2_badge', 'THE EDITORIAL MANDATE')) ?>" style="max-width: 320px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Main Heading (Supports &lt;br&gt;)</label>
          <input type="text" name="home_sec2_title" class="visual-input-styled" value="<?= e(setting('home_sec2_title', "We don't just write content.<br>We engineer conviction.")) ?>" style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Lead Paragraph 1</label>
          <textarea name="home_sec2_p1" class="visual-input-styled" rows="3"><?= e(setting('home_sec2_p1', '')) ?></textarea>
        </div>

        <div class="visual-quote-box">
          <label class="visual-label-upper" style="color: var(--wdr-teal); margin-bottom: 4px;">Magazine Pull Quote</label>
          <input type="text" name="home_sec2_quote" class="visual-input-styled" value="<?= e(setting('home_sec2_quote', '')) ?>" style="font-family: var(--wdr-font-display); font-style: italic; font-size: 15px;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Supporting Paragraph 2</label>
          <textarea name="home_sec2_p2" class="visual-input-styled" rows="2"><?= e(setting('home_sec2_p2', '')) ?></textarea>
        </div>

        <!-- Buttons -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: #FFF; padding: 14px; border-radius: 12px; border: 1px solid #E2E8EE;">
          <div>
            <label class="visual-label-upper">Primary Button Text &amp; URL</label>
            <input type="text" name="home_sec2_btn1_text" class="visual-input-styled" value="<?= e(setting('home_sec2_btn1_text', 'Explore What We Do')) ?>" style="margin-bottom: 6px;">
            <input type="text" name="home_sec2_btn1_url" class="visual-input-styled" value="<?= e(setting('home_sec2_btn1_url', 'services.php')) ?>">
          </div>
          <div>
            <label class="visual-label-upper">Secondary Button Text &amp; URL</label>
            <input type="text" name="home_sec2_btn2_text" class="visual-input-styled" value="<?= e(setting('home_sec2_btn2_text', 'Read Our Story')) ?>" style="margin-bottom: 6px;">
            <input type="text" name="home_sec2_btn2_url" class="visual-input-styled" value="<?= e(setting('home_sec2_btn2_url', 'who-we-are.php')) ?>">
          </div>
        </div>
      </div>

      <!-- Right Column: Visual Framed Artwork -->
      <div>
        <div class="visual-media-frame">
          <span class="visual-media-tag"><i class="ri-sparkling-fill"></i> <?= e(setting('home_sec2_artwork_tag', 'Precision Crafted')) ?></span>
          
          <?php $sec2Art = setting('home_sec2_artwork', '/img/service treasure.png'); ?>
          <div id="preview_sec2_art" style="margin: 16px 0;">
            <img src="<?= media_url($sec2Art) ?>" alt="Artwork Preview" style="max-height: 240px; margin: 0 auto; object-fit: contain; display: block;">
            <div style="font-size: 11px; color: var(--admin-muted); margin-top: 8px; word-break: break-all;"><?= e($sec2Art) ?></div>
            <?php if (!empty($sec2Art)): ?>
              <button type="button" onclick="document.getElementById('remove_sec2_artwork').value='1'; document.getElementById('preview_sec2_art').style.display='none'; this.style.display='none';" class="btn-adm btn-adm-danger" style="margin-top: 8px; padding: 4px 12px; font-size: 11px; border-radius: 20px; cursor: pointer;">
                <i class="ri-delete-bin-line"></i> Remove Image
              </button>
            <?php endif; ?>
          </div>

          <div style="border-top: 1px dashed #E2E8EE; padding-top: 14px; text-align: left;">
            <label class="visual-label-upper">Floating Badge Tag</label>
            <input type="text" name="home_sec2_artwork_tag" class="visual-input-styled" value="<?= e(setting('home_sec2_artwork_tag', 'Precision Crafted')) ?>" style="margin-bottom: 12px;">

            <label class="visual-label-upper">Upload New Artwork Image</label>
            <input type="hidden" name="remove_sec2_artwork" id="remove_sec2_artwork" value="0">
            <input type="file" name="home_sec2_artwork_file" class="visual-input-styled" accept="image/*">
          </div>
        </div>
      </div>

    </div>

    <div style="margin-top: 24px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 02 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 03: WHAT WE DO (6 BENTO TILES + MARQUEE)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec03'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec03" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec03">

  <div class="visual-studio-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
      <span class="visual-badge"><i class="ri-apps-line"></i> SECTION 03 — WHAT WE DO &amp; BENTO GRID</span>
    </div>

    <!-- Master Toggle Banner for 7 New Services (Section 03C) -->
    <?php $sec3cActive = setting('home_sec3c_enabled', '1') !== '0'; ?>
    <input type="hidden" name="home_sec3c_master_toggle_present" value="1">
    <input type="hidden" name="home_sec3c_enabled" class="wdr-sec3c-hidden-val" value="<?= $sec3cActive ? '1' : '0' ?>">

    <div class="wdr-master-switch-card" id="wdr_sec3_banner" style="background: <?= $sec3cActive ? '#F0FDF4' : '#FEF2F2' ?>; border: 1.5px solid <?= $sec3cActive ? '#86EFAC' : '#FECACA' ?>; border-radius: 14px; padding: 16px 20px; margin: 12px 0 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
      <div style="display: flex; align-items: center; gap: 14px;">
        <div class="wdr-icon-box" style="width: 42px; height: 42px; border-radius: 10px; background: <?= $sec3cActive ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $sec3cActive ? '#166534' : '#991B1B' ?>; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
          <i class="<?= $sec3cActive ? 'ri-shield-check-fill' : 'ri-eye-off-fill' ?>"></i>
        </div>
        <div>
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">7 Other / Development Services (Section 03C)</span>
            <span class="wdr-status-badge" style="display: inline-block; padding: 2px 9px; border-radius: 12px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; background: <?= $sec3cActive ? '#166534' : '#991B1B' ?>; color: #FFF;">
              <?= $sec3cActive ? '● LIVE &amp; ACTIVE' : '○ HIDDEN ACROSS SITE' ?>
            </span>
          </div>
          <p class="wdr-status-desc" style="margin: 3px 0 0; font-size: 12.5px; color: var(--admin-muted);">
            <?= $sec3cActive 
                ? 'Visible in Navbar (2-column), Homepage Section 3C, Services Page, and Service Detail pages.' 
                : 'Turned OFF: Navbar has reverted to original 1-column layout, and all 7 dev services are completely hidden across the site.' ?>
          </p>
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <!-- Animated iOS Toggle Switch -->
        <div class="wdr-toggle-widget" onclick="handleWdrToggleClick(this)" style="border: 1.5px solid <?= $sec3cActive ? '#86EFAC' : '#FECACA' ?>;">
          <label class="wdr-toggle-switch" onclick="event.stopPropagation()">
            <input type="checkbox" class="wdr-sec3c-toggle-input" <?= $sec3cActive ? 'checked' : '' ?> onchange="handleWdrToggleChange(this.checked)">
            <span class="wdr-toggle-track" style="background-color: <?= $sec3cActive ? '#10B981' : '#CBD5E1' ?>;">
              <span class="wdr-toggle-knob" style="transform: <?= $sec3cActive ? 'translateX(26px)' : 'translateX(0px)' ?>;">
                <i class="wdr-toggle-knob-icon <?= $sec3cActive ? 'ri-check-line' : 'ri-close-line' ?>" style="font-size: 13px; font-weight: 900; color: <?= $sec3cActive ? '#10B981' : '#94A3B8' ?>;"></i>
              </span>
            </span>
          </label>
          <div style="display: flex; flex-direction: column; cursor: pointer;">
            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-muted); line-height: 1;">Master Switch</span>
            <span class="wdr-toggle-label-val" style="font-size: 13.5px; font-weight: 800; color: <?= $sec3cActive ? '#166534' : '#991B1B' ?>; line-height: 1.2;">
              <?= $sec3cActive ? 'ON' : 'OFF' ?>
            </span>
          </div>
          <span class="wdr-toggle-spinner" style="display: none; font-size: 15px; color: var(--wdr-teal); animation: wdrSpin 0.8s linear infinite;">
            <i class="ri-loader-4-line"></i>
          </span>
        </div>

        <a href="<?= $currentUrl ?>?tab=sec03c" class="btn-adm btn-adm-outline btn-adm-sm" style="font-size: 12px; padding: 7px 12px; text-decoration: none;">
          Edit 03C Bento →
        </a>
      </div>
    </div>
    
    <div style="margin: 16px 0 24px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
          <label class="visual-label-upper">Section Upper Label</label>
          <input type="text" name="home_sec3_label" class="visual-input-styled" value="<?= e(setting('home_sec3_label', 'WHAT WE DO')) ?>">
        </div>
        <div>
          <label class="visual-label-upper">Main Heading</label>
          <input type="text" name="home_sec3_title" class="visual-input-styled" value="<?= e(setting('home_sec3_title', 'Content with a job to do.')) ?>" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700;">
        </div>
      </div>
      <div>
        <label class="visual-label-upper">Description</label>
        <textarea name="home_sec3_desc" class="visual-input-styled" rows="2"><?= e(setting('home_sec3_desc', '')) ?></textarea>
      </div>
    </div>

    <!-- 6 Bento Grid Discipline Tiles -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px; margin-bottom: 28px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-dashboard-line" style="color: var(--wdr-teal);"></i> 6 Core Editorial Discipline Bento Tiles
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px;">
        <?php for ($b = 0; $b < 6; $b++): $bt = $bentoTiles[$b] ?? ['badge' => '', 'icon' => 'ri-quill-pen-line', 'title' => '', 'desc' => '', 'tags' => '', 'btn_text' => '', 'btn_url' => '', 'btn2_text' => '', 'btn2_url' => '']; ?>
        <div style="background: <?= ($b === 1) ? '#1B2A4A; color: #FFF;' : (($b === 5) ? '#0F1E36; color: #FFF;' : '#FFFFFF;') ?>; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 16px; padding: 22px; box-shadow: 0 4px 14px rgba(15,30,54,0.04);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 11px; font-weight: 800; color: var(--wdr-teal); background: var(--wdr-teal-pale); padding: 2px 8px; border-radius: 4px;">TILE <?= $b + 1 ?></span>
            <input type="text" name="bento[<?= $b ?>][badge]" class="visual-input-styled <?= ($b === 1 || $b === 5) ? 'visual-input-dark' : '' ?>" value="<?= e($bt['badge']) ?>" placeholder="Badge Text" style="max-width: 180px; font-size: 11px;">
          </div>
          
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper" style="<?= ($b === 1 || $b === 5) ? 'color: var(--wdr-teal-pale);' : '' ?>">Tile Title</label>
            <input type="text" name="bento[<?= $b ?>][title]" class="visual-input-styled <?= ($b === 1 || $b === 5) ? 'visual-input-dark' : '' ?>" value="<?= e($bt['title']) ?>" style="font-weight: 700; font-size: 15px;">
          </div>

          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper" style="<?= ($b === 1 || $b === 5) ? 'color: var(--wdr-teal-pale);' : '' ?>">Description</label>
            <textarea name="bento[<?= $b ?>][desc]" class="visual-input-styled <?= ($b === 1 || $b === 5) ? 'visual-input-dark' : '' ?>" rows="2"><?= e($bt['desc']) ?></textarea>
          </div>

          <?php if ($b === 0): ?>
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Topic Tags (Comma Separated)</label>
            <input type="text" name="bento[<?= $b ?>][tags]" class="visual-input-styled" value="<?= e($bt['tags']) ?>" placeholder="Tag 1, Tag 2, Tag 3">
          </div>
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <input type="text" name="bento[<?= $b ?>][btn_text]" class="visual-input-styled <?= ($b === 1 || $b === 5) ? 'visual-input-dark' : '' ?>" value="<?= e($bt['btn_text']) ?>" placeholder="Link Text">
            <input type="text" name="bento[<?= $b ?>][btn_url]" class="visual-input-styled <?= ($b === 1 || $b === 5) ? 'visual-input-dark' : '' ?>" value="<?= e($bt['btn_url']) ?>" placeholder="Link URL">
          </div>

          <?php if ($b === 5): ?>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
            <input type="text" name="bento[<?= $b ?>][btn2_text]" class="visual-input-styled visual-input-dark" value="<?= e($bt['btn2_text']) ?>" placeholder="Button 2 Text">
            <input type="text" name="bento[<?= $b ?>][btn2_url]" class="visual-input-styled visual-input-dark" value="<?= e($bt['btn2_url']) ?>" placeholder="Button 2 URL">
          </div>
          <?php endif; ?>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Capabilities Marquee Banner & Pills -->
    <div style="background: #0F1E36; color: #FFF; padding: 28px; border-radius: 18px; border: 1.5px dashed rgba(74, 139, 140, 0.4); margin-top: 24px;">
      <span class="visual-badge" style="background: rgba(74, 139, 140, 0.25); color: var(--wdr-teal-pale); margin-bottom: 14px;">CAPABILITIES MARQUEE STREAM</span>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 14px 0;">
        <div>
          <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Marquee Header Label</label>
          <input type="text" name="home_sec3_marquee_label" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec3_marquee_label', 'EDITORIAL CAPABILITIES')) ?>">
        </div>
        <div>
          <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Marquee Main Title</label>
          <input type="text" name="home_sec3_marquee_title" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec3_marquee_title', 'Content engineered for ambitious market leaders.')) ?>">
        </div>
      </div>

      <!-- Marquee Stream Rows Comma Strings -->
      <div style="margin-top: 14px;">
        <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Row 1 Pills (Left-to-Right Stream - Comma Separated)</label>
        <input type="text" name="marquee_row1" class="visual-input-styled visual-input-dark" value="<?= e($marqueeRows['row1'] ?? 'SEO Content Writing, Brand Voice Architecture, Thought Leadership Essays, Social Editorial Calendars, Email Sequences & Newsletters, Technical Whitepapers, Full-Funnel Content Strategy') ?>" style="margin-bottom: 10px;">

        <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Row 2 Pills (Right-to-Left Stream - Comma Separated)</label>
        <input type="text" name="marquee_row2" class="visual-input-styled visual-input-dark" value="<?= e($marqueeRows['row2'] ?? 'Conversion Copywriting, Case Study Narratives, Topic Cluster Frameworks, Enterprise B2B Whitepapers, Fact-Checked Research, Executive Ghostwriting, Content Audits & Roadmaps') ?>" style="margin-bottom: 10px;">

        <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Row 3 Pills (Fast Left-to-Right Stream - Comma Separated)</label>
        <input type="text" name="marquee_row3" class="visual-input-styled visual-input-dark" value="<?= e($marqueeRows['row3'] ?? 'Keyword Intent Mapping, Long-Form Authority Guides, High-Converting Pitch Decks, Onboarding Email Sequences, Industry Authority Benchmarks, Viral LinkedIn Carousels, Multi-Format Repurposing') ?>">
      </div>

      <!-- Marquee Background Image -->
      <div style="background: rgba(255,255,255,0.06); border: 1px dashed rgba(255,255,255,0.25); border-radius: 12px; padding: 16px; margin-top: 18px;">
        <label class="visual-label-upper" style="color: var(--wdr-teal-pale);"><i class="ri-landscape-line"></i> Marquee Background Texture Image</label>
        <?php $marBg = setting('marquee_bg_image', '/img/papaer banner.png'); ?>
        <div id="preview_mar_bg" style="display: flex; align-items: center; gap: 14px; margin: 10px 0;">
          <img src="<?= media_url($marBg) ?>" alt="Marquee Preview" style="max-height: 40px; border-radius: 4px;">
          <span style="font-size: 11px; color: rgba(255,255,255,0.7); word-break: break-all;"><?= e($marBg) ?></span>
        </div>
        <input type="hidden" name="remove_marquee_bg" id="remove_marquee_bg" value="0">
        <input type="file" name="marquee_bg_file" class="visual-input-styled visual-input-dark" accept="image/*">
      </div>
    </div>

    <div style="margin-top: 24px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 03 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 03C: OTHER / DEV SERVICES BENTO (7 TILES)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec03c'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec03c">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec03c">

  <div class="visual-studio-card" style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.45);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
      <span class="visual-badge" style="background: var(--wdr-deep-navy); color: var(--wdr-teal-pale); font-size: 11px; padding: 4px 12px;">
        <i class="ri-code-box-line"></i> SECTION 03C — OTHER SERVICES &amp; DIGITAL CAPABILITIES BENTO
      </span>
    </div>

    <!-- Master Switch Card for 7 Development & Design Services -->
    <?php $sec3cActive = setting('home_sec3c_enabled', '1') !== '0'; ?>
    <input type="hidden" name="home_sec3c_master_toggle_present" value="1">
    <input type="hidden" name="home_sec3c_enabled" class="wdr-sec3c-hidden-val" value="<?= $sec3cActive ? '1' : '0' ?>">

    <div class="wdr-master-switch-card" id="wdr_sec3c_banner" style="background: <?= $sec3cActive ? '#F0FDF4' : '#FEF2F2' ?>; border: 1.5px solid <?= $sec3cActive ? '#86EFAC' : '#FECACA' ?>; border-radius: 14px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
      <div style="display: flex; align-items: center; gap: 14px;">
        <div class="wdr-icon-box" style="width: 46px; height: 46px; border-radius: 12px; background: <?= $sec3cActive ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $sec3cActive ? '#166534' : '#991B1B' ?>; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
          <i class="<?= $sec3cActive ? 'ri-shield-check-fill' : 'ri-eye-off-fill' ?>"></i>
        </div>
        <div>
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: var(--wdr-navy);">⚡ Master Toggle: 7 Other / Development &amp; Design Services</h4>
            <span class="wdr-status-badge" style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; background: <?= $sec3cActive ? '#166534' : '#991B1B' ?>; color: #FFF;">
              <?= $sec3cActive ? '● ACTIVE (SHOWN ACROSS SITE)' : '○ DISABLED (HIDDEN ACROSS SITE)' ?>
            </span>
          </div>
          <p class="wdr-status-desc" style="margin: 4px 0 0; font-size: 12.8px; color: var(--admin-muted); line-height: 1.5;">
            <?= $sec3cActive 
                ? '<strong>Status: LIVE.</strong> These 7 services are visible in Navbar (2 columns), Homepage Section 3C bento, Services page matrix, and their detail pages are active.' 
                : '<strong>Status: HIDDEN.</strong> All 7 development services are completely hidden across the entire website. Navbar has reverted to its original 1-column layout, and detail page links redirect.' ?>
          </p>
        </div>
      </div>

      <!-- Animated iOS Toggle Switch -->
      <div class="wdr-toggle-widget" onclick="handleWdrToggleClick(this)" style="border: 1.5px solid <?= $sec3cActive ? '#86EFAC' : '#FECACA' ?>; padding: 8px 20px 8px 14px;">
        <label class="wdr-toggle-switch" onclick="event.stopPropagation()">
          <input type="checkbox" class="wdr-sec3c-toggle-input" <?= $sec3cActive ? 'checked' : '' ?> onchange="handleWdrToggleChange(this.checked)">
          <span class="wdr-toggle-track" style="background-color: <?= $sec3cActive ? '#10B981' : '#CBD5E1' ?>;">
            <span class="wdr-toggle-knob" style="transform: <?= $sec3cActive ? 'translateX(26px)' : 'translateX(0px)' ?>;">
              <i class="wdr-toggle-knob-icon <?= $sec3cActive ? 'ri-check-line' : 'ri-close-line' ?>" style="font-size: 13px; font-weight: 900; color: <?= $sec3cActive ? '#10B981' : '#94A3B8' ?>;"></i>
            </span>
          </span>
        </label>
        <div style="display: flex; flex-direction: column; cursor: pointer;">
          <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-muted); line-height: 1;">Master Switch</span>
          <span class="wdr-toggle-label-val" style="font-size: 14px; font-weight: 800; color: <?= $sec3cActive ? '#166534' : '#991B1B' ?>; line-height: 1.2;">
            <?= $sec3cActive ? 'ON' : 'OFF' ?>
          </span>
        </div>
        <span class="wdr-toggle-spinner" style="display: none; font-size: 16px; color: var(--wdr-teal); animation: wdrSpin 0.8s linear infinite;">
          <i class="ri-loader-4-line"></i>
        </span>
      </div>
    </div>
    
    <!-- Section Header Controls -->
    <div style="background: #FFF; border: 1px solid var(--admin-border); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
          <label class="visual-label-upper">Section Upper Badge / Label</label>
          <input type="text" name="home_sec3c_label" class="visual-input-styled" value="<?= e(setting('home_sec3c_label', 'DEVELOPMENT & DESIGN')) ?>">
        </div>
        <div>
          <label class="visual-label-upper">Main Heading</label>
          <input type="text" name="home_sec3c_title" class="visual-input-styled" value="<?= e(setting('home_sec3c_title', 'Building digital experiences.')) ?>" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700;">
        </div>
      </div>
      <div>
        <label class="visual-label-upper">Section Description</label>
        <textarea name="home_sec3c_desc" class="visual-input-styled" rows="2"><?= e(setting('home_sec3c_desc', 'From robust web applications and mobile apps to AI integrations and intuitive UI/UX, we engineer software built to perform.')) ?></textarea>
      </div>
    </div>

    <!-- 7 Bento Grid Service Tiles -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 8px;">
        <i class="ri-dashboard-line" style="color: var(--wdr-teal);"></i> 7 Other Service Bento Tiles
      </h3>
      <p style="font-size: 13px; color: var(--admin-muted); margin-bottom: 20px;">
        Customize the title, badge, icon, and description for all 7 digital &amp; tech capabilities shown in the homepage dark bento matrix.
      </p>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px;">
        <?php for ($d = 0; $d < 7; $d++): 
            $dt = $devBentoTiles[$d] ?? ['badge' => '', 'icon' => 'ri-code-box-line', 'title' => '', 'desc' => '', 'btn_text' => '', 'btn_url' => ''];
            $isHighlighted = ($d === 3); // Website design tile has teal accent
        ?>
        <div style="background: <?= $isHighlighted ? '#F0F7F7' : '#FFFFFF' ?>; border: 1.5px dashed <?= $isHighlighted ? 'var(--wdr-teal)' : 'rgba(74, 139, 140, 0.35)' ?>; border-radius: 16px; padding: 20px; box-shadow: 0 4px 14px rgba(15,30,54,0.04);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 11px; font-weight: 800; color: #FFF; background: var(--wdr-deep-navy); padding: 2px 8px; border-radius: 4px;">TILE <?= $d + 1 ?></span>
            <input type="text" name="dev_bento[<?= $d ?>][badge]" class="visual-input-styled" value="<?= e($dt['badge']) ?>" placeholder="Badge Text (e.g. High Impact)" style="max-width: 170px; font-size: 11px;">
          </div>

          <div style="display: grid; grid-template-columns: 50px 1fr; gap: 10px; margin-bottom: 10px;">
            <div>
              <label class="visual-label-upper" title="Remix Icon Class">Icon</label>
              <input type="text" name="dev_bento[<?= $d ?>][icon]" class="visual-input-styled" value="<?= e($dt['icon']) ?>" placeholder="ri-..." style="font-size: 12px; padding: 8px 4px; text-align: center;">
            </div>
            <div>
              <label class="visual-label-upper">Service Title</label>
              <input type="text" name="dev_bento[<?= $d ?>][title]" class="visual-input-styled" value="<?= e($dt['title']) ?>" style="font-weight: 700; font-size: 14px;">
            </div>
          </div>

          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Description</label>
            <textarea name="dev_bento[<?= $d ?>][desc]" class="visual-input-styled" rows="2" style="font-size: 12.5px;"><?= e($dt['desc']) ?></textarea>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div>
              <label class="visual-label-upper" style="font-size: 10px;">Button Text</label>
              <input type="text" name="dev_bento[<?= $d ?>][btn_text]" class="visual-input-styled" value="<?= e($dt['btn_text']) ?>" placeholder="Explore ...">
            </div>
            <div>
              <label class="visual-label-upper" style="font-size: 10px;">Target URL</label>
              <input type="text" name="dev_bento[<?= $d ?>][btn_url]" class="visual-input-styled" value="<?= e($dt['btn_url']) ?>" placeholder="service/...">
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Save Button Bar -->
    <div style="margin-top: 24px; text-align: right; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--admin-border); padding-top: 18px;">
      <a href="<?= url('') ?>#development-bento" target="_blank" style="font-size: 13px; color: var(--wdr-teal); text-decoration: none; font-weight: 600;">
        <i class="ri-external-link-line"></i> Preview on Homepage
      </a>
      <button type="submit" class="btn-adm btn-adm-primary" style="padding: 10px 24px; font-size: 14px;">
        <i class="ri-save-line"></i> Save Section 03C Changes
      </button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 04: WHY WORDORA (6 PILLARS & 4 STATS)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec04'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec04" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec04">

  <div class="visual-studio-card">
    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start; margin-bottom: 28px;">
      
      <!-- Left Column: Header text & Quote -->
      <div>
        <div style="margin-bottom: 12px;">
          <label class="visual-label-upper">Section Upper Label</label>
          <input type="text" name="home_sec4_label" class="visual-input-styled" value="<?= e(setting('home_sec4_label', 'WHY BUSINESSES CHOOSE WORDORA')) ?>" style="max-width: 340px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Main Title (Supports &lt;br&gt;)</label>
          <input type="text" name="home_sec4_title" class="visual-input-styled" value="<?= e(setting('home_sec4_title', "Not just writers.<br>Content thinkers & growth partners.")) ?>" style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Description</label>
          <textarea name="home_sec4_desc" class="visual-input-styled" rows="3"><?= e(setting('home_sec4_desc', '')) ?></textarea>
        </div>

        <div class="visual-quote-box">
          <label class="visual-label-upper" style="color: var(--wdr-teal); margin-bottom: 4px;">Philosophy Pull Quote</label>
          <input type="text" name="home_sec4_quote" class="visual-input-styled" value="<?= e(setting('home_sec4_quote', '')) ?>" style="font-family: var(--wdr-font-display); font-style: italic;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
          <div>
            <label class="visual-label-upper">Story Button Text</label>
            <input type="text" name="home_sec4_btn_text" class="visual-input-styled" value="<?= e(setting('home_sec4_btn_text', 'Read Our Story')) ?>">
          </div>
          <div>
            <label class="visual-label-upper">Story Button Link</label>
            <input type="text" name="home_sec4_btn_url" class="visual-input-styled" value="<?= e(setting('home_sec4_btn_url', 'who-we-are.php')) ?>">
          </div>
        </div>
      </div>

      <!-- Right Column: Artwork Frame -->
      <div>
        <div class="visual-media-frame">
          <span class="visual-media-tag"><i class="ri-sparkling-fill"></i> Agency Philosophy</span>
          <?php $sec4Art = setting('home_sec4_artwork', '/img/why choose us.png'); ?>
          <div id="preview_sec4_art" style="margin: 16px 0;">
            <img src="<?= media_url($sec4Art) ?>" alt="Why Choose Us Artwork" style="max-height: 220px; margin: 0 auto; object-fit: contain; display: block;">
            <div style="font-size: 11px; color: var(--admin-muted); margin-top: 8px; word-break: break-all;"><?= e($sec4Art) ?></div>
          </div>
          <div style="border-top: 1px dashed #E2E8EE; padding-top: 14px; text-align: left;">
            <label class="visual-label-upper">Upload New Artwork Image</label>
            <input type="hidden" name="remove_sec4_artwork" id="remove_sec4_artwork" value="0">
            <input type="file" name="home_sec4_artwork_file" class="visual-input-styled" accept="image/*">
          </div>
        </div>
      </div>

    </div>

    <!-- 6 Value Pillar Cards (Visual Match) -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px; margin-bottom: 28px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-layout-grid-line" style="color: var(--wdr-teal);"></i> 6 Value Pillar Feature Cards
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px;">
        <?php for ($i = 0; $i < 6; $i++): $card = $whyCards[$i] ?? ['icon' => 'ri-quill-pen-line', 'title' => '', 'desc' => '']; ?>
        <div class="visual-feature-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div class="visual-feature-icon"><i class="<?= e($card['icon']) ?>"></i></div>
            <span style="font-size: 11px; font-weight: 800; color: var(--wdr-teal); background: var(--wdr-teal-pale); padding: 2px 8px; border-radius: 4px;">PILLAR <?= $i + 1 ?></span>
          </div>
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Icon Class (RemixIcon)</label>
            <input type="text" name="why_cards[<?= $i ?>][icon]" class="visual-input-styled" value="<?= e($card['icon']) ?>" style="font-family: var(--wdr-font-mono); font-size: 12px;">
          </div>
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Pillar Title</label>
            <input type="text" name="why_cards[<?= $i ?>][title]" class="visual-input-styled" value="<?= e($card['title']) ?>" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Pillar Description</label>
            <textarea name="why_cards[<?= $i ?>][desc]" class="visual-input-styled" rows="2"><?= e($card['desc']) ?></textarea>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- 4 Counter Statistics (Visual Match) -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-bar-chart-line" style="color: var(--wdr-teal);"></i> 4 Counter Statistics
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <?php for ($s = 0; $s < 4; $s++): $st = $stats[$s] ?? ['count' => '0', 'suffix' => '+', 'label' => '']; ?>
        <div class="visual-stat-card">
          <div class="visual-stat-num"><?= e($st['count']) ?><?= e($st['suffix']) ?></div>
          <div class="visual-stat-line"></div>
          <div style="margin-bottom: 8px;">
            <input type="text" name="stats[<?= $s ?>][count]" class="visual-input-styled" value="<?= e($st['count']) ?>" style="text-align: center; font-weight: 800;" placeholder="Number">
          </div>
          <div style="margin-bottom: 8px;">
            <input type="text" name="stats[<?= $s ?>][suffix]" class="visual-input-styled" value="<?= e($st['suffix']) ?>" style="text-align: center;" placeholder="Suffix (+, %, k)">
          </div>
          <div>
            <input type="text" name="stats[<?= $s ?>][label]" class="visual-input-styled" value="<?= e($st['label']) ?>" style="text-align: center; font-weight: 600;" placeholder="Stat Label">
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <div style="margin-top: 28px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 04 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 05: WHO WE WRITE FOR (8 INDUSTRY SLIDES)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec05'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec05" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec05">

  <div class="visual-studio-card">
    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start; margin-bottom: 28px;">
      <div>
        <div style="margin-bottom: 12px;">
          <label class="visual-label-upper">Section Label</label>
          <input type="text" name="home_sec5_label" class="visual-input-styled" value="<?= e(setting('home_sec5_label', 'WHO WE WRITE FOR')) ?>" style="max-width: 340px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Main Title (Supports &lt;br&gt;)</label>
          <input type="text" name="home_sec5_title" class="visual-input-styled" value="<?= e(setting('home_sec5_title', "Different industries.<br>One obsession: clarity.")) ?>" style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700;">
        </div>

        <div>
          <label class="visual-label-upper">Description</label>
          <textarea name="home_sec5_desc" class="visual-input-styled" rows="2"><?= e(setting('home_sec5_desc', '')) ?></textarea>
        </div>
      </div>

      <div>
        <div class="visual-media-frame">
          <span class="visual-media-tag"><i class="ri-building-line"></i> Industry Matrix</span>
          <?php $sec5Art = setting('home_sec5_artwork', '/img/industry.png'); ?>
          <div id="preview_sec5_art" style="margin: 16px 0;">
            <img src="<?= media_url($sec5Art) ?>" alt="Industry Matrix Artwork" style="max-height: 180px; margin: 0 auto; object-fit: contain; display: block;">
          </div>
          <div style="border-top: 1px dashed #E2E8EE; padding-top: 14px; text-align: left;">
            <label class="visual-label-upper">Upload New Header Artwork</label>
            <input type="hidden" name="remove_sec5_artwork" id="remove_sec5_artwork" value="0">
            <input type="file" name="home_sec5_artwork_file" class="visual-input-styled" accept="image/*">
          </div>
        </div>
      </div>
    </div>

    <!-- 8 Industry Case Study Cards (Visual Match) -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-slideshow-3-line" style="color: var(--wdr-teal);"></i> 8 Industry Case Study Swiper Cards
      </h3>

      <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php foreach ($industrySlides as $idx => $sl): ?>
        <div style="background: #FFFFFF; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 28px; box-shadow: 0 8px 24px rgba(15,30,54,0.04);">
          <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; align-items: center;">
            
            <div>
              <div style="margin-bottom: 10px;">
                <span class="visual-badge"><i class="ri-checkbox-circle-fill"></i> <?= e($sl['badge']) ?></span>
              </div>
              <div style="margin-bottom: 8px;">
                <label class="visual-label-upper">Deliverable Badge</label>
                <input type="text" name="slides[<?= $idx ?>][badge]" class="visual-input-styled" value="<?= e($sl['badge']) ?>">
              </div>
              <div style="margin-bottom: 12px;">
                <label class="visual-label-upper">Case Study Title</label>
                <input type="text" name="slides[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($sl['title']) ?>" style="font-family: var(--wdr-font-display); font-size: 16px; font-weight: 700;">
              </div>
              <div style="margin-bottom: 14px;">
                <label class="visual-label-upper">Narrative Story</label>
                <textarea name="slides[<?= $idx ?>][desc]" class="visual-input-styled" rows="2"><?= e($sl['desc']) ?></textarea>
              </div>

              <!-- 2 Metrics in Card -->
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div style="background: #FAF8F5; border: 1px solid #E2E8EE; border-radius: 10px; padding: 10px;">
                  <input type="text" name="slides[<?= $idx ?>][m1_val]" class="visual-input-styled" value="<?= e($sl['m1_val']) ?>" style="font-weight: 800; color: var(--wdr-teal); text-align: center; margin-bottom: 4px;" placeholder="Metric 1 Val">
                  <input type="text" name="slides[<?= $idx ?>][m1_lbl]" class="visual-input-styled" value="<?= e($sl['m1_lbl']) ?>" style="font-size: 11px; text-align: center;" placeholder="Metric 1 Label">
                </div>
                <div style="background: #FAF8F5; border: 1px solid #E2E8EE; border-radius: 10px; padding: 10px;">
                  <input type="text" name="slides[<?= $idx ?>][m2_val]" class="visual-input-styled" value="<?= e($sl['m2_val']) ?>" style="font-weight: 800; color: var(--wdr-teal); text-align: center; margin-bottom: 4px;" placeholder="Metric 2 Val">
                  <input type="text" name="slides[<?= $idx ?>][m2_lbl]" class="visual-input-styled" value="<?= e($sl['m2_lbl']) ?>" style="font-size: 11px; text-align: center;" placeholder="Metric 2 Label">
                </div>
              </div>

              <!-- CTA Buttons -->
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                  <input type="text" name="slides[<?= $idx ?>][btn1_text]" class="visual-input-styled" value="<?= e($sl['btn1_text']) ?>" style="margin-bottom: 4px;" placeholder="Btn 1 Text">
                  <input type="text" name="slides[<?= $idx ?>][btn1_url]" class="visual-input-styled" value="<?= e($sl['btn1_url']) ?>" placeholder="Btn 1 URL">
                </div>
                <div>
                  <input type="text" name="slides[<?= $idx ?>][btn2_text]" class="visual-input-styled" value="<?= e($sl['btn2_text']) ?>" style="margin-bottom: 4px;" placeholder="Btn 2 Text">
                  <input type="text" name="slides[<?= $idx ?>][btn2_url]" class="visual-input-styled" value="<?= e($sl['btn2_url']) ?>" placeholder="Btn 2 URL">
                </div>
              </div>
            </div>

            <!-- Slide Image Frame -->
            <div class="visual-media-frame">
              <span class="visual-media-tag"><i class="ri-sparkling-fill"></i> <?= e($sl['media_tag'] ?? 'Client Success Study') ?></span>
              <img src="<?= media_url($sl['img']) ?>" alt="Slide Image" style="max-height: 180px; margin: 14px auto; object-fit: contain; display: block;">
              <input type="hidden" name="slides[<?= $idx ?>][img]" value="<?= e($sl['img']) ?>">
              <input type="text" name="slides[<?= $idx ?>][media_tag]" class="visual-input-styled" value="<?= e($sl['media_tag'] ?? 'Client Success Study') ?>" style="margin-bottom: 8px;" placeholder="Media Tag">
              <input type="file" name="slide_img_<?= $idx ?>" class="visual-input-styled" accept="image/*">
            </div>

          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="margin-top: 28px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 05 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 06: HOW WE WORK (OUR PROCESS)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec06'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec06" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec06">

  <div class="visual-studio-card">
    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start; margin-bottom: 28px;">
      <div>
        <div style="margin-bottom: 12px;">
          <label class="visual-label-upper">Section Label</label>
          <input type="text" name="home_sec6_label" class="visual-input-styled" value="<?= e(setting('home_sec6_label', 'OUR PROCESS')) ?>" style="max-width: 340px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Main Title</label>
          <input type="text" name="home_sec6_title" class="visual-input-styled" value="<?= e(setting('home_sec6_title', 'From brief to brilliance.')) ?>" style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Description</label>
          <textarea name="home_sec6_desc" class="visual-input-styled" rows="2"><?= e(setting('home_sec6_desc', '')) ?></textarea>
        </div>

        <div>
          <label class="visual-label-upper">Process Flow Monospace Tag</label>
          <input type="text" name="home_sec6_flow_tag" class="visual-input-styled" value="<?= e(setting('home_sec6_flow_tag', 'Research → Strategy → Writing → Refinement')) ?>" style="font-family: var(--wdr-font-mono); color: var(--wdr-teal); font-weight: 700;">
        </div>
      </div>

      <div>
        <div class="visual-media-frame">
          <span class="visual-media-tag"><i class="ri-route-line"></i> Production Journey</span>
          <?php $sec6Art = setting('home_sec6_artwork', '/img/process.png'); ?>
          <div id="preview_sec6_art" style="margin: 16px 0;">
            <img src="<?= media_url($sec6Art) ?>" alt="Process Artwork" style="max-height: 200px; margin: 0 auto; object-fit: contain; display: block;">
          </div>
          <div style="border-top: 1px dashed #E2E8EE; padding-top: 14px; text-align: left;">
            <label class="visual-label-upper">Upload New Process Artwork</label>
            <input type="hidden" name="remove_sec6_artwork" id="remove_sec6_artwork" value="0">
            <input type="file" name="home_sec6_artwork_file" class="visual-input-styled" accept="image/*">
          </div>
        </div>
      </div>
    </div>

    <!-- 4 Process Step Cards (Visual Match) -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-node-tree" style="color: var(--wdr-teal);"></i> 4 Process Steps
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
        <?php for ($p = 0; $p < 4; $p++): $step = $processSteps[$p] ?? ['step_num' => 'STEP 0' . ($p+1), 'title' => '', 'desc' => '']; ?>
        <div class="visual-feature-card">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
            <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--wdr-teal);"></div>
            <input type="text" name="steps[<?= $p ?>][step_num]" class="visual-input-styled" value="<?= e($step['step_num']) ?>" style="font-family: var(--wdr-font-mono); font-size: 11px; font-weight: 800; color: var(--wdr-teal); padding: 4px 8px;">
          </div>
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Step Title</label>
            <input type="text" name="steps[<?= $p ?>][title]" class="visual-input-styled" value="<?= e($step['title']) ?>" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Step Description</label>
            <textarea name="steps[<?= $p ?>][desc]" class="visual-input-styled" rows="3"><?= e($step['desc']) ?></textarea>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <div style="margin-top: 28px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 06 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 07: CLIENT STORIES & RESULT METRICS
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec07'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec07" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec07">

  <div class="visual-studio-card">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 24px;">
      <div>
        <label class="visual-label-upper">Section Label</label>
        <input type="text" name="home_sec7_label" class="visual-input-styled" value="<?= e(setting('home_sec7_label', 'CLIENT STORIES')) ?>" style="font-weight: 700;">
      </div>
      <div>
        <label class="visual-label-upper">Main Title</label>
        <input type="text" name="home_sec7_title" class="visual-input-styled" value="<?= e(setting('home_sec7_title', '“The work speaks too.”')) ?>" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700;">
      </div>
    </div>

    <!-- 3 Spotlight Testimonials (Visual Match) -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px; margin-bottom: 28px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-chat-quote-line" style="color: var(--wdr-teal);"></i> 3 Spotlight Testimonial Slides
      </h3>

      <div style="display: flex; flex-direction: column; gap: 20px;">
        <?php for ($t = 0; $t < 3; $t++): $testi = $testimonials[$t] ?? ['quote' => '', 'author_name' => '', 'author_role' => '', 'author_badge' => 'Verified Client', 'initials' => 'PS', 'stars' => 5]; ?>
        <div class="visual-testi-card">
          <span class="visual-quote-mark">“</span>
          
          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Client Quote</label>
            <textarea name="testimonials[<?= $t ?>][quote]" class="visual-input-styled" rows="2" style="font-family: var(--wdr-font-display); font-style: italic; font-size: 15px;"><?= e($testi['quote']) ?></textarea>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; background: #FAF8F5; padding: 16px; border-radius: 12px; border: 1px solid #E2E8EE;">
            <div>
              <label class="visual-label-upper">Author Name</label>
              <input type="text" name="testimonials[<?= $t ?>][author_name]" class="visual-input-styled" value="<?= e($testi['author_name']) ?>" style="font-weight: 700;">
            </div>
            <div>
              <label class="visual-label-upper">Author Role &amp; Company</label>
              <input type="text" name="testimonials[<?= $t ?>][author_role]" class="visual-input-styled" value="<?= e($testi['author_role']) ?>">
            </div>
            <div>
              <label class="visual-label-upper">Client Badge</label>
              <input type="text" name="testimonials[<?= $t ?>][author_badge]" class="visual-input-styled" value="<?= e($testi['author_badge']) ?>">
            </div>
            <div>
              <label class="visual-label-upper">Initials &amp; Rating</label>
              <div style="display: flex; gap: 8px;">
                <input type="text" name="testimonials[<?= $t ?>][initials]" class="visual-input-styled" value="<?= e($testi['initials'] ?? '') ?>" placeholder="PS" style="max-width: 60px; text-align: center; font-weight: 800;">
                <input type="number" name="testimonials[<?= $t ?>][stars]" class="visual-input-styled" value="<?= (int)($testi['stars'] ?? 5) ?>" min="1" max="5" style="max-width: 60px; text-align: center;">
              </div>
            </div>
            <div style="grid-column: 1 / -1; margin-top: 6px; padding-top: 12px; border-top: 1px dashed rgba(74, 139, 140, 0.25);">
              <label class="visual-label-upper"><i class="ri-image-line"></i> Client Photo / Avatar Image (Optional)</label>
              <input type="hidden" name="testimonials[<?= $t ?>][remove_avatar]" id="remove_avatar_<?= $t ?>" value="0">
              <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($testi['avatar_img'])): ?>
                  <div id="preview_wrap_<?= $t ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #FFF; padding: 4px 10px; border-radius: 30px; border: 1px solid #E2E8EE;">
                    <img src="<?= media_url($testi['avatar_img']) ?>" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--wdr-teal);">
                    <button type="button" onclick="document.getElementById('avatar_input_<?= $t ?>').value=''; document.getElementById('remove_avatar_<?= $t ?>').value='1'; document.getElementById('preview_wrap_<?= $t ?>').style.display='none';" class="btn-adm btn-adm-danger" style="padding: 3px 10px; font-size: 11px; height: auto; border-radius: 20px; cursor: pointer;">
                      <i class="ri-delete-bin-line"></i> Remove Image
                    </button>
                  </div>
                <?php endif; ?>
                <input type="text" name="testimonials[<?= $t ?>][avatar_img]" id="avatar_input_<?= $t ?>" class="visual-input-styled" value="<?= e($testi['avatar_img'] ?? '') ?>" placeholder="Image path e.g. /uploads/testimonials/client.jpg" style="flex: 1; min-width: 180px;">
                <input type="file" name="testimonials[<?= $t ?>][avatar_file]" accept="image/*" class="visual-input-styled" style="max-width: 190px; padding: 6px 10px; font-size: 11px;">
              </div>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- 4 Result Metrics Strip (Visual Match) -->
    <div style="border-top: 1.5px dashed rgba(74, 139, 140, 0.4); padding-top: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 18px;">
        <i class="ri-percent-line" style="color: var(--wdr-teal);"></i> 4 Result Metrics Strip
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <?php for ($m = 0; $m < 4; $m++): $metric = $resultMetrics[$m] ?? ['num' => '', 'label' => '']; ?>
        <div style="background: var(--wdr-deep-navy); color: #FFF; border-radius: 16px; padding: 20px; text-align: center; border: 1.5px dashed rgba(74, 139, 140, 0.45);">
          <div style="font-family: var(--wdr-font-display); font-size: 28px; font-weight: 800; color: var(--wdr-teal-pale); margin-bottom: 8px;"><?= e($metric['num']) ?></div>
          <input type="text" name="metrics[<?= $m ?>][num]" class="visual-input-styled visual-input-dark" value="<?= e($metric['num']) ?>" style="text-align: center; font-weight: 800; margin-bottom: 6px;" placeholder="Value (+4x)">
          <input type="text" name="metrics[<?= $m ?>][label]" class="visual-input-styled visual-input-dark" value="<?= e($metric['label']) ?>" style="text-align: center; font-size: 11px;" placeholder="Metric Label">
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <div style="margin-top: 28px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 07 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 08: FROM THE EDITORIAL DESK
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec08'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec08">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec08">

  <div class="visual-studio-card">
    <span class="visual-badge"><i class="ri-article-line"></i> SECTION 08 — FROM THE EDITORIAL DESK</span>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin: 18px 0;">
      <div>
        <label class="visual-label-upper">Section Label</label>
        <input type="text" name="home_sec8_label" class="visual-input-styled" value="<?= e(setting('home_sec8_label', 'FROM THE EDITORIAL DESK')) ?>" style="font-weight: 700;">
      </div>
      <div>
        <label class="visual-label-upper">Main Title</label>
        <input type="text" name="home_sec8_title" class="visual-input-styled" value="<?= e(setting('home_sec8_title', 'Ideas worth reading.')) ?>" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700;">
      </div>
      <div>
        <label class="visual-label-upper">Button Text &amp; URL</label>
        <input type="text" name="home_sec8_btn_text" class="visual-input-styled" value="<?= e(setting('home_sec8_btn_text', 'View All Insights')) ?>" style="margin-bottom: 6px;">
        <input type="text" name="home_sec8_btn_url" class="visual-input-styled" value="<?= e(setting('home_sec8_btn_url', 'blog/')) ?>">
      </div>
      <div>
        <label class="visual-label-upper">Display Count (Recent Blog Posts)</label>
        <input type="number" name="home_sec8_count" min="1" max="12" class="visual-input-styled" value="<?= e(setting('home_sec8_count', '3')) ?>">
      </div>
    </div>

    <div style="margin-top: 24px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 08 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 09: START A CONVERSATION (SIGNATURE CTA)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec09'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec09" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="section_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec09">

  <div class="visual-studio-card">
    <div class="visual-dark-cta">
      <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 32px; align-items: center;">
        
        <div>
          <span class="visual-badge" style="background: rgba(74, 139, 140, 0.35); color: var(--wdr-teal-pale); margin-bottom: 12px;">
            <i class="ri-sparkling-fill"></i> <?= e(setting('home_sec9_badge', "LET'S MAKE SOMETHING MEANINGFUL")) ?>
          </span>

          <div style="margin-bottom: 14px;">
            <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Badge Text</label>
            <input type="text" name="home_sec9_badge" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_badge', "LET'S MAKE SOMETHING MEANINGFUL")) ?>">
          </div>

          <div style="margin-bottom: 14px;">
            <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">CTA Title (Supports &lt;em&gt;)</label>
            <input type="text" name="home_sec9_title" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_title', "Start something <em>worth reading.</em>")) ?>" style="font-family: var(--wdr-font-display); font-size: 20px;">
          </div>

          <div style="margin-bottom: 18px;">
            <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">CTA Description</label>
            <textarea name="home_sec9_desc" class="visual-input-styled visual-input-dark" rows="3"><?= e(setting('home_sec9_desc', '')) ?></textarea>
          </div>

          <!-- Buttons -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
            <div>
              <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Primary Button</label>
              <input type="text" name="home_sec9_btn1_text" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_btn1_text', 'Start a Conversation')) ?>" style="margin-bottom: 4px;">
              <input type="text" name="home_sec9_btn1_url" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_btn1_url', 'contact.php')) ?>">
            </div>
            <div>
              <label class="visual-label-upper" style="color: var(--wdr-teal-pale);">Secondary Button</label>
              <input type="text" name="home_sec9_btn2_text" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_btn2_text', 'Explore Studio')) ?>" style="margin-bottom: 4px;">
              <input type="text" name="home_sec9_btn2_url" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_btn2_url', 'who-we-are.php')) ?>">
            </div>
          </div>

          <!-- Trust Pills -->
          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
            <input type="text" name="home_sec9_pill1" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_pill1', '24h Response')) ?>" style="font-size: 11px; text-align: center;">
            <input type="text" name="home_sec9_pill2" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_pill2', 'NDA Protected')) ?>" style="font-size: 11px; text-align: center;">
            <input type="text" name="home_sec9_pill3" class="visual-input-styled visual-input-dark" value="<?= e(setting('home_sec9_pill3', 'Free Content Audit')) ?>" style="font-size: 11px; text-align: center;">
          </div>
        </div>

        <!-- Right Side CTA Artwork -->
        <div>
          <div style="background: rgba(255,255,255,0.06); border: 1.5px dashed rgba(255,255,255,0.3); border-radius: 20px; padding: 24px; text-align: center;">
            <?php $sec9Art = setting('home_sec9_artwork', '/img/cta 1.png'); ?>
            <div id="preview_sec9_art" style="margin-bottom: 14px;">
              <img src="<?= media_url($sec9Art) ?>" alt="CTA Artwork" style="max-height: 200px; margin: 0 auto; object-fit: contain; display: block;">
              <?php if (!empty($sec9Art)): ?>
                <button type="button" onclick="document.getElementById('remove_sec9_artwork').value='1'; document.getElementById('preview_sec9_art').style.display='none'; this.style.display='none';" class="btn-adm btn-adm-danger" style="margin-top: 10px; padding: 4px 12px; font-size: 11px; border-radius: 20px; cursor: pointer;">
                  <i class="ri-delete-bin-line"></i> Remove Image
                </button>
              <?php endif; ?>
            </div>
            <label class="visual-label-upper" style="color: var(--wdr-teal-pale); text-align: left;">Upload New CTA Illustration</label>
            <input type="hidden" name="remove_sec9_artwork" id="remove_sec9_artwork" value="0">
            <input type="file" name="home_sec9_artwork_file" class="visual-input-styled visual-input-dark" accept="image/*">
          </div>
        </div>

      </div>
    </div>

    <div style="margin-top: 24px; text-align: right;">
      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 09 Changes</button>
    </div>
  </div>
</form>
<?php endif; ?>
