<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWOT - Personal Work Off Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">
    <h1 class="text-center">PWOT - Personal Work Off Tracker</h1>
    <?php

    require 'PersonalWorkOffTracker.php';
    require 'form.php';

    date_default_timezone_set('Asia/Tashkent');

    $tracker = new PersonalWorkOffTracker();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST["arrived_at"]) && isset($_POST["leaved_at"])) {
            if (!empty($_POST["arrived_at"]) && !empty($_POST["leaved_at"])) {
                $tracker->addRecord($_POST["arrived_at"], $_POST["leaved_at"]);
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                echo "<p class='text-danger'>Please fill out the form correctly</p>";
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

    $tracker->fetchRecords($page);
    require 'navigation.php';
    require 'formMarkAsDone.php';
    ?>

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

