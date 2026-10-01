"""
Adversarial Camouflage Defense (De-obfuscator)
โมดูลถอดรหัสข้อความอำพรางตัวของมิจฉาชีพ

มิจฉาชีพมักใช้เทคนิคหลบเลี่ยง AI/Regex ตัวกรองคำ (Adversarial Evasion):
1. เคาะวรรคคำ: "รั บ เ งิ น", "กู้ ด่ ว น", "f r e e"
2. ใช้ตัวอักษรแทนที่ (Leet Speak): "กู้งิu", "Uริการ", "08X-XXX-XXXX"
3. อักขระซ่อน/Zero-width characters: \\u200b, \\u200c
4. ลิงก์ย่อหรือโดเมนอันตราย: bit.ly, tinyurl, .xyz, .top
"""

import re
from typing import Dict, List, Tuple

# ตารางจับคู่อักษรแทนที่ยอดนิยมของมิจฉาชีพ (Leet / Obfuscated characters to standard Thai/English)
CHAR_REPLACEMENTS = {
    # ละตินแทนไทย
    'u': 'น',
    'U': 'บ',
    'v': 'ง',
    'V': 'ว',
    'o': 'อ',
    'O': 'อ',
    'a': 'า',
    'b': 'บ',
    'c': 'ค',
    'd': 'ด',
    'e': 'เ',
    'i': 'ิ',
    'j': 'จ',
    'k': 'ก',
    'l': 'ล',
    'm': 'ม',
    'n': 'น',
    'p': 'ป',
    'r': 'ร',
    's': 'ส',
    't': 'ต',
    'w': 'ว',
    'y': 'ย',
    # อักขระพิเศษแทนอักษร
    '@': 'a',
    '$': 's',
    '0': 'o',
    '1': 'i',
    '3': 'e',
    '4': 'a',
    '5': 's',
    '7': 't',
    '8': 'b',
}

# รายชื่อ Shortener URLs และ TLDs อันตราย
SUSPICIOUS_DOMAINS = [
    'bit.ly', 'tinyurl.com', 't.co', 'is.gd', 'cutt.ly', 'rb.gy',
    'shorturl.at', 'lin.ee', 'line.me', 'v.gd', 't.me'
]

SUSPICIOUS_TLDS = [
    '.xyz', '.top', '.club', '.online', '.vip', '.live', '.cc',
    '.work', '.rest', '.app', '.icu', '.cam', '.cfd'
]

# คำสแปมยอดนิยมที่มักถูกเคาะวรรคหลบตัวกรอง
SPACED_KEYWORDS_THAI = [
    ('ร ั บ เ ง ิ น', 'รับเงิน'),
    ('ก ู ้ เ ง ิ น', 'กู้เงิน'),
    ('ก ู ้ ด ่ ว น', 'กู้ด่วน'),
    ('ด ่ ว น', 'ด่วน'),
    ('เ ค ร ด ิ ต ฟ ร ี', 'เครดิตฟรี'),
    ('ส ิ น เ ช ื ่ อ', 'สินเชื่อ'),
    ('อ น ุ ม ั ต ิ', 'อนุมัติ'),
    ('ค ล ิ ก', 'คลิก'),
    ('เ ว ็ บ ต ร ง', 'เว็บตรง'),
    ('โ ค ว ต า', 'โควตา'),
]

SPACED_KEYWORDS_EN = [
    ('f r e e', 'free'),
    ('w i n', 'win'),
    ('c l i c k', 'click'),
    ('l o a n', 'loan'),
    ('c a s h', 'cash'),
    ('u r g e n t', 'urgent'),
    ('p r i z e', 'prize'),
    ('c l a i m', 'claim'),
]


