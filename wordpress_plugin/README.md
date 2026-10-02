# 🛡️ AI SMS & Phishing Firewall for WordPress

ระบบไฟร์วอลล์ดักจับและคัดกรอง SMS มิจฉาชีพ/สแปม/ฟิชชิ่งแบบ Real-Time ด้วย Deep Learning (BiLSTM + Self-Attention) สำหรับติดตั้งบนเว็บไซต์ WordPress เชื่อมต่อกับ Django API Backend ได้ทันที

---

## 🌟 ฟีเจอร์หลัก (Key Features)

1. **Inbound SMS Webhook Receiver (`/wp-json/ai-sms-firewall/v1/incoming-sms`):**
   * รับ SMS ขาเข้าจากค่ายผู้ให้บริการ (เช่น **ThaiBulkSMS**, **Twilio**, **SMSMKT** หรือแอป **Android SMS Forwarder**)
   * วิเคราะห์ข้อความแบบเรียลไทม์ (Latency ~15ms)
   * ถอดรหัสคำอำพรางตัว (De-obfuscation) เช่น *"กู้งิuด่วu"*, *"ด ่ ว น ร ั บ เ ง ิ น"*
   * สั่งระงับ (Block) ข้อความมิจฉาชีพอัตโนมัติ พร้อมบันทึกประวัติ
2. **Comment & Web Form Phishing Guard:**
   * ดักจับและระงับคอมเมนต์สแปมเงินกู้ / เว็บพนัน / ลิงก์ดูดเงินบนเว็บบล็อกอัตโนมัติ
   * รองรับการกรองฟอร์มติดต่อ **Contact Form 7**
3. **WordPress Admin Dashboard & Threat Audit Log:**
   * เมนู **AI SMS Firewall** ในหลังบ้าน WordPress
   * ตั้งค่า URL ของโมเดล AI และปรับเกณฑ์ความเสี่ยง (Risk Threshold %)
   * กล่องทดสอบสด **Interactive SMS Sandbox** ลองพิมพ์ข้อความและดูผลวิเคราะห์
   * ตารางประวัติการดักจับข้อความ (Threat Log) พร้อมป้ายสถานะและคำพิรุธ (Attention Words)
4. **Public Shortcode `[ai_sms_scanner]`:**
   * นำรหัสสั้น `[ai_sms_scanner]` ไปวางในหน้า Page หรือ Post เพื่อเปิดให้ประชาชนหรือผู้เข้าชมเว็บสามารถเข้ามาตรวจเช็คข้อความ SMS ได้ด้วยตนเอง

---

## 📦 โครงสร้างไฟล์ปลั๊กอิน (Plugin Structure)

```text
ai-sms-firewall/
├── ai-sms-firewall.php             # ไฟล์หลักลงทะเบียนปลั๊กอินและฐานข้อมูล
├── includes/
│   ├── class-ai-sms-api-client.php # ตัวส่ง HTTP ยิงหา Django API (/api/inspect/)
│   ├── class-ai-sms-webhook.php    # ตัวรับ Inbound SMS Webhook จากค่ายมือถือ
│   └── class-ai-sms-form-guard.php # ตัวดักจับ Comment และ Contact Form
├── admin/
│   ├── class-ai-sms-admin.php      # จัดการเมนูหลังบ้าน, ตั้งค่า และ AJAX
│   └── views/
│       └── settings-page.php       # หน้าจอ Dashboard และ Threat Log สวยงาม
└── public/
    └── class-ai-sms-public.php     # ตัวจัดการ Shortcode [ai_sms_scanner]
```

---

## 🚀 วิธีการติดตั้งใน WordPress (Step-by-Step Installation)

### วิธีที่ 1: อัปโหลดผ่านไฟล์ ZIP ใน WP-Admin (ง่ายที่สุด)
1. บีบอัดโฟลเดอร์ `ai-sms-firewall` ให้เป็นไฟล์ `ai-sms-firewall.zip`:
   ```bash
   cd wordpress_plugin
   zip -r ai-sms-firewall.zip ai-sms-firewall
   ```
2. เปิดเบราว์เซอร์ไปที่หน้าหลังบ้าน WordPress (เช่น `http://my-test-site.local/wp-admin`)
3. ไปที่เมนู **Plugins (ปลั๊กอิน) -> Add New (เพิ่มปลั๊กอินใหม่)**
4. กดปุ่ม **Upload Plugin (อัปโหลดปลั๊กอิน)** ด้านบนสุด
5. เลือกไฟล์ `ai-sms-firewall.zip` แล้วกด **Install Now (ติดตั้งตอนนี้)**
6. กดปุ่ม **Activate Plugin (เปิดใช้งานปลั๊กอิน)**

---

## ⚙️ การตั้งค่าหลังติดตั้ง (Configuration)

1. ในหน้าหลังบ้าน WordPress จะปรากฏเมนู **"AI SMS Firewall"** ที่แถบเมนูด้านซ้าย
2. ไปที่แท็บ **"⚙️ การตั้งค่าระบบ (Settings)"**:
   - **AI Deep Learning Endpoint URL:** ใส่ `http://127.0.0.1:8000/api/inspect/` (หรือ IP เซิร์ฟเวอร์ที่รัน Django)
   - **เกณฑ์คะแนนความเสี่ยง (Risk Threshold):** 75% (ค่ามาตรฐาน)
   - **คำสั่งเมื่อตรวจพบข้อความมิจฉาชีพ:** เลือก *ระงับทันที (Block & Reject)*
   - กด **"บันทึกการตั้งค่า"**
3. กดปุ่ม **"ทดสอบเชื่อมต่อ AI Server"** ที่มุมขวาบน จะขึ้นสถานะ `🟢 ออนไลน์` พร้อมค่า Latency

---

## 🧪 การเปิดหน้าให้คนทั่วไปเข้ามาสแกน SMS บนหน้าเว็บ

1. ไปที่เมนู **Pages (หน้า) -> Add New (สร้างหน้าใหม่)** ใน WordPress
2. ตั้งชื่อหน้า เช่น *"ระบบตรวจเช็ค SMS มิจฉาชีพ"*
3. ในกล่องข้อความ ให้พิมพ์สั้นๆ ว่า:
   ```text
   [ai_sms_scanner]
   ```
4. กด **Publish (เผยแพร่)** แล้วเปิดดูหน้าเว็บ จะพบกล่องสแกนข้อความสวยงามพร้อมใช้งานทันที!
