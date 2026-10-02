"""
WordPress Plugin & SMS Gateway Integration Tests
ทดสอบการทำงานของ Django API (/api/inspect/) ร่วมกับ WordPress Webhook และ Form Payloads
"""

import json
from django.test import TestCase, Client
from django.urls import reverse


class WordPressIntegrationTestCase(TestCase):
    """ชุดทดสอบความเข้ากันได้ระหว่าง Django AI และ WordPress Plugin"""

    def setUp(self):
        self.client = Client()
        self.inspect_url = '/api/inspect/'

    def test_json_payload_from_wordpress(self):
        """1. ทดสอบการส่ง JSON payload จาก wp_remote_post() ในคลาส AI_SMS_API_Client"""
        payload = {
            'text': "ยินดีด้วย! คุณได้รับสิทธิ์ กู้งิuด่วu 50,000 บ. ดอกเบี้ย 0% คลิก lin.ee/loan99"
        }
        response = self.client.post(
            self.inspect_url,
            data=json.dumps(payload),
            content_type='application/json'
        )
        self.assertEqual(response.status_code, 200)
        data = response.json()
        self.assertTrue(data['is_scam'])
        self.assertGreaterEqual(data['risk_score'], 70.0)
        self.assertTrue(data['has_obfuscation'])
        self.assertIn('latency_ms', data)

    def test_form_urlencoded_payload(self):
        """2. ทดสอบการส่งแบบ Form URL-encoded (กรณีส่งแบบ POST ธรรมดา)"""
        response = self.client.post(
            self.inspect_url,
            data={'text': "วันนี้เลิกงานดึกหน่อยนะ ไม่ต้องรอทานข้าวเย็น เดี๋ยวแวะซื้อของเข้าบ้านก่อน"}
        )
        self.assertEqual(response.status_code, 200)
        data = response.json()
        self.assertFalse(data['is_scam'])
        self.assertLessEqual(data['risk_score'], 25.0)

    def test_empty_payload_rejection(self):
        """3. ทดสอบปฏิเสธข้อความว่างเปล่าอย่างปลอดภัย"""
        response = self.client.post(
            self.inspect_url,
            data=json.dumps({'text': "   "}),
            content_type='application/json'
        )
        self.assertEqual(response.status_code, 400)
        data = response.json()
        self.assertIn('error', data)
