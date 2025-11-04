<?php
// Run this BEFORE any output or session_start()

// Safety: if auto_start is on, turn it off at runtime (best to disable in php.ini too)
ini_set('session.auto_start', '0');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,       // session cookie
    'path'     => '/',
    'domain'   => '',      // leave blank unless you need a specific domain
    'secure'   => true,    // you're on HTTPS now
    'httponly' => true,
    'samesite' => 'Lax',   // or 'Strict' if your flow allows
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('windaninjas');
    session_start();
}
