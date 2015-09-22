<?php 
if (!defined('e107_INIT')) { exit; }


$text = "<table class='help-table' style='width:100%'>
<tr><td>(blank)</td><td>Table missing</td></tr>
<tr><td>&nbsp;<b>&middot;</b></td><td>Empty</td></tr>";
$text .= "<tr><td>".ADMIN_BING_ICON."</td><td title='Toggle' class='toggle-icon' data-type='auto-translated'>Auto-Translated/Review</td></tr>";
$text .= "<tr><td>".ADMIN_TRUE_ICON."</td><td>Translated</td></tr>";
$text .= "<tr><td>".ADMIN_FLAG_ICON."</td><td title='Toggle' class='toggle-icon' data-type='un-translated'>Not Translated (Flagged)</td></tr>";
$text .= "<tr><td>".ADMIN_FALSE_ICON."</td><td>Not Translated (Not Flagged)</td></tr>";
$text .= "</table>";

$text .= "<div style='margin-top:5px'>Mouseover each icon to check the title of the item</div>";

$ns->tablerender("Translation Status",$text);


?>