<?php
    include('security.php');
    include('includes/header.php');
?>

<div class="container-fluid">
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Videos
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addadminprofile">
                    Add Admin Profile 
                </button>
            </h6>
        </div>
        <div class="card-body">
            <?php

            if(isset($_SESSION['success']) && $_SESSION['success'] != ''){
                echo '<div class="col-xl-12 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">' . $_SESSION['success'] . '</div></div></div></div></div></div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
                echo '<div class="col-xl-12 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">' . $_SESSION['status'] . '</div></div></div></div></div></div>';
                unset($_SESSION['status']);
            }            

             ?>
            
<div class="table-responsive">
    <?php
        $email_login = $_SESSION['email'];
        $username = $_SESSION['username'];
        $stmt = $connection->prepare("SELECT * FROM users WHERE email=? AND username=? LIMIT 1");
        $stmt->bind_param("ss", $email_login, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();    
        $stmt->close();

        if ($user) {
            $user_id = $user['userid'];
        } else {
            echo "User not found";
            exit();
        }

        // Handle search
        $search = "";
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }

        // Pagination setup
        $limit = 10; // Number of entries to show in a page.
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        // Total number of pages
        if ($search) {
            $stmt_total = $connection->prepare("SELECT COUNT(*) AS total FROM videos WHERE user_id=? AND name LIKE ?");
            $search_param = "%" . $search . "%";
            $stmt_total->bind_param("is", $user_id, $search_param);
        } else {
            $stmt_total = $connection->prepare("SELECT COUNT(*) AS total FROM videos WHERE user_id=?");
            $stmt_total->bind_param("i", $user_id);
        }
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $total_records = $result_total->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);
        $stmt_total->close();

        // Fetch videos with pagination, sorting, and searching
        if ($search) {
            $stmt_videos = $connection->prepare("SELECT * FROM videos WHERE user_id=? AND name LIKE ? ORDER BY upload_time DESC LIMIT ? OFFSET ?");
            $stmt_videos->bind_param("isii", $user_id, $search_param, $limit, $offset);
        } else {
            $stmt_videos = $connection->prepare("SELECT * FROM videos WHERE user_id=? ORDER BY upload_time DESC LIMIT ? OFFSET ?");
            $stmt_videos->bind_param("iii", $user_id, $limit, $offset);
        }
        $stmt_videos->execute();
        $query_run = $stmt_videos->get_result();
    ?>
    
    <!-- Search form -->
    <form method="GET" action="">
        <div class="input-group mb-3">
            <input type="text" class="form-control" name="search" placeholder="Search videos by name" value="<?php echo htmlspecialchars($search); ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </div>
    </form>
    
    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Details</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($query_run->num_rows > 0) {
                $row_index = $offset + 1;
                while($row = $query_run->fetch_assoc()) {
            ?>
                <tr>
                    <td><?php echo $row_index++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                    <?php
                        if($row['status']=='processing'){
                            echo '<div id="stats-'.$row['id'].'">Processing...</div>';
                            echo '
                            <script>
                                function fetchStats() {
                                    const id = '.$row['id'].';
                                    const xhr = new XMLHttpRequest();
                                    xhr.open("GET", `read_file?id=${id}`, true);
                                    xhr.onload = function() {
                                        if (xhr.status === 200) {
                                            document.getElementById(`stats-${id}`).innerText = xhr.responseText;
                                        } else {
                                            console.error("Failed to fetch stats");
                                        }
                                    };
                                    xhr.send();
                                }

                                // Fetch stats every second (1000 milliseconds)
                                setInterval(fetchStats, 1000);

                                // Initial fetch
                                fetchStats();
                            </script>';
                        }
                        else{
                            echo htmlspecialchars($row['id']); 
                        }
                    ?>

                    </td>
                    <td>
                        <form action="register_edit.php" method="post">
                            <input type="hidden" name="video_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <button class="btn btn-info btn-circle btn-sm">&lt;/&gt;</button>
                            <button type="submit" name="edit_btn" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></button>
                            <button type="submit" name="delete_btn" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></button>
                
                        </form>
                    </td>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='6'>No Record Found</td></tr>";
            }
            $stmt_videos->close();
            ?>
        </tbody>
    </table>
    
    <nav>
        <ul class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<li class='page-item $active'><a class='page-link' href='?page=$i&search=" . urlencode($search) . "'>$i</a></li>";
            }
            ?>
        </ul>
    </nav>
</div>


        </div>
    </div>
</div>

<?php     
    include('includes/scripts.php');
    include('includes/footer.php');
?>