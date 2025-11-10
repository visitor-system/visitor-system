<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_layout.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch notifications ordered by latest
$res = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");

// Mark all unread notifications as read
$conn->query("UPDATE notifications SET status='read' WHERE status='unread'");

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Notifications', 'url' => '', 'icon' => 'bell']
];

echo erp_header('Visitor Notifications', $breadcrumbs);

// Helper for formatting time
function formatDateTime($datetime) {
    return (!empty($datetime) && $datetime != '0000-00-00 00:00:00')
        ? date('d M Y, h:i A', strtotime($datetime))
        : '—';
}
?>

<style>
/* PAGE STYLING */
.erp-card {
    max-width: 1400px;
    margin: 40px auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Header */
.erp-card-header {
    background: linear-gradient(90deg, #0056b3, #007bff);
    color: #fff;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.erp-card-header h3 {
    font-size: 20px;
    font-weight: 600;
}

/* Body */
.erp-card-body {
    padding: 25px 35px;
}

/* Notification Card */
.notification-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9f9f9;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 12px;
    border-left: 6px solid #ccc;
    transition: 0.3s ease;
    cursor: pointer;
}
.notification-card:hover {
    background: #f1f1f1;
    transform: scale(1.01);
}
.notification-card.in {
    border-left-color: #198754;
}
.notification-card.out {
    border-left-color: #dc3545;
}

/* Icon Section */
.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
}
.notification-icon.in {
    background-color: #198754;
}
.notification-icon.out {
    background-color: #dc3545;
}

/* Content Section */
.notification-content {
    flex: 1;
    margin-left: 20px;
}
.notification-content h5 {
    margin: 0;
    font-size: 17px;
    color: #0056b3;
    cursor: pointer;
    text-decoration: underline;
}
.notification-content p {
    margin: 4px 0 0;
    font-size: 14px;
    color: #555;
}

/* Right Info Section */
.notification-info {
    text-align: right;
    font-size: 13px;
}
.notification-info .time {
    color: #777;
}
.notification-info .purpose {
    font-weight: 500;
    color: #0056b3;
}
.no-data {
    text-align: center;
    color: #777;
    font-size: 15px;
    padding: 50px 0;
}

/* MODAL STYLE */
.modal {
    display: none; 
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
}
.modal-content {
    background: #fff;
    margin: 10% auto;
    padding: 25px 30px;
    border-radius: 10px;
    width: 500px;
    max-width: 95%;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    animation: popin 0.3s ease;
}
@keyframes popin {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.modal-header {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #0056b3;
}
.modal-close {
    float: right;
    font-size: 22px;
    color: #999;
    cursor: pointer;
}
.modal-close:hover {
    color: #000;
}
.modal-details p {
    margin: 6px 0;
    font-size: 15px;
    color: #333;
}
.modal-details strong {
    color: #0056b3;
}
</style>

<div class="erp-card">
    <div class="erp-card-header">
        <h3><i class="fas fa-bell"></i> Visitor Notifications</h3>
    </div>

    <div class="erp-card-body">
        <?php if ($res && $res->num_rows > 0): ?>
            <?php while ($n = $res->fetch_assoc()): ?>
                <?php
                    $msg = strtolower($n['message']);
                    $isCheckin = (strpos($msg, 'check-in') !== false || strpos($msg, 'checked in') !== false);
                    $isCheckout = (strpos($msg, 'check-out') !== false || strpos($msg, 'checked out') !== false);
                    $class = $isCheckin ? 'in' : ($isCheckout ? 'out' : '');
                    $iconClass = $isCheckin ? 'fa-sign-in-alt' : ($isCheckout ? 'fa-sign-out-alt' : 'fa-user');
                    $iconColor = $isCheckin ? 'in' : ($isCheckout ? 'out' : '');
                    $created = formatDateTime($n['created_at']);
                ?>
                <div class="notification-card <?= $class ?>" 
                    onclick="showDetails(
                        '<?= htmlspecialchars(addslashes($n['visitor_name'])) ?>',
                        '<?= htmlspecialchars(addslashes($n['mobile'])) ?>',
                        '<?= htmlspecialchars(addslashes($n['purpose'])) ?>',
                        '<?= htmlspecialchars(addslashes($n['message'])) ?>',
                        '<?= $created ?>'
                    )">
                    <div class="notification-icon <?= $iconColor ?>">
                        <i class="fas <?= $iconClass ?>"></i>
                    </div>
                    <div class="notification-content">
                        <h5><?= htmlspecialchars($n['visitor_name']) ?></h5>
                        <p><?= htmlspecialchars($n['message']) ?></p>
                    </div>
                    <div class="notification-info">
                        <div class="purpose"><?= htmlspecialchars($n['purpose']) ?></div>
                        <div class="time"><?= $created ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-bell-slash fa-2x"></i><br>
                No visitor notifications yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- POPUP MODAL -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-header">Visitor Details</div>
        <div class="modal-details">
            <p><strong>Name:</strong> <span id="m_name"></span></p>
            <p><strong>Mobile:</strong> <span id="m_mobile"></span></p>
            <p><strong>Purpose:</strong> <span id="m_purpose"></span></p>
            <p><strong>Message:</strong> <span id="m_message"></span></p>
            <p><strong>Time:</strong> <span id="m_time"></span></p>
        </div>
    </div>
</div>

<script>
function showDetails(name, mobile, purpose, message, time) {
    document.getElementById('m_name').textContent = name;
    document.getElementById('m_mobile').textContent = mobile;
    document.getElementById('m_purpose').textContent = purpose;
    document.getElementById('m_message').textContent = message;
    document.getElementById('m_time').textContent = time;
    document.getElementById('detailsModal').style.display = 'block';
}
function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}
window.onclick = function(e) {
    if (e.target == document.getElementById('detailsModal')) {
        closeModal();
    }
};
</script>

<?php echo erp_footer(); ?>