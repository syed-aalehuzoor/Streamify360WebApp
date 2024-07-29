<?php

function connect_to_ssh($server, $port = 22, $username = 'ubuntu', $password = 'Hurnara12345@'){

    $connection = ssh2_connect($server, $port);
    if (!$connection) {
        return 0;
    }

    if (!ssh2_auth_password($connection, $username, $password)) {
        return 0;
    }
    
    return $connection;
}

?>