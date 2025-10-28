<?php
session_start();
require_once '../includes/db.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/erp_layout.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_appointment'])) {
    $visitor_name = trim($_POST['visitor_name']);
    $mobile = trim($_POST['mobile']);
    $company = trim($_POST['company']);
    $whom_to_meet = trim($_POST['whom_to_meet']);
    $purpose = trim($_POST['purpose']);
    $num_of_people = intval($_POST['num_of_people']);
    $appointment_time = $_POST['appointment_time'];
    header('Content-Type: application/json');

    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        echo json_encode(['success' => false, 'message' => 'Mobile number must be exactly 10 digits']);
        exit;
    }

    if (strtotime($appointment_time) < time()) {
        echo json_encode(['success' => false, 'message' => 'Appointment time cannot be in the past']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO appointments (visitor_name, mobile, company, whom_to_meet, purpose, num_of_people, appointment_time, status, host_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("sssssisi", $visitor_name, $mobile, $company, $whom_to_meet, $purpose, $num_of_people, $appointment_time, $_SESSION['user']['id']);
    $stmt->execute();
    $appointment_id = $conn->insert_id;
    $stmt->close();

    $pass_number = 'VP' . str_pad($appointment_id, 5, '0', STR_PAD_LEFT);
    $qrDir = '../assets/qrcodes/';
    if (!is_dir($qrDir))
        mkdir($qrDir, 0777, true);
    $qrPath = $qrDir . $pass_number . '.png';
    QRcode::png('Visitor Pass ID: ' . $pass_number, $qrPath, QR_ECLEVEL_L, 4);

    $stmt2 = $conn->prepare("INSERT INTO passes (appointment_id, pass_number, qr_code, status) VALUES (?, ?, ?, 'waiting')");
    $stmt2->bind_param("iss", $appointment_id, $pass_number, $qrPath);
    $stmt2->execute();
    $stmt2->close();

    echo json_encode([
        'success' => true,
        'redirect' => 'appointment_success.php',
        'newAppointment' => [
            'visitor_name' => $visitor_name,
            'mobile' => $mobile,
            'company' => $company,
            'whom_to_meet' => $whom_to_meet,
            'purpose' => $purpose,
            'num_of_people' => $num_of_people,
            'appointment_time' => str_replace('T', ' ', $appointment_time)
        ]
    ]);
    exit;
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_appointment'])) {
    $id = intval($_POST['id']);
    $visitor_name = trim($_POST['visitor_name']);
    $mobile = trim($_POST['mobile']);
    $company = trim($_POST['company']);
    $whom_to_meet = trim($_POST['whom_to_meet']);
    $purpose = trim($_POST['purpose']);
    $num_of_people = intval($_POST['num_of_people']);
    $appointment_time = $_POST['appointment_time'];

    $stmt = $conn->prepare("UPDATE appointments SET visitor_name=?, mobile=?, company=?, whom_to_meet=?, purpose=?, num_of_people=?, appointment_time=? WHERE id=? AND host_id=?");
    $stmt->bind_param("sssssisii", $visitor_name, $mobile, $company, $whom_to_meet, $purpose, $num_of_people, $appointment_time, $id, $_SESSION['user']['id']);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE appointments SET deleted = 1 WHERE id=? AND host_id=?");
    $stmt->bind_param("ii", $id, $_SESSION['user']['id']);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

function fetchAppointments($conn, $host_id)
{
    $stmt = $conn->prepare("SELECT id, visitor_name, mobile, company, whom_to_meet, purpose, num_of_people, DATE_FORMAT(appointment_time,'%d-%m-%Y %H:%i') as appointment_time, status, appointment_time as raw_time FROM appointments WHERE host_id=? AND deleted = 0 ORDER BY appointment_time DESC");
    $stmt->bind_param("i", $host_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $appointments = [];
    while ($row = $res->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
    return $appointments;
}

$appointments = fetchAppointments($conn, $_SESSION['user']['id']);
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Book Appointment', 'icon' => 'calendar-plus']
];

echo erp_header('Book Appointment', $breadcrumbs);
?>

<div class="erp-card mb-4">
    <div class="erp-card-header">
        <h5 class="erp-card-title"><i class="fas fa-calendar-plus me-2"></i>Book New Appointment</h5>
    </div>
    <div class="erp-card-body">
        <form id="appointmentForm">
            <input type="hidden" name="save_appointment" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Mobile" required>
                    <div id="mobileError" class="text-danger mt-1" style="display:none;">Mobile number cannot exceed 10
                        digits</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" name="visitor_name" class="form-control" placeholder="Visitor Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control" placeholder="Company" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Host Name (You)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>"
                        readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Whom to Meet</label>
                    <input type="text" name="whom_to_meet" class="form-control"
                        placeholder="Person visitor wants to meet" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Purpose of Visit</label>
                    <textarea name="purpose" class="form-control" placeholder="Purpose" required rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Number of People</label>
                    <input type="number" name="num_of_people" class="form-control" min="1"
                        placeholder="Number of People" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Appointment Date & Time</label>
                    <?php $currentDateTime = date('Y-m-d\TH:i'); ?>
                    <input type="datetime-local" name="appointment_time" class="form-control" required
                        min="<?= $currentDateTime ?>" value="<?= $currentDateTime ?>" id="appointment_datetime">
                </div>
            </div>
            <div class="mt-4 text-end">
                <?= erp_button('Confirm Appointment', 'primary', '', 'fas fa-save', 'type="submit"'); ?>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table -->
<div class="erp-card mt-4">
    <div class="erp-card-header">
        <h5 class="erp-card-title"><i class="fas fa-list me-2"></i>Your Appointments</h5>
    </div>
    <div class="erp-card-body table-responsive">
        <table class="table table-bordered table-striped" id="appointmentsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Visitor Name</th>
                    <th>Mobile</th>
                    <th>Company</th>
                    <th>Whom to Meet</th>
                    <th>Purpose</th>
                    <th>No. of People</th>
                    <th>Appointment Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="appointmentsBody">
                <?php foreach ($appointments as $index => $appt): ?>
                    <tr data-id="<?= $appt['id'] ?>" data-name="<?= htmlspecialchars($appt['visitor_name']) ?>"
                        data-mobile="<?= htmlspecialchars($appt['mobile']) ?>"
                        data-company="<?= htmlspecialchars($appt['company']) ?>"
                        data-whom="<?= htmlspecialchars($appt['whom_to_meet']) ?>"
                        data-purpose="<?= htmlspecialchars($appt['purpose']) ?>"
                        data-num="<?= htmlspecialchars($appt['num_of_people']) ?>"
                        data-time="<?= htmlspecialchars($appt['raw_time']) ?>">
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($appt['visitor_name']) ?></td>
                        <td><?= htmlspecialchars($appt['mobile']) ?></td>
                        <td><?= htmlspecialchars($appt['company']) ?></td>
                        <td><?= htmlspecialchars($appt['whom_to_meet']) ?></td>
                        <td><?= htmlspecialchars($appt['purpose']) ?></td>
                        <td><?= htmlspecialchars($appt['num_of_people']) ?></td>
                        <td><?= $appt['appointment_time'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning editBtn"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger deleteBtn"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-3 text-center">
            <nav>
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="col-md-6"><label>Visitor Name</label><input type="text" name="visitor_name"
                            id="edit_name" class="form-control" required></div>
                    <div class="col-md-6"><label>Mobile</label><input type="text" name="mobile" id="edit_mobile"
                            class="form-control" required></div>
                    <div class="col-md-6"><label>Company</label><input type="text" name="company" id="edit_company"
                            class="form-control" required></div>
                    <div class="col-md-6"><label>Whom to Meet</label><input type="text" name="whom_to_meet"
                            id="edit_whom" class="form-control" required></div>
                    <div class="col-12"><label>Purpose</label><textarea name="purpose" id="edit_purpose"
                            class="form-control" required></textarea></div>
                    <div class="col-md-6"><label>No. of People</label><input type="number" name="num_of_people"
                            id="edit_num" class="form-control" required></div>
                    <div class="col-md-6"><label>Appointment Time</label><input type="datetime-local"
                            name="appointment_time" id="edit_time" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const mobileInput = document.getElementById('mobile');
    const mobileError = document.getElementById('mobileError');
    const datetimeInput = document.getElementById('appointment_datetime');
    const appointmentsBody = document.getElementById('appointmentsBody');
    const rowsPerPage = 5;
    let currentPage = 1;

    mobileInput.addEventListener('input', () => {
        if (mobileInput.value.length > 10) {
            mobileError.style.display = 'block';
            mobileInput.value = mobileInput.value.slice(0, 10);
        } else {
            mobileError.style.display = 'none';
        }
    });
    datetimeInput.addEventListener('input', () => datetimeInput.blur());

    document.getElementById('appointmentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else if (!data.success) {
                    alert(data.message);
                }
            });
    });

    function applyPagination() {
        const rows = Array.from(appointmentsBody.querySelectorAll('tr'));
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        rows.forEach((row, index) => {
            row.style.display = (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) ? '' : 'none';
            row.querySelector('td').textContent = index + 1;
        });
        renderPagination(totalPages);
    }
    function renderPagination(totalPages) {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'page-item' + (i === currentPage ? ' active' : '');
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                applyPagination();
            });
            pagination.appendChild(li);
        }
    }
    applyPagination();

    // Edit and Delete functionality
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tr = this.closest('tr');
            document.getElementById('edit_id').value = tr.dataset.id;
            document.getElementById('edit_name').value = tr.dataset.name;
            document.getElementById('edit_mobile').value = tr.dataset.mobile;
            document.getElementById('edit_company').value = tr.dataset.company;
            document.getElementById('edit_whom').value = tr.dataset.whom;
            document.getElementById('edit_purpose').value = tr.dataset.purpose;
            document.getElementById('edit_num').value = tr.dataset.num;
            document.getElementById('edit_time').value = tr.dataset.time.replace(' ', 'T');
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });

    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('edit_appointment', '1');
        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment updated successfully!');
                    location.reload();
                }
            });
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Are you sure you want to delete this appointment?')) return;
            const id = this.closest('tr').dataset.id;
            const formData = new FormData();
            formData.append('delete_appointment', '1');
            formData.append('id', id);
            fetch('', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Deleted successfully!');
                        location.reload();
                    }
                });
        });
    });
</script>

<?php echo erp_footer(); ?>