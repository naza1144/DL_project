#!/usr/bin/env python3
"""
CLI Tool: ตรวจสอบข้อความ SMS มิจฉาชีพแบบ Interactive และ Standalone
ใช้งานได้ทันทีทั้งแบบส่งข้อความทาง Argument หรือโหมดพิมพ์โต้ตอบ (Interactive Shell)
"""

import sys
import os

# ตั้งค่า path
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
if BASE_DIR not in sys.path:
    sys.path.insert(0, BASE_DIR)

from model.infer import ScamPredictor

# ANSI Colors
RED = "\033[91m"
GREEN = "\033[92m"
YELLOW = "\033[93m"
BLUE = "\033[94m"
MAGENTA = "\033[95m"
CYAN = "\033[96m"
WHITE = "\033[97m"
BOLD = "\033[1m"
RESET = "\033[0m"


def print_result(res: dict):
    print("\n" + "=" * 65)
    risk_color = RED if res['risk_level'] == 'CRITICAL' else (YELLOW if res['risk_level'] == 'HIGH' else GREEN)
    
    print(f"{BOLD}ผลการวิเคราะห์ความเสี่ยง (Risk Assessment):{RESET}")
    print(f"  สถานะ: {risk_color}{BOLD}{res['status_text']}{RESET}")
    print(f"  คะแนนความเสี่ยง: {risk_color}{BOLD}{res['risk_score']}%{RESET} (ระดับ: {res['risk_level']})")
    
    # การอำพรางตัว
    if res['has_obfuscation']:
        print(f"\n{YELLOW}{BOLD}[!] ตรวจพบการอำพรางตัว (Adversarial Camouflage Detected):{RESET}")
        for mod in res['obfuscation_mods']:
            print(f"   • ถอดรหัสคำแฝง: {mod}")
        print(f"   • ข้อความที่ผ่านการคลีนแล้ว: {CYAN}{res['cleaned_text']}{RESET}")
    
    # ลิงก์ต้องสงสัย
    if res['suspicious_urls']:
        print(f"\n{RED}{BOLD}[!] ตรวจพบลิงก์หรือช่องทางติดต่อต้องสงสัย:{RESET}")
        for u in res['suspicious_urls']:
            print(f"   • ช่องทาง: {u['url']}")
            for r in u['reasons']:
                print(f"     - {r}")

    # คำสำคัญบ่งชี้ความเสี่ยง (Explainable AI / Attention)
    crit_tokens = [t for t in res['tokens_highlight'] if t['is_critical']]
    if crit_tokens:
        print(f"\n{MAGENTA}{BOLD}[🔍] คำที่ AI ให้ความสำคัญสูงสุด (Explainable AI Attention):{RESET}")
        token_strs = [f"{t['token']} ({t['weight']}%)" for t in crit_tokens]
        print(f"   {', '.join(token_strs)}")

    # Actionable Safety Card
    card = res['safety_card']
    print(f"\n{CYAN}{BOLD}[🛡️] คำแนะนำเพื่อความปลอดภัย (Actionable Safety Card):{RESET}")
    print(f"   หน่วยงาน: {BOLD}{card['name']}{RESET}")
    print(f"   เบอร์ติดต่อทางการ: {GREEN}{BOLD}{card['official_phone']}{RESET}")
    print(f"   เว็บไซต์ทางการ: {card['official_web']}")
    print(f"   ข้อควรระวัง: {YELLOW}{card['policy']}{RESET}")
    print("=" * 65 + "\n")


def interactive_mode(predictor: ScamPredictor):
    print(f"{BOLD}{CYAN}================================================================={RESET}")
    print(f"{BOLD}{WHITE}     AI SMS SCAM & PHISHING FIREWALL - INTERACTIVE TESTER        {RESET}")
    print(f"{BOLD}{CYAN}================================================================={RESET}")
    print("พิมพ์หรือคัดลอกข้อความ SMS ที่ต้องการตรวจสอบ แล้วกด Enter")
    print("พิมพ์ 'q' หรือ 'exit' เพื่อออกจากโปรแกรม\n")

    while True:
        try:
            sms_text = input(f"{BOLD}{BLUE}ป้อนข้อความ SMS > {RESET}").strip()
            if not sms_text:
                continue
            if sms_text.lower() in ('q', 'exit', 'quit'):
                print("ขอบคุณที่ใช้งานระบบความปลอดภัยครับ!")
                break
            
            res = predictor.predict(sms_text)
            print_result(res)
        except (KeyboardInterrupt, EOFError):
            print("\nขอบคุณที่ใช้งานระบบความปลอดภัยครับ!")
            break


def main():
    predictor = ScamPredictor()
    if len(sys.argv) > 1:
        sms_text = " ".join(sys.argv[1:])
        res = predictor.predict(sms_text)
        print_result(res)
    else:
        interactive_mode(predictor)


if __name__ == '__main__':
    main()
