<?php

declare(strict_types=1);
date_default_timezone_set('Asia/Tashkent'); 
$conn = new PDO(
    'mysql:host=localhost;dbname=vaqt1',
    'root',
    'root'
);

?>

<form action="3.php" method="post">
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

if (!empty($_POST["arrived_at"]) && !empty($_POST["leaved_at"])) {
    $arrived_at = (new DateTime($_POST['arrived_at']))->format('Y-m-d H:i:s');
    $leaved_at  = (new DateTime($_POST['leaved_at']))->format('Y-m-d H:i:s');
    
    $sql = "INSERT INTO vaqt (arrived_at, leaved_at) VALUES (:arrived_at, :leaved_at)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':arrived_at', $arrived_at);
    $stmt->bindParam(':leaved_at', $leaved_at);

    if($stmt->execute()){
        echo "Ma'lumotlar bazaga qo'shildi.";
    }else{
        echo "Ma'lumot bazaga qushilmadi.";
    }

    $sql = "SELECT * FROM vaqt";
    $result = $conn->query($sql);

    if ($result->rowCount() > 0) {
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo '<h2>' ."ID : ". $row["id"] . '</h2>';
            echo '<h2>' ."Arrived At : ". $row["arrived_at"] . '</h2>';
            echo '<h2>' ."Leaved At : ". $row["leaved_at"] . '</h2>';
        }
    }
}
else {
    echo "Iltimos ma'lumotlarni kiriting.";
}
?>
