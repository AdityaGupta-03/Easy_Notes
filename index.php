<?php

// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';

// Connect to MySQL server
$conn = mysqli_connect($host, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create the database if it doesn't exist
$databaseName = 'dbaddy';
$sql = "CREATE DATABASE IF NOT EXISTS $databaseName";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo "Error creating database: " . mysqli_error($conn);
}

// Select the database
mysqli_select_db($conn, $databaseName);

$tableName = 'inotes';
// SQL query to create a table
$sql = "CREATE TABLE IF NOT EXISTS $tableName (
        sno INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

$insert = false;
$update = false;
$delete = false;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_GET['update'])) {
        // Update the record
        $sno = $_POST['snoEdit'];
        $newTitle = $_POST['edit_input_title'];
        $newDescription  = $_POST['edit_input_desc'];

        $sql = "UPDATE $tableName SET `title`='$newTitle', `description`='$newDescription' WHERE `sno`=$sno";
        $update = mysqli_query($conn, $sql);
    } else {
        // Insert the record
        $title = $_POST["input_title"];
        $description = $_POST["input_desc"];

        if ($title != '' || $description != '') {
            // SQL query to insert a record into the 'iNotes' table
            $sql = "INSERT INTO $tableName (title, description) VALUES ('$title', '$description')";
            // Execute query
            $insert = mysqli_query($conn, $sql);
        }
    }
}

if (isset($_GET['delete'])) {
    // Deleteing a record
    $sno = $_GET['delete'];

    // SQL query to delete the record
    $sql = "DELETE FROM $tableName WHERE `sno` = $sno";
    $delete = mysqli_query($conn, $sql);
} 

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes-Made Easy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Adding jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>

</head>

<body>
    <!-- Creating Navbar -->
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="img/PHP-logo.svg.png" height="25px"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">About us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Contact us</a>
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <?php
    if ($insert || $update || $delete) {
        $msg = (($update == true) ? 'updated' : (($delete == true) ? 'deleted' : 'added'));
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your Note has been ' . $msg . ' in the list
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$insert && !$update && !$delete) {
        $error = (($title == '' || $description == '') ? 'Field is empty' : mysqli_error($conn));
        $msg = "Your note has not been added in list as $error";
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ' . $msg . ' 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
    ?>

    <!-- Creating Form -->
    <div class="container py-4 col-8">
        <h2>Add Note</h2>
        <form action="index.php" method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Note Title</label>
                <input type="text" class="form-control" id="title" name="input_title" placeholder="Title">
            </div>
            <div class="mb-3">
                <label for="desc" class="form-label">Note Description</label>
                <textarea class="form-control" id="desc" name="input_desc" rows="3" placeholder="Description"></textarea>
            </div>
            <button type="submit" class="btn btn-outline-success">Add Note</button>
        </form>
    </div>

    <!-- Showing Tabular Data -->
    <div class="container text-center col-8">
        <table class="table table-hover table-bordered" id="myTable">
            <thead class="table-dark">
                <th scope="col">S.No</th>
                <th scope="col">Title</th>
                <th scope="col">Description</th>
                <th scope="col">Actions</th>
            </thead>
            <tbody class="table-group-dividers">
                <?php
                $sql = "SELECT * FROM $tableName";
                $result = mysqli_query($conn, $sql);

                // Check if query executed successfully
                if ($result) {
                    //^ Count the number of rows fetched
                    $num_rows = mysqli_num_rows($result);

                    $count = 0;
                    //^ Fetch each row and iterate over them
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Access each column using array keys
                        $count++;
                        echo '<tr>
                                <th scope="row">' . $count . '</th>
                                <td>' . $row['title'] . '</td>
                                <td>' . $row['description'] . '</td>
                                <td>
                                    <!-- Edit Modal Button -->
                                    <button type="button" class="btn btn-outline-warning edit" id=' . $row['sno'] . ' data-bs-toggle="modal" data-bs-target="#editModal"> Edit </button>

                                    <!-- Delete Modal Button -->
                                    <button type="button" class="btn btn-outline-danger delete" id=d_' . $row['sno'] . '> Delete </button>
                                </td>
                            </tr>';
                    }

                    // Free result set
                    mysqli_free_result($result);
                } else {
                    echo "Error executing query: " . mysqli_error($conn);
                }

                // Close the connection otherwise resubmission will be there
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>

    <!-- Showing Modal for Edit and Delete -->
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editModalLabel">Edit this Note</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?update=true" method="post">
                    <div class="modal-body">
                        <!-- Update Query using sno primary key -->
                        <input type="hidden" name="snoEdit" id="snoEdit">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Note Title</label>
                            <input type="text" class="form-control" id="edit_title" name="edit_input_title" placeholder="Title">
                        </div>
                        <div class="mb-3">
                            <label for="edit_desc" class="form-label">Note Description</label>
                            <textarea class="form-control" id="edit_desc" name="edit_input_desc" rows="3" placeholder="Description"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Creating Footer -->
    <footer class="container-fluid d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
        <p class="col-md-4 mb-0 text-body-secondary">© 2024 Company, Inc</p>

        <ul class="nav col-md-4 justify-content-end">
            <li class="nav-item"><a href="#" class="nav-link px-2 text-body-secondary">Home</a></li>
            <li class="nav-item"><a href="#" class="nav-link px-2 text-body-secondary">Features</a></li>
            <li class="nav-item"><a href="#" class="nav-link px-2 text-body-secondary">Pricing</a></li>
            <li class="nav-item"><a href="#" class="nav-link px-2 text-body-secondary">FAQs</a></li>
            <li class="nav-item"><a href="#" class="nav-link px-2 text-body-secondary">About</a></li>
        </ul>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Adding DataTables CSS/JS extension for viewing the data -->
    <link rel="stylesheet" href="//cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
    <script src="//cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>

    <script>
        let table = new DataTable('#myTable');

        let edits = document.getElementsByClassName('edit');
        Array.from(edits).forEach((element) => {
            element.addEventListener('click', (e) => {
                // This will give access to hole row data content;
                let tr = e.target.parentNode.parentNode;
                let title = tr.getElementsByTagName("td")[0].innerText;
                let desc = tr.getElementsByTagName("td")[1].innerText;

                // putting title desc innerText in the Modal Input fields
                // Targetting inpt edit modal field with the help of id
                edit_title.value = title;
                edit_desc.value = desc;
                snoEdit.value = e.target.id;
            });
        });

        let del = document.getElementsByClassName('delete');
        Array.from(del).forEach((element) => {
            element.addEventListener('click', (e) => {
                let sno = e.target.id.substr(2, ); //coz id is in d_ format

                if (confirm("Are you sure you want to delete this note?")) {
                    // window.location="index.php?delete="+sno;
                    window.location = `index.php?delete=${sno}`;
                }
            });
        });
    </script>
</body>

</html>