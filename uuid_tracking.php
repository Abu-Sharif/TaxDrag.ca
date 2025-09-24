<?php
// anonymous user tracking with uuid
// if the user has a cookie, set the session to the cookie, otherwise create a UUID
if (isset($_COOKIE['anon_user'])) {
    $_SESSION['anon_user'] = $_COOKIE['anon_user'];
} else {
    $uuid = bin2hex(random_bytes(16));
    setcookie('anon_user', $uuid, time() + (86400 * 182), "/"); // 6 months
    // make UUID available immediately
    $_SESSION['anon_user'] = $uuid;
}
?>
