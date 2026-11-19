<?php

use App\Controller as Foo;

function dashboard(): Foo\DashboardController
{
}

?>
-----
<?php

use App\Controller;

function dashboard(): Controller\DashboardController
{
}

?>
