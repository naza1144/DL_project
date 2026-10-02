"""
Django Views for AI SMS Scam & Phishing Firewall Dashboard
รองรับทั้งหน้าเว็บแบบอินเทอร์แอคทีฟ และระบบสตรีมสดผ่าน Server-Sent Events (SSE)
"""

import json
import os
import random
import sys
import time
from django.shortcuts import render
from django.http import StreamingHttpResponse, JsonResponse
from django.views.decorators.csrf import csrf_exempt

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.append(BASE_DIR)

from model.infer import ScamPredictor
from model.train import train_epochs_generator

WEIGHTS_PATH = os.path.join(BASE_DIR, 'model', 'weights.pth')
METRICS_PATH = os.path.join(BASE_DIR, 'model', 'metrics.json')

# Singleton Predictor instance
_predictor = None

def get_predictor():
    global _predictor
    if _predictor is None:
        _predictor = ScamPredictor()
    return _predictor


def index(request):
    """เรนเดอร์หน้า Dashboard หลัก"""
    has_weights = os.path.exists(WEIGHTS_PATH)
    metrics_summary = {}
    if os.path.exists(METRICS_PATH):
        try:
            with open(METRICS_PATH, 'r', encoding='utf-8') as f:
                data = json.load(f)
                metrics_summary = data.get('final_metrics', {})
        except Exception:
            pass

    context = {
        'has_weights': has_weights,
        'metrics': metrics_summary,
    }
    return render(request, 'dashboard/index.html', context)


def train_stream(request):
    """
    SSE Endpoint: สตรีมผลการเทรนราย Epoch ไปยังกราฟบนหน้าเว็บ
    URL: /train_stream/
    """
    def event_stream():
        yield "data: {\"status\": \"started\", \"message\": \"เริ่มต้นฝึกสอนโมเดล...\"}\n\n"
        time.sleep(0.5)

        try:
            for epoch_update in train_epochs_generator(num_epochs=10):
                yield f"data: {json.dumps(epoch_update)}\n\n"
                time.sleep(0.3)

            # รีโหลด predictor เพื่อใช้น้ำหนักโมเดลใหม่
            global _predictor
            _predictor = ScamPredictor()

            yield "data: {\"status\": \"completed\", \"message\": \"ฝึกสอนโมเดลสำเร็จและบันทึก weights.pth เรียบร้อย!\"}\n\n"
        except Exception as e:
            yield f"data: {{\"status\": \"error\", \"message\": \"{str(e)}\"}}\n\n"

    response = StreamingHttpResponse(event_stream(), content_type='text/event-stream')
    response['Cache-Control'] = 'no-cache'
    response['X-Accel-Buffering'] = 'no'
    return response


# ชุดข้อความจำลองการจราจรบนเครือข่ายมือถือ (Telecom Gateway Traffic Simulation)
SAMPLE_TRAFFIC = [
    # Ham
    ("089-112-3456", "รหัส OTP ของคุณคือ 849201 สำหรับเข้าสู่ระบบ K PLUS"),
    ("081-998-1234", "เงินเข้า 15,000.00 บ. บัญชี xxx-1234 ผ่าน K PLUS คงเหลือ 28,400 บ."),
    ("FlashExpress", "Flash Express: พัสดุเลขที่ TH0192 กำลังนำส่งโดยพนักงานส่ง คุณสมชาย 081-234-5678"),
    ("086-554-3210", "พรุ่งนี้เจอกันที่ห้างตอนเที่ยงนะ อย่าลืมเอาชีทสรุปมาด้วย"),
    ("AIS_Service", "AIS: แพ็กเกจเน็ต 5G ของคุณจะหมดอายุในวันที่ 30 ก.ย. เติมเงินเพื่อใช้งานต่อเนื่อง"),
    ("082-334-9876", "ขอบคุณมากสำหรับมื้อเย็นวันนี้ ถึงบ้านปลอดภัยแล้วจ้า"),
    ("SCB_Alert", "SCB: ชำระเงินสำเร็จ 450.00 บาท ที่ร้าน Tops Market ผ่าน QR Code"),
    
    # Scam / Phishing
    ("SMS_ALERT", "ยินดีด้วย! คุณได้รับสิทธิ์ กู้งิuด่วu 50,000 บ. คลิก http://bit.ly/loan999"),
    ("MEA_Refund", "ด ่ ว น ร ั บ เ ง ิ น คืนค่าประกันมิเตอร์ไฟฟ้า กฟภ. 3,500 บ. กด lin.ee/m-pea"),
    ("WinPrize", "W i n f r e e 1000 cash prize claim today at http://prize-winner.xyz"),
    ("ThaiPost_Tax", "ไปรษณีย์ไทย: พัสดุหมายเลข TH928104 ติดค้างศุลกากร ชำระภาษีที่ http://thaipost-tax.top"),
    ("Bank_Security", "ธนาคารกสิกรไทย: ตรวจพบการโอนเงินผิดปกติ บัญชีถูกระงับ ยืนยันตัวตน http://k-verify-online.com"),
    ("Slot_VIP", "แจกเครดิตฟรี 300 บาท ไม่ต้องฝากก่อน ถอนได้จริง เว็บตรง 100% คลิก bit.ly/slot-bonus"),
    ("Gov_Tax", "กรมสรรพากร: คุณมีเงินภาษีคืนค้างอยู่ 7,500 บ. ติดต่อเจ้าหน้าที่คลิก http://rd-refund.cc"),
    ("Flash_Update", "Flash Express: ไม่สามารถนำจ่ายพัสดุได้เนื่องจากที่อยู่ไม่ถูกต้อง อัปเดต http://flash-check.club"),
    ("Urgent_Loan", "Uริการเงินสดทันใจ ไม่เช็คแบล็กลิสต์ โอนเข้าบัญชีทันที 30,000 บ. แอดไลน์ @fastloan"),
]


