"""
Django View & Endpoint Tests
ทดสอบหน้าเว็บหลัก, API วิเคราะห์ข้อความ และ SSE Stream endpoints
"""

import json
from django.test import TestCase, Client


class DashboardViewsTestCase(TestCase):

    def setUp(self):
        self.client = Client()

    def test_index_page(self):
        """ทดสอบหน้าเว็บหลักเรนเดอร์ถูกต้อง"""
        response = self.client.get('/')
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'AI SMS Scam Firewall')
        self.assertContains(response, 'Interactive Inspector')
        self.assertContains(response, 'Live Telco Gateway')

    def test_inspect_api_scam(self):
        """ทดสอบ API วิเคราะห์ข้อความมิจฉาชีพ"""
        payload = {'text': 'กู้งิuด่วu 50,000 บ. คลิก http://bit.ly/scam'}
        response = self.client.post('/api/inspect/', data=json.dumps(payload), content_type='application/json')
        self.assertEqual(response.status_code, 200)
        data = response.json()
        self.assertTrue(data['is_scam'])
        self.assertGreater(data['risk_score'], 80.0)
        self.assertTrue(data['has_obfuscation'])
        self.assertIn('safety_card', data)

    def test_inspect_api_ham(self):
        """ทดสอบ API วิเคราะห์ข้อความปลอดภัย (OTP)"""
        payload = {'text': 'รหัส OTP ของคุณคือ 849201 สำหรับเข้าสู่ระบบ K PLUS'}
        response = self.client.post('/api/inspect/', data=json.dumps(payload), content_type='application/json')
        self.assertEqual(response.status_code, 200)
        data = response.json()
        self.assertFalse(data['is_scam'])
        self.assertLess(data['risk_score'], 20.0)

    def test_gateway_stream_sse(self):
        """ทดสอบ SSE Endpoint ของ Live Telco Gateway"""
        response = self.client.get('/gateway_stream/')
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response['Content-Type'], 'text/event-stream')
        # อ่าน chunk แรก
        stream_content = next(response.streaming_content).decode('utf-8')
        self.assertTrue(stream_content.startswith('data: '))
        json_data = json.loads(stream_content[6:])
        self.assertIn('sender', json_data)
        self.assertIn('action', json_data)
