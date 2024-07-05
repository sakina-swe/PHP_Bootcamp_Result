<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWOT - Personal Work Off Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .worked-off {
            background-color: #d4edda;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center">PWOT - Personal Work Off Tracker</h1>
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

            $required_work_off = sprintf('%02d:%02d:00', $hours, $minutes);

            $sql = "INSERT INTO vaqt (arrived_at, leaved_at, required_work_off) VALUES (:arrived_at, :leaved_at, :required_work_off)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':arrived_at', $arrived_at);
            $stmt->bindParam(':leaved_at', $leaved_at);
            $stmt->bindParam(':required_work_off', $required_work_off);

            $stmt->execute();
        }

        public function fetchRecords($page_id) {
            $offset = ($page_id - 1) * 5;
            $sql = "SELECT * FROM vaqt ORDER BY id DESC LIMIT $offset, 5";
            $result = $this->conn->query($sql);
            $total_hours = 0;
            $total_minutes = 0;

            if ($result->rowCount() > 0) {
                echo '<form action="" method="post">';
                echo '<table class="table table-striped">';
                echo '<thead class="table-dark"><tr><th>#</th><th>Arrived at</th><th>Leaved at</th><th>Required work off</th><th>Worked off</th></tr></thead>';
                echo '<tbody>';
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $worked_off_class = $row["worked_off"] ? 'class="worked-off"' : '';
                    echo "<tr $worked_off_class>";
                    echo '<td>' . $row["id"] . '</td>';
                    echo '<td>' . $row["arrived_at"] . '</td>';
                    echo '<td>' . $row["leaved_at"] . '</td>';
                    echo '<td>' . $row["required_work_off"] . '</td>';
                    echo '<td><button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-id="' . $row["id"] . '">Done</button></td>';
                    echo '</tr>';

                    if (!$row["worked_off"]) {
                        list($hours, $minutes, $seconds) = explode(':', $row["required_work_off"]);
                        $total_hours += (int)$hours;
                        $total_minutes += (int)$minutes;
                    }
                }
                $total_hours += floor($total_minutes / 60);
                $total_minutes = $total_minutes % 60;

                echo '<tr><td colspan="4" class="text-end fw-bold">Total work off hours</td><td>' . $total_hours . ' hours and ' . $total_minutes . ' min.</td></tr>';
                echo '</tbody>';
                echo '</table>';
                echo '<button type="submit" name="export" class="btn btn-primary">Export as CSV</button>';
                echo '</form>';
            }
        }

        public function updateWorkedOff($id) {
            $sql = "UPDATE vaqt SET worked_off = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }

        public function exportCSV() {
            $sql = "SELECT * FROM vaqt";
            $result = $this->conn->query($sql);

            $filename = "work_off_report_" . date('Ymd') . ".csv";
            $file = fopen('php://output', 'w');

            $header = array("ID", "Arrived At", "Leaved At", "Required Work Off", "Worked Off");
            fputcsv($file, $header);

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($file, $row);
            }

            fclose($file);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            exit();
        }

        public function getTotalPages($records_per_page) {
            $sql = "SELECT COUNT(*) as total FROM vaqt";
            $result = $this->conn->query($sql);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return ceil($row['total'] / $records_per_page);
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
                echo "<p class='text-danger'>Iltimos ma'lumotlarni kiriting.</p>";
            }
        } elseif (isset($_POST["worked_off"])) {
            $tracker->updateWorkedOff($_POST["worked_off"]);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } elseif (isset($_POST["export"])) {
            $tracker->exportCSV();
        }
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $total_pages = $tracker->getTotalPages(5);
    ?>

    <div class="form-container mb-4">
        <form action="" method="post" class="row g-3">
            <div class="col-md-6">
                <label for="arrived_at" class="form-label">Arrived At</label>
                <input type="datetime-local" id="arrived_at" name="arrived_at" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="leaved_at" class="form-label">Leaved At</label>
                <input type="datetime-local" id="leaved_at" name="leaved_at" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    <?php
    $tracker->fetchRecords($page);
    ?>

    <!-- Pagination -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item <?php if ($page <= 1) { echo 'disabled'; } ?>">
                <a class="page-link" href="<?php if ($page > 1) { echo '?page=' . ($page - 1); } else { echo '#'; } ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <li class="page-item <?php if ($page == $i) { echo 'active'; } ?>">
                    <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $total_pages) { echo 'disabled'; } ?>">
                <a class="page-link" href="<?php if ($page < $total_pages) { echo '?page=' . ($page + 1); } else { echo '#'; } ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to mark this record as worked off?
            </div>
            <div class="modal-footer">
                <form action="" method="post">
                    <input type="hidden" name="worked_off" id="workedOffId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="update">Yes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var confirmModal = document.getElementById('confirmModal');
    confirmModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var modalInput = document.getElementById('workedOffId');
        modalInput.value = id;
    });
</script>
</body>
</html>

