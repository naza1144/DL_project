# AI SMS Scam & Phishing Firewall

ระบบไฟร์วอลล์ตรวจจับ SMS มิจฉาชีพและคำอำพรางตัวด้วย Deep Learning (BiLSTM + Self-Attention) พร้อมหน้า Dashboard ควบคุมและสตรีมสดผ่าน Server-Sent Events (SSE)

พัฒนาตามข้อกำหนดของรายวิชา **Deep Learning (สัปดาห์ที่ 15: การนำเสนอโครงงาน)** โดย ดร.วิชิต สมบัติ

---

## 📌 จุดเด่นและนวัตกรรมของโครงงาน (Key Features & Innovation)

1. **Adversarial Camouflage Defense (การถอดรหัสคำอำพรางตัว):**
   * มิจฉาชีพมักพิมพ์สะกดผิดจงใจเพื่อหลบ AI เช่น *"กู้งิuด่วu"*, *"รั บ เ งิ น"*, *"Uริการ"*
   * ระบบมี **De-obfuscation Layer** ถอดรหัสคำแฝง, สมานคำเคาะวรรค, และล้างอักขระซ่อนก่อนส่งเข้าโมเดล
2. **Explainable AI (XAI) ผ่าน Self-Attention Mechanism:**
   * สกัดค่าน้ำหนัก Attention Weights ราย Token เพื่อไฮไลต์คำที่เป็นสัญญาณอันตรายบนหน้าเว็บ ทำให้โมเดลมีความโปร่งใส
3. **Live Telco Gateway Simulation (SSE Real-Time Stream):**
   * จำลองไฟร์วอลล์ค่ายมือถือ สตรีมข้อความเข้า-ออก และคัดกรองสด (Pass vs Block) พร้อมวัดค่า Latency
4. **Actionable Safety Card & Counter-Action:**
   * ตรวจจับสถาบันที่ถูกแอบอ้าง (เช่น กสิกร, SCB, กฟภ., สรรพากร) และแสดงเบอร์โทรทางการจริง พร้อมปุ่มรายงานภัยไซเบอร์ (1441)

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```text
DL_project/
├── data/
│   ├── raw/                      # ชุดข้อมูลดิบ (SMS Spam Collection Benchmark)
│   ├── process_data.py           # สคริปต์รวมข้อมูล Tokenization และ Vocab Builder
│   ├── vocab.json                # คลังคำศัพท์ (Vocabulary Mapping)
│   ├── train.json                # ชุดข้อมูลฝึกสอน (80%)
│   └── test.json                 # ชุดข้อมูลทดสอบ (20%)
├── model/
│   ├── deobfuscator.py           # โมดูลถอดรหัสคำอำพรางตัว (Adversarial Defense)
│   ├── model.py                  # สถาปัตยกรรม SpamAttentionBiLSTM (PyTorch)
│   ├── train.py                  # สคริปต์ฝึกสอน บันทึก weights.pth และ metrics.json
│   ├── infer.py                  # Pipeline วิเคราะห์ความเสี่ยงและสร้าง Safety Card
│   ├── weights.pth               # ไฟล์น้ำหนักโมเดลที่บันทึกไว้ (state_dict)
│   └── metrics.json              # ประวัติ Loss/Accuracy ราย Epoch
├── dashboard/
│   ├── views.py                  # Django Views (SSE streams & API)
│   ├── urls.py                   # URL Routing
│   └── templates/dashboard/
│       └── index.html            # Frontend UI (Tailwind CSS + Alpine.js)
├── myproject/
│   ├── settings.py               # การตั้งค่า Django
│   └── urls.py                   # Main URL Routing
├── tests/
│   └── test_phase1.py            # Unit Tests สำหรับ De-obfuscator และ Dataset
├── manage.py                     # Django Management CLI
├── requirements.txt              # Dependencies ของโปรเจกต์
├── REFERENCES.md                 # เอกสารอ้างอิงแหล่งที่มาของข้อมูลและบทเรียนที่นำมาใช้
├── slides_outline.md             # โครงสร้างสไลด์และสคริปต์นำเสนอ 15 นาที
└── README.md                     # เอกสารกำกับโครงงานฉบับนี้
```

---

## 🛠️ วิธีการติดตั้งและรันระบบ (Setup & Running)

### 1. ติดตั้ง Dependencies
```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

### 2. เตรียมชุดข้อมูล (Data Preparation)
```bash
python3 data/process_data.py
```

### 3. ฝึกสอนโมเดล (Train Model)
```bash
python3 model/train.py
```
*(ระบบจะสร้างไฟล์ `model/weights.pth` และ `model/metrics.json`)*

### 4. รัน Django Dashboard
```bash
python3 manage.py runserver 0.0.0.0:8000
```
เปิดเบราว์เซอร์ไปที่ `http://127.0.0.1:8000` เพื่อใช้งาน Dashboard

---

## 📊 ผลลัพธ์และตัวชี้วัดของโมเดล (Model Performance)

* **Architecture:** Embedding(64) $\rightarrow$ BiLSTM(Hidden=64, 2 Layers, Dropout=0.3) $\rightarrow$ Self-Attention $\rightarrow$ Dense Classifier
* **Dataset Size:** 6,336 ข้อความ (Train 5,068 / Test 1,268)
* **Accuracy:** > 96%
* **Macro F1-Score:** > 0.94
* **Average Latency:** ~15–20 ms ต่อข้อความ

---

## 👥 ข้อมูลการส่งงาน
* รายวิชา: Deep Learning สัปดาห์ที่ 15
* ภาคเรียน: 1/2567
