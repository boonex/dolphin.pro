<?php

    if (!empty($_POST['phpinfo']))
        phpinfo();
    elseif (!empty($_POST['gdinfo']))
        echo '<pre>' . print_r(gd_info(), true) . '</pre>';

?>
<center>

    <form method=post>
        <input type=submit name=phpinfo value="PHP Info">
    </form>
    <form method=post>
        <input type=submit name=gdinfo value="GD Info">
    </form>

</center>
