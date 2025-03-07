<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Handle logout action
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy session
    header("Location: login.php");
    exit();
}


// Database connection
$conn = new mysqli("localhost", "root", "", "user");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch dropdown data
$batches = $conn->query("SELECT * FROM batches");
$semesters = $conn->query("SELECT * FROM semesters");
$mids = $conn->query("SELECT * FROM mid_exams");

// Import CSV with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $csvFile = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($csvFile); // Skip headers

        while (($row = fgetcsv($csvFile)) !== false) {
            $roll = $conn->real_escape_string(trim($row[0]));
            $batch = intval($row[1]);
            $semester = intval($row[2]);
            $mid = intval($row[3]);
            $subject = $conn->real_escape_string(trim($row[4]));
            $descriptive = intval($row[5]);
            $open_book = intval($row[6]);
            $online = intval($row[7]);
            $seminar = intval($row[8]);

            $sql = "INSERT INTO marks (roll_number, batch_id, semester_id, mid_exam_id, subject, descriptive_marks, open_book_marks, online_marks, seminar_marks) 
                    VALUES ('$roll', $batch, $semester, $mid, '$subject', $descriptive, $open_book, $online, $seminar)
                    ON DUPLICATE KEY UPDATE subject='$subject', descriptive_marks=$descriptive, open_book_marks=$open_book, online_marks=$online, seminar_marks=$seminar";
            $conn->query($sql);
        }
        fclose($csvFile);
    }
}

// Update Marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $descriptive = intval($_POST['descriptive_marks']);
    $open_book = intval($_POST['open_book_marks']);
    $online = intval($_POST['online_marks']);
    $seminar = intval($_POST['seminar_marks']);

    $sql = "UPDATE marks SET subject='$subject', descriptive_marks=$descriptive, open_book_marks=$open_book, online_marks=$online, seminar_marks=$seminar WHERE id=$id";
    if (!$conn->query($sql)) {
        echo "Error updating record: " . $conn->error;
    }
}

// Delete Marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM marks WHERE id=$id");
}

// Fetch students based on batch, semester, mid, and roll number
$students = [];
if (!empty($_GET['batch']) && !empty($_GET['semester']) && !empty($_GET['mid'])) {
    $batch = intval($_GET['batch']);
    $semester = intval($_GET['semester']);
    $mid = intval($_GET['mid']);
    $roll_condition = "";

    if (!empty($_GET['roll_number'])) {
        $roll = $conn->real_escape_string($_GET['roll_number']);
        $roll_condition = " AND roll_number='$roll'";
    }

    $students = $conn->query("SELECT * FROM marks WHERE batch_id=$batch AND semester_id=$semester AND mid_exam_id=$mid $roll_condition");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .logout-container { position: absolute; top: 10px; right: 10px; padding: 5px; }
        .logout-container button { background-color: red; border-radius: 10px; padding: 10px; }
    </style>
</head>
<body>
    <div class="logout-container">
        <form method="get">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>

    <div class="container mt-5">
        <h1 class="text-center">Admin Panel</h1>

        <!-- Filters -->
        <form method="GET" class="d-flex gap-2 mb-4">
            <select name="batch" class="form-select" required>
                <option value="">Select Batch</option>
                <?php while ($row = $batches->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" <?= (!empty($_GET['batch']) && $_GET['batch'] == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['batch'] ?>
                    </option>
                <?php } ?>
            </select>
            <select name="semester" class="form-select" required>
                <option value="">Select Semester</option>
                <?php while ($row = $semesters->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" <?= (!empty($_GET['semester']) && $_GET['semester'] == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['semester'] ?>
                    </option>
                <?php } ?>
            </select>
            <select name="mid" class="form-select" required>
                <option value="">Select Mid</option>
                <?php while ($row = $mids->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" <?= (!empty($_GET['mid']) && $_GET['mid'] == $row['id']) ? 'selected' : '' ?>>
                        <?= $row['mid_exam'] ?>
                    </option>
                <?php } ?>
            </select>
            <input type="text" name="roll_number" class="form-control" placeholder="Enter Roll Number (Optional)">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- CSV Upload -->
        <form method="POST" enctype="multipart/form-data" class="mb-4">
            <h4>Import Data from CSV</h4>
            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
            <button type="submit" name="import_csv" class="btn btn-info">Import CSV</button>
        </form>

        <!-- Display Students -->
        <?php if (!empty($students) && $students->num_rows > 0) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr><th>Roll Number</th><th>Subject</th><th>Descriptive</th><th>Open Book</th><th>Online</th><th>Seminar</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($row = $students->fetch_assoc()) { ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <td><?= $row['roll_number'] ?></td>
                                <td><input type="text" name="subject" value="<?= $row['subject'] ?>" class="form-control"></td>
                                <td><input type="number" name="descriptive_marks" value="<?= $row['descriptive_marks'] ?>" class="form-control"></td>
                                <td><input type="number" name="open_book_marks" value="<?= $row['open_book_marks'] ?>" class="form-control"></td>
                                <td><input type="number" name="online_marks" value="<?= $row['online_marks'] ?>" class="form-control"></td>
                                <td><input type="number" name="seminar_marks" value="<?= $row['seminar_marks'] ?>" class="form-control"></td>
                                <td>
                                    <button type="submit" name="update" class="btn btn-warning">Update</button>
                                </td>
                            </form>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>