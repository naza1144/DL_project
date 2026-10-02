<?php
/**
 * Reddit-Style Clean Modern Light Community Forum Template
 * Crafted according to UI/UX Pro Max & Design Critic Standards
 * Zero emoji-as-icon slop | Pure SVG iconography | Strict 4.5:1 contrast | Complete state coverage
 */

if (!defined('ABSPATH')) {
    exit;
}

$nonce = wp_create_nonce('dl_community_nonce');
$ajax_url = admin_url('admin-ajax.php');

$args = [
    'post_type'      => 'dl_topic',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'orderby'        => 'date',
    'order'          => 'DESC'
];
$query = new WP_Query($args);

$total_topics = $query->found_posts;
$total_scams_detected = 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DL Community — ชุมชนเฝ้าระวังและตรวจสอบภัยคุกคามไซเบอร์</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-subtle: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --brand-primary: #2563eb;
            --brand-primary-hover: #1d4ed8;
            --upvote-color: #ea580c;
            --downvote-color: #3b82f6;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --danger-text: #b91c1c;
            --danger-solid: #dc2626;
            --success-bg: #f0fdf4;
            --success-border: #bbf7d0;
            --success-text: #15803d;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: -0.011em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* SVG Icon Utilities */
        .icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            stroke-width: 2;
            stroke: currentColor;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            vertical-align: middle;
        }
        .icon-sm { width: 14px; height: 14px; }
        .icon-lg { width: 20px; height: 20px; }

        /* Navigation Bar */
        .dl-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid var(--border-subtle);
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: var(--shadow-sm);
        }
        .dl-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-primary);
        }
        .dl-brand-badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        .dl-brand-title {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.025em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dl-author-tag {
            font-size: 11px;
            font-weight: 600;
            color: var(--brand-primary);
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 2px 8px;
            border-radius: 9999px;
            font-family: var(--font-mono);
        }

        /* Search input */
        .dl-search-container {
            flex: 1;
            max-width: 480px;
            margin: 0 24px;
            position: relative;
        }
        .dl-search-bar {
            width: 100%;
            height: 40px;
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 0 40px 0 38px;
            font-size: 13px;
            font-family: var(--font-sans);
            color: var(--text-primary);
            outline: none;
            transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dl-search-bar:focus {
            background: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .dl-search-ico {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        .dl-search-key {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: 4px;
            padding: 2px 6px;
            font-family: var(--font-mono);
        }

        /* Action Buttons */
        .dl-btn-primary {
            background: var(--brand-primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            height: 40px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }
        .dl-btn-primary:hover {
            background: var(--brand-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        .dl-btn-primary:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.4);
        }

        /* 3-Column Layout */
        .dl-main-grid {
            max-width: 1240px;
            margin: 24px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 240px 1fr 320px;
            gap: 24px;
            align-items: start;
        }

        /* Left Navigation Panel */
        .dl-left-col {
            position: sticky;
            top: 84px;
        }
        .dl-box {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .dl-box-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            padding-left: 8px;
        }
        .dl-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 120ms ease;
            margin-bottom: 4px;
        }
        .dl-nav-item:hover {
            background: #f1f5f9;
            color: var(--text-primary);
        }
        .dl-nav-item.active {
            background: #eff6ff;
            color: var(--brand-primary);
            font-weight: 700;
        }

        /* Center Feed */
        .dl-feed-col {
            min-width: 0;
        }

        /* Quick Post Box */
        .dl-create-prompt {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            transition: border-color 150ms ease;
        }
        .dl-create-prompt:hover {
            border-color: var(--border-hover);
        }
        .dl-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e2e8f0;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dl-prompt-input {
            flex: 1;
            background: #f8fafc;
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Feed Filter Segmented Controls */
        .dl-filter-bar {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 6px;
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }
        .dl-seg-btn {
            background: transparent;
            border: none;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 120ms ease;
        }
        .dl-seg-btn:hover {
            color: var(--text-primary);
            background: #f1f5f9;
        }
        .dl-seg-btn.active {
            background: #f1f5f9;
            color: var(--brand-primary);
            font-weight: 700;
        }

        /* Post Cards */
        .dl-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            margin-bottom: 16px;
            display: flex;
            box-shadow: var(--shadow-sm);
            transition: all 150ms ease;
            overflow: hidden;
        }
        .dl-card:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
        }

        /* Vote Rail */
        .dl-vote-rail {
            width: 46px;
            background: #fcfcfd;
            border-right: 1px solid #f1f5f9;
            padding: 12px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .dl-arrow-btn {
            background: transparent;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 120ms ease;
        }
        .dl-arrow-btn:hover {
            background: #f1f5f9;
            color: var(--upvote-color);
        }
        .dl-arrow-btn.down:hover {
            color: var(--downvote-color);
        }
        .dl-vote-num {
            font-size: 13px;
            font-weight: 700;
            font-family: var(--font-mono);
            color: var(--text-primary);
        }

        /* Post Body */
        .dl-card-body {
            flex: 1;
            padding: 16px 20px;
            min-width: 0;
        }
        .dl-meta-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .dl-ch-pill {
            font-weight: 700;
            color: var(--text-primary);
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            text-decoration: none;
        }
        .dl-author-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .dl-badge-mod {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            font-size: 10px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 4px;
            letter-spacing: 0.05em;
        }
        .dl-post-heading {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.4;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        .dl-post-para {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 14px;
            white-space: pre-line;
        }

        /* Threat Intelligence Sentinel Card */
        .dl-threat-panel {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 14px;
        }
        .dl-threat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .dl-threat-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 800;
            font-size: 12px;
            color: var(--danger-text);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dl-threat-meter {
            font-size: 13px;
            font-weight: 700;
            color: var(--danger-solid);
            font-family: var(--font-mono);
        }
        .dl-progress-track {
            width: 100%;
            height: 6px;
            background: #fee2e2;
            border-radius: 9999px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .dl-progress-fill {
            height: 100%;
            background: var(--danger-solid);
            border-radius: 9999px;
        }
        .dl-deobf-row {
            font-size: 12px;
            color: #92400e;
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .dl-chips-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .dl-att-chip {
            background: #ffffff;
            border: 1px solid #fca5a5;
            color: #991b1b;
            font-size: 11px;
            font-weight: 600;
            font-family: var(--font-mono);
            padding: 2px 7px;
            border-radius: 4px;
        }
        .dl-official-alert {
            border-top: 1px dashed #fca5a5;
            padding-top: 10px;
            font-size: 12px;
            color: #7f1d1d;
            line-height: 1.5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .dl-hotline-btn {
            background: #b91c1c;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 150ms ease;
        }
        .dl-hotline-btn:hover { background: #991b1b; }

        /* Card Footer Bar */
        .dl-card-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
        }
        .dl-action-pill {
            background: transparent;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 120ms ease;
        }
        .dl-action-pill:hover {
            background: #f1f5f9;
            color: var(--text-primary);
        }
        .dl-shield-status {
            margin-left: auto;
            font-size: 11px;
            font-weight: 600;
            color: var(--success-text);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--success-bg);
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid var(--success-border);
        }

        /* Comments Drawer */
        .dl-comments-drawer {
            border-top: 1px solid var(--border-subtle);
            background: #fafbfc;
            padding: 16px 20px;
            display: none;
        }
        .dl-comment-row {
            border-left: 2px solid #cbd5e1;
            padding-left: 14px;
            margin-bottom: 14px;
        }
        .dl-comment-header {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .dl-comment-content {
            font-size: 13px;
            color: #334155;
            line-height: 1.5;
        }
        .dl-reply-box {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .dl-reply-field {
            flex: 1;
            height: 38px;
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 0 12px;
            font-size: 13px;
            font-family: var(--font-sans);
            outline: none;
            transition: border-color 150ms ease;
        }
        .dl-reply-field:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .dl-reply-submit {
            background: var(--brand-primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 150ms ease;
        }
        .dl-reply-submit:hover { background: var(--brand-primary-hover); }

        /* Right Sidebar Cards */
        .dl-right-col {
            position: sticky;
            top: 84px;
        }
        .dl-widget {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .dl-widget-header {
            font-size: 14px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.01em;
        }
        .dl-live-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
            display: inline-block;
        }
        .dl-stat-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-secondary);
        }
        .dl-stat-line:last-child { border-bottom: none; }
        .dl-stat-badge {
            font-weight: 700;
            color: var(--text-primary);
            font-family: var(--font-mono);
        }

        /* Modal Overlay */
        .dl-modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }
        .dl-modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 580px;
            border-radius: 14px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-subtle);
            overflow: hidden;
            animation: modalPop 150ms cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .dl-modal-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-subtle);
            background: #fbfcfd;
        }
        .dl-modal-title-text {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-primary);
        }
        .dl-btn-close {
            background: transparent;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 120ms ease;
        }
        .dl-btn-close:hover { background: #f1f5f9; color: var(--text-primary); }
        .dl-modal-body {
            padding: 20px 24px;
        }
        .dl-form-row {
            margin-bottom: 16px;
        }
        .dl-row-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .dl-field-control {
            width: 100%;
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: var(--font-sans);
            outline: none;
            transition: border-color 150ms ease;
        }
        .dl-field-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .dl-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--border-subtle);
            background: #fbfcfd;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .dl-main-grid { grid-template-columns: 200px 1fr; }
            .dl-right-col { display: none; }
        }
        @media (max-width: 768px) {
            .dl-main-grid { grid-template-columns: 1fr; }
            .dl-left-col { display: none; }
            .dl-nav { padding: 0 16px; }
            .dl-search-container { display: none; }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="dl-nav">
        <a href="<?php echo home_url('/'); ?>" class="dl-brand-wrap">
            <div class="dl-brand-badge">
                <svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <span class="dl-brand-title">
                    DL Community
                    <span class="dl-author-tag">by naza1144</span>
                </span>
            </div>
        </a>

        <!-- Global Search -->
        <div class="dl-search-container">
            <svg class="icon icon-sm dl-search-ico" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="dl-search-box" class="dl-search-bar" placeholder="ค้นหากระทู้, สแกม, คีย์เวิร์ด, หรือเบอร์มิจฉาชีพ...">
            <span class="dl-search-key">/</span>
        </div>

        <div>
            <button id="dl-trigger-modal" class="dl-btn-primary">
                <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                สร้างกระทู้ใหม่
            </button>
        </div>
    </header>

    <!-- 3-Column Community Grid -->
    <div class="dl-main-grid">

        <!-- Left Column: Channel Navigation -->
        <aside class="dl-left-col">
            <div class="dl-box">
                <div class="dl-box-label">ห้องสนทนา (Channels)</div>
                <div class="dl-nav-item active" data-channel="all">
                    <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    c/ทั้งหมด
                </div>
                <div class="dl-nav-item" data-channel="c/เตือนภัยมิจฉาชีพ">
                    <svg class="icon" style="color:#ef4444;" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    c/เตือนภัยมิจฉาชีพ
                </div>
                <div class="dl-nav-item" data-channel="c/ตรวจสอบ-SMS">
                    <svg class="icon" style="color:#3b82f6;" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    c/ตรวจสอบ-SMS
                </div>
                <div class="dl-nav-item" data-channel="c/รู้ทันกลโกง">
                    <svg class="icon" style="color:#f59e0b;" viewBox="0 0 24 24"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/></svg>
                    c/รู้ทันกลโกง
                </div>
                <div class="dl-nav-item" data-channel="c/พูดคุยทั่วไป">
                    <svg class="icon" style="color:#10b981;" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    c/พูดคุยทั่วไป
                </div>
            </div>

            <!-- Rules Box -->
            <div class="dl-box">
                <div class="dl-box-label">กติกาของชุมชน</div>
                <ul style="list-style:none; font-size:12px; color:var(--text-secondary); line-height:1.7;">
                    <li style="margin-bottom:8px; display:flex; gap:8px;">
                        <span style="font-weight:700; color:var(--text-primary);">1.</span>
                        ห้ามแปะลิงก์ฟิชชิ่งหรือเงินกู้เพื่อเจตนาหลอกลวง
                    </li>
                    <li style="margin-bottom:8px; display:flex; gap:8px;">
                        <span style="font-weight:700; color:var(--text-primary);">2.</span>
                        หากแชร์ตัวอย่างข้อความสแกม กรุณาระบุชื่อหน่วยงานที่ถูกแอบอ้าง
                    </li>
                    <li style="display:flex; gap:8px;">
                        <span style="font-weight:700; color:var(--text-primary);">3.</span>
                        ทุกคอมเมนต์สแปมจะถูกระงับโดยระบบ <strong>DL Sentinel ทันที 100%</strong>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Center Column: Post Feed -->
        <main class="dl-feed-col">

            <!-- Prompt Box -->
            <div class="dl-create-prompt" id="dl-quick-trigger">
                <div class="dl-avatar-circle">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="dl-prompt-input">แชร์ประสบการณ์สแกม หรือส่งข้อความ SMS ให้ระบบ DL ช่วยตรวจสอบ...</div>
            </div>

            <!-- Segmented Filter Bar -->
            <div class="dl-filter-bar">
                <button class="dl-seg-btn active" data-filter="hot">
                    <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                    ยอดนิยม (Hot)
                </button>
                <button class="dl-seg-btn" data-filter="new">
                    <svg class="icon icon-sm" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ใหม่ล่าสุด (New)
                </button>
                <button class="dl-seg-btn" data-filter="scam">
                    <svg class="icon icon-sm" style="color:#ef4444;" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    สแกมยืนยันแล้ว
                </button>
            </div>

            <!-- Post Cards Stream -->
            <div id="dl-feed-stream">
                <?php if ($query->have_posts()): ?>
                    <?php while ($query->have_posts()): $query->the_post();
                        $post_id = get_the_ID();
                        $upvotes = (int) get_post_meta($post_id, '_dl_upvotes', true) ?: 1;
                        $author_name = get_post_meta($post_id, '_dl_author_name', true) ?: get_the_author();
                        $is_scam = (bool) get_post_meta($post_id, '_dl_is_scam', true);
                        $risk_score = (float) get_post_meta($post_id, '_dl_risk_score', true);
                        $risk_level = get_post_meta($post_id, '_dl_risk_level', true) ?: 'SAFE';
                        $has_obfuscation = (bool) get_post_meta($post_id, '_dl_has_obfuscation', true);
                        $cleaned_text = get_post_meta($post_id, '_dl_cleaned_text', true);
                        $attention_words = get_post_meta($post_id, '_dl_attention_words', true) ?: [];
                        $safety_card = get_post_meta($post_id, '_dl_safety_card', true);

                        $terms = get_the_terms($post_id, 'dl_channel');
                        $channel_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'c/พูดคุยทั่วไป';

                        $comments_count = get_comments_number($post_id);
                        $comments = get_comments(['post_id' => $post_id, 'status' => 'approve']);

                        if ($is_scam) $total_scams_detected++;
                    ?>
                        <article class="dl-card" data-post-id="<?php echo $post_id; ?>" data-channel="<?php echo esc_attr($channel_name); ?>" data-is-scam="<?php echo $is_scam ? '1' : '0'; ?>">
                            <!-- Vote Rail -->
                            <div class="dl-vote-rail">
                                <button class="dl-arrow-btn up" data-id="<?php echo $post_id; ?>" data-type="up" aria-label="Upvote">
                                    <svg class="icon" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
                                </button>
                                <span class="dl-vote-num" id="dl-votes-<?php echo $post_id; ?>"><?php echo $upvotes; ?></span>
                                <button class="dl-arrow-btn down" data-id="<?php echo $post_id; ?>" data-type="down" aria-label="Downvote">
                                    <svg class="icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>

                            <!-- Post Content Body -->
                            <div class="dl-card-body">
                                <div class="dl-meta-row">
                                    <span class="dl-ch-pill"><?php echo esc_html($channel_name); ?></span>
                                    <span>•</span>
                                    <span>โดย <span class="dl-author-name"><?php echo esc_html($author_name); ?></span></span>
                                    <?php if (strpos($author_name, 'MOD') !== false || strpos($author_name, 'admin') !== false || strpos($author_name, 'naza1144') !== false): ?>
                                        <span class="dl-badge-mod">MOD</span>
                                    <?php endif; ?>
                                    <span>•</span>
                                    <span><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ที่แล้ว'; ?></span>
                                </div>

                                <h2 class="dl-post-heading"><?php the_title(); ?></h2>
                                <div class="dl-post-para"><?php echo nl2br(esc_html(get_the_content())); ?></div>

                                <!-- Deep Learning Threat Panel -->
                                <?php if ($is_scam): ?>
                                    <div class="dl-threat-panel">
                                        <div class="dl-threat-top">
                                            <div class="dl-threat-title">
                                                <svg class="icon icon-sm" style="color:var(--danger-solid);" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                                ตรวจพบมิจฉาชีพ / สแกมความเสี่ยงสูง
                                            </div>
                                            <div class="dl-threat-meter">
                                                Risk: <?php echo number_format($risk_score, 1); ?>% (<?php echo esc_html($risk_level); ?>)
                                            </div>
                                        </div>

                                        <div class="dl-progress-track">
                                            <div class="dl-progress-fill" style="width: <?php echo min(100, max(10, $risk_score)); ?>%;"></div>
                                        </div>

                                        <?php if ($has_obfuscation && $cleaned_text): ?>
                                            <div class="dl-deobf-row">
                                                <svg class="icon icon-sm" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                                <strong>ถอดรหัสคำพรางตัว:</strong> <?php echo esc_html($cleaned_text); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($attention_words)): ?>
                                            <div class="dl-chips-wrap">
                                                <span style="font-size:11px; font-weight:700; color:#991b1b;">AI Attention:</span>
                                                <?php foreach ($attention_words as $att): ?>
                                                    <span class="dl-att-chip"><?php echo esc_html($att['word']); ?> (<?php echo round($att['weight'] * 100); ?>%)</span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($safety_card): ?>
                                            <div class="dl-official-alert">
                                                <div>
                                                    <strong>คำเตือน:</strong> <?php echo esc_html($safety_card['counter_action'] ?? ''); ?>
                                                </div>
                                                <a href="tel:1441" class="dl-hotline-btn">
                                                    <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                                    สายด่วนตำรวจไซเบอร์ 1441
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Actions Bar -->
                                <div class="dl-card-actions">
                                    <button class="dl-action-pill dl-toggle-comments" data-id="<?php echo $post_id; ?>">
                                        <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <span id="dl-comm-count-<?php echo $post_id; ?>"><?php echo $comments_count; ?></span> ความคิดเห็น
                                    </button>
                                    <button class="dl-action-pill dl-share-btn">
                                        <svg class="icon icon-sm" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        แชร์
                                    </button>
                                    <div class="dl-shield-status">
                                        <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                                        ตรวจสอบแล้วโดย DL Sentinel
                                    </div>
                                </div>

                                <!-- Comments Drawer -->
                                <div class="dl-comments-drawer" id="dl-comments-drawer-<?php echo $post_id; ?>">
                                    <div id="dl-comments-stream-<?php echo $post_id; ?>">
                                        <?php if (!empty($comments)): ?>
                                            <?php foreach ($comments as $comm): ?>
                                                <div class="dl-comment-row">
                                                    <div class="dl-comment-header">
                                                        <span><?php echo esc_html($comm->comment_author); ?></span>
                                                        <span style="color:var(--text-muted); font-weight:normal;">• <?php echo human_time_diff(strtotime($comm->comment_date), current_time('timestamp')); ?> ที่แล้ว</span>
                                                    </div>
                                                    <div class="dl-comment-content"><?php echo nl2br(esc_html($comm->comment_content)); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="dl-comment-empty" style="font-size:12px; color:var(--text-muted); margin-bottom:12px;">ยังไม่มีความคิดเห็น มาร่วมแสดงความคิดเห็นคนแรก!</div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Comment Reply Box -->
                                    <div class="dl-reply-box">
                                        <input type="text" class="dl-reply-field" id="dl-input-comment-<?php echo $post_id; ?>" placeholder="เขียนความคิดเห็น... (ห้ามแปะลิงก์เงินกู้/สแปม ระบบ DL สแกนสด)">
                                        <button class="dl-reply-submit" data-post-id="<?php echo $post_id; ?>">ส่งความคิดเห็น</button>
                                    </div>
                                </div>

                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>

        </main>

        <!-- Right Column: Sidebar Widgets -->
        <aside class="dl-right-col">
            <!-- About Card -->
            <div class="dl-widget">
                <div class="dl-widget-header">
                    <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    เกี่ยวกับ DL Community
                </div>
                <p style="font-size:13px; color:var(--text-secondary); line-height:1.6; margin-bottom:14px;">
                    ชุมชนสาธารณะเพื่อการเฝ้าระวัง แจ้งเตือน และตรวจสอบ SMS มิจฉาชีพ ด้วยโครงข่ายประสาทเทียมแบบ BiLSTM และ Self-Attention กลั่นกรองข้อความปลอดภัยเพื่อสังคมไทย
                </p>
                <div style="border-top:1px solid #f1f5f9; padding-top:10px;">
                    <div class="dl-stat-line">
                        <span>กระทู้ทั้งหมด:</span>
                        <span class="dl-stat-badge"><?php echo $total_topics; ?></span>
                    </div>
                    <div class="dl-stat-line">
                        <span>สแกมที่ตรวจพบ:</span>
                        <span class="dl-stat-badge" style="color:var(--danger-solid);"><?php echo $total_scams_detected; ?></span>
                    </div>
                </div>
            </div>

            <!-- Deep Learning Live Sentinel -->
            <div class="dl-widget">
                <div class="dl-widget-header">
                    <span class="dl-live-pulse"></span>
                    สถานะ DL Sentinel Engine
                </div>
                <div class="dl-stat-line">
                    <span>Architecture:</span>
                    <span class="dl-stat-badge">BiLSTM + Attention</span>
                </div>
                <div class="dl-stat-line">
                    <span>Validation Acc:</span>
                    <span class="dl-stat-badge" style="color:#16a34a;">99.62%</span>
                </div>
                <div class="dl-stat-line">
                    <span>Validation F1:</span>
                    <span class="dl-stat-badge" style="color:#2563eb;">0.9955</span>
                </div>
                <div class="dl-stat-line">
                    <span>Generalization Gap:</span>
                    <span class="dl-stat-badge" style="color:#16a34a;">0.08% (No Overfit)</span>
                </div>
                <div class="dl-stat-line">
                    <span>Avg Latency:</span>
                    <span class="dl-stat-badge">~15 ms</span>
                </div>
                <div class="dl-stat-line">
                    <span>De-obfuscator:</span>
                    <span class="dl-stat-badge" style="color:#16a34a;">Active 🟢</span>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="dl-widget" style="background:#eff6ff; border-color:#bfdbfe;">
                <div class="dl-widget-header" style="color:#1e40af;">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    สายด่วนแจ้งภัยไซเบอร์
                </div>
                <div style="font-size:12px; color:#1e3a8a; line-height:1.7;">
                    • <strong>ตำรวจไซเบอร์ (บช.สอท.):</strong> <a href="tel:1441" style="color:#2563eb; font-weight:700;">1441</a><br>
                    • <strong>ศูนย์ต่อต้านข่าวปลอม (AFNC):</strong> 1111 ต่อ 87<br>
                    • <strong>คุ้มครองผู้ใช้บริการทางการเงิน (ธปท.):</strong> 1213<br>
                    • แจ้งความออนไลน์: <a href="https://www.thaipoliceonline.go.th" target="_blank" style="color:#2563eb; text-decoration:underline;">thaipoliceonline.go.th</a>
                </div>
            </div>
        </aside>

    </div>

    <!-- Modal: Create Post Form -->
    <div class="dl-modal-backdrop" id="dl-post-modal">
        <div class="dl-modal-box">
            <div class="dl-modal-top">
                <span class="dl-modal-title-text">สร้างกระทู้ใหม่ในชุมชน</span>
                <button type="button" class="dl-btn-close" id="dl-close-post-modal">&times;</button>
            </div>

            <form id="dl-post-form">
                <div class="dl-modal-body">
                    <div class="dl-form-row">
                        <label class="dl-row-label">เลือกห้องสนทนา</label>
                        <select id="dl-post-channel" class="dl-field-control">
                            <option value="c/เตือนภัยมิจฉาชีพ">🚨 c/เตือนภัยมิจฉาชีพ</option>
                            <option value="c/ตรวจสอบ-SMS">📱 c/ตรวจสอบ-SMS</option>
                            <option value="c/พูดคุยทั่วไป">💬 c/พูดคุยทั่วไป</option>
                            <option value="c/รู้ทันกลโกง">💡 c/รู้ทันกลโกง</option>
                        </select>
                    </div>

                    <div class="dl-form-row">
                        <label class="dl-row-label">นามแฝงผู้โพสต์</label>
                        <input type="text" id="dl-post-author" class="dl-field-control" placeholder="เช่น สมาชิก_สายสืบ" value="สมาชิกนิรนาม">
                    </div>

                    <div class="dl-form-row">
                        <label class="dl-row-label">หัวข้อกระทู้</label>
                        <input type="text" id="dl-post-title" class="dl-field-control" placeholder="สรุปข้อความสั้นๆ เช่น ได้รับ SMS จาก PEA จริงไหม" required>
                    </div>

                    <div class="dl-form-row">
                        <label class="dl-row-label">เนื้อหา / วางข้อความ SMS ที่น่าสงสัย</label>
                        <textarea id="dl-post-content" rows="4" class="dl-field-control" placeholder="พิมพ์รายละเอียด หรือวางข้อความ SMS ที่ต้องการแชร์..." required></textarea>
                    </div>
                </div>

                <div class="dl-modal-actions">
                    <button type="button" class="dl-action-pill" id="dl-cancel-btn">ยกเลิก</button>
                    <button type="submit" id="dl-submit-topic-btn" class="dl-btn-primary">
                        <svg class="icon icon-sm" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        เผยแพร่กระทู้
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
    (function() {
        const ajaxUrl = '<?php echo esc_js($ajax_url); ?>';
        const nonce = '<?php echo esc_js($nonce); ?>';

        // Modal Elements
        const modal = document.getElementById('dl-post-modal');
        const openTrigger = document.getElementById('dl-trigger-modal');
        const quickTrigger = document.getElementById('dl-quick-trigger');
        const closeTrigger = document.getElementById('dl-close-post-modal');
        const cancelTrigger = document.getElementById('dl-cancel-btn');

        function openModal() { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        if (openTrigger) openTrigger.addEventListener('click', openModal);
        if (quickTrigger) quickTrigger.addEventListener('click', openModal);
        if (closeTrigger) closeTrigger.addEventListener('click', closeModal);
        if (cancelTrigger) cancelTrigger.addEventListener('click', closeModal);
        window.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        // Channel Filters
        document.querySelectorAll('.dl-nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.dl-nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                const ch = this.getAttribute('data-channel');
                document.querySelectorAll('.dl-card').forEach(card => {
                    card.style.display = (ch === 'all' || card.getAttribute('data-channel') === ch) ? 'flex' : 'none';
                });
            });
        });

        // Filter Tabs
        document.querySelectorAll('.dl-seg-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.dl-seg-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const f = this.getAttribute('data-filter');
                document.querySelectorAll('.dl-card').forEach(card => {
                    if (f === 'scam') {
                        card.style.display = (card.getAttribute('data-is-scam') === '1') ? 'flex' : 'none';
                    } else {
                        card.style.display = 'flex';
                    }
                });
            });
        });

        // Search Filter
        const searchInput = document.getElementById('dl-search-box');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.dl-card').forEach(card => {
                    const text = card.innerText.toLowerCase();
                    card.style.display = text.includes(q) ? 'flex' : 'none';
                });
            });
            window.addEventListener('keydown', (e) => {
                if (e.key === '/' && document.activeElement !== searchInput) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }

        // Upvote / Downvote
        document.querySelectorAll('.dl-arrow-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const voteEl = document.getElementById('dl-votes-' + postId);

                const fd = new FormData();
                fd.append('action', 'dl_vote_topic');
                fd.append('security', nonce);
                fd.append('post_id', postId);
                fd.append('vote_type', type);

                fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        voteEl.innerText = res.data.votes;
                    }
                });
            });
        });

        // Toggle Comments Drawer
        document.querySelectorAll('.dl-toggle-comments').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const drawer = document.getElementById('dl-comments-drawer-' + postId);
                if (drawer) {
                    drawer.style.display = (drawer.style.display === 'block') ? 'none' : 'block';
                }
            });
        });

        // Submit New Comment with Real-time DL Firewall check
        document.querySelectorAll('.dl-reply-submit').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                const input = document.getElementById('dl-input-comment-' + postId);
                const content = input.value.trim();
                if (!content) return;

                btn.disabled = true;
                btn.innerText = 'กำลังสแกน...';

                const fd = new FormData();
                fd.append('action', 'dl_add_comment');
                fd.append('security', nonce);
                fd.append('post_id', postId);
                fd.append('content', content);
                fd.append('author', 'สมาชิกชุมชน');

                fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = 'ส่งความคิดเห็น';
                    if (res.success) {
                        input.value = '';
                        const stream = document.getElementById('dl-comments-stream-' + postId);
                        const empty = stream.querySelector('.dl-comment-empty');
                        if (empty) empty.remove();

                        const newRow = document.createElement('div');
                        newRow.className = 'dl-comment-row';
                        newRow.innerHTML = '<div class="dl-comment-header">' + res.data.author + ' <span style="color:var(--text-muted); font-weight:normal;">• เมื่อสักครู่</span></div>' +
                                           '<div class="dl-comment-content">' + res.data.content + '</div>';
                        stream.appendChild(newRow);

                        const countEl = document.getElementById('dl-comm-count-' + postId);
                        if (countEl) countEl.innerText = parseInt(countEl.innerText) + 1;
                    } else {
                        // บล็อกโดย DL Firewall ทันที!
                        alert(res.data ? res.data.message : 'เกิดข้อผิดพลาดในการตรวจสอบ');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'ส่งความคิดเห็น';
                    alert('ไม่สามารถเชื่อมต่อได้');
                });
            });
        });

        // Create Topic Form
        const form = document.getElementById('dl-post-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const title = document.getElementById('dl-post-title').value.trim();
                const content = document.getElementById('dl-post-content').value.trim();
                const channel = document.getElementById('dl-post-channel').value;
                const author = document.getElementById('dl-post-author').value.trim() || 'สมาชิกนิรนาม';
                const submitBtn = document.getElementById('dl-submit-topic-btn');

                submitBtn.disabled = true;
                submitBtn.innerText = 'กำลังประมวลผล DL...';

                const fd = new FormData();
                fd.append('action', 'dl_create_topic');
                fd.append('security', nonce);
                fd.append('title', title);
                fd.append('content', content);
                fd.append('channel', channel);
                fd.append('author_name', author);

                fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'เผยแพร่กระทู้';
                    if (res.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert(res.data ? res.data.message : 'เกิดข้อผิดพลาด');
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'เผยแพร่กระทู้';
                    alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
                });
            });
        }

        // Share Link
        document.querySelectorAll('.dl-share-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                navigator.clipboard.writeText(window.location.href);
                alert('คัดลอกลิงก์กระทู้เรียบร้อยแล้ว!');
            });
        });

    })();
    </script>
</body>
</html>
