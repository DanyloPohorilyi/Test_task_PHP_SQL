<?php
$orders = [ 
    ["id"=>1, "user"=>"Ivan", "amount"=>100, 
"status"=>"paid"], 
    ["id"=>2, "user"=>"Oksana", "amount"=>-50, 
"status"=>"paid"], // аномалія 
    ["id"=>3, "user"=>"Ivan", "amount"=>200, 
"status"=>"pending"], // не враховується 
    ["id"=>4, "user"=>"Petro", "amount"=>300, 
"status"=>"paid"], 
]; 
 
function totalPaid($orders) { 
    $sum = 0; 
    foreach ($orders as $o) { 
        if ($o["status"] === "paid") { 
            $sum += $o["amount"]; 
        } 
    } 
    return $sum; 
} 
function writeReport($file, $orders) { 
$total = totalPaid($orders); 
$txt = "Total paid = " . $total . PHP_EOL; 
file_put_contents($file, $txt);  
echo "Report saved to $file\n"; 
} 
writeReport("report.txt", $orders);