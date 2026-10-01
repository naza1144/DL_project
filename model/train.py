"""
Model Training Pipeline for SpamAttentionBiLSTM
ฝึกสอนโมเดล BiLSTM + Attention, ประเมินผล Metrics และบันทึก weights.pth
รองรับทั้งการรันผ่าน CLI ปกติ และการทำงานเป็น Generator สำหรับ Django SSE Real-time Stream
"""

import json
import os
import sys
import torch
import torch.nn as nn
from torch.utils.data import Dataset, DataLoader
from sklearn.metrics import accuracy_score, precision_recall_fscore_support, confusion_matrix

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
if BASE_DIR not in sys.path:
    sys.path.insert(0, BASE_DIR)

try:
    from model.model import SpamAttentionBiLSTM
except (ImportError, ModuleNotFoundError):
    from model import SpamAttentionBiLSTM

from data.process_data import TextTokenizer

DATA_DIR = os.path.join(BASE_DIR, 'data')
MODEL_DIR = os.path.join(BASE_DIR, 'model')
VOCAB_PATH = os.path.join(DATA_DIR, 'vocab.json')
TRAIN_PATH = os.path.join(DATA_DIR, 'train.json')
TEST_PATH = os.path.join(DATA_DIR, 'test.json')
WEIGHTS_PATH = os.path.join(MODEL_DIR, 'weights.pth')
METRICS_PATH = os.path.join(MODEL_DIR, 'metrics.json')


class SMSDataset(Dataset):
    """PyTorch Dataset สำหรับแปลงข้อความเป็นเวกเตอร์ตัวเลข"""

    def __init__(self, data_path: str, vocab: dict, max_len: int = 50):
        with open(data_path, 'r', encoding='utf-8') as f:
            self.samples = json.load(f)
        self.tokenizer = TextTokenizer(vocab=vocab)
        self.max_len = max_len

    def __len__(self):
        return len(self.samples)

    def __getitem__(self, idx):
        item = self.samples[idx]
        tokens = self.tokenizer.encode(item['text'], max_len=self.max_len)
        return torch.tensor(tokens, dtype=torch.long), torch.tensor(item['label'], dtype=torch.float32)


def get_data_loaders(batch_size: int = 64, max_len: int = 50):
    """สร้าง Train และ Test DataLoader"""
    with open(VOCAB_PATH, 'r', encoding='utf-8') as f:
        vocab = json.load(f)

    train_ds = SMSDataset(TRAIN_PATH, vocab, max_len=max_len)
    test_ds = SMSDataset(TEST_PATH, vocab, max_len=max_len)

    train_loader = DataLoader(train_ds, batch_size=batch_size, shuffle=True)
    test_loader = DataLoader(test_ds, batch_size=batch_size, shuffle=False)

    return train_loader, test_loader, len(vocab)


def evaluate_model(model, test_loader, criterion, device):
    """ประเมินผลโมเดลบนชุดข้อมูลทดสอบ"""
    model.eval()
    total_loss = 0.0
    all_preds = []
    all_targets = []

    with torch.no_grad():
        for x, y in test_loader:
            x, y = x.to(device), y.to(device)
            probs = model(x).squeeze(-1)
            loss = criterion(probs, y)
            total_loss += loss.item() * len(y)

            preds = (probs >= 0.5).float()
            all_preds.extend(preds.cpu().tolist())
            all_targets.extend(y.cpu().tolist())

    avg_loss = total_loss / len(test_loader.dataset)
    acc = accuracy_score(all_targets, all_preds)
    p, r, f1, _ = precision_recall_fscore_support(all_targets, all_preds, average='macro', zero_division=0)
    cm = confusion_matrix(all_targets, all_preds).tolist()

    return {
        'loss': round(avg_loss, 4),
        'accuracy': round(acc, 4),
        'precision': round(p, 4),
        'recall': round(r, 4),
        'f1': round(f1, 4),
        'confusion_matrix': cm
    }


def train_epochs_generator(num_epochs: int = 10, batch_size: int = 64, lr: float = 0.001):
    """
    Generator Function สำหรับสตรีมผลการเทรนราย Epoch (ใช้ใน Django SSE)
    """
    device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
    train_loader, test_loader, vocab_size = get_data_loaders(batch_size=batch_size)

    model = SpamAttentionBiLSTM(vocab_size=vocab_size, embed_dim=64, hidden_dim=64, num_layers=2, dropout=0.3)
    model.to(device)

    criterion = nn.BCELoss()
    optimizer = torch.optim.Adam(model.parameters(), lr=lr)

    best_f1 = 0.0
    history = []

    for epoch in range(1, num_epochs + 1):
        model.train()
        train_loss = 0.0
        train_correct = 0

        for x, y in train_loader:
            x, y = x.to(device), y.to(device)
            optimizer.zero_grad()
            probs = model(x).squeeze(-1)
            loss = criterion(probs, y)
            loss.backward()
            torch.nn.utils.clip_grad_norm_(model.parameters(), max_norm=1.0)
            optimizer.step()

            train_loss += loss.item() * len(y)
            train_correct += ((probs >= 0.5).float() == y).sum().item()

        avg_train_loss = train_loss / len(train_loader.dataset)
        train_acc = train_correct / len(train_loader.dataset)

        # ประเมินผลบน Validation/Test
        val_metrics = evaluate_model(model, test_loader, criterion, device)

        # บันทึกโมเดลที่ดีที่สุด
        if val_metrics['f1'] > best_f1:
            best_f1 = val_metrics['f1']
            torch.save(model.state_dict(), WEIGHTS_PATH)

        epoch_data = {
            'epoch': epoch,
            'total_epochs': num_epochs,
            'train_loss': round(avg_train_loss, 4),
            'train_acc': round(train_acc, 4),
            'val_loss': val_metrics['loss'],
            'val_acc': val_metrics['accuracy'],
            'val_f1': val_metrics['f1'],
            'is_best': val_metrics['f1'] == best_f1
        }
        history.append(epoch_data)

        # ส่งข้อมูลออกเป็น Generator สตรีม
        yield epoch_data

    # บันทึกประวัติสรุป
    with open(METRICS_PATH, 'w', encoding='utf-8') as f:
        json.dump({
            'history': history,
            'final_metrics': val_metrics,
            'best_f1': best_f1
        }, f, indent=2)


def run_standalone_training(epochs: int = 10):
    """สั่งเทรนโมเดลแบบ Standalone CLI"""
    print(f"[*] เริ่มต้นฝึกสอนโมเดล SpamAttentionBiLSTM ({epochs} Epochs)...")
    for update in train_epochs_generator(num_epochs=epochs):
        star = " ★ BEST" if update['is_best'] else ""
        print(f"Epoch [{update['epoch']:02d}/{update['total_epochs']:02d}] "
              f"Loss: {update['train_loss']:.4f} | "
              f"Acc: {update['train_acc']*100:.2f}% | "
              f"Val Loss: {update['val_loss']:.4f} | "
              f"Val Acc: {update['val_acc']*100:.2f}% | "
              f"Val F1: {update['val_f1']:.4f}{star}")

    print(f"\n[+] ฝึกสอนเสร็จสิ้น บันทึกน้ำหนักโมเดลที่ดีที่สุดที่: {WEIGHTS_PATH}")
    print(f"[+] บันทึกประวัติการเทรนที่: {METRICS_PATH}")


if __name__ == '__main__':
    run_standalone_training(epochs=10)
