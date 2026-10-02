"""
Inference Pipeline & Explainable AI (XAI) Engine
ระบบวิเคราะห์ความเสี่ยง ตรวจจับลิงก์ คำนวณ Attention Weights และสร้าง Actionable Safety Card
"""

import json
import os
import sys
import torch
from typing import Dict, Any, List

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
if BASE_DIR not in sys.path:
    sys.path.insert(0, BASE_DIR)

try:
    from model.model import SpamAttentionBiLSTM
    from model.deobfuscator import Deobfuscator
except (ImportError, ModuleNotFoundError):
    from model import SpamAttentionBiLSTM
    from deobfuscator import Deobfuscator

from data.process_data import TextTokenizer

VOCAB_PATH = os.path.join(BASE_DIR, 'data', 'vocab.json')
WEIGHTS_PATH = os.path.join(BASE_DIR, 'model', 'weights.pth')

# ฐานข้อมูลองค์กรที่มักถูกมิจฉาชีพแอบอ้าง พร้อมเบอร์โทรและช่องทางติดต่อจริง
OFFICIAL_ORGANIZATIONS = [
    {
        'keywords': ['กสิกร', 'kbank', 'k-bank', 'k plus'],
        'name': 'ธนาคารกสิกรไทย (KBank)',
        'official_phone': '02-888-8888',
        'official_web': 'www.kasikornbank.com',
        'policy': 'ธนาคารกสิกรไทยไม่มีนโยบายส่ง SMS แนบลิงก์เพื่อขอข้อมูลส่วนตัวหรือให้ดาวน์โหลดแอป'
    },
    {
        'keywords': ['ไทยพาณิชย์', 'scb', 'scb easy'],
        'name': 'ธนาคารไทยพาณิชย์ (SCB)',
        'official_phone': '02-777-7777',
        'official_web': 'www.scb.co.th',
        'policy': 'ธนาคารไทยพาณิชย์ไม่มีการส่ง SMS แนบลิงก์เพื่อให้กู้เงินหรือยืนยันตัวตน'
    },
    {
        'keywords': ['กรุงไทย', 'ktb', 'krungthai next'],
        'name': 'ธนาคารกรุงไทย (Krungthai)',
        'official_phone': '02-111-1111',
        'official_web': 'krungthai.com',
        'policy': 'กรุงไทยไม่มีนโยบายส่งลิงก์ให้กดปลดล็อกบัญชีผ่านข้อความ SMS'
    },
    {
        'keywords': ['กฟภ', 'pea', 'การไฟฟ้าส่วนภูมิภาค', 'มิเตอร์ไฟฟ้า'],
        'name': 'การไฟฟ้าส่วนภูมิภาค (PEA)',
        'official_phone': '1129',
        'official_web': 'www.pea.co.th',
        'policy': 'กฟภ. ไม่มีนโยบายส่ง SMS คืนเงินประกันมิเตอร์ผ่านลิงก์ ไลน์ หรือเว็บไซต์ภายนอก'
    },
    {
        'keywords': ['กฟน', 'mea', 'การไฟฟ้านครหลวง'],
        'name': 'การไฟฟ้านครหลวง (MEA)',
        'official_phone': '1130',
        'official_web': 'www.mea.or.th',
        'policy': 'MEA ไม่มีนโยบายส่งข้อความเพื่อให้กรอกเลขบัญชีรับเงินค่าประกันคืน'
    },
    {
        'keywords': ['สรรพากร', 'คืนภาษี', 'ภาษีค้างชำระ', 'rd.go.th'],
        'name': 'กรมสรรพากร (Revenue Department)',
        'official_phone': '1161',
        'official_web': 'www.rd.go.th',
        'policy': 'กรมสรรพากรไม่เคยส่ง SMS แจ้งคืนเงินภาษีหรือแนบลิงก์ให้แอดไลน์เจ้าหน้าที่'
    },
    {
        'keywords': ['ไปรษณีย์ไทย', 'thaipost', 'ศุลกากร', 'พัสดุตกค้าง'],
        'name': 'บริษัท ไปรษณีย์ไทย จำกัด',
        'official_phone': '1545',
        'official_web': 'www.thailandpost.co.th',
        'policy': 'ไปรษณีย์ไทยไม่มีบริการให้โอนเงินค่าภาษีศุลกากรหรือค่าปรับผ่านลิงก์ SMS'
    },
    {
        'keywords': ['flash express', 'flash'],
        'name': 'Flash Express',
        'official_phone': '1436',
        'official_web': 'www.flashexpress.co.th',
        'policy': 'Flash Express ไม่ส่งลิงก์ให้ดาวน์โหลดไฟล์ .apk หรือให้โอนเงินค่าส่งซ้ำ'
    }
]


