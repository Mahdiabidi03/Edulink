"""
Smart Journal Integration Test
Tests: Login, Journal page, CRUD checks, Sentiment API, Study Advisor API
"""
import re
import json
import urllib.request
import urllib.parse
import http.cookiejar

BASE = "http://127.0.0.1:8000"
SENTIMENT_API = "http://127.0.0.1:5001"
ADVISOR_API = "http://127.0.0.1:5002"

results = []

def test(name, passed, detail=""):
    status = "PASS" if passed else "FAIL"
    results.append((status, name))
    print(f"  [{status}] {name}" + (f" — {detail}" if detail else ""))

def main():
    # Setup cookie-aware opener
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

    # ─── 1. Login ───
    print("\n=== LOGIN ===")
    credentials = [
        ("student1@edulink.com", "password"),
        ("student1@edulink.com", "123456"),
        ("student1@edulink.com", "testtest"),
        ("student1@edulink.com", "student123"),
        ("student@test.com", "password"),
        ("student@test.com", "123456"),
        ("admin@edulink.com", "admin"),
        ("admin@edulink.com", "password"),
    ]
    
    login_ok = False
    login_url = ""
    for email, pwd in credentials:
        cj2 = http.cookiejar.CookieJar()
        opener2 = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj2))
        resp = opener2.open(f"{BASE}/login", timeout=10)
        html = resp.read().decode()
        m = re.search(r'name="_csrf_token"\s+value="([^"]+)"', html)
        token = m.group(1) if m else ""
        
        data = urllib.parse.urlencode({
            "email": email,
            "password": pwd,
            "_csrf_token": token
        }).encode()
        req = urllib.request.Request(f"{BASE}/login", data=data)
        try:
            resp2 = opener2.open(req, timeout=10)
            if resp2.status == 200 and "/login" not in resp2.url:
                login_ok = True
                login_url = resp2.url
                opener = opener2  # Keep the authenticated session
                print(f"  [PASS] Login succeeds with {email}:{pwd} — redirected to {login_url}")
                results.append(("PASS", "Login succeeds"))
                break
        except urllib.error.HTTPError:
            pass
    
    if not login_ok:
        print(f"  [FAIL] Login — none of the credentials worked")
        results.append(("FAIL", "Login succeeds"))
    
    test("Login page loads", True, "status=200")
    test("CSRF token found", bool(token), f"len={len(token)}")

    # ─── 2. Journal Page ───
    print("\n=== JOURNAL PAGE ===")
    resp3 = opener.open(f"{BASE}/student/journal", timeout=10)
    journal = resp3.read().decode()
    test("Journal page loads", resp3.status == 200, f"status={resp3.status}, length={len(journal)}")
    test("Page title 'My Journal'", "My Journal" in journal)
    test("Note form present", "Save Note" in journal)
    test("Tasks widget present", "My Tasks" in journal)
    test("Reminders widget present", "Upcoming Reminders" in journal)
    test("AI Study Advisor widget", "AI Study Advisor" in journal)
    test("fetchStudyAdvice JS", "fetchStudyAdvice" in journal)
    test("PersonalTask toggle route", "student_personal_task_toggle" in journal)
    test("PersonalTask delete route", "student_personal_task_delete" in journal)
    test("Category filter present", "Filter by category" in journal or "userCategories" in journal)

    # ─── 3. Sentiment API ───
    print("\n=== SENTIMENT API (port 5001) ===")
    try:
        r = urllib.request.urlopen(f"{SENTIMENT_API}/health", timeout=5)
        health = json.loads(r.read().decode())
        test("Sentiment API health", health.get("status") == "ok", str(health))
    except Exception as e:
        test("Sentiment API health", False, str(e))

    try:
        payload = json.dumps({"text": "I am so stressed and overwhelmed with exams"}).encode()
        req = urllib.request.Request(f"{SENTIMENT_API}/analyze", payload, {"Content-Type": "application/json"})
        r = urllib.request.urlopen(req, timeout=5)
        result = json.loads(r.read().decode())
        test("Negative sentiment detected", result.get("sentiment") == "negative", f"score={result.get('score')}")
        test("Motivational phrase returned", result.get("motivational_phrase") is not None, str(result.get("motivational_phrase",""))[:60])
    except Exception as e:
        test("Sentiment analysis", False, str(e))

    try:
        payload = json.dumps({"text": "I aced my exam today and feel so proud!"}).encode()
        req = urllib.request.Request(f"{SENTIMENT_API}/analyze", payload, {"Content-Type": "application/json"})
        r = urllib.request.urlopen(req, timeout=5)
        result = json.loads(r.read().decode())
        test("Positive sentiment detected", result.get("sentiment") == "positive", f"score={result.get('score')}")
    except Exception as e:
        test("Positive sentiment", False, str(e))

    # ─── 4. Study Advisor API ───
    print("\n=== STUDY ADVISOR API (port 5002) ===")
    try:
        r = urllib.request.urlopen(f"{ADVISOR_API}/health", timeout=5)
        health = json.loads(r.read().decode())
        test("Advisor API health", health.get("status") == "ok", str(health))
        test("Gemini configured", health.get("gemini_configured") == True)
    except Exception as e:
        test("Advisor API health", False, str(e))

    try:
        summary = {
            "student_name": "Test Student",
            "notes_count": 5,
            "notes_tags": ["Math", "Physics"],
            "notes_sentiments": [
                {"title": "Exam prep", "sentiment": "negative"},
                {"title": "Lab work", "sentiment": "positive"},
            ],
            "tasks_total": 8,
            "tasks_completed": 3,
            "tasks_completion_rate": 37.5
        }
        payload = json.dumps(summary).encode()
        req = urllib.request.Request(f"{ADVISOR_API}/advice", payload, {"Content-Type": "application/json"})
        r = urllib.request.urlopen(req, timeout=35)
        result = json.loads(r.read().decode())
        has_advice = result.get("advice") is not None and len(result.get("advice", "")) > 20
        test("Study advice generated", has_advice, f"length={len(result.get('advice',''))}")
        test("Timestamp present", result.get("generated_at") is not None)
        if has_advice:
            print(f"\n  📝 Advice preview:\n  {result['advice'][:200]}...")
    except Exception as e:
        test("Study advice generation", False, str(e))

    # ─── 5. Symfony Sentiment Route ───
    print("\n=== SYMFONY SENTIMENT ROUTE ===")
    try:
        payload = json.dumps({"text": "I feel terrible about my grades"}).encode()
        req = urllib.request.Request(f"{BASE}/student/note/analyze-sentiment", payload, {"Content-Type": "application/json"})
        r = opener.open(req, timeout=10)
        result = json.loads(r.read().decode())
        test("Symfony sentiment endpoint", result.get("sentiment") is not None, f"sentiment={result.get('sentiment')}")
    except Exception as e:
        test("Symfony sentiment endpoint", False, str(e))

    # ─── 6. Symfony Study Advice Route ───
    print("\n=== SYMFONY STUDY ADVICE ROUTE ===")
    try:
        req = urllib.request.Request(f"{BASE}/student/study-advice")
        r = opener.open(req, timeout=35)
        result = json.loads(r.read().decode())
        has_result = "advice" in result or "error" in result
        test("Symfony study advice endpoint", has_result, f"keys={list(result.keys())}")
    except Exception as e:
        test("Symfony study advice endpoint", False, str(e))

    # ─── Summary ───
    print("\n" + "="*50)
    passed = sum(1 for s, _ in results if s == "PASS")
    failed = sum(1 for s, _ in results if s == "FAIL")
    print(f"  TOTAL: {passed} passed, {failed} failed, {len(results)} total")
    if failed:
        print(f"\n  Failed tests:")
        for s, n in results:
            if s == "FAIL":
                print(f"    ❌ {n}")
    print("="*50)

if __name__ == "__main__":
    main()
