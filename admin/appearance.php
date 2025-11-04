<?php
session_start();
require '../includes/db.php';
require '../includes/erp_layout.php';

// Handle system theme updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_system_theme'])) {
    $primary_color = $_POST['primary_color'];
    $secondary_color = $_POST['secondary_color'];

    // Handle logo upload
    $logo_path = '';
    if (isset($_FILES['system_logo']) && $_FILES['system_logo']['error'] == 0) {
        $target_dir = "../assets/themes/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['system_logo']['name'], PATHINFO_EXTENSION));
        $logo_path = $target_dir . "system_logo." . $file_extension;

        if (move_uploaded_file($_FILES['system_logo']['tmp_name'], $logo_path)) {
            $logo_path = "assets/themes/system_logo." . $file_extension;
        }
    }

    // Update system theme
    $sql = "UPDATE system_theme SET primary_color = ?, secondary_color = ?";
    $params = [$primary_color, $secondary_color];
    $types = "ss";

    if ($logo_path) {
        $sql .= ", logo_path = ?";
        $params[] = $logo_path;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $success_message = "System appearance updated successfully!";
    } else {
        $error_message = "Error updating appearance: " . $conn->error;
    }
}

// Get current theme settings
$theme_query = "SELECT * FROM system_theme LIMIT 1";
$theme_result = $conn->query($theme_query);
$theme = $theme_result->fetch_assoc();

$primary_color = $theme['primary_color'] ?? '#007bff';
$secondary_color = $theme['secondary_color'] ?? '#0056b3';
$system_logo = $theme['logo_path'] ?? 'assets/default-logo.png';

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'tachometer-alt'],
    ['title' => 'Appearance Settings', 'icon' => 'palette']
];

echo erp_header('System Appearance', $breadcrumbs);
?>

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
    <?php echo erp_alert($success_message, 'success'); ?>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <?php echo erp_alert($error_message, 'danger'); ?>
<?php endif; ?>

<!-- Styles for Section Background -->
<style>
    .erp-card.form-card {
        background-color: #dde0e2ff;
        /* Gray background for forms/sections */
        border: 1px solid #ccc;
        color: #333;
    }

    .erp-card.form-card .form-label {
        color: #333;
    }
</style>

<!-- Theme Settings Form -->
<div class="erp-card form-card shadow-sm mb-4" style="max-width: 1200px; margin: 0 auto;">
    <div class="erp-card-header bg-light">
        <h3 class="erp-card-title mb-0">
            <i class="fas fa-palette"></i> System Appearance Settings
        </h3>
    </div>

    <form method="POST" enctype="multipart/form-data" class="p-4">
        <div class="row g-4 align-items-center">

            <!-- Logo Upload -->
            <div class="col-md-3 text-center">
                <label class="erp-form-label fw-semibold d-block mb-2">System Logo</label>
                <?php if ($system_logo && file_exists("../" . $system_logo)): ?>
                    <img src="../<?php echo $system_logo; ?>" alt="Current Logo" style="max-width: 220px; max-height: 70px;"
                        class="border rounded p-2 mb-2 shadow-sm">
                <?php endif; ?>
                <input type="file" class="erp-form-control" name="system_logo" accept="image/*">
                <small class="text-muted d-block mt-1">Recommended size: 200x70 pixels</small>
            </div>

            <!-- Primary Color -->
            <div class="col-md-3 text-center">
                <label class="erp-form-label fw-semibold d-block mb-2">Primary Color</label>
                <input type="color" class="form-control form-control-color border-0 shadow-sm" name="primary_color"
                    value="<?php echo $primary_color; ?>"
                    style="width: 70px; height: 35px; cursor: pointer; border-radius: 6px;">
            </div>

            <!-- Secondary Color -->
            <div class="col-md-3 text-center">
                <label class="erp-form-label fw-semibold d-block mb-2">Secondary Color</label>
                <input type="color" class="form-control form-control-color border-0 shadow-sm" name="secondary_color"
                    value="<?php echo $secondary_color; ?>"
                    style="width: 70px; height: 35px; cursor: pointer; border-radius: 6px;">
            </div>

            <!-- Save Button -->
            <div class="col-md-3 text-center">
                <label class="erp-form-label d-block mb-2">&nbsp;</label>
                <button type="submit" name="update_system_theme" class="erp-btn erp-btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>

        </div>
    </form>
</div>

<!-- Theme Preview -->
<div class="erp-card shadow-sm form-card mt-4" style="max-width: 1200px; margin: 0 auto;">
    <div class="erp-card-header bg-light">
        <h3 class="erp-card-title mb-0">
            <i class="fas fa-eye"></i> Theme Preview
        </h3>
    </div>

    <div class="row g-3 p-3">
        <div class="col-md-4">
            <div class="p-3 rounded shadow-sm text-center"
                style="background: <?php echo $primary_color; ?>; color: white;">
                <h6 class="mb-2 fw-semibold">Primary Color</h6>
                <p class="mb-0"><?php echo $primary_color; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded shadow-sm text-center"
                style="background: <?php echo $secondary_color; ?>; color: white;">
                <h6 class="mb-2 fw-semibold">Secondary Color</h6>
                <p class="mb-0"><?php echo $secondary_color; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded shadow-sm bg-light text-center">
                <h6 class="mb-2 fw-semibold">Sample Buttons</h6>
                <button class="erp-btn erp-btn-primary erp-btn-sm me-2 mb-2">Primary</button>
                <button class="erp-btn erp-btn-secondary erp-btn-sm mb-2">Secondary</button>
            </div>
        </div>
    </div>
</div>

<!-- Live Preview Script -->
<script>
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function () {
            if (this.name === 'primary_color') {
                document.querySelectorAll('.erp-card .row .col-md-4')[0].style.backgroundColor = this.value;
            } else if (this.name === 'secondary_color') {
                document.querySelectorAll('.erp-card .row .col-md-4')[1].style.backgroundColor = this.value;
            }
        });
    });
</script>

<!-- Optional: alignment tweak -->
<style>
    input[type="color"] {
        vertical-align: middle;
    }
</style>

<?php echo erp_footer(); ?>