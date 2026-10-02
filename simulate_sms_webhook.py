#!/usr/bin/env python3
"""
Simulate SMS Gateway Webhook
สคริปต์จำลองการทำงานของผู้ให้บริการ SMS (เช่น ThaiBulkSMS, Twilio)
ส่ง Inbound SMS เข้ามาทดสอบทั้งที่ WordPress Webhook URL หรือทดสอบกับโมเดลโดยตรง
"""

import sys
import json
import urllib.request
import urllib.error

# รายการ SMS จำลองที่วิ่งเข้ามาในระบบ
TEST_INBOUND_SMS = [
    {
        "provider": "ThaiBulkSMS",
        "sender": "0819998888",
        "message": "ด ่ ว น ร ั บ เ ง ิ น คืนค่าประกันมิเตอร์ไฟฟ้า กฟภ. 3,500 บ. กด lin.ee/m-pea",
        "expected_action": "BLOCKED"
    },
    {
        "provider": "Twilio",
        "sender": "+66891234567",
        "message": "กู้งิuด่วu 50,000 บ. ดอกเบี้ย 0% อนุมัติไว แอดไลน์ @loan99",
        "expected_action": "BLOCKED"
    },
    {
        "provider": "ThaiBulkSMS",
        "sender": "0823334444",
        "message": "วันนี้เลิกงานดึกหน่อยนะ ไม่ต้องรอทานข้าวเย็น เดี๋ยวแวะซื้อของเข้าบ้านก่อน",
        "expected_action": "ALLOWED"
    },
    {
        "provider": "Android_Forwarder",
        "sender": "0895556666",
        "message": "Flash Express: พัสดุหมายเลข TH0192 กำลังนำส่งโดยพนักงาน เบอร์ติดต่อ 081-234-5678",
        "expected_action": "ALLOWED"
    },
    {
        "provider": "Twilio",
        "sender": "+66917778888",
        "message": "แจกเครดิตฟรี 300 ไม่ต้องฝากก่อน คลิก bit.ly/slot-win",
        "expected_action": "BLOCKED"
    }
]


def test_django_direct(endpoint="http://127.0.0.1:8000/api/inspect/"):
    """ทดสอบส่งตรงไปยัง Django AI API"""
    print(f"\n[*] เริ่มการทดสอบส่ง Inbound SMS ไปยัง Django AI Endpoint: {endpoint}")
    print("=" * 70)

    for idx, item in enumerate(TEST_INBOUND_SMS, 1):
        payload = json.dumps({"text": item["message"]}).encode("utf-8")
        req = urllib.request.Request(
            endpoint,
            data=payload,
            headers={"Content-Type": "application/json"}
        )
        try:
            with urllib.request.urlopen(req, timeout=5) as resp:
                data = json.loads(resp.read().decode("utf-8"))
                is_scam = data.get("is_scam", False)
                risk_score = data.get("risk_score", 0.0)
                action = "BLOCKED" if is_scam else "ALLOWED"
                status_icon = "🔴 BLOCKED" if is_scam else "🟢 ALLOWED"
                match = "✅ ตรงเป้า" if action == item["expected_action"] else "❌ ไม่ตรง"

                print(f"[{idx}] จากค่าย: {item['provider']} | ผู้ส่ง: {item['sender']}")
                print(f"    ข้อความ: {item['message']}")
                print(f"    ผลวิเคราะห์: {status_icon} (Risk: {risk_score}%, Latency: {data.get('latency_ms', 0)}ms) [{match}]")
                if data.get("has_obfuscation"):
                    print(f"    ⚡ De-obfuscated: {data.get('cleaned_text')}")
                print("-" * 70)
        except Exception as e:
            print(f"[*] เซิร์ฟเวอร์ HTTP {endpoint} ยังไม่เปิดทำงาน -> สลับสู่โหมดวิเคราะห์โมเดลตรง (Direct Engine Mode)...")
            from model.infer import ScamPredictor
            predictor = ScamPredictor()
            for i, itm in enumerate(TEST_INBOUND_SMS, 1):
                res = predictor.predict(itm["message"])
                action = "BLOCKED" if res["is_scam"] else "ALLOWED"
                status_icon = "🔴 BLOCKED" if res["is_scam"] else "🟢 ALLOWED"
                match = "✅ ตรงเป้า" if action == itm["expected_action"] else "❌ ไม่ตรง"
                print(f"[{i}] จากค่าย: {itm['provider']} | ผู้ส่ง: {itm['sender']}")
                print(f"    ข้อความ: {itm['message']}")
                print(f"    ผลวิเคราะห์: {status_icon} (Risk: {res['risk_score']}%, Level: {res['risk_level']}) [{match}]")
                if res.get("has_obfuscation"):
                    print(f"    ⚡ De-obfuscated: {res.get('cleaned_text')}")
                print("-" * 70)
            break


def main():
    if len(sys.argv) > 1 and sys.argv[1].startswith("http"):
        endpoint = sys.argv[1]
    else:
        endpoint = "http://127.0.0.1:8000/api/inspect/"

    test_django_direct(endpoint)


if __name__ == '__main__':
    main()
