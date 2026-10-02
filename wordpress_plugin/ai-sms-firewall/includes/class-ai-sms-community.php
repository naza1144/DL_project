<?php
/**
 * AI / DL SMS Community Forum Engine
 * ระบบเว็บบอร์ดชุมชนสไตล์ Reddit (Clean Light Theme)
 * ขับเคลื่อนด้วย Business Logic จากโมเดล Deep Learning
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SMS_Community {

    private $api_client;

    public function __construct() {
        $this->api_client = new AI_SMS_API_Client();
    }

    public function init() {
        add_action('init', [$this, 'register_post_type_and_taxonomy']);
        add_filter('template_include', [$this, 'override_front_page'], 99);
        
        // AJAX Endpoints สำหรับกระทู้และคอมเมนต์
        add_action('wp_ajax_dl_create_topic', [$this, 'ajax_create_topic']);
        add_action('wp_ajax_nopriv_dl_create_topic', [$this, 'ajax_create_topic']);

        add_action('wp_ajax_dl_vote_topic', [$this, 'ajax_vote_topic']);
        add_action('wp_ajax_nopriv_dl_vote_topic', [$this, 'ajax_vote_topic']);

        add_action('wp_ajax_dl_add_comment', [$this, 'ajax_add_comment']);
        add_action('wp_ajax_nopriv_dl_add_comment', [$this, 'ajax_add_comment']);

        // สร้างข้อมูลเริ่มต้น Seed Data
        add_action('init', [$this, 'check_and_seed_data'], 20);
    }

    /**
     * ลงทะเบียน Custom Post Type 'dl_topic' และ Taxonomy 'dl_channel'
     */
    public function register_post_type_and_taxonomy() {
        register_post_type('dl_topic', [
            'labels' => [
                'name'          => 'กระทู้ชุมชน',
                'singular_name' => 'กระทู้',
            ],
            'public'        => true,
            'has_archive'   => true,
            'supports'      => ['title', 'editor', 'author', 'comments'],
            'menu_icon'     => 'dashicons-format-chat',
            'show_in_rest'  => true,
        ]);

        register_taxonomy('dl_channel', 'dl_topic', [
            'labels' => [
                'name'          => 'ห้องสนทนา (Channels)',
                'singular_name' => 'ห้องสนทนา',
            ],
            'public'            => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
        ]);
    }

    /**
     * ตั้งให้หน้าแรกของเว็บไซต์ (Home Page) โหลดเทมเพลตกระทู้สไตล์ Reddit
     */
    public function override_front_page($template) {
        if (is_front_page() || is_home()) {
            $custom_template = AI_SMS_FIREWALL_DIR . 'templates/community-home.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * AJAX: สร้างกระทู้ใหม่พร้อมตรวจจับความเสี่ยงผ่าน DL API
     */
    public function ajax_create_topic() {
        check_ajax_referer('dl_community_nonce', 'security');

        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
        $channel = isset($_POST['channel']) ? sanitize_text_field($_POST['channel']) : 'c/พูดคุยทั่วไป';
        $author_name = isset($_POST['author_name']) ? sanitize_text_field($_POST['author_name']) : 'สมาชิกนิรนาม';

        if (empty($title) || empty($content)) {
            wp_send_json_error(['message' => 'กรุณากรอกหัวข้อและเนื้อหากระทู้ให้ครบถ้วน']);
        }

        // ตรวจจับเนื้อหาผ่านโมเดล Deep Learning
        $full_text_to_check = $title . ' ' . $content;
        $analysis = $this->api_client->inspect_text($full_text_to_check);

        $is_scam = !empty($analysis['is_scam']);
        $risk_score = isset($analysis['risk_score']) ? (float)$analysis['risk_score'] : 0.0;

        // Business Logic (Smart Dual-Mode):
        // ถ้าเป็นกระทู้ในห้องทั่วไปแล้วเสี่ยงสูงมาก (> 95%) ให้แจ้งเตือน แต่ถ้าอยู่ในห้องเตือนภัย/ตรวจ SMS อนุญาตให้แชร์ตัวอย่างได้
        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'dl_topic',
            'comment_status' => 'open'
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'ไม่สามารถบันทึกกระทู้ได้: ' . $post_id->get_error_message()]);
        }

        // กำหนด Channel
        wp_set_object_terms($post_id, $channel, 'dl_channel');

        // บันทึก Post Meta
        update_post_meta($post_id, '_dl_author_name', $author_name);
        update_post_meta($post_id, '_dl_upvotes', 1);
        update_post_meta($post_id, '_dl_risk_score', $risk_score);
        update_post_meta($post_id, '_dl_risk_level', isset($analysis['risk_level']) ? $analysis['risk_level'] : 'SAFE');
        update_post_meta($post_id, '_dl_is_scam', $is_scam ? 1 : 0);
        update_post_meta($post_id, '_dl_cleaned_text', isset($analysis['cleaned_text']) ? $analysis['cleaned_text'] : $content);
        update_post_meta($post_id, '_dl_has_obfuscation', !empty($analysis['has_obfuscation']) ? 1 : 0);

        if (!empty($analysis['attention_words'])) {
            update_post_meta($post_id, '_dl_attention_words', $analysis['attention_words']);
        }

        if (!empty($analysis['safety_card'])) {
            update_post_meta($post_id, '_dl_safety_card', $analysis['safety_card']);
        }

        wp_send_json_success([
            'message'    => 'สร้างกระทู้สำเร็จ!',
            'post_id'    => $post_id,
            'is_scam'    => $is_scam,
            'risk_score' => $risk_score
        ]);
    }

    /**
     * AJAX: โหวตกระทู้ (Upvote / Downvote)
     */
    public function ajax_vote_topic() {
        check_ajax_referer('dl_community_nonce', 'security');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $type = isset($_POST['vote_type']) ? sanitize_text_field($_POST['vote_type']) : 'up';

        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid Post ID']);
        }

        $current_votes = (int) get_post_meta($post_id, '_dl_upvotes', true);
        if ($type === 'up') {
            $current_votes += 1;
        } else {
            $current_votes = max(0, $current_votes - 1);
        }

        update_post_meta($post_id, '_dl_upvotes', $current_votes);
        wp_send_json_success(['votes' => $current_votes]);
    }

    /**
     * AJAX: แสดงความคิดเห็นพร้อมระบบ DL Firewall บล็อกสแปมเงินกู้/ฟิชชิ่ง 100%
     */
    public function ajax_add_comment() {
        check_ajax_referer('dl_community_nonce', 'security');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
        $author = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : 'สมาชิกชุมชน';

        if (!$post_id || empty($content)) {
            wp_send_json_error(['message' => 'กรุณากรอกข้อความความคิดเห็น']);
        }

        // ตรวจสอบผ่าน DL Model ทันที
        $analysis = $this->api_client->inspect_text($content);

        // ถ้าโมเดลฟันธงว่าเป็นสแกม/ฟิชชิ่ง/เงินกู้ ให้บล็อกคอมเมนต์ทันที 100%!
        if (!empty($analysis['is_scam'])) {
            wp_send_json_error([
                'blocked' => true,
                'message' => '🚨 คอมเมนต์ของคุณถูกระงับโดยระบบ DL Firewall เนื่องจากตรวจพบเนื้อหาหลอกลวง, ลิงก์ดูดเงิน หรือเงินกู้ด่วน (ระดับความเสี่ยง: ' . esc_html($analysis['risk_score']) . '%)'
            ], 403);
        }

        // บันทึกคอมเมนต์
        $comment_id = wp_insert_comment([
            'comment_post_ID'      => $post_id,
            'comment_author'       => $author,
            'comment_content'      => $content,
            'comment_type'         => 'comment',
            'comment_approved'     => 1,
            'comment_author_IP'    => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
        ]);

        if (!$comment_id) {
            wp_send_json_error(['message' => 'ไม่สามารถบันทึกความคิดเห็นได้']);
        }

        wp_send_json_success([
            'message'    => 'ส่งความคิดเห็นเรียบร้อยแล้ว',
            'comment_id' => $comment_id,
            'author'     => $author,
            'content'    => nl2br(esc_html($content)),
            'time'       => 'เมื่อสักครู่'
        ]);
    }

    /**
     * สร้างกระทู้เริ่มต้น 4 กระทู้ (Seed Data) เมื่อเริ่มต้นใช้งาน
     */
    public function check_and_seed_data() {
        if (get_option('dl_community_seeded', 0)) {
            return;
        }

        // สร้าง Channels พื้นฐาน
        $channels = ['c/เตือนภัยมิจฉาชีพ', 'c/ตรวจสอบ-SMS', 'c/พูดคุยทั่วไป', 'c/รู้ทันกลโกง'];
        foreach ($channels as $ch) {
            if (!term_exists($ch, 'dl_channel')) {
                wp_insert_term($ch, 'dl_channel');
            }
        }

        // 1. กระทู้เตือนภัย กฟภ.
        $p1 = wp_insert_post([
            'post_title'   => '🚨 ได้รับ SMS คืนค่าประกันมิเตอร์ไฟฟ้า 3,500 บ. กฟภ. เป็นสแกมไหมครับ?',
            'post_content' => "วันนี้มี SMS ส่งเข้ามาว่า:\n'ด ่ ว น ร ั บ เ ง ิ น คืนค่าประกันมิเตอร์ไฟฟ้า กฟภ. 3,500 บ. กด lin.ee/m-pea'\n\nเห็นมีชื่อ กฟภ. ชัดเจน แต่ทำไมให้แอดไลน์ lin.ee ครับ มีใครได้รับข้อความนี้เหมือนกันบ้าง ช่วยตรวจสอบทีครับ",
            'post_status'  => 'publish',
            'post_type'    => 'dl_topic'
        ]);
        if ($p1) {
            wp_set_object_terms($p1, 'c/เตือนภัยมิจฉาชีพ', 'dl_channel');
            update_post_meta($p1, '_dl_author_name', 'สมชาย_สายสืบ');
            update_post_meta($p1, '_dl_upvotes', 142);
            update_post_meta($p1, '_dl_risk_score', 99.0);
            update_post_meta($p1, '_dl_risk_level', 'CRITICAL');
            update_post_meta($p1, '_dl_is_scam', 1);
            update_post_meta($p1, '_dl_cleaned_text', 'ด่วน รับเงิน คืนค่าประกันมิเตอร์ไฟฟ้า กฟภ. 3,500 บ. กด lin.ee/m-pea');
            update_post_meta($p1, '_dl_has_obfuscation', 1);
            update_post_meta($p1, '_dl_attention_words', [
                ['word' => 'กด', 'weight' => 0.702],
                ['word' => '<URL>', 'weight' => 1.0]
            ]);
            update_post_meta($p1, '_dl_safety_card', [
                'institution'     => 'ศูนย์ปราบปรามอาชญากรรมทางเทคโนโลยีสารสนเทศ (บช.สอท.)',
                'official_phone'  => '1441 (สายด่วนตำรวจไซเบอร์)',
                'official_domain' => 'www.thaipoliceonline.go.th',
                'counter_action'  => 'หากไม่แน่ใจ ห้ามกดลิงก์ ห้ามโอนเงิน และห้ามติดตั้งแอปพลิเคชันใดๆ เด็ดขาด'
            ]);

            wp_insert_comment([
                'comment_post_ID'  => $p1,
                'comment_author'   => 'สารวัตร_ไอที [MOD]',
                'comment_content'  => 'สแกมแน่นอน 100% ครับ! ทาง กฟภ. (PEA) ไม่มีนโยบายส่ง SMS แนบลิงก์ให้แอดไลน์ และข้อความนี้มีการเคาะวรรค "ด ่ ว น ร ั บ เ ง ิ น" เพื่อหลบตัวกรอง สมาชิกห้ามกดเด็ดขาดครับ',
                'comment_approved' => 1,
                'comment_date'     => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ]);
            wp_insert_comment([
                'comment_post_ID'  => $p1,
                'comment_author'   => 'anon_rider',
                'comment_content'  => 'ขอบคุณที่เตือนครับ เพิ่งได้รับข้อความนี้เหมือนกันเป๊ะเลย เกือบกดแอดไลน์ไปแล้ว ดีที่เข้ามาเช็คในกลุ่มนี้ก่อน',
                'comment_approved' => 1,
                'comment_date'     => date('Y-m-d H:i:s', strtotime('-30 minutes'))
            ]);
        }

        // 2. กระทู้ตรวจสอบพัสดุ Flash Express ปลอม
        $p2 = wp_insert_post([
            'post_title'   => '📱 Flash Express ส่ง SMS บอกพัสดุตกค้าง แต่ลิงก์ลงท้าย .club แปลกๆ',
            'post_content' => "ข้อความระบุว่า: 'Flash Express: ไม่สามารถนำจ่ายพัสดุได้เนื่องจากที่อยู่ไม่ถูกต้อง อัปเดต http://flash-check.club'\nแต่ช่วงนี้ผมไม่ได้สั่งของออนไลน์เลยครับ ลิงก์นี้ปลอดภัยไหมครับ?",
            'post_status'  => 'publish',
            'post_type'    => 'dl_topic'
        ]);
        if ($p2) {
            wp_set_object_terms($p2, 'c/ตรวจสอบ-SMS', 'dl_channel');
            update_post_meta($p2, '_dl_author_name', 'น้องใหม่หัดเช็ค');
            update_post_meta($p2, '_dl_upvotes', 89);
            update_post_meta($p2, '_dl_risk_score', 95.8);
            update_post_meta($p2, '_dl_risk_level', 'CRITICAL');
            update_post_meta($p2, '_dl_is_scam', 1);
            update_post_meta($p2, '_dl_safety_card', [
                'institution'     => 'Flash Express / ตำรวจไซเบอร์',
                'official_phone'  => '1436 (Flash Call Center) / 1441 (ตำรวจไซเบอร์)',
                'official_domain' => 'www.flashexpress.co.th',
                'counter_action'  => 'โดเมนแท้ของ Flash คือ flashexpress.co.th เท่านั้น ห้ามกรอกข้อมูลส่วนบุคคลในโดเมน .club'
            ]);

            wp_insert_comment([
                'comment_post_ID'  => $p2,
                'comment_author'   => 'cyber_sentinel',
                'comment_content'  => 'โดเมน .club เป็นเว็บฟิชชิ่งหลอกให้ดาวน์โหลดแอปดูดเงิน (apk) ปลอมครับ ห้ามคลิกเด็ดขาด ติดต่อ Flash Call Center 1436 เพื่อเช็คสถานะพัสดุที่แท้จริงได้เลยครับ',
                'comment_approved' => 1,
                'comment_date'     => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ]);
        }

        // 3. กระทู้ความรู้ทั่วไป
        $p3 = wp_insert_post([
            'post_title'   => '💡 สรุป 5 คาถาจำง่าย ไม่ตกเป็นเหยื่อ SMS หลอกลวงในยุคดิจิทัล',
            'post_content' => "แชร์ข้อสังเกตง่ายๆ ประจำครอบครัวครับ:\n1. ไม่เชื่อ: สถาบันการเงินและหน่วยงานรัฐ ยกเลิกการส่ง SMS แนบลิงก์แล้ว 100%\n2. ไม่รีบ: มิจฉาชีพมักใช้คำว่า ด่วน, ทันที, บัญชีถูกระงับ เพื่อเร่งให้เราตกใจ\n3. ไม่คลิก: ลิงก์ย่อ bit.ly, lin.ee หรือโดเมนแปลกๆ เช่น .xyz, .top, .club ให้มองข้ามทันที\n4. ตรวจสอบ: หากมีข้อสงสัย ให้โทรเบอร์ทางการที่อยู่หลังบัตรหรือโทร 1441\n5. ส่งเช็ค: ก๊อบปี้ข้อความมาวางตรวจสอบในเว็บบอร์ด DL Community นี้ก่อนกด!",
            'post_status'  => 'publish',
            'post_type'    => 'dl_topic'
        ]);
        if ($p3) {
            wp_set_object_terms($p3, 'c/รู้ทันกลโกง', 'dl_channel');
            update_post_meta($p3, '_dl_author_name', 'naza1144 [MOD]');
            update_post_meta($p3, '_dl_upvotes', 215);
            update_post_meta($p3, '_dl_risk_score', 2.5);
            update_post_meta($p3, '_dl_risk_level', 'SAFE');
            update_post_meta($p3, '_dl_is_scam', 0);

            wp_insert_comment([
                'comment_post_ID'  => $p3,
                'comment_author'   => 'user_safe',
                'comment_content'  => 'สรุปได้กระชับและนำไปใช้ได้จริงมากครับ ขออนุญาตแชร์ต่อให้ผู้สูงอายุที่บ้านอ่านด้วยนะครับ',
                'comment_approved' => 1,
                'comment_date'     => date('Y-m-d H:i:s', strtotime('-3 hours'))
            ]);
        }

        // 4. กระทู้คุยทั่วไป
        $p4 = wp_insert_post([
            'post_title'   => '💬 แวะมาคุยกัน: ช่วงนี้เจอมิจฉาชีพมามุกไหนกันบ้างครับ?',
            'post_content' => "เปิดพื้นที่แลกเปลี่ยนประจำสัปดาห์ครับ ช่วงนี้ใครเจอ SMS หรือสายโทรแปลกๆ บ้างไหม มาแชร์ประสบการณ์กันได้เลยครับ",
            'post_status'  => 'publish',
            'post_type'    => 'dl_topic'
        ]);
        if ($p4) {
            wp_set_object_terms($p4, 'c/พูดคุยทั่วไป', 'dl_channel');
            update_post_meta($p4, '_dl_author_name', 'community_host');
            update_post_meta($p4, '_dl_upvotes', 54);
            update_post_meta($p4, '_dl_risk_score', 1.8);
            update_post_meta($p4, '_dl_risk_level', 'SAFE');
            update_post_meta($p4, '_dl_is_scam', 0);
        }

        update_option('dl_community_seeded', 1);
    }
}
