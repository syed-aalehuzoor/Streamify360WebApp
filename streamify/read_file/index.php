<?php
// readfile.php
header('Content-Type: text/plain');

if (isset($_GET['id']) && ctype_alnum($_GET['id'])) {
    $filePath = '../../' . $_GET['id'] . '.txt';
    
    // Check if the file exists and is readable
    if (file_exists($filePath) && is_readable($filePath)) {
        echo file_get_contents($filePath);
    } else {
        echo "Processing";
    }
} else {
    echo "Processing";
}
?>
