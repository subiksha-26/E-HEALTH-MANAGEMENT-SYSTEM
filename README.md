# ScamShield AI — Website Prototype

A Flask website prototype inspired by the ScamShield AI presentation. It lets a user paste a job or internship message and checks for a small set of common warning phrases.

## Features in this starter
- Student mode: paste job offer text.
- Rule-based demo score and matched warning phrases.
- Safety advice and official cybercrime reporting link.
- Responsive HTML/CSS pages.

## Tech
- Python + Flask
- HTML, CSS
- Simple keyword rules (not a trained machine-learning model)

## Run locally
1. Install Python 3.10+.
2. Open a terminal in this folder.
3. Run `python -m venv .venv`
4. Activate it:
   - Windows: `.venv\\Scripts\\activate`
   - macOS/Linux: `source .venv/bin/activate`
5. Run `pip install -r requirements.txt`
6. Run `python app.py`
7. Open `http://127.0.0.1:5000`

## Important limitation
This starter does not yet implement the complete presentation design. It does not include trained TF-IDF + Logistic Regression, SHAP/LIME explanations, OCR/PDF upload, resume verification, company reviews, dashboard persistence, or complaint submission. It is a basic educational prototype and its score is not proof that an offer is fraudulent or safe.
