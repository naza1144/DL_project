<?php
/**
 * Inbound SMS Webhook Handler
 * ลงทะเบียน REST API endpoint สำหรับรับข้อความ SMS ขาเข้าจากผู้ให้บริการ (SMS Gateway)
 * เช่น ThaiBulkSMS, Twilio, SMSMKT, หรือแอป Android SMS Forwarder
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SMS_Webhook {

    private $api_client;

    public function __construct() {
        $this->api_client = new AI_SMS_API_Client();
    }

    public function init() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * ลงทะเบียน REST API Endpoints
     */
    public function register_routes() {
        register_rest_route('ai-sms-firewall/v1', '/incoming-sms', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_incoming_sms'],
            'permission_callback' => '__return_true', // รองรับ webhook ภายนอก
        ]);

        register_rest_route('ai-sms-firewall/v1', '/scan', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_scan_request'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * จัดการ Inbound SMS จาก Gateway
     */
    public function handle_incoming_sms($request) {
        $enabled = get_option('ai_sms_enable_webhook', 1);
        if (!$enabled) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'Inbound SMS Webhook ถูกปิดใช้งานอยู่ในการตั้งค่า'
            ], 403);
        }

        $params = $request->get_params();

        // สกัดข้อความและเบอร์ผู้ส่งจากค่ายต่างๆ (ThaiBulkSMS, Twilio, Generic)
        $sender = 'Unknown';
        if (!empty($params['sender'])) {
            $sender = sanitize_text_field($params['sender']);
        } elseif (!empty($params['From'])) {
            $sender = sanitize_text_field($params['From']);
        } elseif (!empty($params['msisdn'])) {
            $sender = sanitize_text_field($params['msisdn']);
        } elseif (!empty($params['phone'])) {
            $sender = sanitize_text_field($params['phone']);
        }

        $message = '';
        if (!empty($params['message'])) {
            $message = sanitize_textarea_field($params['message']);
        } elseif (!empty($params['Body'])) {
            $message = sanitize_textarea_field($params['Body']);
        } elseif (!empty($params['text'])) {
            $message = sanitize_textarea_field($params['text']);
        } elseif (!empty($params['msg'])) {
            $message = sanitize_textarea_field($params['msg']);
        }

        if (empty($message)) {
            return new WP_REST_Response([
                'status'  => 'error',
                'message' => 'ไม่พบเนื้อหาข้อความ (message/Body/text)'
            ], 400);
        }

        // ส่งให้โมเดล AI ตรวจสอบ
        $analysis = $this->api_client->inspect_text($message);

        // ตัดสินใจ Action
        $default_action = get_option('ai_sms_action', 'block');
        $action_taken = 'ALLOWED';

        if ($analysis['is_scam']) {
            if ($default_action === 'block') {
                $action_taken = 'BLOCKED';
            } elseif ($default_action === 'quarantine') {
                $action_taken = 'QUARANTINED';
            } else {
                $action_taken = 'LOGGED_ONLY';
            }
        }

        // บันทึกลงฐานข้อมูล
        $this->log_to_db($sender, $message, $analysis, $action_taken, 'webhook_sms');

        return new WP_REST_Response([
            'status'          => 'success',
            'action_taken'    => $action_taken,
            'is_scam'         => $analysis['is_scam'],
            'risk_score'      => $analysis['risk_score'],
            'risk_level'      => $analysis['risk_level'],
            'sender'          => $sender,
            'cleaned_text'    => $analysis['cleaned_text'],
            'attention_words' => $analysis['attention_words'],
            'safety_card'     => $analysis['safety_card'],
            'latency_ms'      => $analysis['latency_ms']
        ], 200);
    }

    /**
     * สำหรับการสแกนทั่วไป
     */
    public function handle_scan_request($request) {
        $params = $request->get_params();
        $text = !empty($params['text']) ? sanitize_textarea_field($params['text']) : '';
        if (empty($text)) {
            return new WP_REST_Response(['error' => 'กรุณาระบุ text'], 400);
        }

        $analysis = $this->api_client->inspect_text($text);
        return new WP_REST_Response($analysis, 200);
    }

    /**
     * บันทึกประวัติการสแกนลงฐานข้อมูล
     */
    private function log_to_db($sender, $message, $analysis, $action_taken, $source_type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_sms_logs';

        $attention_str = '';
        if (!empty($analysis['attention_words'])) {
            $words = array_map(function($w) {
                return $w['word'] . '(' . round($w['weight'] * 100) . '%)';
            }, array_slice($analysis['attention_words'], 0, 5));
            $attention_str = implode(', ', $words);
        }

        $wpdb->insert($table_name, [
            'sender'          => substr($sender, 0, 100),
            'message'         => $message,
            'is_scam'         => $analysis['is_scam'] ? 1 : 0,
            'risk_score'      => $analysis['risk_score'],
            'risk_level'      => $analysis['risk_level'],
            'action_taken'    => $action_taken,
            'attention_words' => $attention_str,
            'source_type'     => $source_type,
            'created_at'      => current_time('mysql')
        ]);
    }
}
