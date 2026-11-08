<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * Staff Authentication & Session Controller API
 * Native PHP 8.x / MySQL PDO
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Read JSON Body if POST/PUT
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

if ($action === '') {
    $action = $data['action'] ?? '';
}

// 1. GET CURRENT LOGGED IN USER
if ($method === 'GET' && $action === 'me') {
    if (isset($_SESSION['user'])) {
        jsonResponse([
            'success' => true,
            'logged_in' => true,
            'user' => $_SESSION['user']
        ]);
    } else {
        jsonResponse([
            'success' => true,
            'logged_in' => false,
            'user' => null
        ]);
    }
}

// 2. STAFF LOGOUT
if ($action === 'logout') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    jsonResponse([
        'success' => true,
        'message' => 'Logged out successfully.'
    ]);
}

// 3. STAFF LOGIN (Email & Password)
if ($action === 'login') {
    $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = (string)($data['password'] ?? '');
    $requestedRedirect = sanitize($data['redirect'] ?? '');

    if (!$email || empty($password)) {
        jsonResponse([
            'success' => false,
            'message' => 'Please provide a valid account email and password.'
        ], 422);
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role, affiliation, id_number, phone FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse([
                'success' => false,
                'message' => 'Invalid email or password credentials. Please verify your staff access.'
            ], 401);
        }

        // Establish Secure Session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'affiliation' => $user['affiliation'],
            'id_number' => $user['id_number'],
            'phone' => $user['phone'],
            'logged_in_at' => time()
        ];

        // Determine destination strictly matched to user role
        $targetRedirect = 'booking';
        if ($user['role'] === 'admin') {
            $targetRedirect = 'admin';
        } elseif ($user['role'] === 'cashier') {
            $targetRedirect = ($requestedRedirect === 'scanner') ? 'scanner' : 'pos';
        } elseif ($user['role'] === 'barista') {
            $targetRedirect = 'kitchen';
        } elseif ($user['role'] === 'staff' || $user['role'] === 'concierge') {
            $targetRedirect = 'scanner';
        } elseif ($user['role'] === 'customer') {
            $targetRedirect = 'booking';
        }

        jsonResponse([
            'success' => true,
            'message' => "Welcome back, {$user['name']}!",
            'redirect' => $targetRedirect,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'],
                'email' => $user['email']
            ]
        ]);
    } catch (Exception $e) {
        jsonResponse([
            'success' => false,
            'message' => 'Database error during authentication: ' . $e->getMessage()
        ], 500);
    }
}

// 4. QUICK TEST 1-CLICK DEMO LOGIN (For Evaluation & Defense Panel)
if ($action === 'quick_login') {
    $role = sanitize($data['role'] ?? '');
    $requestedRedirect = sanitize($data['redirect'] ?? '');

    $targetEmail = '';
    if ($role === 'admin') {
        $targetEmail = 'admin@raedens.com';
    } elseif ($role === 'cashier') {
        $targetEmail = 'cashier@raedens.com';
    } elseif ($role === 'barista') {
        $targetEmail = 'barista@raedens.com';
    } elseif ($role === 'staff' || $role === 'concierge') {
        $targetEmail = 'staff@raedens.com';
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid quick login role specified.'], 422);
    }

    try {
        if ($role === 'staff' || $role === 'concierge') {
            $stmt = $pdo->prepare("SELECT id, name, email, role, affiliation, id_number, phone FROM users WHERE email = ? OR role IN ('staff', 'cashier') ORDER BY (role = 'staff') DESC, id ASC LIMIT 1");
            $stmt->execute([$targetEmail]);
        } else {
            $stmt = $pdo->prepare("SELECT id, name, email, role, affiliation, id_number, phone FROM users WHERE email = ? OR role = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$targetEmail, $role]);
        }
        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse(['success' => false, 'message' => "Quick demo account for '$role' not found in database."], 404);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'affiliation' => $user['affiliation'],
            'id_number' => $user['id_number'],
            'phone' => $user['phone'],
            'logged_in_at' => time()
        ];

        // Determine target view
        $targetRedirect = 'booking';
        if ($role === 'admin') {
            $targetRedirect = 'admin';
        } elseif ($role === 'barista') {
            $targetRedirect = 'kitchen';
        } elseif ($role === 'staff' || $role === 'concierge') {
            $targetRedirect = 'scanner';
        } elseif ($role === 'cashier') {
            $targetRedirect = ($requestedRedirect === 'scanner') ? 'scanner' : 'pos';
        }

        jsonResponse([
            'success' => true,
            'message' => "Authenticated as {$user['name']} (" . strtoupper($role) . ").",
            'redirect' => $targetRedirect,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'],
                'email' => $user['email']
            ]
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid authentication request action.'], 400);

