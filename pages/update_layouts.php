<?php
$pages_dir = __DIR__ . '/../pages/';
$files = scandir($pages_dir);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $filepath = $pages_dir . $file;
        $content = file_get_contents($filepath);

        // Add layout.php require if not exists
        if (strpos($content, 'require_once') === false) {
            $content = "<?php require_once '../includes/layout.php'; ?>\n" . $content;
        }

        // Replace header section with page_header call
        $content = preg_replace(
            '/<!DOCTYPE.*?<style>/s',
            '<?php echo page_header("' . ucfirst(str_replace('.php', '', $file)) . '"); ?>\n<style>',
            $content
        );

        // Replace footer section with page_footer call
        $content = preg_replace(
            '/<script>.*?<\/html>/s',
            '<?php echo page_footer(); ?>',
            $content
        );

        // Save updated content
        file_put_contents($filepath, $content);
    }
}

echo "All pages updated with new layout template\n";
?>