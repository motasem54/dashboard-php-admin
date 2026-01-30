<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/users.php';
require_once 'includes/logger.php';

requireAuth();

if (!isAdmin()) {
    header('Location: /dashboard.php');
    exit;
}

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    header('Location: /users.php');
    exit;
}

$user = getUserById($userId);
if (!$user) {
    header('Location: /users.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validation
    if (empty($username) || empty($email)) {
        $error = 'اسم المستخدم والبريد الإلكتروني مطلوبان';
    } elseif (strlen($username) < 3) {
        $error = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif (!empty($password) && $password !== $confirmPassword) {
        $error = 'كلمة المرور وتأكيدها غير متطابقتين';
    } else {
        $conn = getConnection();
        
        try {
            // Update user
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, password = ?, role = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $hashedPassword, $role, $userId]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, role = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $role, $userId]);
            }
            
            $currentUser = getCurrentUser();
            logActivity($currentUser['id'], 'USER_UPDATE', "تم تحديث المستخدم: {$username}");
            
            header('Location: /users.php?success=updated');
            exit;
        } catch (PDOException $e) {
            $error = 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً';
        }
    }
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل مستخدم - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>تعديل مستخدم</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/users.php" class="btn btn-secondary">← العودة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3 class="card-title">تعديل المستخدم: <?= htmlspecialchars($user['username']) ?></h3>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">اسم المستخدم *</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="اتركها فارغة إذا لم ترد التغيير"
                    >
                    <small style="color: var(--text-secondary); font-size: 0.85rem;">* اترك هذا الحقل فارغاً إذا لم ترد تغيير كلمة المرور</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="أعد إدخال كلمة المرور الجديدة"
                    >
                </div>

                <div class="form-group">
                    <label for="role">الدور *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>مستخدم عادي</option>
                        <option value="admin" <?= ($user['role'] ?? 'user') === 'admin' ? 'selected' : '' ?>>مدير</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                    <a href="/users.php" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
