<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/csv-logs.php';

requireAuth();

$currentUser = getCurrentUser();

// Filters
$filters = [
    'username' => $_GET['username'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

$logs = getCsvLogs($perPage, $offset, $filters);
$totalLogs = getCsvLogsCount($filters);
$totalPages = ceil($totalLogs / $perPage);
$stats = getCsvLogsStats();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجلات CSV - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>سجلات CSV - إجمالي <?= number_format($totalLogs) ?> سجل</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <?php if (isAdmin()): ?>
                <a href="/csv-upload.php" class="btn btn-primary">⬆️ رفع CSV</a>
                <?php endif; ?>
                <a href="/dashboard.php" class="btn btn-secondary">← العودة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div>
                    <p class="stat-label">إجمالي السجلات</p>
                    <h2 class="stat-value"><?= number_format($stats['total']) ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div>
                    <p class="stat-label">عدد المستخدمين</p>
                    <h2 class="stat-value"><?= number_format($stats['unique_users']) ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div>
                    <p class="stat-label">تسجيلات دخول</p>
                    <h2 class="stat-value"><?= number_format($stats['total_logins']) ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div>
                    <p class="stat-label">فشل المصادقة</p>
                    <h2 class="stat-value"><?= number_format($stats['failed_auths']) ?></h2>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <h3 class="card-title">🔍 فلترة البحث</h3>
            <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($filters['username']) ?>" placeholder="بحث بالاسم...">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="action_type">نوع الإجراء</label>
                    <select id="action_type" name="action_type" class="form-control">
                        <option value="">الكل</option>
                        <option value="login" <?= $filters['action_type'] === 'login' ? 'selected' : '' ?>>تسجيل دخول</option>
                        <option value="logout" <?= $filters['action_type'] === 'logout' ? 'selected' : '' ?>>تسجيل خروج</option>
                        <option value="auth_failed" <?= $filters['action_type'] === 'auth_failed' ? 'selected' : '' ?>>فشل المصادقة</option>
                        <option value="authenticated" <?= $filters['action_type'] === 'authenticated' ? 'selected' : '' ?>>مصادقة</option>
                        <option value="connected" <?= $filters['action_type'] === 'connected' ? 'selected' : '' ?>>متصل</option>
                        <option value="disconnected" <?= $filters['action_type'] === 'disconnected' ? 'selected' : '' ?>>منفصل</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="date_from">من تاريخ</label>
                    <input type="datetime-local" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from']) ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="date_to">إلى تاريخ</label>
                    <input type="datetime-local" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to']) ?>">
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: end;">
                    <button type="submit" class="btn btn-primary">🔍 بحث</button>
                    <a href="/csv-logs.php" class="btn btn-secondary">🔄 إعادة تعيين</a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <h3 class="card-title">📝 سجلات PPPoE</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>التاريخ والوقت</th>
                            <th>المستخدم</th>
                            <th>الإجراء</th>
                            <th>الرسالة</th>
                            <th>IP</th>
                            <th>MAC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                لا توجد سجلات
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('Y-m-d H:i:s', strtotime($log['timestamp'])) ?></td>
                            <td><strong><?= $log['username'] ? htmlspecialchars($log['username']) : '-' ?></strong></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-info';
                                $actionLabel = $log['action_type'] ?? '-';
                                
                                if ($log['action_type'] === 'login') {
                                    $badgeClass = 'badge-success';
                                    $actionLabel = 'دخول';
                                } elseif ($log['action_type'] === 'logout') {
                                    $badgeClass = 'badge-warning';
                                    $actionLabel = 'خروج';
                                } elseif ($log['action_type'] === 'auth_failed') {
                                    $badgeClass = 'badge-danger';
                                    $actionLabel = 'فشل';
                                } elseif ($log['action_type'] === 'authenticated') {
                                    $badgeClass = 'badge-success';
                                    $actionLabel = 'مصادقة';
                                } elseif ($log['action_type'] === 'connected') {
                                    $badgeClass = 'badge-info';
                                    $actionLabel = 'متصل';
                                } elseif ($log['action_type'] === 'disconnected') {
                                    $badgeClass = 'badge-warning';
                                    $actionLabel = 'منفصل';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $actionLabel ?></span>
                            </td>
                            <td style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['message']) ?>">
                                <?= htmlspecialchars($log['message']) ?>
                            </td>
                            <td><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                            <td><code style="font-size: 0.8rem;"><?= htmlspecialchars($log['mac_address'] ?? '-') ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= !empty($filters['username']) ? '&username=' . urlencode($filters['username']) : '' ?><?= !empty($filters['action_type']) ? '&action_type=' . urlencode($filters['action_type']) : '' ?>" class="btn btn-secondary">← السابق</a>
                <?php endif; ?>
                
                <span style="color: var(--text-secondary);">
                    صفحة <?= $page ?> من <?= $totalPages ?>
                </span>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= !empty($filters['username']) ? '&username=' . urlencode($filters['username']) : '' ?><?= !empty($filters['action_type']) ? '&action_type=' . urlencode($filters['action_type']) : '' ?>" class="btn btn-secondary">التالي →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
