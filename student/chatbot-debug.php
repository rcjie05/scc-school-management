<?php
// CHATBOT DEBUG FILE - DELETE AFTER DEBUGGING
// Visit: localhost/school-mgmt-fixed/student/chatbot-debug.php
require_once '../php/config.php';
?>
<!DOCTYPE html>
<html>
<head><title>Chatbot Debug</title>
<style>body{font-family:monospace;padding:20px;} pre{background:#f0f0f0;padding:10px;border-radius:6px;white-space:pre-wrap;word-break:break-all;} .ok{color:green;font-weight:bold;} .err{color:red;font-weight:bold;}</style>
</head>
<body>
<h2>Chatbot API Debug</h2>

<h3>1. Session Status</h3>
<pre><?php
echo "Session ID: " . session_id() . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "isLoggedIn(): " . (isLoggedIn() ? '<span class="ok">YES</span>' : '<span class="err">NO - this is why chatbot fails!</span>') . "\n";
echo "hasRole(student): " . (hasRole('student') ? '<span class="ok">YES</span>' : '<span class="err">NO</span>') . "\n";
?></pre>

<h3>2. Live API Test</h3>
<button onclick="testApi()">Send Test Message to API</button>
<pre id="result">Click button to test...</pre>

<h3>3. Raw Response</h3>
<pre id="raw">Will show raw text here...</pre>

<script>
async function testApi() {
    document.getElementById('result').textContent = 'Testing...';
    const api = '../php/api/student/chatbot.php';
    console.log('Fetching:', api);
    try {
        const res = await fetch(api, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({message: 'hi'})
        });
        const rawText = await res.text();
        document.getElementById('raw').textContent = 
            'Status: ' + res.status + '\n' +
            'Content-Type: ' + res.headers.get('content-type') + '\n' +
            'Redirected: ' + res.redirected + '\n' +
            'URL: ' + res.url + '\n\n' +
            'Raw body:\n' + rawText;
        
        try {
            const data = JSON.parse(rawText);
            document.getElementById('result').innerHTML = '<span class="ok">✓ Valid JSON received!</span>\nReply: ' + data.reply;
        } catch(e) {
            document.getElementById('result').innerHTML = '<span class="err">✗ NOT valid JSON - body is shown in Raw Response above</span>';
        }
    } catch(e) {
        document.getElementById('result').textContent = 'FETCH ERROR: ' + e.message;
    }
}
</script>
    <script>
    (function() {
        var toggle   = document.getElementById('sidebarToggle');
        var sidebar  = document.querySelector('.sidebar');
        var overlay  = document.getElementById('sidebarOverlay');
        if (!toggle || !sidebar) return;

        function openSidebar() {
            sidebar.classList.add('active');
            overlay && overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay && overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function() {
            sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
        });
        overlay && overlay.addEventListener('click', closeSidebar);

        // Close sidebar when a nav link is clicked (mobile UX)
        document.querySelectorAll('.nav-item').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) closeSidebar();
            });
        });
    })();
    </script>
</body>
</html>