class ScamPredictor:
    """
    คลาสศูนย์รวมระบบ Inference และการวิเคราะห์ภัยคุกคาม
    """

    def __init__(self, weights_path: str = WEIGHTS_PATH, vocab_path: str = VOCAB_PATH):
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')

        # โหลดคลังคำศัพท์
        if os.path.exists(vocab_path):
            with open(vocab_path, 'r', encoding='utf-8') as f:
                self.vocab = json.load(f)
        else:
            self.vocab = {'<PAD>': 0, '<UNK>': 1, '<URL>': 2, '<NUM>': 3}

        self.tokenizer = TextTokenizer(vocab=self.vocab)

        # โหลดโมเดล
        self.model = SpamAttentionBiLSTM(
            vocab_size=len(self.vocab),
            embed_dim=64,
            hidden_dim=64,
            num_layers=2,
            dropout=0.3
        )

        if os.path.exists(weights_path):
            self.model.load_state_dict(torch.load(weights_path, map_location=self.device))
            self.model_loaded = True
        else:
            self.model_loaded = False

        self.model.to(self.device)
        self.model.eval()

    def identify_impersonation(self, text: str) -> Dict[str, Any]:
        """ตรวจสอบว่ามีการแอบอ้างสถาบัน/หน่วยงานใดหรือไม่"""
        text_lower = text.lower()
        for org in OFFICIAL_ORGANIZATIONS:
            for kw in org['keywords']:
                if kw in text_lower:
                    return {
                        'detected': True,
                        'name': org['name'],
                        'official_phone': org['official_phone'],
                        'official_web': org['official_web'],
                        'policy': org['policy']
                    }

        # ถ้าไม่ระบุเจาะจง ให้คำแนะนำของตำรวจไซเบอร์
        return {
            'detected': False,
            'name': 'ศูนย์ปราบปรามอาชญากรรมทางเทคโนโลยีสารสนเทศ (บช.สอท.)',
            'official_phone': '1441 (สายด่วนตำรวจไซเบอร์)',
            'official_web': 'www.thaipoliceonline.go.th',
            'policy': 'หากไม่แน่ใจ ห้ามกดลิงก์ ห้ามโอนเงิน และห้ามติดตั้งแอปพลิเคชันใดๆ เด็ดขาด'
        }

    def predict(self, raw_text: str, max_len: int = 50) -> Dict[str, Any]:
        """
        วิเคราะห์ข้อความ SMS แบบครบวงจร:
        1. De-obfuscation (ถอดรหัสคำแฝง)
        2. Tokenization & Attention weights
        3. Model prediction
        4. Link analysis
        5. Actionable Safety Card
        """
        # 1. ถอดรหัสคำอำพราง
        deobf_res = Deobfuscator.process(raw_text)
        cleaned_text = deobf_res['cleaned_text']

        # 2. Tokenize
        tokens = self.tokenizer.tokenize(cleaned_text)
        token_indices = self.tokenizer.encode(cleaned_text, max_len=max_len)
        input_tensor = torch.tensor([token_indices], dtype=torch.long).to(self.device)

        # 3. Model Inference
        with torch.no_grad():
            prob_tensor, attn_weights_tensor = self.model(input_tensor, return_attention=True)
            prob = prob_tensor.item()
            attn_weights = attn_weights_tensor[0].cpu().tolist()

        # ปรับความเสี่ยงถ้าพบลิงก์อันตรายหรือคำอำพรางตัวชัดเจน (Heuristic Booster)
        if deobf_res['suspicious_url_count'] > 0:
            prob = max(prob, 0.85)
        if deobf_res['has_obfuscation'] and prob > 0.4:
            prob = min(prob + 0.15, 0.99)

        # 4. จับคู่น้ำหนัก Attention กับแต่ละ Token
        num_tokens = min(len(tokens), max_len)
        active_weights = attn_weights[:num_tokens]
        # Normalize weights ให้แสดงเป็นเปอร์เซ็นต์
        max_w = max(active_weights) if active_weights and max(active_weights) > 0 else 1.0
        normalized_weights = [round((w / max_w) * 100, 1) for w in active_weights]

        tokens_highlight = []
        for i in range(num_tokens):
            tokens_highlight.append({
                'token': tokens[i],
                'weight': normalized_weights[i],
                'is_critical': normalized_weights[i] >= 65.0
            })

        # 5. ระดับความเสี่ยง
        risk_pct = round(prob * 100, 2)
        if risk_pct >= 80.0:
            risk_level = 'CRITICAL'
            risk_color = 'red'
            status_text = 'ตรวจพบมิจฉาชีพความเสี่ยงสูง (Scam Detected)'
        elif risk_pct >= 50.0:
            risk_level = 'HIGH'
            risk_color = 'orange'
            status_text = 'ข้อความน่าสงสัย (Suspicious / Potential Scam)'
        elif risk_pct >= 25.0:
            risk_level = 'MEDIUM'
            risk_color = 'yellow'
            status_text = 'ข้อความทั่วไปที่ควรระวัง (Caution)'
        else:
            risk_level = 'SAFE'
            risk_color = 'green'
            status_text = 'ข้อความปลอดภัย (Legitimate / Ham)'

        # 6. Safety Card
        safety_card = self.identify_impersonation(cleaned_text)

        return {
            'original_text': raw_text,
            'cleaned_text': cleaned_text,
            'is_scam': prob >= 0.5,
            'risk_score': risk_pct,
            'risk_level': risk_level,
            'risk_color': risk_color,
            'status_text': status_text,
            'has_obfuscation': deobf_res['has_obfuscation'],
            'obfuscation_mods': deobf_res['modifications'],
            'suspicious_urls': deobf_res['urls_info'],
            'tokens_highlight': tokens_highlight,
            'safety_card': safety_card,
            'model_loaded': self.model_loaded
        }


