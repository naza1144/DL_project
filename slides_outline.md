# โครงร่างการนำเสนอโครงงาน 15 นาที (15-Minute Presentation Outline)
## โครงงาน: AI SMS Scam & Phishing Firewall
### วิชา: Deep Learning (สัปดาห์ที่ 15: Project Presentation)

---

### ⏱️ ไทม์ไลน์การนำเสนอ (Time Allocation)
* **สไลด์ 1 (30 วินาที):** ปกและแนะนำทีมงาน
* **สไลด์ 2–3 (1 นาที):** ปัญหาภัยไซเบอร์ SMS มิจฉาชีพ และเหตุผลที่ต้องใช้ Deep Learning
* **สไลด์ 4–5 (1 นาที):** ชุดข้อมูล (SMS Spam Benchmark + Thai Scam Corpus) และ De-obfuscator
* **สไลด์ 6–8 (3 นาที):** สถาปัตยกรรมโมเดล BiLSTM + Self-Attention (Explainable AI)
* **สไลด์ 9–10 (3 นาที):** ผลการทดลองและการวัดผล (Loss/Accuracy, Confusion Matrix, Macro F1)
* **สไลด์ 11 (3 นาที):** การสาธิตสด (Live Demo ผ่าน Django Dashboard: 3 แท็บ)
* **สไลด์ 12 (2 นาที):** ปัญหาและอุปสรรคที่พบ (Adversarial Evasion, Class Imbalance) และวิธีแก้ไข
* **สไลด์ 13 (1 นาที):** สรุปผล แผนต่อยอดในอนาคต และช่วงถาม-ตอบ (Q&A)

---

### 📋 รายละเอียดเนื้อหาแต่ละสไลด์ (Slide-by-Slide Details)

#### สไลด์ 1: หน้าปก (Title Slide)
* **หัวข้อ:** AI SMS Scam & Phishing Firewall: ระบบไฟร์วอลล์ตรวจจับ SMS มิจฉาชีพและคำอำพรางตัวด้วย BiLSTM และ Self-Attention
* **ผู้จัดทำ:** [ชื่อ-นามสกุล และรหัสนักศึกษา]
* **อาจารย์ผู้สอน:** ดร.วิชิต สมบัติ (Wichit Sombat, PhD.)

#### สไลด์ 2–3: ปัญหาและความสำคัญ (Problem & Motivation)
* **ทำไมต้องทำ (The "Why"):**
  * สถิติคดีอาชญากรรมทางเทคโนโลยีในไทย: คนไทยถูกหลอกดูดเงินและขโมยข้อมูลจาก SMS มิจฉาชีพปีละหลายพันล้านบาท
  * กฎระเบียบ Regex หรือ Keyword Filter แบบเดิมล้มเหลว เพราะมิจฉาชีพดัดแปลงคำตลอดเวลา เช่น *"กู้งิuด่วu"*, *"รั บ เ งิ น"*
  * ค่ายมือถือและผู้ใช้ต้องการระบบคัดกรองอัตโนมัติระดับ Real-Time ที่เข้าใจ "บริบททางภาษา (Context)"

#### สไลด์ 4–5: ชุดข้อมูลและการเตรียมข้อมูล (Dataset & Preprocessing)
* **ชุดข้อมูลสองภาษาที่ใช้ (10,574 รายการ):**
  * SMS Spam Collection Benchmark (5,574 ข้อความสากล จาก Kaggle/UCI - 52.71%)
  * Thai Threat Intelligence & Field Curation (5,000 ข้อความไทย - 47.29% ตามเป้าหมาย 40-50%)
  * สร้างจากรอยประทุษกรรมจริง: เงินกู้เถื่อน, มิเตอร์ค่าไฟ PEA, สรรพากร, ขนส่ง Flash, เว็บพนัน, ธนาคาร และข้อความจริง (OTP, บิล, แจ้งส่งของ)
