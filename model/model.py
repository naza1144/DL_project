"""
SpamAttentionBiLSTM Architecture
สถาปัตยกรรมโมเดล Deep Learning สำหรับจำแนกข้อความ SMS มิจฉาชีพ
ออกแบบตามข้อกำหนดสัปดาห์ที่ 15 ของรายวิชา Deep Learning
"""

import torch
import torch.nn as nn
import torch.nn.functional as F


class SelfAttention(nn.Module):
    """
    Self-Attention Mechanism with Attention Dropout
    คำนวณค่าน้ำหนักความสำคัญ (Attention Weights) ของแต่ละ Token ในประโยค
    พร้อมระบบ Attention Dropout เพื่อป้องกัน Attention Collapse / Overconfidence
    """

    def __init__(self, hidden_dim: int, dropout: float = 0.1):
        super().__init__()
        self.projection = nn.Sequential(
            nn.Linear(hidden_dim, 64),
            nn.Tanh(),
            nn.Linear(64, 1)
        )
        self.attn_dropout = nn.Dropout(dropout)

    def forward(self, lstm_outputs, mask=None):
        """
        Input:
            lstm_outputs: (batch_size, seq_len, hidden_dim)
            mask: (batch_size, seq_len) สำหรับคัดกรอง <PAD>
        Output:
            context_vector: (batch_size, hidden_dim)
            attention_weights: (batch_size, seq_len)
        """
        # (batch_size, seq_len, 1)
        energy = self.projection(lstm_outputs)
        scores = energy.squeeze(-1)

        if mask is not None:
            scores = scores.masked_fill(mask == 0, -1e9)

        attention_weights = F.softmax(scores, dim=-1)
        # Attention Dropout ระหว่างเทรนเพื่อป้องกัน Attention กระจุกตัว
        dropped_weights = self.attn_dropout(attention_weights)
        context_vector = torch.sum(lstm_outputs * dropped_weights.unsqueeze(-1), dim=1)

        return context_vector, attention_weights


class SpamAttentionBiLSTM(nn.Module):
    """
    Text Classifier using Bidirectional LSTM with Self-Attention

    Architecture:
    1. Embedding Layer: แปลง Token IDs เป็น Dense Vectors (vocab_size -> embed_dim)
    2. Bidirectional LSTM: 2 Layers เรียนรู้บริบททั้งหน้าและหลัง (embed_dim -> hidden_dim * 2)
    3. Self-Attention Layer: สกัดน้ำหนักความสนใจรายคำ (hidden_dim * 2 -> context_vector)
    4. Dropout Layer: ป้องกัน Overfitting (p=0.3)
    5. Fully Connected Layers: Dense(hidden_dim*2, 64) -> ReLU -> Dense(64, 1) -> Sigmoid

    Input:
        x: (batch_size, seq_len) - รายการตัวเลข index ของคำในประโยค
    Output:
        prob: (batch_size, 1) - ค่าความน่าจะเป็นของการเป็นมิจฉาชีพ/สแปม [0.0 - 1.0]
        attn_weights: (batch_size, seq_len) - ค่าน้ำหนักความสำคัญของแต่ละคำ

    Hyperparameters:
    - Vocab Size: 5,000
    - Embed Dim: 64
    - Hidden Dim: 64 (Bidirectional รวมเป็น 128)
    - Num Layers: 2
    - Dropout: 0.3
    - Optimizer: Adam (lr=1e-3)
    - Loss: BCELoss
    """

    def __init__(self, vocab_size: int = 5002, embed_dim: int = 64,
                 hidden_dim: int = 64, num_layers: int = 2, dropout: float = 0.3):
        super().__init__()
        self.vocab_size = vocab_size
        self.embed_dim = embed_dim
        self.hidden_dim = hidden_dim

        # 1. Embedding Layer & Embedding Dropout
        self.embedding = nn.Embedding(vocab_size, embed_dim, padding_idx=0)
        self.embed_dropout = nn.Dropout(dropout)

        # 2. Bidirectional LSTM
        self.lstm = nn.LSTM(
            input_size=embed_dim,
            hidden_size=hidden_dim,
            num_layers=num_layers,
            bidirectional=True,
            batch_first=True,
            dropout=dropout if num_layers > 1 else 0
        )

        # 3. Attention Mechanism (BiLSTM output size = hidden_dim * 2)
        self.attention = SelfAttention(hidden_dim * 2, dropout=0.1)

        # 4. Dense Classifier
        self.dropout = nn.Dropout(dropout)
        self.fc1 = nn.Linear(hidden_dim * 2, 64)
        self.fc2 = nn.Linear(64, 1)

    def forward(self, x, return_attention: bool = False):
        # สร้าง mask สำหรับตำแหน่งที่ไม่ใช่ padding (index 0)
        mask = (x != 0).float()

        # Embedding: (batch_size, seq_len, embed_dim)
        embedded = self.embedding(x)
        embedded = self.embed_dropout(embedded)

        # BiLSTM: (batch_size, seq_len, hidden_dim * 2)
        lstm_out, _ = self.lstm(embedded)

        # Attention: context (batch_size, hidden_dim * 2), weights (batch_size, seq_len)
        context, attn_weights = self.attention(lstm_out, mask=mask)

        # Classifier
        out = self.dropout(context)
        out = F.relu(self.fc1(out))
        out = self.dropout(out)
        logits = self.fc2(out)
        prob = torch.sigmoid(logits)

        if return_attention:
            return prob, attn_weights
        return prob


# Quick Architecture Inspection
if __name__ == '__main__':
    model = SpamAttentionBiLSTM(vocab_size=5000, embed_dim=64, hidden_dim=64)
    sample_input = torch.randint(1, 1000, (4, 30))  # batch=4, len=30
    prob, attn = model(sample_input, return_attention=True)
    print("=== โมเดล SpamAttentionBiLSTM ===")
    print(f"ขนาด Input: {sample_input.shape}")
    print(f"ขนาด Output Probability: {prob.shape}")
    print(f"ขนาด Attention Weights: {attn.shape}")
    print(f"ตัวอย่างค่าน้ำหนักความเสี่ยง: {prob.squeeze().tolist()}")
