#!/usr/bin/env python3
"""
Launcher script for AI SMS Scam & Phishing Firewall Dashboard.
Automatically detects a free port (8000, 8001, 8002, 8888, etc.) and starts Django.
"""

import os
import sys
import socket
import subprocess

CANDIDATE_PORTS = [8000, 8001, 8002, 8081, 8888, 5000, 3000]


def is_port_available(port: int) -> bool:
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    try:
        s.bind(('0.0.0.0', port))
        s.close()
        return True
    except OSError:
        return False


def find_free_port() -> int:
    for port in CANDIDATE_PORTS:
        if is_port_available(port):
            return port
    # Fallback to random free port
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.bind(('0.0.0.0', 0))
    port = s.getsockname()[1]
    s.close()
    return port


def main():
    port = find_free_port()
    base_dir = os.path.dirname(os.path.abspath(__file__))
    manage_py = os.path.join(base_dir, 'manage.py')
    python_bin = sys.executable

    print("\n" + "=" * 65)
    print(" 🚀 STARTING AI SMS SCAM & PHISHING FIREWALL DASHBOARD")
    print("=" * 65)
    print(f" [+] ตรวจพบพอร์ตว่างที่พร้อมใช้งาน: {port}")
    print(f" [+] เปิดใช้งานเซิร์ฟเวอร์บน: http://127.0.0.1:{port}/")
    print(" [+] กด Ctrl + C เพื่อหยุดการทำงานของเซิร์ฟเวอร์")
    print("=" * 65 + "\n")

    cmd = [python_bin, manage_py, 'runserver', f'0.0.0.0:{port}', '--noreload']
    try:
        subprocess.run(cmd)
    except KeyboardInterrupt:
        print("\n[!] หยุดการทำงานของเซิร์ฟเวอร์เรียบร้อยครับ")


if __name__ == '__main__':
    main()
