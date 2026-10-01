# 🎨 UI/UX & Design Skill System — Master Guide
> **สถาปัตยกรรมและคู่มือการใช้งานระบบ Agent Skill ขั้นสูงสำหรับงาน UI/UX, Design Systems และ Dashboard**

---

## 📌 1. ภาพรวมระบบ (System Overview)

โฟลเดอร์ `skill/` นี้ประกอบด้วย 2 ระบบย่อยระดับแถวหน้าของวงการ AI Agent Design ซึ่งถูกนำมาผสานการทำงานเข้าด้วยกันอย่างสมบูรณ์แบบ:

```text
                           ┌───────────────────────────────────────────────┐
                           │          USER REQUEST / GOAL                  │
                           └───────────────────────┬───────────────────────┘
                                                   │
                                                   ▼
                                ┌─────────────────────────────────────┐
                                │     senior-ui-ux-orchestrator       │
                                │   (ประธานและศูนย์กลางการ Route งาน)     │
                                └──────────────────┬──────────────────┘
                                                   │
         ┌─────────────────────────────────────────┼────────────────────────────────────────┐
         │                                         │                                        │
         ▼                                         ▼                                        ▼
┌──────────────────┐                     ┌──────────────────┐                     ┌──────────────────┐
│   ui-ux-pro-max  │                     │   open-design    │                     │  webapp-ui-skill │
│ (Design Database)│                     │ (Brand Substrate)│                     │(Dashboard Builder│
│• 67 UI Styles    │                     │• 150+ Brands     │                     │• State Matrix    │
│• 161 Palettes    │                     │• 110+ Templates  │                     │• Data Tables     │
│• 57 Font Pairs   │                     │• 11 Craft Rules  │                     │• DL Metrics      │
└────────┬─────────┘                     └────────┬─────────┘                     └────────┬─────────┘
         │                                        │                                        │
         └────────────────────────────────────────┼────────────────────────────────────────┘
                                                  │
                                                  ▼
                                ┌─────────────────────────────────────┐
                                │      AUDIT & QUALITY ASSURANCE      │
                                │ • design-critic-skill (ความงาม/Hierarchy) │
                                │ • ux-audit-skill (WCAG/Heuristics)  │
                                │ • workflow-compliance-supervisor    │
                                └─────────────────────────────────────┘
```

### คลังทรัพยากรที่มีพร้อมใช้งานในระบบ:
1. **72 Canonical Skills**: ครอบคลุมตั้งแต่การวางแผนเชิงกลยุทธ์, การออกแบบ, การสร้างโค้ด, การตรวจสอบคุณภาพ (Audit), การเชื่อมต่อ Figma, ไปจนถึง SEO/LLM Optimization
2. **150+ Brand DESIGN.md Systems**: ระบบแบรนด์ระดับโลก (เช่น Apple, Airbnb, Stripe, Linear, BMW, Ant Design, Vercel, Supabase, Arc)
3. **110+ Rendering Templates**: เทมเพลตที่พร้อมคอมไพล์เป็น HTML/CSS/JS (Dashboards, Pitch Decks, Landing Pages, Admin Consoles)
4. **11 Universal Craft Standards**: กฎมาตรฐานสากล (Anti-AI-Slop, Typography Hierarchy, Color Harmony, Animation Discipline, Accessibility Baseline, State Coverage)
5. **Interactive UI Review Boards**: หน้าจอ Visual Dashboard แบบ Single-Page App สำหรับคลิกดูโครงสร้าง Skill และความสัมพันธ์ได้แบบ Interactive

---

## 🚀 2. การจัดโครงสร้างให้อัตโนมัติ (Integration with Antigravity IDE)

เพื่อให้ Antigravity ตรวจจับและดึง Skill เหล่านี้มาใช้งานได้อย่างแม่นยำตามสถาปัตยกรรม **Progressive Disclosure**:

