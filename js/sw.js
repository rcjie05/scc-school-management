/**
 * IMPROVEMENT 4: Push Notification System
 *
 * This file REPLACES /sw.js (your existing Service Worker).
 * It adds:
 *   - Announcement push notifications (background + foreground)
 *   - Offline fallback caching so key pages work without internet
 *   - Periodic background sync to check for new announcements
 *
 * ─── Setup Steps ──────────────────────────────────────────────────────────
 * 1. Replace your /sw.js with this file.
 * 2. Add notification_helper.php (provided below as a comment) to your project.
 * 3. Include push_subscribe.js in your student dashboard page.
 * 4. Run the SQL at the bottom of this file to add the push_subscriptions table.
 * ──────────────────────────────────────────────────────────────────────────
 */

const CACHE_NAME    = 'scc-portal-v2';
const OFFLINE_PAGE  = '/student/dashboard.php';

// Pages to pre-cache for offline access
const PRECACHE_URLS = [
    '/',
    '/student/dashboard.php',
    '/student/schedule.php',
    '/student/grades.php',
    '/student/announcements.php',
    '/css/style.css',
    '/css/themes.css',
    '/images/logo.png',
    '/images/logo2.jpg',
];

// ── Install: pre-cache key pages ──────────────────────────────────────────────
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS)).catch(() => {})
    );
});

// ── Activate: clean old caches ────────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// ── Fetch: network-first, cache fallback ─────────────────────────────────────
self.addEventListener('fetch', event => {
    const req = event.request;
    // Only handle GET requests; skip API calls (always need fresh data)
    if (req.method !== 'GET') return;
    if (req.url.includes('/php/api/')) return;
    if (req.url.includes('/php/logout')) return;

    event.respondWith(
        fetch(req)
            .then(res => {
                // Cache successful responses for static assets
                if (res.ok && (req.url.includes('/css/') || req.url.includes('/images/') || req.url.includes('/js/'))) {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(c => c.put(req, clone));
                }
                return res;
            })
            .catch(() =>
                caches.match(req).then(cached => cached || caches.match(OFFLINE_PAGE))
            )
    );
});

// ── Push: show notification when server sends one ─────────────────────────────
self.addEventListener('push', event => {
    let payload = { title: 'SCC Portal', body: 'New update available.', url: '/student/announcements.php', icon: '/images/logo.png' };

    if (event.data) {
        try { payload = { ...payload, ...event.data.json() }; }
        catch(e) { payload.body = event.data.text(); }
    }

    const options = {
        body:    payload.body,
        icon:    payload.icon  || '/images/logo.png',
        badge:   payload.badge || '/images/logo.png',
        tag:     payload.tag   || 'scc-announcement',
        renotify: true,
        data:    { url: payload.url || '/student/announcements.php' },
        actions: [
            { action: 'view',    title: '👁️ View' },
            { action: 'dismiss', title: '✕ Dismiss' },
        ],
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// ── Notification click: open the linked page ──────────────────────────────────
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'dismiss') return;

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/student/announcements.php';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            // Focus existing window if open
            for (const client of list) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open a new window
            if (clients.openWindow) return clients.openWindow(targetUrl);
        })
    );
});

// ── Periodic sync: check for new announcements in background ──────────────────
self.addEventListener('periodicsync', event => {
    if (event.tag === 'check-announcements') {
        event.waitUntil(checkForNewAnnouncements());
    }
});

async function checkForNewAnnouncements() {
    try {
        const res  = await fetch('/php/api/student/get_announcements.php?limit=1&since=' + Date.now());
        const data = await res.json();
        if (data.new && data.announcements && data.announcements.length > 0) {
            const ann = data.announcements[0];
            await self.registration.showNotification('📢 ' + ann.title, {
                body:  ann.content ? ann.content.substring(0, 100) + '…' : 'New announcement posted.',
                icon:  '/images/logo.png',
                tag:   'scc-ann-' + ann.id,
                data:  { url: '/student/announcements.php' },
            });
        }
    } catch(e) {}
}

