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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif (strlen($username) < 3) {
        $error = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $confirmPassword) {
        $error = 'كلمة المرور وتأكيدها غير متطابقتين';
    } else {
        $userId = createUser($username, $email, $password, $role);
        
        if ($userId) {
            $currentUser = getCurrentUser();
            logActivity($currentUser['id'], 'USER_CREATE', "تم إنشاء مستخدم جديد: {$username}");
            header('Location: /users.php?success=created');
            exit;
        } else {
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
    <title>إضافة مستخدم جديد - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>إضافة مستخدم جديد</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/users.php" class="btn btn-secondary">← العودة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3 class="card-title">إضافة مستخدم جديد</h3>

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
                        placeholder="أدخل اسم المستخدم"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
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
                        placeholder="أدخل البريد الإلكتروني"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="أدخل كلمة المرور (6 أحرف على الأقل)"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="أعد إدخال كلمة المرور"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="role">الدور *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>مستخدم عادي</option>
                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>مدير</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">💾 حفظ المستخدم</button>
                    <a href="/users.php" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
