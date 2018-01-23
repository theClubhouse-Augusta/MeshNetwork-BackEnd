<?php
  $date = new DateTime("now");

  echo $date->format('m-d-Y')."\n";
 // echo $date->format('m-d-Y');
 // $pos = strrpos($e->start, " ");
 // $bar = substr($e->start, 0, $pos);

 $date->sub(new DateInterval('P31D'));
 echo $date->format('m-d-Y')."\n";