- **โฟลเดอร์ Customization ของโปรเจกต์**: ได้ทำการสร้าง `.agents/skills/` ที่ Root ของ Workspace และ Symlink ทุก Skill (72 รายการ) พร้อมเชื่อมโยง `SKILL.md` ทุกไฟล์เรียบร้อย
- **กฎของโปรเจกต์ (Rules)**: ได้สร้าง `.agents/rules/ui_ux_system.md` เพื่อกำหนดแนวทางการตัดสินใจ (Doctrine) และการป้องกันข้อผิดพลาด (Anti-Patterns) ให้ตัว Agent เสมอ
- **แคตตาล็อก Open Design**: โคลนไว้ที่ `~/.open-design-skill/repo` เรียบร้อย สามารถเรียกใช้ไฟล์แบรนด์และเทมเพลตได้ทันที

---

## 🧭 3. การแบ่งหมวดหมู่ทักษะ 8 ระดับ (8 Functional Tiers)

### 🔹 Tier 1: Main Control & Orchestration (ศูนย์ควบคุมและกำกับการทำงาน)
- **`senior-ui-ux-orchestrator`**: **ประธานหลัก (Chair)** ทำหน้าที่วิเคราะห์โจทย์ เลือก Specialist Skills ที่เหมาะสมที่สุด และระงับข้อขัดแย้งข้ามสายงาน (Conflict Resolution)
- **`task-plan-v2-orchestrator`**: จัดการวางแผนงานขนาดใหญ่แบบ multi-step, task status, และ handoff policy
- **`workflow-compliance-supervisor`**: ตรวจสอบว่าผลงานที่สร้างขึ้นจริง (Code/Artifacts) ตรงกับสิ่งที่สัญญากับผู้ใช้หรือไม่ ป้องกันข้ออ้างหลักฐานลอยๆ (Fake-evidence risk)
- **`agent-progress-visualizer`**: สร้าง Cockpit หน้าจอ HTML แสดงความคืบหน้าของงานให้ผู้ใช้เห็นแบบเรียลไทม์

### 🔹 Tier 2: Design Intelligence & Exploration (คลังปัญญาและการสำรวจงานดีไซน์)
- **`ui-ux-pro-max`**: เอนจินค้นหาและแนะนำงานดีไซน์อัจฉริยะ (67 UI styles, 161 color palettes, 57 font pairings, 161 product reasoning rules, 25 chart types)
- **`visual-content-director`**: กำกับทิศทางงานศิลป์ (Visual Art Direction), สไตล์ภาพถ่าย, ภาพประกอบ 3D
- **`stitch-design-bridge`**: สะพานเชื่อมต่อกับ Google Stitch / AI UI generators
- **`pencil-design-bridge`**: สะพานสร้าง Wireframe และ Sketch ด้วย AI
- **`design-critic-skill`**: วิจารณ์ความงาม ลำดับชั้นสายตา (Hierarchy), Contrast และป้องกันการออกแบบที่ดูเป็น AI-Slop
- **`ux-audit-skill`**: ตรวจสอบ UX ตามหลักเกณฑ์มาตรฐานสากล (Nielsen-Norman 10 Usability Heuristics, WCAG 2.1 AA/AAA)

### 🔹 Tier 3: Open Design Substrate (ระบบรากฐานแบรนด์และแม่แบบสากล)
- **`open-design`**: ดึงระบบแบรนด์ระดับโลกกว่า 150 แบรนด์และแม่แบบกว่า 110 รูปแบบมาประกอบเข้าด้วยกันผ่านไฟล์คอนฟิก `.open-design.json`
- **ลำดับสิทธิ์เมื่อมีข้อขัดแย้ง**:
  1. `DESIGN.md` (Brand Tokens ชนะเสมอ)
  2. `craft/*.md` (กฎสากลที่ Brand ไม่ได้ทับทิม)
  3. `SKILL.md` (Workflow ลำดับการเขียนไฟล์)

