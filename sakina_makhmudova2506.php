<form action="project1.php" method="post">
    <h3>Monday</h3>
    Arrived at:<input type="datetime-local" name="arrivedAt[Monday]"> <br>
    Leaved at:<input type="datetime-local" name="leavedAt[Monday]"> <br>

    <h3>Tuesday</h3>
    Arrived at:<input type="datetime-local" name="arrivedAt[Tuesday]"> <br>
    Leaved at:<input type="datetime-local" name="leavedAt[Tuesday]"> <br>

    <h3>Wednesday</h3>
    Arrived at:<input type="datetime-local" name="arrivedAt[Wednesday]"> <br>
    Leaved at:<input type="datetime-local" name="leavedAt[Wednesday]"> <br>

    <h3>Thursday</h3>
    Arrived at:<input type="datetime-local" name="arrivedAt[Thursday]"> <br>
    Leaved at:<input type="datetime-local" name="leavedAt[Thursday]"> <br>

    <h3>Friday</h3>
    Arrived at:<input type="datetime-local" name="arrivedAt[Friday]"> <br>
    Leaved at:<input type="datetime-local" name="leavedAt[Friday]"> <br>

    <button>Send</button>
</form>


<pre>
<?php

function calculateWorkTime($arrivedAt, $leavedAt) {
    $arrivedAt = new DateTime($arrivedAt);
    $leavedAt = new DateTime($leavedAt);
    $interval = $arrivedAt->diff($leavedAt);
    return $interval;
}

function calculateTotalWorkOffTime($workTime) {
    $totalTime = 0;
    $workSchedule = 540; // in minutes

    if ($workTime <= 540) {
        $workOffTime = $workSchedule - $workTime;
        $totalTime += $workOffTime;
    } else {
        $workOffTime = $workTime - $workSchedule;
        $totalTime -= $workOffTime;
    }

    return $totalTime;
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

foreach ($days as $day) {
    $workTime = calculateWorkTime($_POST["arrivedAt"][$day], $_POST["leavedAt"][$day])->h * 60 + calculateWorkTime($_POST["arrivedAt"][$day], $_POST["leavedAt"][$day])->i;
    var_dump("Work duration: " . calculateWorkTime($_POST["arrivedAt"][$day], $_POST["leavedAt"][$day])->format("%H:%I"));

    $workOffTime = calculateTotalWorkOffTime($workTime);
    var_dump("Debt: $workOffTime\n");
}

?>
</pre>