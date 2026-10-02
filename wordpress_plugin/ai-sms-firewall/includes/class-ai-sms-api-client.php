<?php
/**
 * AI SMS Firewall API Client
 * สื่อสารกับ Django Deep Learning Backend (/api/inspect/)
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SMS_API_Client {

    private $api_url;
    private $threshold;

    public function __construct() {
        $this->api_url = get_option('ai_sms_api_url', 'http://127.0.0.1:8000/api/inspect/');
        $this->threshold = (float) get_option('ai_sms_risk_threshold', 75);
    }

    /**
     * ส่งข้อความไปวิเคราะห์ที่ Django AI Server
     *
     * @param string $text ข้อความ SMS หรือเนื้อหาที่ต้องการตรวจสอบ
     * @return array ผลการตรวจสอบ
     */
    public function inspect_text($text) {
        $text = trim($text);
        if (empty($text)) {
            return [
                'success' => false,
                'error' => 'ข้อความว่างเปล่า',
                'is_scam' => false,
                'risk_score' => 0.0,
                'risk_level' => 'SAFE'
            ];
        }

        $body = wp_json_encode(['text' => $text]);

        $response = wp_remote_post($this->api_url, [
            'method'      => 'POST',
            'timeout'     => 4, // โมเดลใช้เวลา ~15ms เผื่อ network 4s
            'redirection' => 2,
            'httpversion' => '1.1',
            'blocking'    => true,
            'headers'     => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ],
            'body'        => $body,
            'data_format' => 'body'
        ]);

        if (is_wp_error($response)) {
            // Fail-safe กรณีเชื่อมต่อ AI Backend ไม่ได้
            return [
                'success'        => false,
                'error'          => 'ไม่สามารถเชื่อมต่อ AI Server: ' . $response->get_error_message(),
                'is_scam'        => false,
                'risk_score'     => 0.0,
                'risk_level'     => 'UNKNOWN',
                'cleaned_text'   => $text,
                'attention_words'=> [],
                'action'         => 'ALLOWED'
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($status_code !== 200 || !is_array($data)) {
            return [
                'success'        => false,
                'error'          => 'AI Server ตอบกลับสถานะ ' . $status_code,
                'is_scam'        => false,
                'risk_score'     => 0.0,
                'risk_level'     => 'ERROR',
                'cleaned_text'   => $text,
                'attention_words'=> [],
                'action'         => 'ALLOWED'
            ];
        }

        $risk_score = isset($data['risk_score']) ? (float)$data['risk_score'] : 0.0;
        $is_scam = ($risk_score >= $this->threshold) || (!empty($data['is_scam']));

        return [
            'success'          => true,
            'is_scam'          => $is_scam,
            'risk_score'       => $risk_score,
            'risk_level'       => isset($data['risk_level']) ? $data['risk_level'] : ($is_scam ? 'HIGH' : 'SAFE'),
            'cleaned_text'     => isset($data['cleaned_text']) ? $data['cleaned_text'] : $text,
            'has_obfuscation'  => !empty($data['has_obfuscation']),
            'attention_words'  => isset($data['attention_words']) ? $data['attention_words'] : [],
            'safety_card'      => isset($data['safety_card']) ? $data['safety_card'] : null,
            'suspicious_urls'  => isset($data['suspicious_urls']) ? $data['suspicious_urls'] : [],
            'latency_ms'       => isset($data['latency_ms']) ? $data['latency_ms'] : 0
        ];
    }
}
