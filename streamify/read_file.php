<?php
// readfile.php
header('Content-Type: text/plain');
if (isset($_GET['id'])) {
    echo file_get_contents('../'.$_GET['id'].'.txt');
}
?>
