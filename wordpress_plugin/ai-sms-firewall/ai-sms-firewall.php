<?php
/**
 * Plugin Name: AI SMS & Phishing Firewall
 * Plugin URI: https://github.com/naza1144/DL_project
 * Description: ระบบไฟร์วอลล์ดักจับและคัดกรอง SMS มิจฉาชีพ/สแปม/ฟิชชิ่งแบบเรียลไทม์ด้วย Deep Learning (BiLSTM + Self-Attention) เชื่อมต่อ API อัตโนมัติ
 * Version: 1.0.0
 * Author: Antigravity AI & DL Project Team
 * Author URI: https://github.com/naza1144/DL_project
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-sms-firewall
 */

if (!defined('ABSPATH')) {
    exit; // ป้องกันการเรียกตรงผ่านเบราว์เซอร์
}

define('AI_SMS_FIREWALL_VERSION', '1.0.0');
define('AI_SMS_FIREWALL_DIR', plugin_dir_path(__FILE__));
define('AI_SMS_FIREWALL_URL', plugin_dir_url(__FILE__));

// รวมไฟล์โมดูลย่อย
require_once AI_SMS_FIREWALL_DIR . 'includes/class-ai-sms-api-client.php';
require_once AI_SMS_FIREWALL_DIR . 'includes/class-ai-sms-webhook.php';
require_once AI_SMS_FIREWALL_DIR . 'includes/class-ai-sms-form-guard.php';

if (is_admin()) {
    require_once AI_SMS_FIREWALL_DIR . 'admin/class-ai-sms-admin.php';
}

/**
 * ฟังก์ชันทำงานเมื่อเปิดใช้งานปลั๊กอิน (Activation Hook)
 * สร้างตารางบันทึกประวัติการสแกน wp_ai_sms_logs
 */
register_activation_hook(__FILE__, 'ai_sms_firewall_activate');
function ai_sms_firewall_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_sms_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        sender varchar(100) NOT NULL DEFAULT 'Unknown',
        message text NOT NULL,
        is_scam tinyint(1) NOT NULL DEFAULT 0,
        risk_score decimal(5,2) NOT NULL DEFAULT 0.00,
        risk_level varchar(20) NOT NULL DEFAULT 'SAFE',
        action_taken varchar(30) NOT NULL DEFAULT 'ALLOWED',
        attention_words text,
        source_type varchar(50) NOT NULL DEFAULT 'webhook_sms',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY is_scam (is_scam),
        KEY risk_score (risk_score),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // ค่าเริ่มต้นการตั้งค่า
    add_option('ai_sms_api_url', 'http://127.0.0.1:8000/api/inspect/');
    add_option('ai_sms_risk_threshold', 75);
    add_option('ai_sms_enable_comments', 1);
    add_option('ai_sms_enable_webhook', 1);
    add_option('ai_sms_action', 'block'); // 'block' หรือ 'quarantine' หรือ 'log_only'
}

/**
 * เริ่มต้นทำงานโมดูลต่างๆ
 */
function ai_sms_firewall_init() {
    // ลงทะเบียน Inbound SMS Webhook
    $webhook = new AI_SMS_Webhook();
    $webhook->init();

    // เริ่มต้นระบบ Form & Comment Guard
    $form_guard = new AI_SMS_Form_Guard();
    $form_guard->init();

    // เริ่มต้นส่วนจัดการหลังบ้าน (WP-Admin)
    if (is_admin()) {
        $admin = new AI_SMS_Admin();
        $admin->init();
    }
}
add_action('plugins_loaded', 'ai_sms_firewall_init');
