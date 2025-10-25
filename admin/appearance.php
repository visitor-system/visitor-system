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

<!-- Theme Settings -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-palette"></i>
                System Appearance Settings
        </h3>
    </div>

            <form method="POST" enctype="multipart/form-data">
        <div class="row g-4">
            <!-- Logo Upload -->
            <div class="col-md-6">
                <div class="erp-form-group">
                    <label class="erp-form-label">System Logo</label>
                    <?php if ($system_logo && file_exists("../" . $system_logo)): ?>
                        <div class="mb-3">
                            <img src="../<?php echo $system_logo; ?>" alt="Current Logo"
                                 style="max-width: 200px; max-height: 100px;" 
                                 class="border rounded p-2">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="erp-form-control" name="system_logo" accept="image/*">
                    <small class="text-muted">Recommended size: 200x100 pixels</small>
                </div>
                </div>

            <!-- Color Settings -->
            <div class="col-md-6">
                <div class="erp-form-group">
                    <label class="erp-form-label">Primary Color</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="color-preview" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ddd; background-color: <?php echo $primary_color; ?>"></div>
                        <input type="color" class="erp-form-control" name="primary_color" value="<?php echo $primary_color; ?>" style="width: 60px; height: 40px;">
                    </div>
                    <small class="text-muted">Used for buttons, links, and active elements</small>
                    </div>
                </div>

            <div class="col-md-6">
                <div class="erp-form-group">
                    <label class="erp-form-label">Secondary Color</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="color-preview" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ddd; background-color: <?php echo $secondary_color; ?>"></div>
                        <input type="color" class="erp-form-control" name="secondary_color" value="<?php echo $secondary_color; ?>" style="width: 60px; height: 40px;">
                    </div>
                    <small class="text-muted">Used for hover states and secondary elements</small>
                </div>
                    </div>
                </div>

        <div class="mt-4">
            <button type="submit" name="update_system_theme" class="erp-btn erp-btn-primary">
                <i class="fas fa-save"></i>
                    Save Changes
                </button>
        </div>
            </form>
</div>

<!-- Theme Preview -->
<div class="erp-card">
    <div class="erp-card-header">
        <h3 class="erp-card-title">
            <i class="fas fa-eye"></i>
            Theme Preview
        </h3>
    </div>
    
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 border rounded" style="background: <?php echo $primary_color; ?>; color: white;">
                <h6>Primary Color</h6>
                <p class="mb-0"><?php echo $primary_color; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded" style="background: <?php echo $secondary_color; ?>; color: white;">
                <h6>Secondary Color</h6>
                <p class="mb-0"><?php echo $secondary_color; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light">
                <h6>Sample Buttons</h6>
                <button class="erp-btn erp-btn-primary erp-btn-sm me-2">Primary</button>
                <button class="erp-btn erp-btn-secondary erp-btn-sm">Secondary</button>
            </div>
        </div>
        </div>
    </div>

    <script>
        // Live preview of color changes
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', function () {
                const preview = this.previousElementSibling;
                preview.style.backgroundColor = this.value;
        
        // Update preview section
        const colorName = this.name === 'primary_color' ? 'Primary Color' : 'Secondary Color';
        const previewDiv = document.querySelector(`[style*="${colorName}"]`);
        if (previewDiv) {
            previewDiv.style.backgroundColor = this.value;
        }
            });
        });
    </script>

<?php echo erp_footer(); ?>