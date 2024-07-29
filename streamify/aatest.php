<?php
// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connection parameters
$server = '48.217.244.62';
$port = 22;
$username = 'ubuntu';
$password = 'Hurnara12345@';
$local_file = './uploaded_files/video.mp4';
$remote_file = '/home/ubuntu/video.mp4';

// Establishing connection
$connection = ssh2_connect($server, $port);
if (!$connection) {
    die('Connection failed');
}
echo "Connection established\n";

// Authenticating
if (!ssh2_auth_password($connection, $username, $password)) {
    die('Authentication failed');
}
echo "Authentication successful\n";

// Sending file
if (!ssh2_scp_send($connection, $local_file, $remote_file, 0644)) {
    die('File transfer failed');
}
echo "File transfer successful\n";

// Close connection
unset($connection);
?>
