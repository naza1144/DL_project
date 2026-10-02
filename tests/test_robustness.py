"""
Robustness & Adversarial Generalization Tests
ชุดทดสอบความทนทานต่อการโจมตีแบบ Adversarial, ข้อมูลนอกการฝึกสอน (Zero-Shot / OOD),
และตรวจวัด Generalization Gap เพื่อรับประกันว่าโมเดลไม่มี Overfitting
"""

import json
import os
from django.test import TestCase
from model.infer import ScamPredictor


class RobustnessAndAdversarialTestCase(TestCase):
    """ชุดทดสอบความทนทานของโมเดล AI ต่อการหลบหลีกและข้อมูลที่ไม่เคยพบ"""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.predictor = ScamPredictor()

    def test_adversarial_camouflaged_scams(self):
        """1. ทดสอบตรวจจับข้อความสแกมที่พยายามพรางคำ / เคาะวรรค / ตัวสะกดเลียนแบบ"""
        evasion_cases = [
            "ด ่ ว น ร ั บ เ ง ิ น 50,000 บ. ก ด lin.ee/fastloan",
            "กู้งิuด่วu 50,000 บ. ดอกเบี้ย 0% แอดไลน์ @loan99",
            "กู้-เงิน-ด่วน-อนุมัติ-ไว ผ่อนสบาย 24 เดือน คลิก http://bit.ly/cash",
            "แ จ ก เ ค ร ดิ ต ฟ รี 300 ไม่ต้องฝาก สมัครคลิก bit.ly/slot-win",
            "คืน-เงิน-ประกันมิเตอร์ไฟฟ้า กฟภ. 3,500 บาท ติดต่อ lin.ee/m-pea"
        ]
        for msg in evasion_cases:
            res = self.predictor.predict(msg)
            self.assertTrue(
                res['is_scam'],
                f"หลุดการตรวจจับข้อความพราง: {msg} (Risk: {res['risk_score']}%)"
            )
            self.assertGreaterEqual(
                res['risk_score'], 70.0,
                f"คะแนนความเสี่ยงต่ำเกินไปสำหรับสแกมพรางคำ: {res['risk_score']}%"
            )

    def test_zero_shot_novel_scams(self):
        """2. ทดสอบข้อความสแกมประเภทใหม่ที่ไม่มีในเทมเพลตฝึกสอน (Zero-Shot OOD)"""
        novel_scams = [
            "ลงทุนเหรียญคริปโตผลตอบแทน 30% ต่อวัน การันตีถอนเงินได้จริง สมัคร bit.ly/crypto99",
            "ภารกิจกดยืนยันออเดอร์ในแอป รับเงินวันละ 1,500 บาท ทักไลน์ @order_vip",
            "คุณถูกสุ่มแจกรางวัล iPhone 16 Pro Max กรุณายืนยันสิทธิ์ด่วนที่ http://apple-claim.xyz"
        ]
        for msg in novel_scams:
            res = self.predictor.predict(msg)
            self.assertTrue(
                res['is_scam'],
                f"ตรวจไม่พบสแกมรูปแบบใหม่: {msg} (Risk: {res['risk_score']}%)"
            )

    def test_open_domain_casual_hams(self):
        """3. ทดสอบข้อความสนทนาและข้อความปกติในชีวิตประจำวัน (ต้องไม่ False Alarm)"""
        casual_hams = [
            "วันนี้เลิกงานดึกหน่อยนะ ไม่ต้องรอทานข้าวเย็น เดี๋ยวแวะซื้อของเข้าบ้านก่อน",
            "ส่งไฟล์สรุปรายงานประจำสัปดาห์เข้าไปในอีเมลแล้ว รบกวนตรวจดูหน่อยครับ",
            "อาจารย์แจ้งเลื่อนคลาสเรียนเป็นพรุ่งนี้บ่ายโมงที่ห้อง 402",
            "ยอดเงินคงเหลือในบัตรโดยสารรถไฟฟ้าของคุณเหลือ 45 บาท กรุณาเติมเงินที่สถานี",
            "โรงพยาบาลศิริราช: แจ้งเตือนนัดตรวจสุขภาพประจำปี วันที่ 15 ต.ค. เวลา 09:00 น. กรุณามาล่วงหน้า 15 นาที",
            "Flash Express: พัสดุเลขที่ TH0192 กำลังนำส่งโดยพนักงาน คุณสมชาย เบอร์ติดต่อ 081-234-5678"
        ]
        for msg in casual_hams:
            res = self.predictor.predict(msg)
            self.assertFalse(
                res['is_scam'],
                f"เกิด False Alarm บนข้อความปกติ: {msg} (Risk: {res['risk_score']}%)"
            )
            self.assertLessEqual(
                res['risk_score'], 25.0,
                f"คะแนนความเสี่ยงสูงผิดปกติบนข้อความปลอดภัย: {res['risk_score']}%"
            )

    def test_generalization_gap(self):
        """4. ตรวจสอบประวัติการเทรนว่า Generalization Gap อยู่ในเกณฑ์สุขภาพดี (ไม่ Overfit)"""
        metrics_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'model', 'metrics.json')
        if not os.path.exists(metrics_path):
            self.skipTest("ยังไม่มีไฟล์ metrics.json")

        with open(metrics_path, 'r', encoding='utf-8') as f:
            data = json.load(f)

        history = data.get('history', [])
        if not history:
            self.skipTest("ไม่มีประวัติ epoch ใน metrics.json")

        best_epoch_data = min(history, key=lambda x: x['val_loss'])
        train_loss = best_epoch_data['train_loss']
        val_loss = best_epoch_data['val_loss']
        train_acc = best_epoch_data['train_acc']
        val_acc = best_epoch_data['val_acc']
        acc_gap = abs(train_acc - val_acc)

        # 1. Train Loss ต้องไม่ดิ่งลง 0.0005 (ต้องมี Regularization effect จาก Label Smoothing & Word Dropout)
        self.assertGreaterEqual(
            train_loss, 0.10,
            f"Train Loss ดิ่งลงต่ำเกินไป ({train_loss}) บ่งชี้ว่าเกิด Template Memorization ขาด Regularization"
        )
        self.assertLessEqual(
            train_loss, 0.35,
            f"Train Loss สูงเกินไป ({train_loss}) โมเดลยังไม่ลู่เข้า"
        )

        # 2. Accuracy Generalization Gap ระหว่าง Train และ Val ต้องแคบมาก (< 3% หรือ 0.03)
        self.assertLess(
            acc_gap, 0.03,
            f"Accuracy Generalization Gap กว้างเกินไป ({acc_gap:.4f}) บ่งชี้อาการ Overfitting"
        )

        # 3. Validation Performance ต้องแข็งแกร่ง (Acc >= 98%, Loss <= 0.15)
        self.assertGreaterEqual(val_acc, 0.98, f"Validation Accuracy ต่ำกว่าเกณฑ์: {val_acc:.4f}")
        self.assertLessEqual(val_loss, 0.15, f"Validation Loss สูงกว่าเกณฑ์: {val_loss:.4f}")
