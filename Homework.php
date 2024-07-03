<form action="2.php" method="post">
    <?php
    $days=["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
    echo "<h1>Ish soatlarini hisoblash.</h1>";
    $soat=[];
    foreach($days as $day){
        echo "<h1>$day</h1>\n";
        if ($day=="Saturday" or $day=="Sunday"){
            echo '<h2 style="background-color:Tomato;">Dam olish kuni 😎😎😎</h2>';;
        }
        else{
        echo "<input type='date' name='kun_$day'>";
        echo "Arrived at";
        echo "<input type='time' name='soat_$day'>";
        echo "Leaved at";
        echo "<input type='time' name='soat1_$day'> <br><br>";}}
        echo "<br><button><h2> submit </h2></button>";
    ?>
</form>

<pre>
<?php

$days=["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
$results=[];

foreach ($days as $day){
    if (isset($_POST["kun_$day"]) && isset($_POST["soat_$day"]) && isset($_POST["soat1_$day"])){
        $kun=$_POST["kun_$day"];
        $soat=$_POST["soat_$day"];
        $soat1=$_POST["soat1_$day"];

        
        $qol=((strtotime($_POST["soat1_$day"])-strtotime($_POST["soat_$day"]))-9*3600);
        $a=($qol/3600);
        $b=($qol%3600)/60;
        $c=($qol%60);

        $time=sprintf("%02d:%02d:%02d",$a,$b,$c);
        $results[$day]=[
            'kun' => $kun,
            'soat'=> $soat,
            'soat1'=> $soat1,
            'time'=> $time];
}}

echo '<h1 style="background-color:Green;">Hisobotlar.........</h1>';
foreach($results as $day => $result){
    echo "<h1> $day </h1>";
    echo "Kun : ".$result['kun']."\n";
    echo "Ishga kelgan vaqti : ".$result['soat']."\n";
    echo "Ishdan ketgan vaqti : ".$result['soat1']."\n";
    if (($result['time'])==("00:00:00")){
        echo "Reja bajarildi.";}
    elseif($result['time'][0]=="-"){
        echo "Reja bajarilmadi.😫😫😫\n".$result['time'];
    }
    else{
        echo "Reja ortig'i bilan bajarildi.😎😎😎\n".$result['time'];
    }
    echo '<h1 style="background-color:DodgerBlue;">-----------------------------------------</h1>';
}
?>