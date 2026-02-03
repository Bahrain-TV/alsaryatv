# System Architecture & Flow Diagrams

## 1. CSRF Token Protection Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER VISITS REGISTRATION PAGE                 │
│                   GET /callers/create                            │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
            ┌────────────────────────────┐
            │  Server receives GET       │
            │  request                   │
            └────────────┬───────────────┘
                         │
                         ▼
            ┌────────────────────────────┐
            │  Generate CSRF token:      │
            │  - Random 40-char string   │
            │  - Store in session        │
            │  - Create session cookie   │
            └────────────┬───────────────┘
                         │
                         ▼
            ┌────────────────────────────┐
            │  Render HTML form with:    │
            │  - Meta tag with token     │
            │  - @csrf hidden input      │
            │  - Session cookie header   │
            └────────────┬───────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│         USER SUBMITS FORM                                        │
│         POST /callers                                            │
│         Headers: Cookie: LARAVEL_SESSION=xyz...                │
│         Body: _token=abc123...&name=Test&phone=333...          │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
            ┌────────────────────────────┐
            │  CSRF Middleware:          │
            │  Checks:                   │
            │  1. Is request POST?       │
            │  2. Is _token present?     │
            │  3. Does token match       │
            │     session token?         │
            └────────────┬───────────────┘
                         │
         ┌───────────────┴────────────────┐
         │                                │
         ▼                                ▼
    ✅ TOKEN                         ❌ TOKEN INVALID
       VALID                            OR MISSING
         │                                │
         ▼                                ▼
    Continue to              Return 419 Mismatch
    Rate Limiting            (CSRF Protection)
         │                                │
         └────────────┬───────────────────┘
                      │
                      ▼
            ┌────────────────────────────┐
            │  Request logged with:      │
            │  - IP address              │
            │  - Session ID              │
            │  - Timestamp               │
            │  - User agent              │
            └────────────────────────────┘
```

---

## 2. Rate Limiting Flow

```
┌─────────────────────────────────────────────────────────────────┐
│              USER SUBMITS REGISTRATION FORM                      │
│                 POST /callers                                    │
│              CPR: 123456789                                      │
│              IP: 192.168.1.100                                   │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
            ┌────────────────────────────┐
            │  RATE LIMIT CHECK 1        │
            │  Per-CPR Limit             │
            │  Key: caller_creation:     │
            │        123456789           │
            └────────────┬───────────────┘
                         │
         ┌───────────────┴────────────────┐
         │                                │
         ▼                                ▼
    First attempt?              Attempt within
    Cache empty?                5 minutes?
         │                                │
         │                                │
    ✅ YES                             ❌ YES
    Increment                          Return Error:
    Set 5-min timer              "You can only register
         │                        once every 5 minutes"
         │
         ▼
    ┌────────────────────────────┐
    │  RATE LIMIT CHECK 2        │
    │  Per-IP Limit              │
    │  Key: caller_creation_ip:  │
    │        192.168.1.100       │
    └────────────┬───────────────┘
                 │
     ┌───────────┴────────────┐
     │                        │
     ▼                        ▼
 Count < 10?              Count >= 10?
 In last hour?            In last hour?
     │                        │
     │                        │
 ✅ YES                    ❌ YES
 Increment                 Return Error:
 Set 1-hour timer          "Too many registrations
     │                     from your location"
     │
     ▼
 ┌──────────────────────────┐
 │  ALL CHECKS PASS         │
 │  Process registration    │
 │  ✅ Store in database    │
 │  ✅ Update cache         │
 │  ✅ Log success          │
 │  ✅ Redirect to success  │
 └──────────────────────────┘
```

---

## 3. Security Event Logging Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   SECURITY EVENT OCCURS                          │
│              Registration / Rate Limit / CSRF                    │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │  logSecurityEvent() called      │
        │  with event data                │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │  Collect context:               │
        │  - User ID (or "guest")         │
        │  - Session ID                   │
        │  - Request ID (UUID)            │
        │  - Timestamp (ISO 8601)         │
        │  - User agent                   │
        │  - HTTP method                  │
        │  - Request path                 │
        │  - IP address                   │
        │  - Event-specific data          │
        │    (CPR, limits, etc)           │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │  Log to channel:                │
        │  storage/logs/laravel.log       │
        │  (JSON format for parsing)      │
        └────────────────────────────────┘
                         │
                         ▼
        ┌────────────────────────────────┐
        │  View logs with:                │
        │  tail -f storage/logs/...       │
        │  grep "rate_limit\|csrf\|..."   │
        └────────────────────────────────┘
```

