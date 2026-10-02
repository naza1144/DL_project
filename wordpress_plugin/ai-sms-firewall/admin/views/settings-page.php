<?php
/**
 * WordPress Admin Settings & Threat Monitor View
 */

if (!defined('ABSPATH')) {
    exit;
}

$block_rate = $total_scanned > 0 ? round(($total_blocked / $total_scanned) * 100, 1) : 0;
?>

<div class="wrap ai-sms-wrap" style="max-width: 1200px; margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    
    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; background:#0f172a; color:#fff; padding:24px 30px; border-radius:12px; margin-bottom:24px; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
        <div>
            <h1 style="color:#f8fafc; font-size:24px; font-weight:700; margin:0 0 6px 0; display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-shield" style="font-size:28px; width:28px; height:28px; color:#38bdf8;"></span>
                DL SMS & Phishing Firewall
            </h1>
            <p style="margin:0; color:#94a3b8; font-size:14px;">
                ระบบไฟร์วอลล์ตรวจจับ SMS มิจฉาชีพ/สแปม/ฟิชชิ่งแบบ Real-Time ด้วย Deep Learning (BiLSTM + Self-Attention) | โดย naza1144 (DL Project)
            </p>
        </div>
        <div>
            <button id="ai-sms-btn-ping" class="button" style="background:#1e293b; color:#38bdf8; border:1px solid #334155; padding:6px 16px; border-radius:8px; font-weight:600; cursor:pointer;">
                <span class="dashicons dashicons-update" style="margin-top:2px;"></span> ทดสอบเชื่อมต่อ DL Server
            </button>
            <span id="ai-sms-ping-status" style="margin-left:8px; font-size:13px;"></span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin-bottom:24px;">
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">ข้อความที่สแกนทั้งหมด</div>
            <div style="font-size:30px; font-weight:800; color:#0f172a; margin-top:6px;"><?php echo number_format($total_scanned); ?></div>
        </div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">สแกม/สแปม ที่บล็อกได้</div>
            <div style="font-size:30px; font-weight:800; color:#e11d48; margin-top:6px;"><?php echo number_format($total_blocked); ?></div>
        </div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">อัตราการดักจับ (Block Rate)</div>
            <div style="font-size:30px; font-weight:800; color:#2563eb; margin-top:6px;"><?php echo $block_rate; ?>%</div>
        </div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:13px; font-weight:600; color:#64748b; text-transform:uppercase;">สถานะ DL Firewall</div>
            <div style="font-size:18px; font-weight:700; color:#16a34a; margin-top:12px; display:flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#16a34a;"></span> เปิดคุ้มครองปกติ
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div style="border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; display:flex; gap:10px;">
        <button type="button" class="nav-tab nav-tab-active" data-tab="tab-logs" style="font-size:14px; font-weight:600; padding:10px 18px; cursor:pointer;">
            📋 ประวัติการดักจับ SMS (Threat Log)
        </button>
        <button type="button" class="nav-tab" data-tab="tab-sandbox" style="font-size:14px; font-weight:600; padding:10px 18px; cursor:pointer;">
            🧪 ทดสอบสแกนสด (Live SMS Inspector)
        </button>
        <button type="button" class="nav-tab" data-tab="tab-settings" style="font-size:14px; font-weight:600; padding:10px 18px; cursor:pointer;">
            ⚙️ การตั้งค่าระบบ (Settings)
        </button>
        <button type="button" class="nav-tab" data-tab="tab-guide" style="font-size:14px; font-weight:600; padding:10px 18px; cursor:pointer;">
            🔌 การเชื่อมต่อ Webhook (SMS Gateway)
        </button>
    </div>

    <!-- Tab 1: Threat Audit Log -->
    <div id="tab-logs" class="tab-content" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h2 style="font-size:18px; font-weight:700; color:#0f172a; margin:0;">รายการ SMS / ข้อความที่ระบบดักจับได้ล่าสุด</h2>
            <?php if (!empty($recent_logs)): ?>
                <button id="ai-sms-btn-clear-logs" class="button" style="color:#ef4444; border-color:#fca5a5;">ล้างประวัติข้อความ</button>
            <?php endif; ?>
        </div>

        <?php if (empty($recent_logs)): ?>
            <div style="text-align:center; padding:50px 20px; color:#64748b;">
                <span class="dashicons dashicons-yes-alt" style="font-size:48px; width:48px; height:48px; color:#10b981; margin-bottom:10px;"></span>
                <p style="font-size:16px; font-weight:600; margin:0;">ยังไม่มีประวัติข้อความที่ต้องสงสัย</p>
                <p style="font-size:13px; color:#94a3b8;">เมื่อมี SMS วิ่งเข้ามาผ่าน Webhook หรือมีผู้ใช้ส่งฟอร์ม ข้อความจะถูกนำมาแสดงที่นี่</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped" style="border:none;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="width:130px; font-weight:700;">เวลา</th>
                        <th style="width:140px; font-weight:700;">ผู้ส่ง / แหล่งที่มา</th>
                        <th style="font-weight:700;">ข้อความ SMS</th>
                        <th style="width:110px; font-weight:700;">ระดับความเสี่ยง</th>
                        <th style="width:100px; font-weight:700;">คำสั่ง (Action)</th>
                        <th style="width:200px; font-weight:700;">คำพิรุธ (Attention)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td style="color:#64748b; font-size:12px;"><?php echo esc_html($log->created_at); ?></td>
                            <td>
                                <strong><?php echo esc_html($log->sender); ?></strong><br>
                                <span style="font-size:11px; color:#94a3b8;"><?php echo esc_html($log->source_type); ?></span>
                            </td>
                            <td style="font-size:13px; line-height:1.5;">
                                <?php echo esc_html($log->message); ?>
                            </td>
                            <td>
                                <?php if ($log->risk_score >= 70): ?>
                                    <span style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:12px; font-weight:700; font-size:12px;">
                                        🔴 <?php echo esc_html($log->risk_score); ?>%
                                    </span>
                                <?php else: ?>
                                    <span style="background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:12px; font-weight:700; font-size:12px;">
                                        🟢 <?php echo esc_html($log->risk_score); ?>%
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log->action_taken === 'BLOCKED'): ?>
                                    <span style="color:#e11d48; font-weight:700;">ระงับ (Block)</span>
                                <?php else: ?>
                                    <span style="color:#16a34a; font-weight:600;">ผ่าน (Pass)</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:#475569;">
                                <?php echo esc_html($log->attention_words ?: '-'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Tab 2: Live Sandbox -->
    <div id="tab-sandbox" class="tab-content" style="display:none; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <h2 style="font-size:18px; font-weight:700; color:#0f172a; margin-top:0;">ทดสอบวิเคราะห์ข้อความ SMS (Interactive Sandbox)</h2>
        <p style="color:#64748b; font-size:14px;">ลองพิมพ์หรือวางข้อความ SMS ที่น่าสงสัย เพื่อให้โมเดล BiLSTM + Self-Attention ตรวจสอบและถอดรหัสคำพรางตัวได้ทันที</p>

        <!-- Quick Presets -->
        <div style="margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap;">
            <button type="button" class="button ai-sms-preset" data-text="ยินดีด้วย! คุณได้รับสิทธิ์ กู้งิuด่วu 50,000 บ. ดอกเบี้ย 0% คลิก lin.ee/loan99">
                🚨 ตัวอย่าง: เงินกู้ด่วน + พรางคำ
            </button>
            <button type="button" class="button ai-sms-preset" data-text="ด ่ ว น ร ั บ เ ง ิ น คืนค่าประกันมิเตอร์ไฟฟ้า กฟภ. 3,500 บ. กด lin.ee/m-pea">
                ⚡ ตัวอย่าง: เคาะวรรค + อ้าง กฟภ.
            </button>
            <button type="button" class="button ai-sms-preset" data-text="วันนี้เลิกงานดึกหน่อยนะ ไม่ต้องรอทานข้าวเย็น เดี๋ยวแวะซื้อของเข้าบ้านก่อน">
                💬 ตัวอย่าง: ข้อความปกติในชีวิตประจำวัน
            </button>
        </div>

        <div style="margin-bottom:16px;">
            <textarea id="ai-sms-test-input" rows="4" style="width:100%; border-radius:8px; padding:12px; font-size:14px; border:1px solid #cbd5e1;" placeholder="วางข้อความ SMS ที่ต้องการทดสอบที่นี่..."></textarea>
        </div>

        <div>
            <button id="ai-sms-btn-run-scan" class="button button-primary" style="padding:6px 22px; font-weight:600; font-size:14px; height:auto;">
                🔍 กดสแกนข้อความ
            </button>
        </div>

        <!-- Result Box -->
        <div id="ai-sms-sandbox-result" style="display:none; margin-top:20px; border-radius:8px; padding:20px; border:1px solid #e2e8f0; background:#f8fafc;">
            <div id="ai-sms-res-badge" style="display:inline-block; font-size:14px; font-weight:800; padding:4px 12px; border-radius:20px; margin-bottom:12px;"></div>
            
            <div style="margin-bottom:10px;">
                <strong>คะแนนความเสี่ยง (Risk Score):</strong> <span id="ai-sms-res-score" style="font-weight:700; font-size:16px;"></span>
                <span style="color:#64748b; font-size:12px; margin-left:10px;">(ประมวลผลใน <span id="ai-sms-res-latency"></span> ms)</span>
            </div>

            <div id="ai-sms-res-deobf-box" style="margin-bottom:10px; display:none; background:#fff; padding:10px; border-radius:6px; border:1px solid #e2e8f0;">
                <strong style="color:#d97706;">[!] ถอดรหัสคำอำพรางตัว (De-obfuscated):</strong>
                <p id="ai-sms-res-cleaned" style="margin:4px 0 0 0; color:#334155; font-size:13px;"></p>
            </div>

            <div style="margin-bottom:10px;">
                <strong>คำที่ AI ให้ความสำคัญสูงสุด (Explainable AI Attention):</strong>
                <div id="ai-sms-res-words" style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap;"></div>
            </div>

            <div id="ai-sms-res-safety-card" style="margin-top:14px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; display:none;">
                <strong style="color:#1d4ed8;">🛡️ คำแนะนำและเบอร์ติดต่อทางการ:</strong>
                <div id="ai-sms-res-safety-content" style="margin-top:4px; font-size:13px; color:#1e40af;"></div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Settings -->
    <div id="tab-settings" class="tab-content" style="display:none; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <h2 style="font-size:18px; font-weight:700; color:#0f172a; margin-top:0;">การตั้งค่าการเชื่อมต่อ AI Server</h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('ai_sms_settings_group'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ai_sms_api_url">AI Deep Learning Endpoint URL</label></th>
                    <td>
                        <input name="ai_sms_api_url" type="url" id="ai_sms_api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" style="width:450px;">
                        <p class="description">URL ของ Django API ที่รันอยู่ เช่น <code>http://127.0.0.1:8000/api/inspect/</code> หรือ IP ของเซิร์ฟเวอร์</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_sms_risk_threshold">เกณฑ์คะแนนความเสี่ยง (Risk Threshold %)</label></th>
                    <td>
                        <input name="ai_sms_risk_threshold" type="number" id="ai_sms_risk_threshold" value="<?php echo esc_attr($threshold); ?>" min="1" max="99" style="width:90px;"> %
                        <p class="description">หากข้อความมีคะแนนความเสี่ยงตั้งแต่เกณฑ์นี้ขึ้นไป จะถือว่าเป็นมิจฉาชีพ (แนะนำ 75% - 80%)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_sms_action">คำสั่งเมื่อตรวจพบข้อความมิจฉาชีพ (Action)</label></th>
                    <td>
                        <select name="ai_sms_action" id="ai_sms_action">
                            <option value="block" <?php selected($action_pref, 'block'); ?>>ระงับทันที (Block & Reject)</option>
                            <option value="quarantine" <?php selected($action_pref, 'quarantine'); ?>>กักกัน/ส่งลงถังขยะ (Quarantine to Spam)</option>
                            <option value="log_only" <?php selected($action_pref, 'log_only'); ?>>บันทึกประวัติอย่างเดียว (Log Only)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">การดักจับข้อความบนเว็บไซต์</th>
                    <td>
                        <label>
                            <input name="ai_sms_enable_comments" type="checkbox" value="1" <?php checked($enable_comments, 1); ?>>
                            กรองคอมเมนต์สแปม/เงินกู้/เว็บพนันในบล็อกอัตโนมัติ (Comment Guard)
                        </label><br><br>
                        <label>
                            <input name="ai_sms_enable_webhook" type="checkbox" value="1" <?php checked($enable_webhook, 1); ?>>
                            เปิดรับ Inbound SMS Webhook จากค่ายมือถือ / SMS Gateway
                        </label>
                    </td>
                </tr>
            </table>

            <?php submit_button('บันทึกการตั้งค่า'); ?>
        </form>
    </div>

    <!-- Tab 4: Webhook Guide -->
    <div id="tab-guide" class="tab-content" style="display:none; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <h2 style="font-size:18px; font-weight:700; color:#0f172a; margin-top:0;">วิธีการเชื่อมต่อ Webhook กับผู้ให้บริการ SMS</h2>
        <p style="color:#64748b; font-size:14px;">เมื่อมีลูกค้าหรือผู้ใช้ส่ง SMS เข้ามาที่เบอร์ขององค์กร คุณสามารถนำ URL นี้ไปกรอกในหน้า Dashboard ของค่าย SMS เพื่อให้ระบบตรวจสอบอัตโนมัติได้ทันที:</p>

        <div style="background:#f1f5f9; padding:14px 18px; border-radius:8px; margin-bottom:20px; border:1px solid #e2e8f0;">
            <div style="font-size:12px; color:#64748b; font-weight:600; margin-bottom:4px;">WEBHOOK ENDPOINT URL สำหรับเว็บไซต์ของคุณ:</div>
            <code style="font-size:14px; font-weight:700; color:#0f172a; background:none; padding:0;"><?php echo esc_html($webhook_url); ?></code>
        </div>

        <h3 style="font-size:15px; font-weight:700; color:#1e293b;">ตัวอย่างการตั้งค่าแต่ละค่าย:</h3>
        <ol style="color:#475569; font-size:14px; line-height:1.7;">
            <li><strong>ThaiBulkSMS:</strong> ไปที่เมนู <em>Inbound SMS -> Webhook Settings</em> นำ URL ด้านบนไปใส่ในช่อง Webhook URL</li>
            <li><strong>Twilio:</strong> ไปที่ <em>Phone Numbers -> Active Numbers -> A Message Comes In</em> เลือก <code>Webhook (HTTP POST)</code> แล้ววาง URL ด้านบน</li>
            <li><strong>Shortcode สำหรับนำกล่องสแกนไปติดบนหน้าเว็บ:</strong> สามารถนำรหัส <code>[ai_sms_scanner]</code> ไปวางใน Page ใดๆ เพื่อเปิดให้ผู้เยี่ยมชมเว็บสแกนข้อความได้ด้วยตนเอง</li>
        </ol>
    </div>

</div>

<!-- JavaScript สำหรับจัดการ Tabs และ AJAX -->
<script>
jQuery(document).ready(function($) {
    const nonce = '<?php echo wp_create_nonce("ai_sms_admin_nonce"); ?>';

    // การเปลี่ยนแท็บ
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        const target = $(this).data('tab');
        $('.tab-content').hide();
        $('#' + target).show();
    });

    // Preset Buttons
    $('.ai-sms-preset').on('click', function() {
        $('#ai-sms-test-input').val($(this).data('text'));
    });

    // ปุ่มทดสอบ Ping
    $('#ai-sms-btn-ping').on('click', function() {
        const $btn = $(this);
        const $status = $('#ai-sms-ping-status');
        $status.html('<span style="color:#e2e8f0;">กำลังเชื่อมต่อ...</span>');

        $.post(ajaxurl, {
            action: 'ai_sms_test_ping',
            security: nonce
        }, function(res) {
            if (res.success) {
                $status.html('<span style="color:#4ade80; font-weight:bold;">🟢 ออนไลน์ (' + res.data.latency_ms + 'ms)</span>');
            } else {
                $status.html('<span style="color:#f87171; font-weight:bold;">🔴 ออฟไลน์: ' + res.data.message + '</span>');
            }
        }).fail(function() {
            $status.html('<span style="color:#f87171; font-weight:bold;">🔴 เชื่อมต่อไม่สำเร็จ</span>');
        });
    });

    // สแกนใน Sandbox
    $('#ai-sms-btn-run-scan').on('click', function() {
        const text = $('#ai-sms-test-input').val().trim();
        if (!text) {
            alert('กรุณากรอกข้อความก่อนกดสแกน');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('กำลังวิเคราะห์...');

        $.post(ajaxurl, {
            action: 'ai_sms_test_scan',
            security: nonce,
            text: text
        }, function(res) {
            $btn.prop('disabled', false).text('🔍 กดสแกนข้อความ');
            if (res.success) {
                const d = res.data;
                $('#ai-sms-sandbox-result').show();
                $('#ai-sms-res-score').text(d.risk_score + '% (' + d.risk_level + ')');
                $('#ai-sms-res-latency').text(d.latency_ms);

                if (d.is_scam) {
                    $('#ai-sms-res-badge')
                        .css({'background': '#fee2e2', 'color': '#b91c1c'})
                        .text('🚨 ตรวจพบมิจฉาชีพ/สแปม (Scam Detected)');
                } else {
                    $('#ai-sms-res-badge')
                        .css({'background': '#dcfce7', 'color': '#15803d'})
                        .text('✅ ข้อความปลอดภัย (Safe Message)');
                }

                if (d.has_obfuscation) {
                    $('#ai-sms-res-deobf-box').show();
                    $('#ai-sms-res-cleaned').text(d.cleaned_text);
                } else {
                    $('#ai-sms-res-deobf-box').hide();
                }

                // Render Attention words
                const $words = $('#ai-sms-res-words');
                $words.empty();
                if (d.attention_words && d.attention_words.length > 0) {
                    d.attention_words.forEach(function(item) {
                        const pct = Math.round(item.weight * 100);
                        const chip = $('<span></span>')
                            .text(item.word + ' (' + pct + '%)')
                            .css({
                                'background': pct > 60 ? '#fee2e2' : '#f1f5f9',
                                'color': pct > 60 ? '#b91c1c' : '#334155',
                                'padding': '3px 8px',
                                'border-radius': '6px',
                                'font-size': '12px',
                                'font-weight': pct > 60 ? '700' : '500'
                            });
                        $words.append(chip);
                    });
                } else {
                    $words.text('-');
                }

                // Safety Card
                if (d.safety_card) {
                    $('#ai-sms-res-safety-card').show();
                    $('#ai-sms-res-safety-content').html(
                        '<strong>หน่วยงาน:</strong> ' + d.safety_card.institution + '<br>' +
                        '<strong>เบอร์ติดต่อทางการ:</strong> <a href="tel:' + d.safety_card.official_phone + '" style="color:#2563eb; font-weight:bold;">' + d.safety_card.official_phone + '</a> | ' +
                        '<strong>เว็บไซต์:</strong> <a href="' + d.safety_card.official_domain + '" target="_blank">' + d.safety_card.official_domain + '</a><br>' +
                        '<span style="color:#dc2626;">' + d.safety_card.counter_action + '</span>'
                    );
                } else {
                    $('#ai-sms-res-safety-card').hide();
                }

            } else {
                alert('เกิดข้อผิดพลาด: ' + (res.data ? res.data.message : 'Unknown'));
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('🔍 กดสแกนข้อความ');
            alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
        });
    });

    // ล้างประวัติ Logs
    $('#ai-sms-btn-clear-logs').on('click', function() {
        if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการล้างประวัติการดักจับข้อความทั้งหมด?')) {
            return;
        }
        $.post(ajaxurl, {
            action: 'ai_sms_clear_logs',
            security: nonce
        }, function(res) {
            location.reload();
        });
    });
});
</script>
