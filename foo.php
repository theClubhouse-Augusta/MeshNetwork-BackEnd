<?php
    $files = [
        'foo' => 'bar',
    ];
    $exists = $files['bar'];
    if ($exists == NULL) echo "foo";
    else echo "bar";
?>