### 🔹 Tier 4: Implementation Specialists (ผู้เชี่ยวชาญการลงโค้ดแต่ละประเภท)
- **`webapp-ui-skill`**: สำหรับ Web Application, Deep Learning Dashboards, SaaS, Admin Panels เน้นจัดการสถานะทั้ง 8: *Loading, Empty, Error, Disabled, Focus, Selected, Submitting, Success*
- **`marketing-site-skill`**: สำหรับ Landing Page สาธารณะที่เน้น Conversion Rate, Hero Section ที่แสดงคุณค่าชัดเจน
- **`admin-ui-orchestrator` & `admin-ui-builder`**: สร้างระบบหลังบ้าน, Data Grids, CRUD operations, สิทธิ์การใช้งาน (RBAC)
- **`site-ai-assistant-builder`**: สร้างหน้าต่างแชท AI, Input box อัจฉริยะ, การแสดงผลข้อความแบบ Streaming
- **`omnichannel-comms-builder`**: ศูนย์แจ้งเตือน (Notification Center), อีเมลเทมเพลต, Toast Alerts

### 🔹 Tier 5: Figma Subsystem (ระบบปฏิบัติการเชื่อมโยง Figma)
- **`senior-figma-orchestrator`**: ผู้ประสานงานคำสั่ง Figma MCP และ REST API
- **`figma-context-reader`**: อ่านโครงสร้าง Frame, Components, Variables จากไฟล์ Figma
- **`figma-design-to-code-bridge`**: แปลง Design ใน Figma ออกมาเป็น React, Tailwind, HTML สะอาด
- **`figma-code-to-canvas`**: ดึง Component จากโค้ดขึ้นไปวาดบน Canvas ของ Figma
- **`figma-canvas-editor`**: สั่งปรับ Auto-layout, Typography, Color styles บน Figma โดยตรง
- **`figma-design-system-sync`**: ซิงค์ Figma Variables กับ CSS Variables หรือ Design Tokens ในโปรเจกต์
- **`figma-assets-manager`**: Export และบีบอัดรูปภาพ SVG/WebP จาก Figma

### 🔹 Tier 6: Interactive & Visual Effects (ลูกเล่นภาพและการเคลื่อนไหวระดับสูง)
- **`cursor-reveal-hero`**: เอฟเฟกต์ไฟฉาย/สปอตไลต์เปิดเผยเลเยอร์ตามการเลื่อนของเมาส์ (Canvas masking / Cursor trail)
- **`image-layer-alignment-validator`**: ตรวจจับความคลาดเคลื่อน (Pixel drift) ของเลเยอร์ภาพสองชั้น
- **`website-to-hyperframes`**: แปลงหน้าเว็บธรรมดาให้เป็น Animated Interactive Storytelling

### 🔹 Tier 7: Product & SEO/LLM Architecture (โครงสร้างผลิตภัณฑ์และการรองรับ AI Engine)
- **`ui-ux-llm-product-architect`**: ออกแบบ Information Architecture (IA) และ Responsive Flows
- **`ux-journey-architect`**: วิเคราะห์ขั้นตอนผู้ใช้งาน ลดจุดสะดุด (Friction) และลดการทิ้งหน้าเว็บ (Drop-off)
- **`seo-llm-site-architect` & `llm-friendly-site-architect`**: ออกแบบโครงสร้างเว็บให้อ่านเข้าใจง่ายทั้ง Google Bot และ AI Engine (ChatGPT, Perplexity, Gemini)
- **`technical-seo-schema-engineer`**: เขียน JSON-LD Schema.org (Product, FAQ, Dataset, Breadcrumb)

### 🔹 Tier 8: Infrastructure, Launch & Analytics (การขึ้นระบบและการวัดผล)
- **`tech-stack-selector`**: ประเมินและเลือกเทคโนโลยีที่เหมาะสมกับโจทย์
- **`analytics-setup-architect`**: วาง Event Data Layer สำหรับ GA4, PostHog, Mixpanel
- **`web-security-architect`**: ตั้งค่า CSP (Content Security Policy), ป้องกัน XSS/CSRF
- **`launch-readiness-auditor`**: Checklist ตรวจสอบความพร้อมก่อนเปิดระบบจริง

---

## 🛠️ 4. เครื่องมือศูนย์กลาง `hub.py` (Unified CLI)