---

## 4. Complete Registration Flow with All Checks

```
START: User visits http://localhost:8001/callers/create
│
├─ GET /callers/create
│  ├─ Generate CSRF token
│  ├─ Create session cookie
│  └─ Return form with token
│
└─ User sees registration form
   │
   ▼
   User fills form:
   - Name: John Doe
   - CPR: 123456789
   - Phone: 33333333
   │
   ▼
   User clicks "Register"
   │
   ▼
   POST /callers/
   │
   ├─ Headers: 
   │  └─ Cookie: LARAVEL_SESSION=xyz123...
   │
   ├─ Body:
   │  ├─ _token: abc123def456... (from @csrf)
   │  ├─ name: John Doe
   │  ├─ cpr: 123456789
   │  └─ phone_number: 33333333
   │
   ▼
   SECURITY CHECKS:
   │
   ├─ [1] CSRF Token Validation
   │  ├─ Token present? YES ✅
   │  ├─ Token valid? YES ✅
   │  └─ Continue ▶
   │
   ├─ [2] CPR Rate Limit (1 per 5 min)
   │  ├─ Cache key: caller_creation:123456789
   │  ├─ First registration? YES ✅
   │  ├─ Increment cache
   │  ├─ Set 5-minute timer
   │  └─ Continue ▶
   │
   ├─ [3] IP Rate Limit (10 per hour)
   │  ├─ Cache key: caller_creation_ip:192.168.1.100
   │  ├─ Count < 10? YES ✅
   │  ├─ Increment cache
   │  ├─ Set 1-hour timer
   │  └─ Continue ▶
   │
   ▼
   ALL CHECKS PASS ✅
   │
   ├─ Log event: caller.registration.success
   ├─ Store caller in database
   ├─ Update cache counters
   ├─ Store session data
   └─ Redirect to /callers/success
   
   ▼
   SUCCESS PAGE:
   - Show "Registration Complete"
   - Display confirmation
   - 30-second countdown
   
───────────────────────────────────────────────────

ATTEMPT 2: User tries to register again immediately
│
└─ POST /callers/ (same CPR: 123456789)
   │
   ├─ [1] CSRF Token Validation ✅
   │
   ├─ [2] CPR Rate Limit Check
   │  ├─ Cache key: caller_creation:123456789
   │  ├─ Count >= 1? YES ❌
   │  ├─ Log: caller_registration.rate_limit_exceeded
   │  └─ Return error
   │
   ▼
   ERROR RESPONSE:
   ├─ Show error message
   ├─ "You can only register once every 5 minutes"
   └─ Suggest trying again later
```

---

## 5. Cache System for Rate Limiting

```
RATE LIMIT CACHE STORAGE
(Database: SQLite/MySQL)

────────────────────────────────────────────────────

Cache Entry 1:
  Key: caller_creation:123456789
  Value: 1
  TTL: 300 seconds (5 minutes)
  ├─ Created: 2026-02-02 19:30:00
  ├─ Expires: 2026-02-02 19:35:00
  └─ Auto-deleted after expiry

Cache Entry 2:
  Key: caller_creation_ip:192.168.1.100
  Value: 3
  TTL: 3600 seconds (1 hour)
  ├─ Created: 2026-02-02 19:00:00
  ├─ Expires: 2026-02-02 20:00:00
  └─ Auto-deleted after expiry

────────────────────────────────────────────────────

HOW IT WORKS:

1. First registration with CPR 123456789:
   ├─ Cache::get('caller_creation:123456789', 0)
   ├─ Returns: 0 (not set yet)
   ├─ Check: 0 >= 1? NO ✅
   ├─ Increment: Cache::put(key, 1, 300)
   └─ Success

2. Second registration (within 5 min):
   ├─ Cache::get('caller_creation:123456789', 0)
   ├─ Returns: 1 (was set in step 1)
   ├─ Check: 1 >= 1? YES ❌
   └─ Blocked with error

3. After 5 minutes (timeout):
   ├─ Cache key automatically expires
   ├─ Database removes entry
   ├─ Cache::get returns 0 again
   ├─ User can register again ✅
   └─ New timer starts
```

---

## 6. Security Logging Architecture

