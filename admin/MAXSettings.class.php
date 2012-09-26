<?php
/**
 * This file is part of TheCartPress-Limits.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class TCPMaxSettings {

	private $updated = false;

	function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 99 );
		global $tcp_miranda;
		if ( $tcp_miranda ) $tcp_miranda->add_item( 'Limits', 'limits_settings', __( 'Limits', 'tcp-limits' ), false, array( 'TCPMaxSettings', __FILE__ ) );
	}

	function admin_menu() {
		if ( ! current_user_can( 'tcp_edit_settings' ) ) return;
		global $thecartpress;
		if ( $thecartpress ) {
			$base = $thecartpress->get_base_settings();
			$page = add_submenu_page( $base, __( 'Lmits Setup', 'tcp-limits' ), __( 'Limits Setup', 'tcp-limits' ), 'tcp_edit_settings', 'max_setup', array( &$this, 'admin_page' ) );
			//$page = add_submenu_page( $base, __( 'First Time Setup', 'tcp' ), __( 'First time', 'tcp' ), 'tcp_edit_settings', 'first_time_setup', array( &$this, 'admin_page' ) );
			add_action( "load-$page", array( &$this, 'admin_load' ) );
			add_action( "load-$page", array( &$this, 'admin_action' ) );
		}
	}

	function admin_load() {
		get_current_screen()->add_help_tab( array(
		    'id'      => 'overview',
		    'title'   => __( 'Overview' ),
		    'content' =>
	            '<p>' . __( 'You can customize TheCartPress to only accept orders with a minimum or maximum price and weigth.', 'tcp-limits' ) . '</p>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'tcp-limits' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://thecartpress.com" target="_blank">Documentation on TheCartPress</a>', 'tcp' ) . '</p>' .
			'<p>' . __( '<a href="http://community.thecartpress.com/" target="_blank">Support Forums</a>', 'tcp' ) . '</p>' .
			'<p>' . __( '<a href="http://extend.thecartpress.com/" target="_blank">Extend site</a>', 'tcp' ) . '</p>'
		);
	}

	function admin_page() { ?>
<div class="wrap">
	<?php screen_icon( 'tcp-max-min' ); ?><h2><?php _e( 'Limits Setup', 'tcp-limits' ); ?></h2>
<?php if ( !empty( $this->updated ) ) : ?>
	<div id="message" class="updated">
	<p><?php _e( 'Settings updated', 'tcp-limits' ); ?></p>
	</div>
<?php endif; ?>
<?php global $thecartpress;
$min_price	= $thecartpress->get_setting( 'min_price', 0 );
$fee_price	= $thecartpress->get_setting( 'fee_price', 0 );
$max_price	= $thecartpress->get_setting( 'max_price', 0 );
$min_weight	= $thecartpress->get_setting( 'min_weight', 0 );
$fee_weight	= $thecartpress->get_setting( 'fee_weight', 0 );
$max_weight	= $thecartpress->get_setting( 'max_weight', 0 ); ?>
<form method="post" action="">
<table class="form-table">
<tbody>
<tr valign="top">
	<th scope="row">
		<label for="min_price"><?php _e( 'Minimum price', 'tcp-limits' ); ?></label>
	</th>
	<td>
		<input type="text" id="min_price" name="min_price" value="<?php echo $min_price; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="fee_price"><?php _e( 'Small Order Fee', 'tcp-limits' ); ?></label>
	</th>
	<td>
		<input type="text" id="fee_price" name="fee_price" value="<?php echo $fee_price; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
		<p class="description"><?php _e( 'This fee will be applicable if the minimum price is not exceeded. If this value is zero the minimum price must exceed to proceed to order.', 'tcp-limits'); ?></p>
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="max_price"><?php _e( 'Maximum price', 'tcp-limits' ); ?></label>
	</th>
	<td>
		<input type="text" id="max_price" name="max_price" value="<?php echo $max_price; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="min_weight"><?php _e( 'Minimum weight', 'tcp-limits' ); ?></label>
	</th>
	<td>
		<input type="text" id="min_weight" name="min_weight" value="<?php echo $min_weight; ?>" size="10" maxlength="15" /> <?php tcp_the_unit_weight(); ?>
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="fee_weight"><?php _e( 'Small Order Fee', 'tcp-limits' ); ?></label>
	</th>
	<td>
		<input type="text" id="fee_weight" name="fee_weight" value="<?php echo $fee_weight; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
		<p class="description"><?php _e( 'This fee will be applicable if the minimum weight is not exceeded. If this value is zero the minimum weight must exceed to proceed to order.', 'tcp-limits'); ?></p>
	</td>
</tr>
<tr valign="top">
	<th scope="row">
		<label for="max_weight"><?php _e( 'Maximum Weight', 'tcp-limits' ); ?></label>
	</th>
	<td>
		<input type="text" id="max_weight" name="max_weight" value="<?php echo $max_weight; ?>" size="10" maxlength="15" /> <?php tcp_the_unit_weight(); ?>
	</td>
</tr>
<?php do_action( 'tcp_max_settings_page' ); ?>
</tbody>
</table>
<?php wp_nonce_field( 'tcp_max_settings' ); ?>
<?php submit_button( null, 'primary', 'save-max-settings' ); ?>
</form>
</div><?php
	}

	function admin_action() {
		if ( empty( $_POST ) ) return;
		check_admin_referer( 'tcp_max_settings' );
		$settings = get_option( 'tcp_settings' );
		$settings['min_price']	= $_POST['min_price'];
		$settings['fee_price']	= $_POST['fee_price'];
		$settings['max_price']	= $_POST['max_price'];
		$settings['min_weight']	= $_POST['min_weight'];
		$settings['fee_weight']	= $_POST['fee_weight'];
		$settings['max_weight']	= $_POST['max_weight'];
		$settings = apply_filters( 'tcp_max_settings_action', $settings );
		update_option( 'tcp_settings', $settings );
		$this->updated = true;
		global $thecartpress;
		$thecartpress->load_settings();
	}
}

new TCPMaxSettings();
?>
