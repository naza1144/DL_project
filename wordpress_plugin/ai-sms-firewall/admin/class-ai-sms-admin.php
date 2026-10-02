<?php
/**
 * WordPress Admin Management Interface
 * จัดการเมนูหลังบ้าน, การตั้งค่า, AJAX Test Sandbox, และตาราง Audit Log
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SMS_Admin {

    private $api_client;

    public function __construct() {
        $this->api_client = new AI_SMS_API_Client();
    }

    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // AJAX handlers สำหรับ Sandbox และ Ping
        add_action('wp_ajax_ai_sms_test_scan', [$this, 'ajax_test_scan']);
        add_action('wp_ajax_ai_sms_test_ping', [$this, 'ajax_test_ping']);
        add_action('wp_ajax_ai_sms_clear_logs', [$this, 'ajax_clear_logs']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'DL SMS Firewall',
            'DL SMS Firewall',
            'manage_options',
            'ai-sms-firewall',
            [$this, 'render_admin_page'],
            'dashicons-shield',
            30
        );
    }

    public function register_settings() {
        register_setting('ai_sms_settings_group', 'ai_sms_api_url', [
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => 'http://127.0.0.1:8000/api/inspect/'
        ]);

        register_setting('ai_sms_settings_group', 'ai_sms_risk_threshold', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 75
        ]);

        register_setting('ai_sms_settings_group', 'ai_sms_action', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'block'
        ]);

        register_setting('ai_sms_settings_group', 'ai_sms_enable_comments', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 1
        ]);

        register_setting('ai_sms_settings_group', 'ai_sms_enable_webhook', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 1
        ]);
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // ดึงสถิติจากตาราง wp_ai_sms_logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_sms_logs';

        $total_scanned = 0;
        $total_blocked = 0;
        $recent_logs = [];

        // เช็คว่ามีตารางหรือไม่
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            $total_scanned = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $total_blocked = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_scam = 1");
            $recent_logs   = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 50");
        }

        $api_url = get_option('ai_sms_api_url', 'http://127.0.0.1:8000/api/inspect/');
        $threshold = get_option('ai_sms_risk_threshold', 75);
        $action_pref = get_option('ai_sms_action', 'block');
        $enable_comments = get_option('ai_sms_enable_comments', 1);
        $enable_webhook = get_option('ai_sms_enable_webhook', 1);

        $webhook_url = rest_url('ai-sms-firewall/v1/incoming-sms');

        include AI_SMS_FIREWALL_DIR . 'admin/views/settings-page.php';
    }

    /**
     * AJAX ทดสอบการเชื่อมต่อไปยัง Django AI Server
     */
    public function ajax_test_ping() {
        check_ajax_referer('ai_sms_admin_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $client = new AI_SMS_API_Client();
        $res = $client->inspect_text('ทดสอบระบบ ping');

        if ($res['success']) {
            wp_send_json_success([
                'message'    => 'เชื่อมต่อ AI Deep Learning Server สำเร็จ!',
                'latency_ms' => $res['latency_ms']
            ]);
        } else {
            wp_send_json_error([
                'message' => 'เชื่อมต่อไม่สำเร็จ: ' . $res['error']
            ]);
        }
    }

    /**
     * AJAX ทดสอบข้อความใน Live Sandbox
     */
    public function ajax_test_scan() {
        check_ajax_referer('ai_sms_admin_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $text = isset($_POST['text']) ? sanitize_textarea_field($_POST['text']) : '';
        if (empty($text)) {
            wp_send_json_error(['message' => 'กรุณากรอกข้อความ']);
        }

        $client = new AI_SMS_API_Client();
        $res = $client->inspect_text($text);

        wp_send_json_success($res);
    }

    /**
     * AJAX ล้างประวัติ Logs
     */
    public function ajax_clear_logs() {
        check_ajax_referer('ai_sms_admin_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_sms_logs';
        $wpdb->query("TRUNCATE TABLE $table_name");

        wp_send_json_success(['message' => 'ล้างประวัติข้อความเรียบร้อยแล้ว']);
    }
}
