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

$roll_number = $_SESSION['username']; // Roll number from session

// Handle logout action
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch dropdown values
function getOptions($table, $id_column, $name_column) {
    global $conn;
    return $conn->query("SELECT $id_column, $name_column FROM $table");
}

$marks = [];

if (isset($_GET['batch'], $_GET['semester'], $_GET['mid'])) {
    $batch = $_GET['batch'];
    $semester = $_GET['semester'];
    $mid = $_GET['mid'];

    $sql = "SELECT subject, descriptive_marks, open_book_marks, online_marks, seminar_marks FROM marks 
            WHERE batch_id = $batch AND semester_id = $semester 
            AND mid_exam_id = $mid AND roll_number = '$roll_number'";
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $final_mark = ($row['descriptive_marks'] / 3) + ($row['open_book_marks'] / 4) + ($row['online_marks'] / 2) + $row['seminar_marks'];
        $row['final_mark'] = round($final_mark, 2);
        $marks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Marks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { width: 80%; margin: 50px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #751f1f; }
        .form-container { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; }
        select, button { padding: 10px; font-size: 16px; border-radius: 5px; }
        button { background-color: #751f1f; color: white; cursor: pointer; }
        button:hover { background-color: #5a1616; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #751f1f; padding: 10px; text-align: center; }
        th { background-color: #751f1f; color: white; }
        .logout-container { position: absolute; top: 10px; right: 10px; }
        .logout-container button { background-color: red; }
    </style>
</head>
<body>

<!-- Logout Button -->
<div class="logout-container">
    <form method="get">
        <button type="submit" name="logout">Logout</button>
    </form>
</div>

<div class="container">
    <h1>Student Mid Marks</h1>

    <form id="marksForm" method="GET">
        <div class="form-container">
            <select name="batch" required>
                <option value="">Select Batch</option>
                <?php foreach (getOptions('batches', 'id', 'batch') as $row) echo "<option value='{$row['id']}'>{$row['batch']}</option>"; ?>
            </select>
            <select name="semester" required>
                <option value="">Select Semester</option>
                <?php foreach (getOptions('semesters', 'id', 'semester') as $row) echo "<option value='{$row['id']}'>{$row['semester']}</option>"; ?>
            </select>
            <select name="mid" required>
                <option value="">Select Mid Exam</option>
                <?php foreach (getOptions('mid_exams', 'id', 'mid_exam') as $row) echo "<option value='{$row['id']}'>{$row['mid_exam']}</option>"; ?>
            </select>
            <button type="submit">Search</button>
        </div>
    </form>

    <?php if (!empty($marks)) { ?>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Descriptive Marks</th>
                    <th>Open Book Marks</th>
                    <th>Online Marks</th>
                    <th>Seminar Marks</th>
                    <th>Final Mark</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($marks as $row) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subject']) ?></td>
                        <td><?= htmlspecialchars($row['descriptive_marks']) ?></td>
                        <td><?= htmlspecialchars($row['open_book_marks']) ?></td>
                        <td><?= htmlspecialchars($row['online_marks']) ?></td>
                        <td><?= htmlspecialchars($row['seminar_marks']) ?></td>
                        <td><?= htmlspecialchars($row['final_mark']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } elseif (isset($_GET['batch'], $_GET['semester'], $_GET['mid'])) { ?>
        <p style="text-align: center; color: red;">No records found for the selected criteria.</p>
    <?php } ?>
</div>

</body>
</html>