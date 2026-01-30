<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/users.php';
require_once 'includes/logger.php';

requireAuth();

$currentUser = getCurrentUser();
$users = getAllUsers();
$logs = getLogs(50);
$stats = getLogStats();
$userCount = getUserCount();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>مرحباً <strong><?= htmlspecialchars($currentUser['username']) ?></strong></p>
            </div>
            <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div>
                    <p class="stat-label">إجمالي المستخدمين</p>
                    <h2 class="stat-value"><?= $userCount ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div>
                    <p class="stat-label">إجمالي السجلات</p>
                    <h2 class="stat-value"><?= $stats['total'] ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div>
                    <p class="stat-label">تسجيلات ناجحة</p>
                    <h2 class="stat-value"><?= $stats['successful_logins'] ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div>
                    <p class="stat-label">تسجيلات فاشلة</p>
                    <h2 class="stat-value"><?= $stats['failed_logins'] ?></h2>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('users')">👥 المستخدمون</button>
            <button class="tab" onclick="showTab('logs')">📝 سجل البيانات</button>
        </div>

        <!-- Users Tab -->
        <div id="users-tab" class="card">
            <h3 class="card-title">جدول المستخدمين</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الدور</th>
                            <th>تاريخ الإنشاء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'badge-danger' : 'badge-info' ?>">
                                    <?= $user['role'] === 'admin' ? 'مدير' : 'مستخدم' ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Logs Tab -->
        <div id="logs-tab" class="card" style="display: none;">
            <h3 class="card-title">سجل البيانات</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>الإجراء</th>
                            <th>الوصف</th>
                            <th>عنوان IP</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td><?= $log['username'] ? htmlspecialchars($log['username']) : '-' ?></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-info';
                                if ($log['action'] === 'LOGIN_SUCCESS') $badgeClass = 'badge-success';
                                elseif ($log['action'] === 'LOGIN_FAILED') $badgeClass = 'badge-danger';
                                elseif ($log['action'] === 'LOGOUT') $badgeClass = 'badge-warning';
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td><?= $log['description'] ? htmlspecialchars($log['description']) : '-' ?></td>
                            <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.getElementById('users-tab').style.display = 'none';
            document.getElementById('logs-tab').style.display = 'none';
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
