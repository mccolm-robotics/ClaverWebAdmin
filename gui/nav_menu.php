<?php

$end = strpos($_SERVER['REQUEST_URI'], ".") - 1;
$page_name = substr($_SERVER['REQUEST_URI'], 1, $end);

$dashboard = ($page_name == "gui/dashboard") ? "<a class=\"active\">" : "<a href=\"dashboard.php\">";
$node = ($page_name == "gui/node") ? "<a class=\"active\">" : "<a href=\"node.php\">";
$account = ($page_name == "gui/account") ? "<a class=\"active\">" : "<a href=\"account.php\">";

$admin_link = ($_SESSION['user_level'] == 3) ? "<li>".$dashboard."<i class=\"fas fa-cog\"></i><label class=\"nav-icon-label\">Dashboard</label></a></li>" : "";

$node_link = ($_SESSION['user_level'] >= 2) ? "<li>".$node."<i class=\"fas fa-sliders-h\"></i><label class=\"nav-icon-label\">Node</label>" : "";

echo "
<label class=\"nav-label\">Menu</label>
<ul>
    ".$admin_link."
    ".$node_link."
    <li>".$account."<i class=\"fas fa-user-circle\"></i><label class=\"nav-icon-label\">Account</label></a></li>
    <li><a href=\"../actions/logout.php\"><i class=\"fas fa-sign-out-alt\"></i><label class=\"nav-icon-label\">Logout</label></a></li>
</ul> ";

?>
