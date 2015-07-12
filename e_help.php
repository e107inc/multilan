<?php 
if (!defined('e107_INIT')) { exit; }


$text = "<table style='width:100%'>
<tr><td>(blank)</td><td>Table missing</td></tr>
<tr><td>&nbsp;<b>&middot;</b></td><td>Not-Copied</td></tr>";
$text .= "<tr><td>".ADMIN_BING_ICON."</td><td>Auto-Translated</td></tr>";
$text .= "<tr><td>".ADMIN_TRUE_ICON."</td><td>Reviewed/Translated</td></tr>";
$text .= "<tr><td>".ADMIN_FALSE_ICON."</td><td>Not Translated</td></tr>";

$text .= "</table>";

$text .= "<div style='margin-top:5px'>Mouseover each icon to check the title of the item</div>";

$ns->tablerender("Translation Status",$text);


?>