เราได้สร้างตัวช่วย CLI ในตัวโปรเจกต์ที่ `skill/hub.py` เพื่อให้เรียกใช้งานได้ในบรรทัดเดียว:

### 1. ดูรายชื่อทักษะทั้งหมดตามหมวดหมู่
```bash
python3 skill/hub.py skills
```

### 2. ค้นหาไอเดียและคำแนะนำดีไซน์ผ่าน `ui-ux-pro-max`
```bash
# ค้นหาคำแนะนำสำหรับ Dashboard
python3 skill/hub.py search "dashboard" --domain product

# ค้นหาโทนสีและสไตล์ Dark Mode
python3 skill/hub.py search "dark mode" --domain style

# ค้นหารูปแบบกราฟและชาร์ต
python3 skill/hub.py search "real-time analytics" --domain chart
```

### 3. ค้นหา Brand Design Systems จาก Open Design
```bash
# ค้นหาแบรนด์ที่มีคำว่า tech หรือ dark
python3 skill/hub.py open-design systems --filter tech
python3 skill/hub.py open-design systems --filter stripe

# ค้นหาเทมเพลตพร้อมใช้
python3 skill/hub.py open-design templates --filter dashboard

# ดูกฎ Craft สากล (Anti-AI-Slop, Typography, State Coverage)
python3 skill/hub.py open-design craft
```

### 4. ผูกดีไซน์ซิสเต็มเข้ากับโปรเจกต์ (Binding)
```bash
# ผูกระบบ Stripe + Dashboard เข้ากับโปรเจกต์ (จะสร้างไฟล์ .open-design.json ให้อัตโนมัติ)
python3 skill/hub.py open-design bind --system stripe --template dashboard
```

### 5. เปิดหน้าจอ Visual Review Board & Graph ใน Browser
```bash
python3 skill/hub.py view-board
```
จะแสดงลิงก์ไฟล์ HTML เช่น:
- [skill-review-board.html](file:///home/naza1144/work/DL_project/skill/ui-ux-agent-skill-system/docs/skill-review-board.html)
- [skill-graph.html](file:///home/naza1144/work/DL_project/skill/ui-ux-agent-skill-system/docs/skill-graph.html)
- [task-dashboard.html](file:///home/naza1144/work/DL_project/skill/ui-ux-agent-skill-system/docs/task-dashboard.html)

---

## 🎯 5. แนวทางปฏิบัติสำหรับโปรเจกต์ Deep Learning Dashboard (`DL_project`)

สำหรับโปรเจกต์ปัจจุบันของคุณ (`dashboard/templates/dashboard/index.html`):

1. **สไตล์ที่แนะนำสูงสุดสำหรับ AI/DL Dashboard**:
   - **Style Category**: `Data-Dense + Dark Mode (OLED)` หรือ `Linear-Inspired SaaS`
   - **Color Palette**: Dark base (`#090D16` หรือ `#0B0F19`), Accent เป็น Gradient ไฮเทค เช่น Cyan (`#00F2FE` / `#4FACFE`) หรือ Indigo/Violet (`#6366F1`)
   - **Typography**: Inter หรือ Geist คู่กับ JetBrains Mono สำหรับตัวเลข Epochs / Loss / Accuracy
2. **การจัดวางเลย์เอาต์ (Doctrine)**:
   - ห้ามใส่ Marketing Hero ขนาดใหญ่ในหน้า Dashboard ที่ใช้งานจริง
   - บล็อกแสดงผล Metrics ต้องเสถียร ไม่ขยับขนาด (No layout shift) เมื่อข้อมูลกำลัง Fetch หรือ Hover
   - กราฟ Loss & Accuracy ต้องมีสถานะ: *กำลังเทรน (Live Stream), ว่างเปล่า (Empty Run), เกิดข้อผิดพลาด (Error)*
3. **การตรวจสอบผลงาน**:
   - เมื่อสร้างหรือแก้ UI เสร็จ ให้รัน `design-critic-skill` และ `ux-audit-skill` เพื่อตรวจ Contrast และ Visual Polish ทันที