* **นวัตกรรม De-obfuscation Layer:**
  * ถอดรหัสคำเคาะเว้นวรรค (Spaced words: *"ด ่ ว น ร ั บ เ ง ิ น"*)
  * แปลงอักษรแฝง (Leet speak: *"กู้งิuด่วu"*) กลับเป็นคำมาตรฐานก่อนเข้าโมเดล
  * สแกนโดเมนระดับบนและลิงก์ย่อต้องสงสัย (`.xyz`, `.top`, `bit.ly`, `lin.ee`)

#### สไลด์ 6–8: สถาปัตยกรรมโมเดลเชิงลึก (Model Architecture)
* **โครงสร้างโมเดล:**
  1. **Embedding Layer (Vocab=6,002, Dim=64):** แปลงคำศัพท์เป็นเวกเตอร์
  2. **Bidirectional LSTM (2 Layers, Hidden=64):** อ่านบริบทประโยคทั้งสองทิศทาง
  3. **Self-Attention Mechanism:** คำนวณค่าน้ำหนักความสนใจรายคำ เพื่อนำมาทำ **Explainable AI (XAI)**
  4. **Dense Classifier + Dropout (0.3):** จำแนกความเสี่ยงเป็นค่าความน่าจะเป็น [0.0 - 1.0]

#### สไลด์ 9–10: ผลการทดลองและการวัดผล (Results & Evaluation)
* **ตัวชี้วัดประสิทธิภาพจริง (Metrics):**
  * **Validation Accuracy:** **99.10%** (เกณฑ์รายวิชา $\ge 90\%$)
  * **Macro F1-Score:** **0.9889** (เกณฑ์รายวิชา $\ge 0.90$)
  * **Training Loss:** ลดลงอย่างสม่ำเสมอจาก 0.2418 สู่ 0.0003 ใน 10 Epochs
  * **Inference Latency:** เฉลี่ยเพียง ~15 ms ต่อข้อความ
* **Confusion Matrix & Loss Curves:**
  * กราฟ Loss & Accuracy บน Training Monitor วาดผลสดราย Epoch แบบ Real-time ผ่าน SSE
  * อัตรา False Negative ต่ำมาก ป้องกันภัยคุกคามหลุดรอดได้อย่างดีเยี่ยม

#### สไลด์ 11: การสาธิตระบบ (Live Demo Showcase)
* **Demo 3 จุดเด่นบน Django Dashboard:**
  1. **Interactive Inspector:** พิมพ์ข้อความ *"กู้งิuด่วu คลิก bit.ly/xxx"* $\rightarrow$ ระบบตรวจจับคำอำพรางตัว ไฮไลต์คำอันตรายด้วย Attention Weights และเปิด **Safety Card** แจ้งเบอร์โทรจริง
  2. **Live Telco Gateway Simulator:** สตรีมการคัดกรองข้อความ SMS แบบสดผ่าน SSE แสดงค่า Latency (~15ms)
  3. **Model Training Monitor:** กราฟ HTML5 Canvas วาดผลสดราย Epoch

#### สไลด์ 12: ความท้าทายและวิธีแก้ไข (Challenges & Solutions)
* **Challenge 1 (Adversarial Evasion):** มิจฉาชีพสะกดผิดจงใจหลบคำ $\rightarrow$ แก้ด้วยโมดูล De-obfuscation กรองคำหน้าบ้าน
* **Challenge 2 (Class Imbalance):** ข้อความปกติมีมากกว่ามิจฉาชีพ $\rightarrow$ แก้ด้วย Stratified Sampling และ Macro F1 Evaluation

#### สไลด์ 13: สรุปผลและตอบคำถาม (Conclusion & Q&A)
* **สรุป:** ระบบทำงานได้จริง รวดเร็ว รองรับสองภาษา และช่วยปกป้องผู้ใช้จากการถูกหลอกลวง
* **แนวทางต่อยอด:** พัฒนาเป็น Mobile App Extension สำหรับ iOS/Android เพื่อบล็อก SMS อัตโนมัติ
* **ถาม-ตอบ (Q&A):** พร้อมตอบคำถามเชิงลึกเกี่ยวกับ Hyperparameters, Attention mechanism และ Data Pipeline
