<?php
    include('security.php');
    include('includes/header.php');
?>

<div class="container-fluid">

<div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Upload Video</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="upload.php" method="POST" enctype="multipart/form-data">

        <div class="modal-body">
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

            <div class="form-group">
                <label> Video Name </label>
                <input type="text" name="videoname" class="form-control" placeholder="Enter Name For Video">
            </div>
            <div class="form-group">
                <label>Qualities</label>
                <div class="form-group">
                    <div class="col-sm-2">
                        <label>
                            <input type="checkbox" name="resolutions[]" value="360p" checked>
                            360p
                        </label>
                    </div>
                    <div class="col-sm-2">
                        <label>
                            <input type="checkbox" name="resolutions[]" value="480p" checked>
                            480p
                        </label>
                    </div>
                    <div class="col-sm-2">
                        <label>
                            <input type="checkbox" name="resolutions[]" value="720p" checked>
                            720p
                        </label>
                    </div>
                    <div class="col-sm-2">
                        <label>
                            <input type="checkbox" name="resolutions[]" value="1080p">
                            1080p
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Video</label>
                <input type="file" name="video_file" id="video_file" class="form-control-file">
            </div>
            <input type="hidden" name="usertype" value="admin">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary">Close</button>
            <button type="submit" name="upload_btn" class="btn btn-primary">Upload</button>
        </div>
      </form>

    </div>

</div>

<?php     
    include('includes/scripts.php');
    include('includes/footer.php');
?>