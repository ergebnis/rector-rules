<?php

$a = match ($status) {
    'Status10' => handle10(),
    'Status2' => handle2(),
    'Status' => handleBase(),
    'Status1' => handle1(),
};

?>
-----
<?php

$a = match ($status) {
    'Status' => handleBase(),
    'Status1' => handle1(),
    'Status2' => handle2(),
    'Status10' => handle10(),
};

?>
