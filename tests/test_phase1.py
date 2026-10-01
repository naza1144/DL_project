"""
Unit Tests for Phase 1: Data Preparation & Adversarial Defense
ทดสอบความถูกต้องของโมดูล De-obfuscator, Tokenizer และชุดข้อมูล Train/Test
"""

import json
import os
import sys
import unittest

# เพิ่ม root dir เข้า sys.path
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.append(BASE_DIR)

from model.deobfuscator import Deobfuscator
from data.process_data import TextTokenizer


class TestPhase1(unittest.TestCase):

    def setUp(self):
        self.data_dir = os.path.join(BASE_DIR, 'data')
        self.vocab_path = os.path.join(self.data_dir, 'vocab.json')
        self.train_path = os.path.join(self.data_dir, 'train.json')
        self.test_path = os.path.join(self.data_dir, 'test.json')

    def test_deobfuscator_leet_speak(self):
        """ทดสอบการถอดรหัสคำ Leet speak เช่น กู้งิu -> กู้งิน"""
        raw = "กู้งิuด่วu อนุมัติไว"
        res = Deobfuscator.process(raw)
        self.assertTrue(res['has_obfuscation'], "ต้องตรวจพบว่ามีการอำพรางคำ")
        self.assertIn("กู้งินด่วน", res['cleaned_text'], "ต้องแปลง งิu -> งิน ได้สำเร็จ")

    def test_deobfuscator_spaced_words(self):
        """ทดสอบการสมานคำที่ถูกเคาะเว้นวรรค เช่น ร ั บ เ ง ิ น และ f r e e"""
        raw_th = "ด ่ ว น ร ั บ เ ง ิ น คืนประกัน"
        res_th = Deobfuscator.process(raw_th)
        self.assertTrue(res_th['has_obfuscation'])
        self.assertIn("รับเงิน", res_th['cleaned_text'])

        raw_en = "W i n f r e e prize now"
        res_en = Deobfuscator.process(raw_en)
        self.assertTrue(res_en['has_obfuscation'])
        self.assertIn("free", res_en['cleaned_text'].lower())

    def test_deobfuscator_suspicious_urls(self):
        """ทดสอบการตรวจจับ URL Shortener และ TLDs อันตราย"""
        text_with_shortener = "คลิกด่วนที่ http://bit.ly/scam123 หรือ http://free.xyz"
        res = Deobfuscator.process(text_with_shortener)
        self.assertGreaterEqual(res['suspicious_url_count'], 2, "ต้องตรวจพบนามสกุล/Shortener ต้องสงสัยอย่างน้อย 2 ลิงก์")

    def test_deobfuscator_legitimate_preserved(self):
        """ทดสอบว่าข้อความปกติไม่ถูกเปลี่ยนแปลงจนเสียความหมาย"""
        normal_text = "รหัส OTP ของคุณคือ 849201 ใช้สำหรับยืนยันการเข้าสู่ระบบ ธนาคารกสิกรไทย"
        res = Deobfuscator.process(normal_text)
        self.assertEqual(res['suspicious_url_count'], 0)
        self.assertIn("OTP", res['cleaned_text'])
        self.assertIn("ธนาคารกสิกรไทย", res['cleaned_text'])

    def test_dataset_files_exist_and_valid(self):
        """ทดสอบว่าไฟล์ Dataset และ Vocab ถูกสร้างและโครงสร้างถูกต้อง"""
        self.assertTrue(os.path.exists(self.vocab_path), "ไฟล์ vocab.json ต้องมีอยู่จริง")
        self.assertTrue(os.path.exists(self.train_path), "ไฟล์ train.json ต้องมีอยู่จริง")
        self.assertTrue(os.path.exists(self.test_path), "ไฟล์ test.json ต้องมีอยู่จริง")

        # ตรวจสอบขนาด Vocab
        with open(self.vocab_path, 'r', encoding='utf-8') as f:
            vocab = json.load(f)
        self.assertGreaterEqual(len(vocab), 1000, "คลังคำศัพท์ต้องมีอย่างน้อย 1,000 คำ")
        self.assertIn('<PAD>', vocab)
        self.assertIn('<UNK>', vocab)
        self.assertIn('<URL>', vocab)

        # ตรวจสอบขนาด Train/Test
        with open(self.train_path, 'r', encoding='utf-8') as f:
            train_data = json.load(f)
        with open(self.test_path, 'r', encoding='utf-8') as f:
            test_data = json.load(f)

        self.assertGreater(len(train_data), 4000, "Train dataset ต้องมีอย่างน้อย 4,000 รายการ")
        self.assertGreater(len(test_data), 1000, "Test dataset ต้องมีอย่างน้อย 1,000 รายการ")

    def test_tokenizer_encoding(self):
        """ทดสอบการ Tokenize และแปลงเป็นตัวเลขความยาวคงที่"""
        with open(self.vocab_path, 'r', encoding='utf-8') as f:
            vocab = json.load(f)
        tokenizer = TextTokenizer(vocab=vocab)

        sample = "กู้เงินด่วน 50,000 บาท คลิก http://bit.ly/scam"
        encoded = tokenizer.encode(sample, max_len=20)
        self.assertEqual(len(encoded), 20, "เวกเตอร์ต้องมีขนาดความยาว 20 ตามที่กำหนด (Padding)")
        self.assertIsInstance(encoded[0], int)


if __name__ == '__main__':
    unittest.main()
