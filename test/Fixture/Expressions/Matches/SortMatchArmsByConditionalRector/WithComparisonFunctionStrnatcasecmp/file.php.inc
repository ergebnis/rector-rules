<?php

$a = match ($status) {
    'Status10' => handle10(),
    'status2' => handle2(),
    'Status' => handleBase(),
    'status1' => handle1(),
};

?>
-----
<?php

$a = match ($status) {
    'Status' => handleBase(),
    'status1' => handle1(),
    'status2' => handle2(),
    'Status10' => handle10(),
};

?>