/*
═══════════════════════════════════════════════════════════════════════════════
FILE 2: /js/push_subscribe.js
Include this on student/dashboard.php after the SW registration script.
───────────────────────────────────────────────────────────────────────────────

(function() {
    // Your VAPID public key — generate a key pair at: https://vapidkeys.com
    // Store the PRIVATE key in your .env / Railway env vars as VAPID_PRIVATE_KEY
    const VAPID_PUBLIC_KEY = 'YOUR_VAPID_PUBLIC_KEY_HERE';

    async function subscribePush() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        const reg = await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        if (sub) return; // Already subscribed

        // Request permission
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') return;

        // Subscribe
        sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
        });

        // Send subscription to server
        await fetch('/php/api/student/save_push_subscription.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(sub),
        });
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw     = atob(base64);
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
    }

    // Subscribe after a short delay (don't prompt immediately on load)
    setTimeout(subscribePush, 5000);
})();

═══════════════════════════════════════════════════════════════════════════════
FILE 3: /php/api/student/save_push_subscription.php
───────────────────────────────────────────────────────────────────────────────

<?php
require_once '../../config.php';
requireRole('student');
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$sub = json_decode($raw, true);
if (!$sub || empty($sub['endpoint'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid subscription']); exit;
}

$conn     = getDBConnection();
$user_id  = $_SESSION['user_id'];
$endpoint = $sub['endpoint'];
$p256dh   = $sub['keys']['p256dh'] ?? '';
$auth     = $sub['keys']['auth']   ?? '';
$sub_json = $raw;

$stmt = $conn->prepare("
    INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth, subscription_json)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE p256dh=VALUES(p256dh), auth=VALUES(auth), subscription_json=VALUES(subscription_json), updated_at=NOW()
");
$stmt->bind_param('issss', $user_id, $endpoint, $p256dh, $auth, $sub_json);
$stmt->execute();
echo json_encode(['success' => true]);

═══════════════════════════════════════════════════════════════════════════════
FILE 4: /php/api/admin/send_push_notification.php
Call this from announcements.php when posting a new announcement.
───────────────────────────────────────────────────────────────────────────────

<?php
// Install web-push library: composer require minishlink/web-push
require_once __DIR__ . '/../../../vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function sendPushToStudents($conn, $title, $body, $url = '/student/announcements.php') {
    $auth = [
        'VAPID' => [
            'subject'    => 'mailto:admin@saintceciliacollege.edu.ph',
            'publicKey'  => getenv('VAPID_PUBLIC_KEY'),
            'privateKey' => getenv('VAPID_PRIVATE_KEY'),
        ],
    ];

    $webPush = new WebPush($auth);

    $stmt = $conn->prepare("SELECT subscription_json FROM push_subscriptions WHERE user_id IN (SELECT id FROM users WHERE role='student' AND status='active')");
    $stmt->execute();
    $res  = $stmt->get_result();

    $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url, 'icon' => '/images/logo.png']);

    while ($row = $res->fetch_assoc()) {
        $sub = json_decode($row['subscription_json'], true);
        $webPush->queueNotification(Subscription::create($sub), $payload);
    }

    $webPush->flush();
}

═══════════════════════════════════════════════════════════════════════════════
SQL: Add push_subscriptions table
Run this in phpMyAdmin or your MySQL console.
───────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS push_subscriptions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    endpoint         VARCHAR(1000) NOT NULL UNIQUE,
    p256dh           VARCHAR(500),
    auth             VARCHAR(500),
    subscription_json TEXT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

═══════════════════════════════════════════════════════════════════════════════
Install web-push PHP library:
    composer require minishlink/web-push
Generate VAPID keys:
    Go to https://vapidkeys.com — get a public/private pair.
    Set VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY in Railway env vars.
═══════════════════════════════════════════════════════════════════════════════
*/