def gateway_stream(request):
    """
    SSE Endpoint: จำลองไฟร์วอลล์โทรคมนาคม (Live Telco Gateway)
    สตรีมการคัดกรองข้อความ SMS เข้า-ออกแบบสด ๆ
    URL: /gateway_stream/
    """
    predictor = get_predictor()

    def stream():
        # สลับลำดับข้อความ
        traffic = list(SAMPLE_TRAFFIC)
        random.shuffle(traffic)

        total_count = 0
        blocked_count = 0
        passed_count = 0

        for sender, msg in traffic:
            time.sleep(random.uniform(0.6, 1.2))  # จำลองจังหวะที่ข้อความวิ่งเข้ามา
            t0 = time.time()
            res = predictor.predict(msg)
            latency_ms = round((time.time() - t0) * 1000, 1)

            total_count += 1
            if res['is_scam']:
                blocked_count += 1
                action = "BLOCKED"
            else:
                passed_count += 1
                action = "PASSED"

            payload = {
                'id': total_count,
                'sender': sender,
                'raw_text': msg,
                'cleaned_text': res['cleaned_text'],
                'action': action,
                'is_scam': res['is_scam'],
                'risk_score': res['risk_score'],
                'risk_level': res['risk_level'],
                'has_obfuscation': res['has_obfuscation'],
                'latency_ms': latency_ms,
                'stats': {
                    'total': total_count,
                    'blocked': blocked_count,
                    'passed': passed_count,
                    'block_rate': round((blocked_count / total_count) * 100, 1)
                }
            }
            yield f"data: {json.dumps(payload)}\n\n"

        yield "data: {\"finished\": true}\n\n"

    response = StreamingHttpResponse(stream(), content_type='text/event-stream')
    response['Cache-Control'] = 'no-cache'
    response['X-Accel-Buffering'] = 'no'
    return response


@csrf_exempt
def inspect_api(request):
    """
    API สำหรับการทดสอบข้อความเดี่ยวแบบ Interactive
    URL: /api/inspect/
    """
    if request.method == 'POST':
        try:
            if request.content_type == 'application/json':
                body = json.loads(request.body.decode('utf-8'))
                text = body.get('text', '')
            else:
                text = request.POST.get('text', '')
        except Exception:
            text = request.POST.get('text', '')
    else:
        text = request.GET.get('text', '')

    if not text.strip():
        return JsonResponse({'error': 'กรุณากรอกข้อความที่ต้องการทดสอบ'}, status=400)

    predictor = get_predictor()
    t0 = time.time()
    result = predictor.predict(text)
    latency_ms = round((time.time() - t0) * 1000, 1)
    result['latency_ms'] = latency_ms
    return JsonResponse(result)
