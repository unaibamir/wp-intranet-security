<?php
/**
 * Created by PhpStorm.
 * User: malayladu
 * Date: 2019-01-11
 * Time: 14:57
 */

$system_info = new wpis_Sytem_Info();

?>

<button class="wpis-click-to-copy-btn" data-clipboard-action="copy" data-clipboard-target="#tlwp-system-info-data">Click To Copy</button>
<div class="wrap wpis-form" id="tlwp-system-info-data">
	<?php
		echo $system_info->render_system_info_page();
	?>
</div>

<br />
