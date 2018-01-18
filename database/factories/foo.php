<?php
/**
 * Created by PhpStorm.
 * User: austi
 * Date: 1/8/2018
 * Time: 12:12 PM
 */

//foreach ($as as $a) {
//    $now = new DateTime();
//    $rangeOfDays = rand(-730, 730);
//    $addDays = DateInterval::createFromDateString($rangeOfDays . ' days');
//    $unixTimeStamp = '@' . $now->add($addDays)->getTimeStamp();
//    $appearanceDate = new DateTime($unixTimeStamp);
//    $a->created_at = $appearanceDate;
//    $a->save();
//}

//$stamp = mktime(0, 0, 0, 1, 1, 2016);
//$foo = new DateTime($stamp);
//$lastday = date('t',strtotime('3/1/2009'));
//echo $lastday;
//$foo = date('m', 01);
//echo $foo;
$stamp = mktime(0, 0, 0, 1, 1,2017);
$dateFormat = date('Y-m-d', $stamp);
echo $dateFormat;