class Deobfuscator:
    """
    คลาสสำหรับตรวจจับและถอดรหัสข้อความที่ถูกอำพรางตัว
    """

    @staticmethod
    def strip_invisible_characters(text: str) -> str:
        """ลบอักขระซ่อนและ Zero-width characters"""
        # \u200B-\u200D, \uFEFF, \u00A0
        cleaned = re.sub(r'[\u200B-\u200D\uFEFF\u00A0\u2060]', '', text)
        # ลบวรรณยุกต์/สระซ้ำซ้อนผิดปกติ เช่น กู้้้้้
        cleaned = re.sub(r'([่-๋ะ-ู])\1+', r'\1', cleaned)
        return cleaned

    @staticmethod
    def despace_thai_words(text: str) -> str:
        """สมานคำภาษาไทยที่ถูกเคาะเว้นวรรคทีละตัวอักษร โดยไม่กระทบช่องว่างระหว่างประโยคปกติ"""
        result = text
        # แปลงคำที่มีแพทเทิร์นชัดเจนก่อน
        for spaced, fixed in SPACED_KEYWORDS_THAI:
            result = result.replace(spaced, fixed)

        # สมานพยัญชนะไทยเดี่ยวที่มีการเคาะวรรคติดต่อกัน (เช่น "ร ั บ เ ง ิ น" หรือ "ก ู ้")
        # มองหาลำดับของตัวอักษรเดี่ยวที่มีช่องว่างคั่นอย่างน้อย 2 ตัวอักษรขึ้นไป
        def despace_match(match):
            return match.group(0).replace(' ', '')

        # ตรวจจับพยัญชนะ/สระเดี่ยวๆ คั่นด้วยช่องว่าง 2 ครั้งขึ้นไป เช่น "ร ั บ" หรือ "เ ง ิ น"
        spaced_char_pattern = re.compile(r'([ก-ฮะ-์]\s+){2,}[ก-ฮะ-์]')
        result = spaced_char_pattern.sub(despace_match, result)
        return result

    @staticmethod
    def despace_english_words(text: str) -> str:
        """สมานคำภาษาอังกฤษที่ถูกเคาะเว้นวรรค เช่น f r e e -> free"""
        result = text
        for spaced, fixed in SPACED_KEYWORDS_EN:
            # Case insensitive replace
            pattern = re.compile(re.escape(spaced), re.IGNORECASE)
            result = pattern.sub(fixed, result)
        return result

    @staticmethod
    def normalize_leet_speak(text: str) -> Tuple[str, List[str]]:
        """
        ตรวจจับและแปลง Leet speak ในบริบทคำไทย
        เช่น 'กู้งิu' -> 'กู้เงิน', 'Uริการ' -> 'บริการ'
        """
        modifications = []
        words = text.split()
        normalized_words = []

        for word in words:
            original_word = word
            # เช็คว่ามีตัวอักษรไทยปนกับตัวละตินบางตัวหรือไม่ (Hybrid Obfuscation)
            has_thai = bool(re.search(r'[ก-ฮะ-์]', word))
            has_latin = bool(re.search(r'[a-zA-Z0-9]', word))

            if has_thai and has_latin:
                new_word = word
                # แทนที่ตัวละตินเฉพาะจุดที่ปนในคำไทย
                for char, repl in CHAR_REPLACEMENTS.items():
                    if char in new_word:
                        new_word = new_word.replace(char, repl)
                
                # เคสเฉพาะทาง เช่น "งิu" -> "เงิน"
                new_word = new_word.replace('งิu', 'เงิน').replace('งิU', 'เงิน')
                new_word = new_word.replace('งิu', 'เงิน').replace('งิu', 'เงิน')

                if new_word != original_word:
                    modifications.append(f"{original_word} -> {new_word}")
                    word = new_word

            normalized_words.append(word)

        return ' '.join(normalized_words), modifications

    @staticmethod
    def extract_and_analyze_urls(text: str) -> List[Dict[str, any]]:
        """ดึงและวิเคราะห์ลิงก์ในข้อความว่าเป็นลิงก์ต้องสงสัยหรือไม่"""
        url_pattern = r'(https?://[^\s]+|www\.[^\s]+|[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?:/[^\s]*)?)'
        urls = re.findall(url_pattern, text)
        analysis = []

        for url in urls:
            is_suspicious = False
            reasons = []

            # เช็ค URL Shortener
            for shortener in SUSPICIOUS_DOMAINS:
                if shortener in url.lower():
                    is_suspicious = True
                    reasons.append(f"ใช้โดเมนย่อลิงก์ปิดบังปลายทาง ({shortener})")
                    break

            # เช็ค TLDs ผิดปกติ
            for tld in SUSPICIOUS_TLDS:
                if tld in url.lower():
                    is_suspicious = True
                    reasons.append(f"ใช้นามสกุลโดเมนความเสี่ยงสูง ({tld})")
                    break

            # เช็ค HTTP ธรรมดา (ไม่มี S)
            if url.startswith('http://'):
                reasons.append("การเชื่อมต่อไม่เข้ารหัสความปลอดภัย (HTTP)")

            analysis.append({
                'url': url,
                'is_suspicious': is_suspicious,
                'reasons': reasons
            })

        return analysis

    @classmethod
    def process(cls, raw_text: str) -> Dict[str, any]:
        """
        ประมวลผลข้อความดิบผ่านทุก Layer ของ De-obfuscator
        คืนค่าข้อความที่คลีนแล้ว และข้อมูลรายงานสิ่งที่ตรวจพบ
        """
        # 1. ลบอักขระซ่อน
        step1 = cls.strip_invisible_characters(raw_text)

        # 2. ถอดรหัส Leet speak
        step2, leet_mods = cls.normalize_leet_speak(step1)

        # 3. สมานคำที่ถูกเคาะเว้นวรรค (ทั้งไทยและอังกฤษ)
        step3 = cls.despace_english_words(step2)
        cleaned_text = cls.despace_thai_words(step3)

        # 4. วิเคราะห์ลิงก์
        urls_info = cls.extract_and_analyze_urls(raw_text)

        has_obfuscation = (raw_text.strip() != cleaned_text.strip()) or len(leet_mods) > 0

        return {
            'original_text': raw_text,
            'cleaned_text': cleaned_text,
            'has_obfuscation': has_obfuscation,
            'modifications': leet_mods,
            'urls_info': urls_info,
            'suspicious_url_count': sum(1 for u in urls_info if u['is_suspicious'])
        }


# Quick test interface
if __name__ == '__main__':
    test_cases = [
        "ยินดีด้วย! คุณได้รับสิทธิ์ กู้งิuด่วu 50,000 บ. คลิก http://bit.ly/loan999",
        "ด ่ ว น ร ั บ เ ง ิ น คืนค่าประกันมิเตอร์ไฟฟ้า กด line.me/ti/p/~scam",
        "W i n f r e e 1000 cash prize claim at http://prize.xyz",
        "รหัส OTP ของคุณคือ 849201 ใช้สำหรับยืนยันการเข้าสู่ระบบ ธนาคารกสิกรไทย",
    ]

    print("=== ทดสอบโมดูล De-obfuscator ===")
    for t in test_cases:
        res = Deobfuscator.process(t)
        print(f"\n[ต้นฉบับ]: {res['original_text']}")
        print(f"[คลีนแล้ว]: {res['cleaned_text']}")
        print(f"[พบคำอำพราง]: {res['has_obfuscation']} | ลิงก์ต้องสงสัย: {res['suspicious_url_count']}")
