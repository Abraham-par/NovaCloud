<?php
echo '<h1>Simple Test</h1>';

// Check current directory
echo 'Current dir: ' . __DIR__ . '<br>';

// List all files and folders
echo '<h3>All files in this folder:</h3>';
$files = scandir(__DIR__);
echo '<ul>';
foreach ($files as $file) {
    $type = is_dir($file) ? '[DIR]' : '[FILE]';
    echo "<li>$type $file</li>";
}
echo '</ul>';

// Check if includes folder exists
echo '<h3>Checking includes folder:</h3>';
if (is_dir(__DIR__ . '/includes')) {
    echo '✅ includes folder exists<br>';
    
    // List files in includes
    $inc_files = scandir(__DIR__ . '/includes');
    echo 'Files in includes: ' . implode(', ', $inc_files);
} else {
    echo '❌ includes folder DOES NOT exist<br>';
    echo 'Creating it...<br>';
    
    if (mkdir(__DIR__ . '/includes', 0755, true)) {
        echo '✅ Created includes folder<br>';
    } else {
        echo '❌ Failed to create includes folder<br>';
    }
}
?>