```
SECURITY LOGGING SYSTEM

Application Event
    │
    ├─ Registration attempt
    ├─ Rate limit exceeded
    ├─ CSRF token invalid
    └─ ... other events
    │
    ▼
logSecurityEvent() method
    │
    ├─ Collect context:
    │  ├─ User ID
    │  ├─ Session ID
    │  ├─ Request ID (UUID)
    │  ├─ Timestamp (ISO 8601)
    │  ├─ User agent
    │  ├─ Method (GET/POST/etc)
    │  ├─ Path (/callers, /login, etc)
    │  ├─ IP address
    │  └─ Event-specific data
    │
    ├─ Format as JSON
    │
    ▼
Log Channel (Laravel Logging)
    │
    ├─ Destination: storage/logs/laravel.log
    │
    ├─ Format (example):
    │  {
    │    "timestamp": "2026-02-02T19:30:45Z",
    │    "event": "caller_registration.rate_limit_exceeded",
    │    "user_id": "guest",
    │    "session_id": "abc123def...",
    │    "request_id": "uuid-1234...",
    │    "ip": "192.168.1.100",
    │    "cpr": "123***", // Partial for security
    │    "method": "POST",
    │    "path": "/callers",
    │    "user_agent": "Mozilla/5.0..."
    │  }
    │
    ▼
Log File Management
    │
    ├─ Rotation: daily (configurable)
    ├─ Retention: 14 days (configurable)
    ├─ Searchable: grep, tail, log parsers
    │
    ▼
Monitoring & Alerting
    │
    ├─ Real-time: tail -f storage/logs/laravel.log
    ├─ Search: grep "rate_limit" storage/logs/laravel.log
    ├─ Count: grep -c "error" storage/logs/laravel.log
    ├─ Alert: If threshold exceeded
    └─ Dashboard: Parse logs for metrics
```

---

## 7. State Machine: Rate Limit Counter Lifecycle

```
┌─────────────────────────────────────────────────┐
│          RATE LIMIT COUNTER STATES              │
└─────────────────────────────────────────────────┘

                    INITIAL STATE
                    (No cache entry)
                           │
                           │ First registration
                           │ Cache::put(key, 1, TTL)
                           │
                           ▼
                    ┌──────────────┐
                    │ Count = 1    │
                    │ TTL = 5 min  │
                    └──────┬───────┘
                           │
                    ┌──────┴──────────┐
                    │                 │
                    │ Within 5 min?   │ After 5 min?
                    │                 │
            ✅ YES  │                 │  ✅ YES
    Can register    │                 │  TTL expires
    again ❌        │                 │  Entry deleted
    (blocked)       │                 │
                    │                 ▼
                    │          INITIAL STATE
                    │          (Cache cleared)
                    │          Can register again
                    │
    ❌ NO           │
    Attempt blocked │
    Error shown     │
    Log created     │
                    │
                    ▼ (After 5 min)
          Entry auto-deleted
          Counter resets
          User can register again
```

---

## 8. HTTP Status Codes & Flow

```
REGISTRATION REQUEST OUTCOMES

Request arrives
│
├─ [1] CSRF validation fails
│  └─ Response: 419 Mismatch Token
│     (CSRF Middleware blocks)
│
├─ [2] CPR rate limit exceeded
│  └─ Response: 422 Unprocessable Entity
│     (Exception caught, error shown)
│
├─ [3] IP rate limit exceeded
│  └─ Response: 422 Unprocessable Entity
│     (Exception caught, error shown)
│
├─ [4] Validation fails (missing fields)
│  └─ Response: 302 Redirect to form
│     (With error messages)
│
├─ [5] All checks pass
│  └─ Response: 302 Redirect to success page
│     (Registration processed)
│
└─ [6] Database error
   └─ Response: 500 Server Error
      (Logged for debugging)
```

---

## Summary

These diagrams show:
1. **CSRF Flow**: How tokens are generated and validated
2. **Rate Limiting**: How limits are enforced per CPR and IP
3. **Security Logging**: How all events are tracked
4. **Complete Flow**: The full registration process with all checks
5. **Cache System**: How rate limit counters work
6. **Logging Architecture**: How security events are stored
7. **State Machine**: Counter lifecycle over time
8. **HTTP Status**: Different response codes for different scenarios

All layers work together to:
- ✅ Prevent CSRF attacks
- ✅ Prevent duplicate registrations
- ✅ Prevent bulk abuse
- ✅ Maintain audit trail
- ✅ Provide user feedback
