<?php

declare(strict_types=1);
date_default_timezone_set('Asia/Tashkent'); 
$conn = new PDO(
    'mysql:host=localhost;dbname=vaqt1',
    'root',
    'root'
);

?>

<form action="5.php" method="post">
    <label>
        Arrived At
        <input type="datetime-local" name="arrived_at" required>
    </label><br>

    <label>
        Leaved At
        <input type="datetime-local" name="leaved_at" required>
    </label>
    <button>Submit</button>
</form>

<?php

function calculateTimeDifference($start, $end) {
    $start_datetime = new DateTime($start);
    $end_datetime = new DateTime($end);
    $diff = $start_datetime->diff($end_datetime);
    $hours = $diff->h;
    $minutes = $diff->i;
    $seconds = $diff->s;
    $time_farqi = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    if (($hours==9) and $minutes==0 and $minutes==0){
        return (sprintf("%02d:%02d:%02d", $hours-9, $minutes, $seconds)."<br>Reja bajarildi.<br><br>");
    }
    elseif($hours==9 and $minutes>0){
        return (sprintf("%02d:%02d:%02d", (9-$hours), $minutes, $seconds)."<br> Ko'p ishladi.<br><br>");}
    elseif($hours>9){
        return (sprintf("%02d:%02d:%02d", ($hours-9), $minutes, $seconds)."<br> Ko'p ishladi<br><br>");
    }
    else{
        return (sprintf("%02d:%02d:%02d", (9-$hours),(60-$minutes),$seconds)."<br>Reja bajarilmadi.<br><br>");
    }
}

if (!empty($_POST["arrived_at"]) && !empty($_POST["leaved_at"])) {
    $arrived_at = (new DateTime($_POST['arrived_at']))->format('Y-m-d H:i:s');
    $leaved_at  = (new DateTime($_POST['leaved_at']))->format('Y-m-d H:i:s');

    
    $sql = "INSERT INTO vaqt (arrived_at, leaved_at) VALUES (:arrived_at, :leaved_at)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':arrived_at', $arrived_at);
    $stmt->bindParam(':leaved_at', $leaved_at);

    if($stmt->execute()){
        echo "Ma'lumotlar bazaga qo'shildi.";
    } else {
        echo "'<h1>'Ma'lumot bazaga qushilmadi.'</h1>'";
    }

    $sql = "SELECT * FROM vaqt";
    $result = $conn->query($sql);

    if ($result->rowCount() > 0) {
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo '<h2>' ."ID : ". $row["id"] . '</h2>';
            echo '<h2>' ."Arrived At : ". $row["arrived_at"] . '</h2>';
            echo '<h2>' ."Leaved At : ". $row["leaved_at"] . '</h2>'; 
            echo '<h2>' ."Qarz farqi : ". calculateTimeDifference($row["arrived_at"], $row["leaved_at"]) . '</h2>';    
        }
        $conn = null; 
    }
} else {
    echo "Iltimos ma'lumotlarni kiriting.";
}
?>
