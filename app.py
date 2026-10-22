from flask import Flask, render_template, request
import re

app = Flask(__name__)

RED_FLAGS = {
    "registration fee": "Requests an upfront registration fee",
    "pay now": "Creates pressure to pay immediately",
    "security deposit": "Requests a security deposit",
    "processing fee": "Requests a processing fee",
    "telegram": "Uses Telegram for recruitment communication",
    "whatsapp": "Uses WhatsApp for recruitment communication",
    "guaranteed job": "Promises a guaranteed job",
    "no interview": "Claims a job without an interview",
    "urgent": "Uses urgency to pressure the applicant",
    "upi": "Mentions UPI payment",
    "bank details": "Requests bank details",
    "aadhaar": "Mentions sensitive identity information",
    "pan card": "Mentions sensitive identity information",
}

def scan_offer(text):
    lowered = text.lower()
    found = [reason for phrase, reason in RED_FLAGS.items() if phrase in lowered]
    score = min(95, len(found) * 18)
    if score >= 54:
        level = "High"
        advice = "Do not pay or share sensitive information. Verify the company using its official website and contact details."
    elif score >= 18:
        level = "Uncertain"
        advice = "Pause and independently verify the recruiter, company email domain, job listing and interview process."
    else:
        level = "Low"
        advice = "No listed red flags were found by this basic demo. This does not prove the offer is genuine; verify independently."
    if not found:
        found = ["No matching demo red-flag phrases found"]
    return {"score": score, "level": level, "reasons": found, "advice": advice}

@app.route("/", methods=["GET", "POST"])
def home():
    result = None
    offer_text = ""
    if request.method == "POST":
        offer_text = request.form.get("offer_text", "")[:12000]
        if offer_text.strip():
            result = scan_offer(offer_text)
    return render_template("index.html", result=result, offer_text=offer_text)

@app.route("/about")
def about():
    return render_template("about.html")

if __name__ == "__main__":
    app.run(debug=True)
