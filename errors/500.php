<?php
require_once __DIR__ . '/../includes/config.php';
http_response_code(500);
include __DIR__ . '/../includes/header.php';
?>
<div class="error-container">
    <div class="error-content">
        <h1>500</h1>
        <h2 data-key="server_error">Server Error</h2>
        <p data-key="500_message">Something went wrong on our end. Please try again later.</p>
        <a href="<?php echo SITE_URL; ?>index.php" class="btn btn-primary" data-key="go_home">Go to Homepage</a>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>