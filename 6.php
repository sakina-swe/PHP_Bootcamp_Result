<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWOT - Personal Work Off Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-container {
            margin-bottom: 20px;
        }
        .form-container label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .form-container input, .form-container button {
            padding: 10px;
            margin: 5px 0;
            width: calc(100% - 22px);
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
        .styled-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        .styled-table th, .styled-table td {
            border: 1px solid #ddd;
            text-align: left;
            padding: 8px;
        }
        .styled-table th {
            background-color: #4CAF50;
            color: white;
        }
        .styled-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>PWOT - Personal Work Off Tracker</h1>

    <?php

    date_default_timezone_set('Asia/Tashkent');

    class PersonalWorkOffTracker {
        private $conn;

        public function __construct() {
            $this->conn = new PDO('mysql:host=localhost;dbname=vaqt1', 'root', 'root');
        }

        public function addRecord($arrived_at, $leaved_at) {
            $arrived_at_dt = new DateTime($arrived_at);
            $leaved_at_dt = new DateTime($leaved_at); 

            $interval = $arrived_at_dt->diff($leaved_at_dt);
             
            $hours = $interval->h + ($interval->days * 24); 
            $minutes = $interval->i;

            
            $required_work_off = sprintf('%02d:%02d:00',$hours,$minutes);

            $sql = "INSERT INTO vaqt (arrived_at, leaved_at, required_work_off) VALUES (:arrived_at, :leaved_at, :required_work_off)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':arrived_at', $arrived_at);
            $stmt->bindParam(':leaved_at', $leaved_at);
            $stmt->bindParam(':required_work_off', $required_work_off);

            if($stmt->execute()){
                echo "Ma'lumotlar bazaga qo'shildi.<br>";
            } else {
                echo "Ma'lumot bazaga qo'shilmadi.<br>";
            }
        }

        public function fetchRecords() {
            $sql = "SELECT * FROM vaqt";
            $result = $this->conn->query($sql);
            $total_hours = 0;
            $total_minutes = 0;

            if ($result->rowCount() > 0) {
                echo '<form action="" method="post">';
                echo '<table class="styled-table">';
                echo '<thead><tr><th>#</th><th>Arrived at</th><th>Leaved at</th><th>Required work off</th><th>Worked off</th></tr></thead>';
                echo '<tbody>';
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . $row["id"] . '</td>';
                    echo '<td>' . $row["arrived_at"] . '</td>';
                    echo '<td>' . $row["leaved_at"] . '</td>';
                    echo '<td>' . $row["required_work_off"] . '</td>';
                    
                    echo '<td><input type="checkbox" name="worked_off[]" value="' . $row["id"] . '"' . ($row["worked_off"] ? ' checked' : '') . '></td>';
                    echo '</tr>';

                    if (!$row["worked_off"]) {
                        list($hours, $minutes, $seconds) = explode(':', $row["required_work_off"]);
                        $total_hours += (int)$hours;
                        $total_minutes += (int)$minutes;
                    }
                } 
                $total_hours += floor($total_minutes / 60);
                $total_minutes = $total_minutes % 60;

                echo '<tr><td colspan="4" style="text-align: right;">Total work off hours</td><td>' . $total_hours . ' hours and ' . $total_minutes . ' min.</td></tr>';
                echo '</tbody>';
                echo '</table>';
                echo '<button type="submit" name="update">Update</button>';
                echo '</form>';
            }
        }

        public function updateWorkedOff($worked_off) {
            $sql = "UPDATE vaqt SET worked_off = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            foreach ($worked_off as $id) {
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
        }
    }

    $tracker = new PersonalWorkOffTracker();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST["arrived_at"]) && isset($_POST["leaved_at"])) {
            if (!empty($_POST["arrived_at"]) && !empty($_POST["leaved_at"])) {
                $tracker->addRecord($_POST["arrived_at"], $_POST["leaved_at"]);
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                echo "<p style='color: red;'>Iltimos ma'lumotlarni kiriting.</p>";
            }
        } elseif (isset($_POST["update"])) {
            if (!empty($_POST["worked_off"])) {
                $tracker->updateWorkedOff($_POST["worked_off"]);
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
    }

    ?>

    <div class="form-container">
        <form action="" method="post">
            <label for="arrived_at">Arrived At</label>
            <input type="datetime-local" id="arrived_at" name="arrived_at" required>
            <label for="leaved_at">Leaved At</label>
            <input type="datetime-local" id="leaved_at" name="leaved_at" required>
            <button type="submit">Submit</button>
        </form>
    </div>

    <?php
    $tracker->fetchRecords();
    ?>

</div>

</body>
</html>
