# 📖 Project Master Playbook & Agent Directive (สมุดนำร่อง AI ประจำโปรเจกต์)

> **คำสั่งบังคับสำหรับ AI Agent (Antigravity / Gemini / Coding Agents)**:  
> เมื่อใดก็ตามที่ได้รับคำสั่งจากผู้ใช้เกี่ยวกับงาน UI, UX, Frontend, Dashboard, Styling, Design System หรือการตรวจสอบคุณภาพ **ห้ามให้ผู้ใช้ต้องมานั่งเลือกหรือพิมพ์ชื่อ Skill เองเด็ดขาด**  
> AI ต้องอ่านคู่มือนี้เป็นเล่มหลัก แล้วเลือกหยิบ Skill จาก `.agents/skills/` มาทำงานให้สอดคล้องกับโจทย์ของผู้ใช้โดยอัตโนมัติ

---

## 🎯 1. ศูนย์กลางการตัดสินใจ (The Central Orchestrator)
- **ทักษะหัวหน้า**: `senior-ui-ux-orchestrator`
- **หน้าที่**: วิเคราะห์เจตนาของผู้ใช้ (User Intent) -> จัดกลุ่มประเภทงาน -> เลือก Specialist Skills ที่เกี่ยวข้องมาประกอบกันเป็นชุดทำงานย่อย (Pipeline) ทันที

---

## 🗺️ 2. ตารางการหยิบ Skill อัตโนมัติ (Automated Routing Matrix)

เมื่อผู้ใช้พิมพ์คำสั่งในลักษณะต่างๆ ให้ AI ดึง Skill ตามตารางนี้มาใช้งานทันที:

| โจทย์ / สิ่งที่ผู้ใช้ต้องการ | ทักษะที่ AI ต้องหยิบมาใช้ (Auto-selected Skills) | แหล่งข้อมูลอ้างอิง |
|---|---|---|
| **หน้า Dashboard, Deep Learning Monitor, ตารางข้อมูล, สถานะโมเดล** | `webapp-ui-skill` + `ui-ux-pro-max` | `core/skills/webapp-ui-skill`<br>`references/dashboard-patterns.md` |
| **แนะนำธีม, สไตล์ (Dark/OLED/Cyberpunk), โทนสี, คู่ฟอนต์** | `ui-ux-pro-max` | รัน `python3 skill/hub.py search` หรือค้น `styles.csv`, `colors.csv` |
| **นำสไตล์แบรนด์ชั้นนำมาใช้ (Stripe, Linear, Apple, Ant, Vercel)** | `open-design` | ค้นหาและ Bind ผ่าน `skill/hub.py open-design` |
| **วิจารณ์ความสวยงาม, จัดลำดับสายตา (Hierarchy), ขจัดความเชย/AI-Slop** | `design-critic-skill` | `core/skills/design-critic-skill` |
| **ตรวจ UX, จุดสะดุด (Friction), ความเข้าถึงง่าย (WCAG/Accessibility)** | `ux-audit-skill` | `core/skills/ux-audit-skill` |
| **หน้า Landing Page, หน้านำเสนอผลิตภัณฑ์, การโปรโมทผลงาน** | `marketing-site-skill` | `core/skills/marketing-site-skill` |
| **แผนงานขนาดใหญ่, การแบ่งเฟสงานทีละขั้นตอน** | `task-plan-v2-orchestrator` + `agent-progress-visualizer` | `TASK-PLAN.md` |
| **งานเชื่อมต่อและดึงดีไซน์จาก Figma** | `senior-figma-orchestrator` | `core/skills/senior-figma-orchestrator` |

---

## 🛡️ 3. กฎเหล็กในการพัฒนา UI สำหรับโปรเจกต์นี้ (Design Doctrine)
1. **No Marketing Hero in Operational Dashboard**: หน้า Deep Learning Dashboard ต้องเน้นข้อมูล (Data-Dense), อ่านค่าง่าย, ตัวเลข Epochs/Loss/Accuracy ชัดเจน ห้ามใส่ Banner การตลาดรกตา
2. **Complete State Coverage**: ทุกองค์ประกอบที่มีการโหลดข้อมูลแบบ Asynchronous ต้องมีสถานะครบ 8 ประการเสมอ: *Loading, Empty, Error, Disabled, Focus, Selected, Submitting, Success*
3. **Color & Typography Discipline**:
   - Dark Mode: ห้ามใช้สีดำสนิท `#000000` ล้วน ให้ใช้โทน Dark Slate/Navy เช่น `#090D16` หรือ `#0B0F19`
   - ตัวเลขและโค้ด: ให้ใช้ Monospace เช่น `JetBrains Mono` หรือ `Fira Code`
   - ตัวหนังสือทั่วไป: ให้ใช้ฟอนต์สะอาดทันสมัย เช่น `Inter` หรือ `Geist`
   - อัตราส่วน Contrast ต้องผ่านเกณฑ์ 4.5:1 เสมอ
