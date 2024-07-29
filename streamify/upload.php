<?php
session_start();
require('dbconfig.php');
require('functions.php');

function get_server($user_id) {
    global $connection;

    // First, try to get the server dedicated to the given user_id
    $stmt = $connection->prepare("SELECT * FROM servers WHERE dedicated=? LIMIT 1");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $server = $result->fetch_assoc();
    $stmt->close();

    // If a dedicated server is found, return it
    if ($server) {
        return $server;
    }

    // If no dedicated server is found, look for servers with dedicated == 0
    $stmt = $connection->prepare("SELECT * FROM servers WHERE dedicated=0");
    $stmt->execute();
    $result = $stmt->get_result();
    $servers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // If there are no servers with dedicated == 0, return null
    if (empty($servers)) {
        return null;
    }

    // Look for a server with processing == 0
    foreach ($servers as $server) {
        if ($server['processing'] == 0) {
            return $server;
        }
    }

    // If no server with processing == 0 is found, return the server with the minimum processing value
    $min_processing_server = $servers[0];
    foreach ($servers as $server) {
        if ($server['processing'] < $min_processing_server['processing']) {
            $min_processing_server = $server;
        }
    }

    return $min_processing_server;
}

function enqueue($item) {
    $queueFile = '../queue.json';

    // Read the current queue
    $queue = [];
    if (file_exists($queueFile)) {
        $queue = json_decode(file_get_contents($queueFile), true);
    }

    // Add the new item to the queue
    $queue[] = $item;

    // Write the updated queue back to the file with JSON_UNESCAPED_SLASHES option
    file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}


if (isset($_FILES['video_file'])) {

    $video_name = $_POST['videoname'];
    if($video_name == ''){
        $video_name = $fileName = $_FILES['video_file']['name'];
    }

    $resolutions = [];

    if (isset($_POST['resolutions'])) {
        foreach ($_POST['resolutions'] as $resolution) {
            // Append resolution to the array
            $resolutions[] = $resolution;
        }
    }

    $fileTmpPath = $_FILES['video_file']['tmp_name'];
    $fileName = $_FILES['video_file']['name'];
    $fileSize = $_FILES['video_file']['size'];
    $fileType = $_FILES['video_file']['type'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Specify the directory where the file will be moved
    $uploadFileDir = '../uploaded_files/';

    // Create directory if it doesn't exist
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0777, true);
    }
    
    $email_login = $_SESSION['email'];
    $username = $_SESSION['username'];

    // Use prepared statements to prevent SQL injection
    $stmt = $connection->prepare("SELECT * FROM users WHERE email=? AND username=? LIMIT 1");
    $stmt->bind_param("ss", $email_login, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();    
    $stmt->close();

    if ($user) {
        $user_id = $user['userid'];
        $server = get_server($user_id);
        $server_key = $server['id'];

        $stmt = $connection->prepare("INSERT INTO videos (user_id, name, server_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $video_name, $server_key);
        if ($stmt->execute()) {
            $video_key = $stmt->insert_id;
            $stmt->close();
            // Move the file to the destination path
            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $video_key . '.' .$fileExtension)) {

                // Prepare video details
                $new_video_details = array(
                    'video_key' => $video_key,
                    'video_ext' => $fileExtension,
                    'video_resolutions' => $resolutions,
                    'server' => $server
                );

                enqueue($new_video_details);

                $_SESSION['success'] = "Video uploaded and processing.";
                header('Location: videos.php');
                exit();

            } else {        
                $_SESSION['status'] = "There was an error in file.";
            }
        } else {
            $_SESSION['status'] = "Error getting video details";
        }
    } else {
        $_SESSION['status'] = "User not found.";
    }
} else {
    $_SESSION['status'] = "Video Upload Error.";
}
header('Location: addvideo.php');
exit();
?>