# Quick test & CLI execution
if __name__ == '__main__':
    predictor = ScamPredictor()
    if len(sys.argv) > 1:
        sms_text = " ".join(sys.argv[1:])
        res = predictor.predict(sms_text)
        print("=" * 60)
        print(f"Input: {res['original_text']}")
        print(f"Status: {res['status_text']} ({res['risk_score']}%)")
        print(f"Risk Level: {res['risk_level']}")
        print(f"Obfuscation Detected: {res['has_obfuscation']}")
        if res['has_obfuscation']:
            print(f"Cleaned Text: {res['cleaned_text']}")
        if res['suspicious_urls']:
            print("Suspicious URLs/Channels:")
            for u in res['suspicious_urls']:
                print(f"  - {u['url']} -> {', '.join(u['reasons'])}")
        print(f"Safety Hotline: {res['safety_card']['name']} - {res['safety_card']['official_phone']}")
        print("=" * 60)
    else:
        test_cases = [
            "ยินดีด้วย! คุณได้รับสิทธิ์ กู้งิuด่วu 50,000 บ. คลิก http://bit.ly/loan999",
            "SCB: รหัส OTP คือ 193847 ใช้ยืนยันการทำธุรกรรมบัตรเครดิต ยอด 1,250.00 บาท",
            "ด ่ ว น ร ั บ เ ง ิ น คืนค่าประกันมิเตอร์ไฟฟ้า กฟภ. กด lin.ee/m-pea",
        ]
        for t in test_cases:
            res = predictor.predict(t)
            print("=" * 60)
            print(f"Input: {res['original_text']}")
            print(f"Status: {res['status_text']} ({res['risk_score']}%)")
            print(f"Obfuscation Detected: {res['has_obfuscation']}")
            print(f"Safety Hotline: {res['safety_card']['name']} - {res['safety_card']['official_phone']}")
