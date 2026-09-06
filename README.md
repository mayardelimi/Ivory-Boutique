# Tarjmli 

> **An intelligent multilingual assistant for Algerian code-switching detection and text re-generation.**

Tarjmli is an NLP system designed for the Algerian linguistic context, where **Algerian Darija, Arabic, French, and English** are naturally mixed in everyday communication.

The system analyzes a multilingual/code-switched sentence, identifies its language segments, visualizes the detected languages, and can generate **four monolingual versions** of the input:

- 🇩🇿 Darija (Arabic script)
-  Darija (Latin / Arabizi)
- 🇸🇦 Modern Standard Arabic
- 🇫🇷 French
- 🇬🇧 English

---

##  Project Overview

In Algeria, code-switching between Darija, Arabic, French, and English is common. While this reflects the country's linguistic diversity, mixed-language text can create comprehension difficulties for people from different generations, professional backgrounds, or linguistic environments.

**Tarjmli** aims to preserve the user's original expression while automatically adapting it to the target language.

##  Features

###  Code-Switching Detection

The segmentation pipeline identifies language boundaries in mixed sentences.

Example:

```text
Sbah el kheir guys, wach rakom ready pour la réunion?
```

Detected segments:

```text
Sbah el kheir   → DZ
guys            → EN
wach rakom      → DZ
ready           → EN
pour la réunion → FR
```


---


#  Data Processing

The data preparation pipeline consists of several stages:

```text
Raw Data
   │
   ▼
Extraction
   │
   ▼
Class Balancing
   │
   ▼
Tokenization
   │
   ▼
Label Normalization
   │
   ▼
Text Cleaning
   │
   ▼
TF-IDF Vectorization
   │
   ▼
Training Dataset
```

### Normalization

The preprocessing pipeline includes:

- Unicode normalization
- Arabic Alef / Teh-Marbuta normalization
- Arabizi mapping
- Lowercasing
- Special-character removal
- Short-token filtering
- Punctuation and numeric-tag removal

#  Datasets

The project combines multiple resources to cover different aspects of Algerian multilingual text.

| Dataset | Approx. Size | Purpose |
|---|---:|---|
| **ELNER-DZ** | 2M rows | Named entity annotations for Darija |
| **Algerian Darija** | 200K rows | General Algerian dialect corpus |
| **ArE-CSTD** | 110K rows | Standard Arabic tokenized corpus |
| **SwitchLingua** | 60K rows | Mixed-language / code-switched samples |
| **MADAR** | 2,000 rows | Arabic dialect translation data |
| **Mo3jam** | 4,000 rows | Dialect vocabulary and lexical resources |

For the annotation pipeline, data was normalized and filtered using a confidence threshold of **0.8**. The project reports reducing approximately **2.8M samples to 200K high-quality samples**.

The manually checked annotation set achieved a **Cohen's Kappa of 0.77**.

---

#  Model Evaluation

The project evaluates both language classification and re-generation.

### Classification

| Accuracy | Macro F1 | AR F1 | DZ F1 | FR F1 | EN F1 |
|---|---:|---:|---:|---:|---:|---:|
| **94.6%** | **0.937** | 0.874 | **0.953** | **0.964** | **0.958** |


##  Re-Generation / Translation

After detecting the linguistic structure of the input, Tarjmli can generate several language-specific versions.

The re-generation component is based on **NLLB-200** with **LoRA (Low-Rank Adaptation)** fine-tuning.

### Model configuration

- **Model:** NLLB-200
- **Model size:** 600M parameters
- **Trainable parameters with LoRA:** ~1.2%
- **Architecture:** sequence-to-sequence Transformer
- **Tokenizer:** SentencePiece
- **Training paradigm:** paired input/target examples

The project prepared approximately **48,000 translation instances** covering Darija Arabic-script and Darija Latin-script data.

# ️ Screenshots

Screenshots are included here to demonstrate the models working on real examples.

![Code-switching detection](assets/screenshots/code-switching.png)

---
