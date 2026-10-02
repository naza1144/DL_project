<?php
/**
 * Frontend Public Shortcode [ai_sms_scanner]
 * เปิดให้ผู้เข้าชมเว็บไซต์สามารถพิมพ์/วางข้อความเพื่อสแกนสแกมได้เองบนหน้าเว็บ
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SMS_Public {

    private $api_client;

    public function __construct() {
        $this->api_client = new AI_SMS_API_Client();
    }

    public function init() {
        add_shortcode('ai_sms_scanner', [$this, 'render_scanner_shortcode']);
        add_action('wp_ajax_ai_sms_public_scan', [$this, 'ajax_public_scan']);
        add_action('wp_ajax_nopriv_ai_sms_public_scan', [$this, 'ajax_public_scan']);
    }

    public function ajax_public_scan() {
        check_ajax_referer('ai_sms_public_nonce', 'security');

        $text = isset($_POST['text']) ? sanitize_textarea_field($_POST['text']) : '';
        if (empty($text)) {
            wp_send_json_error(['message' => 'กรุณากรอกข้อความ']);
        }

        $res = $this->api_client->inspect_text($text);
        wp_send_json_success($res);
    }

    public function render_scanner_shortcode($atts) {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('ai_sms_public_nonce');

        ob_start();
        ?>
        <div class="ai-sms-public-box" style="max-width:680px; margin:24px auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:28px; box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.08); font-family:inherit;">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="display:inline-flex; align-items:center; justify-content:center; width:52px; height:52px; background:#eff6ff; color:#2563eb; border-radius:50%; margin-bottom:12px;">
                    <span style="font-size:26px;">🛡️</span>
                </div>
                <h3 style="margin:0 0 6px 0; font-size:22px; font-weight:700; color:#0f172a;">ตรวจเช็ค SMS และลิงก์มิจฉาชีพ</h3>
                <p style="margin:0; font-size:14px; color:#64748b;">วิเคราะห์ความเสี่ยง ตรวจจับการพรางคำ และตรวจสอบช่องทางติดต่อทางการด้วย AI</p>
            </div>

            <div style="margin-bottom:16px;">
                <textarea id="ai-sms-pub-input" rows="4" style="width:100%; box-sizing:border-box; border-radius:10px; padding:14px; font-size:15px; border:1px solid #cbd5e1; outline:none; transition:border-color 0.2s;" placeholder="วางข้อความ SMS หรือข้อความแชตที่น่าสงสัยที่นี่..."></textarea>
            </div>

            <button type="button" id="ai-sms-pub-btn" style="width:100%; background:#2563eb; color:#ffffff; font-size:16px; font-weight:600; padding:12px; border:none; border-radius:10px; cursor:pointer; transition:background 0.2s;">
                🔍 ตรวจสอบข้อความทันที
            </button>

            <!-- Result Area -->
            <div id="ai-sms-pub-result" style="display:none; margin-top:20px; border-radius:10px; padding:18px; border:1px solid #e2e8f0; background:#f8fafc;">
                <div id="ai-sms-pub-badge" style="display:inline-block; font-size:14px; font-weight:700; padding:4px 14px; border-radius:20px; margin-bottom:12px;"></div>
                
                <div style="margin-bottom:8px; font-size:15px; color:#1e293b;">
                    <strong>คะแนนความเสี่ยง:</strong> <span id="ai-sms-pub-score" style="font-weight:700;"></span>
                </div>

                <div id="ai-sms-pub-deobf" style="display:none; margin-bottom:10px; font-size:13px; color:#475569; background:#fff; padding:10px; border-radius:6px; border:1px solid #e2e8f0;">
                    <strong style="color:#d97706;">ข้อความที่ถอดรหัสคำพรางตัวแล้ว:</strong> <span id="ai-sms-pub-cleaned"></span>
                </div>

                <div id="ai-sms-pub-safety" style="display:none; margin-top:12px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; font-size:13px; color:#1e40af;">
                    <strong>📞 ช่องทางติดต่อทางการของหน่วยงาน:</strong>
                    <div id="ai-sms-pub-safety-text" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            const btn = document.getElementById('ai-sms-pub-btn');
            const input = document.getElementById('ai-sms-pub-input');
            const resultBox = document.getElementById('ai-sms-pub-result');
            const badge = document.getElementById('ai-sms-pub-badge');
            const score = document.getElementById('ai-sms-pub-score');
            const deobf = document.getElementById('ai-sms-pub-deobf');
            const cleaned = document.getElementById('ai-sms-pub-cleaned');
            const safety = document.getElementById('ai-sms-pub-safety');
            const safetyText = document.getElementById('ai-sms-pub-safety-text');

            btn.addEventListener('click', function() {
                const text = input.value.trim();
                if (!text) {
                    alert('กรุณากรอกข้อความก่อนกดตรวจสอบ');
                    return;
                }

                btn.disabled = true;
                btn.innerText = 'กำลังประมวลผล...';

                const formData = new FormData();
                formData.append('action', 'ai_sms_public_scan');
                formData.append('security', '<?php echo esc_js($nonce); ?>');
                formData.append('text', text);

                fetch('<?php echo esc_url($ajax_url); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = '🔍 ตรวจสอบข้อความทันที';
                    if (res.success) {
                        const d = res.data;
                        resultBox.style.display = 'block';
                        score.innerText = d.risk_score + '% (' + d.risk_level + ')';

                        if (d.is_scam) {
                            badge.style.background = '#fee2e2';
                            badge.style.color = '#b91c1c';
                            badge.innerText = '🚨 ข้อความนี้เข้าข่ายมิจฉาชีพ/สแกม!';
                        } else {
                            badge.style.background = '#dcfce7';
                            badge.style.color = '#15803d';
                            badge.innerText = '✅ ข้อความนี้ปลอดภัย (ความเสี่ยงต่ำ)';
                        }

                        if (d.has_obfuscation) {
                            deobf.style.display = 'block';
                            cleaned.innerText = d.cleaned_text;
                        } else {
                            deobf.style.display = 'none';
                        }

                        if (d.safety_card) {
                            safety.style.display = 'block';
                            safetyText.innerHTML = '<strong>หน่วยงาน:</strong> ' + d.safety_card.institution + '<br>' +
                                '<strong>สายด่วนทางการ:</strong> ' + d.safety_card.official_phone + '<br>' +
                                '<span style="color:#dc2626;">' + d.safety_card.counter_action + '</span>';
                        } else {
                            safety.style.display = 'none';
                        }
                    } else {
                        alert(res.data ? res.data.message : 'เกิดข้อผิดพลาด');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = '🔍 ตรวจสอบข้อความทันที';
                    alert('ไม่สามารถเชื่อมต่อกับระบบได้');
                });
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}
