<?php
/**
 * List Temporary Logins
 *
 * @package WP Intranet Security
 */

?>
<table class="wp-list-table widefat fixed striped users">
	<thead>
	<?php echo Wp_Intranet_Security_Layout::prepare_header_footer_row(); ?>
	</thead>

	<tbody>
	<?php
	$temp_users = Wp_Intranet_Security_Common::get_temporary_logins();
	
	if ( is_array( $temp_users ) && count( $temp_users ) > 0 ) {

		foreach ( $temp_users as $temp_user ) {
			echo Wp_Intranet_Security_Layout::prepare_single_user_row( $temp_user );
		}
	} else {
		echo Wp_Intranet_Security_Layout::prepare_empty_user_row();
	}

	?>

	</tbody>

	<tfoot>
	<?php echo Wp_Intranet_Security_Layout::prepare_header_footer_row(); ?>
	</tfoot>
</table>
