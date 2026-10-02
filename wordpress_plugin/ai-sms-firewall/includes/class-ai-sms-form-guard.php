<?php
/**
 * Comment & Web Form Phishing Guard
 * ดักจับและคัดกรองเนื้อหาสแปม/มิจฉาชีพผ่านคอมเมนต์และฟอร์มติดต่อ
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SMS_Form_Guard {

    private $api_client;

    public function __construct() {
        $this->api_client = new AI_SMS_API_Client();
    }

    public function init() {
        if (get_option('ai_sms_enable_comments', 1)) {
            add_filter('preprocess_comment', [$this, 'filter_comment']);
        }

        // รองรับ Contact Form 7
        add_action('wpcf7_before_send_mail', [$this, 'filter_contact_form_7']);
    }

    /**
     * ดักจับและคัดกรอง Comment ในบล็อก
     */
    public function filter_comment($commentdata) {
        // ข้ามการตรวจถ้าเป็น Admin กำลังตอบ
        if (current_user_can('manage_options')) {
            return $commentdata;
        }

        $content = isset($commentdata['comment_content']) ? $commentdata['comment_content'] : '';
        if (empty($content)) {
            return $commentdata;
        }

        $analysis = $this->api_client->inspect_text($content);
        $action_pref = get_option('ai_sms_action', 'block');

        if ($analysis['is_scam']) {
            $author = isset($commentdata['comment_author']) ? $commentdata['comment_author'] : 'Web Visitor';

            // บันทึกลงฐานข้อมูล
            $this->log_to_db($author, $content, $analysis, 'BLOCKED', 'comment');

            if ($action_pref === 'block') {
                wp_die(
                    '<div style="font-family:sans-serif; text-align:center; padding:40px;">' .
                    '<h2 style="color:#e11d48;">⚠️ ตรวจพบข้อความเข้าข่ายมิจฉาชีพหรือฟิชชิ่ง</h2>' .
                    '<p style="color:#475569; font-size:16px;">ข้อความของคุณถูกระงับโดยระบบ <strong>AI Phishing Firewall</strong> เนื่องจากตรวจพบคีย์เวิร์ดหรือลิงก์ที่มีความเสี่ยงสูง (' . esc_html($analysis['risk_score']) . '%)</p>' .
                    '<p style="color:#64748b; font-size:14px;">หากคิดว่าเป็นความผิดพลาด กรุณาติดต่อผู้ดูแลระบบ</p>' .
                    '<p><a href="javascript:history.back()" style="display:inline-block; margin-top:15px; padding:10px 20px; background:#2563eb; color:#fff; text-decoration:none; border-radius:6px;">ย้อนกลับ</a></p>' .
                    '</div>',
                    'ตรวจพบสแปม/ฟิชชิ่ง',
                    ['response' => 403, 'back_link' => true]
                );
            } else {
                // ส่งเข้า Spam Queue
                add_filter('pre_comment_approved', function() {
                    return 'spam';
                });
            }
        }

        return $commentdata;
    }

    /**
     * ดักจับ Contact Form 7
     */
    public function filter_contact_form_7($contact_form) {
        $submission = WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }

        $posted_data = $submission->get_posted_data();
        $message_parts = [];

        foreach ($posted_data as $key => $val) {
            if (is_string($val) && strlen($val) > 10) {
                $message_parts[] = $val;
            }
        }

        $combined = implode("\n", $message_parts);
        if (empty($combined)) {
            return;
        }

        $analysis = $this->api_client->inspect_text($combined);
        if ($analysis['is_scam']) {
            $sender = isset($posted_data['your-name']) ? $posted_data['your-name'] : 'Form User';
            $this->log_to_db($sender, $combined, $analysis, 'BLOCKED', 'contact_form_7');

            // บังคับระงับการส่งอีเมล
            $contact_form->skip_mail = true;
        }
    }

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
