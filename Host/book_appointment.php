<?php
session_start();
require_once '../includes/db.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/erp_layout.php';
date_default_timezone_set('Asia/Kolkata');

require '../vendor/autoload.php';  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

if (!isset($_SESSION['user'])) {
    header("Location: ../pages/login.php");
    exit;
}

//Fetch all departments for the dropdown safely
$departments = [];
if ($conn->query("SHOW TABLES LIKE 'departments'")->num_rows > 0) {
    $departmentsResult = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
    if ($departmentsResult) {
        while ($d = $departmentsResult->fetch_assoc()) {
            $departments[] = $d;
        }
    }
}

// AJAX & form handling code
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $response = [];
    $action = $_GET['ajax'];
    $user_id = $_SESSION['user']['id'];

    $limit = 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    if ($action == 'load_table') {
        $search = $_GET['search'] ?? '';
        $search = "%$search%";

        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE host_id=? AND deleted=0 AND (visitor_name LIKE ? OR mobile LIKE ? OR company LIKE ? OR Email LIKE ?)");
        $stmt->bind_param("issss", $user_id, $search, $search, $search, $search);
        $stmt->execute();
        $res = $stmt->get_result();
        $total = $res->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);
        $stmt->close();

        $stmt = $conn->prepare("SELECT id, visitor_name, mobile, company, whom_to_meet, department, purpose, num_of_people, DATE_FORMAT(appointment_time,'%d-%m-%Y %H:%i') as appointment_time, Email 
            FROM appointments WHERE host_id=? AND deleted=0 AND (visitor_name LIKE ? OR mobile LIKE ? OR company LIKE ? OR Email LIKE ?) ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("issssii", $user_id, $search, $search, $search, $search, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $appointments = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        ob_start();
        ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Visitor Name</th>
                    <th>Mobile</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Whom to Meet</th>
                    <th>Purpose</th>
                    <th>No. of People</th>
                    <th>Appointment Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $idx => $a): ?>
                    <tr>
                        <td><?= $offset + $idx + 1 ?></td>
                        <td><?= htmlspecialchars($a['visitor_name']) ?></td>
                        <td><?= htmlspecialchars($a['mobile']) ?></td>
                        <td><?= htmlspecialchars($a['company']) ?></td>
                        <td><?= htmlspecialchars($a['Email']) ?></td>
                        <td><?= htmlspecialchars($a['department']) ?></td>
                        <td><?= htmlspecialchars($a['whom_to_meet']) ?></td>
                        <td><?= htmlspecialchars($a['purpose']) ?></td>
                        <td><?= htmlspecialchars($a['num_of_people']) ?></td>
                        <td><?= $a['appointment_time'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editAppointment(<?= $a['id'] ?>)">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteAppointment(<?= $a['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p == $page ? 'active' : '' ?>"><a class="page-link" href="#"
                            onclick="loadAppointments(<?= $p ?>, document.getElementById('appointmentSearch').value);return false;">
                            <?= $p ?>
                        </a></li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php
        $response['html'] = ob_get_clean();
        echo json_encode($response);
        exit;
    }

    if ($action == 'get' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM appointments WHERE id=? AND host_id=? AND deleted=0");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();
        $data['appointment_time'] = date('Y-m-d\TH:i', strtotime($data['appointment_time']));
        echo json_encode($data);
        exit;
    }

    if ($action == 'fetch_by_mobile' && isset($_GET['mobile'])) {
        $mobile = $_GET['mobile'];
        $stmt = $conn->prepare("SELECT * FROM appointments WHERE mobile=? AND host_id=? AND deleted=0 ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("si", $mobile, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc() ?? [];
        if ($data) $data['appointment_time'] = date('Y-m-d\TH:i', strtotime($data['appointment_time']));
        echo json_encode($data);
        exit;
    }

    if ($action == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE appointments SET deleted=1 WHERE id=? AND host_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $stmt->close();
        $stmt2 = $conn->prepare("DELETE FROM passes WHERE appointment_id=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
        echo json_encode(['status' => 'success', 'message' => 'Appointment deleted successfully!']);
        exit;
    }
}

// Handle add/update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_appointment'])) {
    $errors = [];
    $visitor_name = trim($_POST['visitor_name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $whom_to_meet = trim($_POST['whom_to_meet'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $num_of_people = intval($_POST['num_of_people'] ?? 0);
    $appointment_time = $_POST['appointment_time'] ?? '';
    $Email = trim($_POST['email'] ?? '');
    $edit_id = intval($_POST['edit_id'] ?? 0);

    if ($visitor_name === '') $errors['visitor_name'] = 'Please enter visitor name.';
    if ($mobile === '') $errors['mobile'] = 'Please enter mobile number.';
    elseif (!preg_match('/^[0-9]{10}$/', $mobile)) $errors['mobile'] = 'Mobile must be 10 digits.';
    if ($company === '') $errors['company'] = 'Enter company name.';
    if ($whom_to_meet === '') $errors['whom_to_meet'] = 'Enter whom to meet.';
    if ($department === '') $errors['department'] = 'Enter department.';
    if ($purpose === '') $errors['purpose'] = 'Enter purpose.';
    if ($num_of_people <= 0) $errors['num_of_people'] = 'Enter number of people.';
    if ($appointment_time === '') $errors['appointment_time'] = 'Select appointment time.';
    elseif (strtotime($appointment_time) < time()) $errors['appointment_time'] = 'Cannot be in past.';
    if ($Email === '') $errors['email'] = 'Please enter email address.';
    elseif (!filter_var($Email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address.';

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    $userid = $_SESSION['user']['id'];

    if ($edit_id > 0) {
        $stmt = $conn->prepare("UPDATE appointments SET visitor_name=?, mobile=?, company=?, whom_to_meet=?, department=?, purpose=?, num_of_people=?, appointment_time=?, Email=? WHERE id=? AND host_id=?");
        $stmt->bind_param("ssssssisiii", $visitor_name, $mobile, $company, $whom_to_meet, $department, $purpose, $num_of_people, $appointment_time, $Email, $edit_id, $userid);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Appointment updated successfully!']);
        exit;
    } else {
        $status = 'pending';
        $stmt = $conn->prepare("INSERT INTO appointments (visitor_name,mobile,company,whom_to_meet,department,purpose,num_of_people,appointment_time,status,host_id,Email) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssissis", $visitor_name, $mobile, $company, $whom_to_meet, $department, $purpose, $num_of_people, $appointment_time, $status, $userid, $Email);
        $stmt->execute();
        $appointment_id = $conn->insert_id;
        $stmt->close();

        $pass_number = 'VP' . str_pad($appointment_id, 5, '0', STR_PAD_LEFT);
        $qrDir = '../assets/qrcodes/';
        if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);
        $qrPath = $qrDir . $pass_number . '.png';
        if (function_exists('imagecreate')) QRcode::png('Visitor Pass ID: ' . $pass_number, $qrPath, QR_ECLEVEL_L, 4);

        $stmt2 = $conn->prepare("INSERT INTO passes (appointment_id, pass_number, qr_code, status) VALUES (?,?,?,'waiting')");
        $stmt2->bind_param("iss", $appointment_id, $pass_number, $qrPath);
        $stmt2->execute();
        $stmt2->close();

        echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
        exit;
    }
}

$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Book Appointment', 'icon' => 'calendar-plus']
];
echo erp_header('Book Appointment', $breadcrumbs);
?>

<!-- Appointment Form -->
<div id="alert-container"></div>

<div class="erp-card mb-4 appointment-card">
    <div class="erp-card-header">
        <h5 class="erp-card-title" id="formHeading"><i class="fas fa-calendar-plus me-2"></i>Book New Appointment</h5>
    </div>
    <div class="erp-card-body compact-form">
        <form id="appointmentForm">
            <input type="hidden" name="save_appointment" value="1">
            <input type="hidden" name="edit_id" id="edit_id" value="">
            <div class="row g-2">
                <div class="col-md-3">
                    <label>Mobile <span class="text-danger">*</span></label>
                    <input autocomplete="off" type="text" id="mobile" name="mobile" class="form-control form-sm" maxlength="10">
                    <div class="text-danger small" id="mobile_error"></div>
                </div>
                <div class="col-md-3">
                    <label>Visitor Name <span class="text-danger">*</span></label>
                    <input autocomplete="off" type="text" id="visitor_name" name="visitor_name" class="form-control form-sm" >
                    <div class="text-danger small" id="visitor_name_error"></div>
                </div>
                <div class="col-md-5">
                    <label>Company <span class="text-danger">*</span></label>
                    <input autocomplete="off" type="text" id="company" name="company" class="form-control form-sm" >
                    <div class="text-danger small" id="company_error"></div>
                </div>
                <div class="col-md-3">
                    <label>Whom to Meet <span class="text-danger">*</span></label>
                    <input autocomplete="off" type="text" id="whom_to_meet" name="whom_to_meet" class="form-control form-sm" >
                    <div class="text-danger small" id="whom_to_meet_error"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select name="department" id="department" class="form-select" style="line-height:1.2">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['name'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="text-danger small" id="department_error"></div>
                </div>
                <div class="col-md-3">
                    <label>Purpose <span class="text-danger">*</span></label>
                    <textarea id="purpose" name="purpose" class="form-control form-sm" rows="2"></textarea>
                    <div class="text-danger small" id="purpose_error"></div>
                </div>
                <div class="col-md-3">
                    <label>No. of People <span class="text-danger">*</span></label>
                    <input autocomplete="off" type="number" id="num_of_people" name="num_of_people" class="form-control form-sm" min="1" value="1">
                    <div class="text-danger small" id="num_of_people_error"></div>
                </div>
                <div class="col-md-3">
                    <label>Appointment Date & Time <span class="text-danger">*</span></label>
                    <input id="appointment_time" type="datetime-local" name="appointment_time" class="form-control form-sm" value="<?= htmlspecialchars($_POST['appointment_time'] ?? date('Y-m-d\TH:i')) ?>" min="<?= date('Y-m-d\TH:i') ?>">
                    <div class="text-danger small" id="appointment_time_error"></div>
                </div>
                <div class="col-md-3">
                    <label>Email <span class="text-danger">*</span></label>
                    <input autocomplete="off" type="email" id="email" name="email" class="form-control form-sm">
                    <div class="text-danger small" id="email_error"></div>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary btn-sm" id="formSubmitBtn"><i class="fas fa-save"></i> Confirm Appointment</button>
            </div>
        </form>
    </div>
</div>

<!-- Appointment List with Search -->
<div class="erp-card mt-4 appointment-list-card">
    <div class="erp-card-header d-flex justify-content-between align-items-center">
        <h5 class="erp-card-title"><i class="fas fa-list me-2"></i>Your Appointments</h5>
        <input type="text" id="appointmentSearch" class="form-control form-control-sm" placeholder="Search by Name, Mobile, Company, or Email" style="max-width: 300px;">
    </div>
    <div class="erp-card-body table-responsive" id="appointmentsTableContainer"></div>
</div>

<style>
    .appointment-card { background: #dfe0e0ff; border-radius: 8px; }
    .compact-form .form-control { padding: 4px 8px; font-size: 13px; height: 30px; }
    .compact-form textarea.form-control { height: 30px; resize: none; overflow-y: hidden; line-height: 1.2; padding: 4px 8px; font-size: 13px; }
    .compact-form label { font-size: 13px; margin-bottom: 3px; }
    .compact-form .row.g-2 { row-gap: 0.5rem !important; }
    .appointment-list-card { background: #fdfdfdff; border: 1px solid #eee; }
</style>

<script>
let currentPage = 1;

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

function loadAppointments(page = 1, query = '') {
    currentPage = page;
    fetch(`?ajax=load_table&page=${page}&search=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => { document.getElementById('appointmentsTableContainer').innerHTML = data.html; });
}

function deleteAppointment(id) {
    if (confirm('Are you sure you want to delete this appointment?')) {
        fetch('?ajax=delete&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('alert-container').innerHTML = '<div class="alert alert-success mt-2">' + data.message + '</div>';
                    loadAppointments(currentPage, document.getElementById('appointmentSearch').value);
                }
            });
    }
}

loadAppointments();

document.getElementById('appointmentSearch').addEventListener('input', debounce(function() {
    loadAppointments(1, this.value.trim());
}, 300));

document.getElementById('appointmentForm').addEventListener('submit', function (e) {
    e.preventDefault();
    let fd = new FormData(this);
    fetch('', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
        ['visitor_name','mobile','company','whom_to_meet','purpose','num_of_people','appointment_time','email','department'].forEach(f => document.getElementById(f+'_error').innerText='');
        document.getElementById('alert-container').innerHTML='';

        if(data.status==='error'){
            for(let f in data.errors) document.getElementById(f+'_error').innerText = data.errors[f];
        } else {
            if(document.getElementById('edit_id').value > 0){
                document.getElementById('alert-container').innerHTML = '<div class="alert alert-success mt-2">' + data.message + '</div>';
                document.getElementById('formHeading').innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Book New Appointment';
                document.getElementById('formSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Confirm Appointment';
                document.getElementById('appointmentForm').reset();
                document.getElementById('edit_id').value = '';
                loadAppointments(currentPage, document.getElementById('appointmentSearch').value);
            } else {
                window.location.href='appointment_success.php';
            }
        }
    });
});

document.getElementById('mobile').addEventListener('blur', function(){
    let mobile = this.value.trim();
    if(mobile.length===10){
        fetch('?ajax=fetch_by_mobile&mobile='+mobile)
        .then(r=>r.json())
        .then(data=>{
            if(Object.keys(data).length>0){
                document.getElementById('visitor_name').value = data.visitor_name;
                document.getElementById('company').value = data.company;
                document.getElementById('whom_to_meet').value = data.whom_to_meet;
                document.getElementById('department').value = data.department;
                document.getElementById('purpose').value = data.purpose;
                document.getElementById('num_of_people').value = data.num_of_people;
                document.getElementById('appointment_time').value = data.appointment_time;
                document.getElementById('email').value = data.Email;
            }
        });
    }
});

function editAppointment(id) {
    fetch('?ajax=get&id=' + id).then(r => r.json()).then(data => {
        document.getElementById('visitor_name').value = data.visitor_name;
        document.getElementById('mobile').value = data.mobile;
        document.getElementById('company').value = data.company;
        document.getElementById('whom_to_meet').value = data.whom_to_meet;
        document.getElementById('department').value = data.department;
        document.getElementById('purpose').value = data.purpose;
        document.getElementById('num_of_people').value = data.num_of_people;
        document.getElementById('appointment_time').value = data.appointment_time;
        document.getElementById('email').value = data.Email;
        document.getElementById('edit_id').value = data.id;

        document.getElementById('formHeading').innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Edit Appointment';
        document.getElementById('formSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Edit Appointment';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

const appointmentInput = document.getElementById('appointment_time');
appointmentInput.addEventListener('input', () => appointmentInput.blur());
appointmentInput.addEventListener('keydown', e => e.preventDefault());
appointmentInput.addEventListener('focus', () => { if (appointmentInput.showPicker) appointmentInput.showPicker(); });
</script>

<?php echo erp_footer(); ?>