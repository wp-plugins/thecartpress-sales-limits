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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TCPMaxSettings' ) ) :

/**
 * Sales limits settings menu page
 */
class TCPMaxSettings {

	private $updated = false;

	function __construct() {
		add_action( 'tcp_admin_menu', array( $this, 'tcp_admin_menu' ), 99 );
		global $tcp_miranda;
		if ( $tcp_miranda ) $tcp_miranda->add_item( 'Limits', 'limits_settings', __( 'Limits', 'tcp_max' ), false, array( 'TCPMaxSettings', __FILE__ ) );
	}

	function tcp_admin_menu( $thecartpress ) {
		if ( ! current_user_can( 'tcp_edit_settings' ) ) return;

		$base = $thecartpress->get_base_settings();
		$page = add_submenu_page( $base, __( 'Sales Limits', 'tcp_max' ), __( 'Sales Limits', 'tcp_max' ), 'tcp_edit_settings', 'max_setup', array( &$this, 'admin_page' ) );
		add_action( "load-$page", array( $this, 'admin_load' ) );
		add_action( "load-$page", array( $this, 'admin_action' ) );

	}

	function admin_load() {
		get_current_screen()->add_help_tab( array(
		    'id'      => 'overview',
		    'title'   => __( 'Overview' ),
		    'content' =>
	            '<p>' . __( 'You can customize TheCartPress to only accept orders with a minimum or maximum price and weight.', 'tcp_max' ) . '</p>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'tcp' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://thecartpress.com" target="_blank">Documentation on TheCartPress</a>', 'tcp' ) . '</p>' .
			'<p>' . __( '<a href="http://thecartpress.com/community" target="_blank">Support Forums</a>', 'tcp' ) . '</p>' .
			'<p>' . __( '<a href="http://thecartpress.com/extend" target="_blank">Extend site</a>', 'tcp' ) . '</p>'
		);
	}

	function admin_page() { ?>
<div class="wrap">
	<?php screen_icon( 'tcp-default' ); ?><h2><?php _e( 'Sales Limits', 'tcp_max' ); ?></h2>
<?php if ( !empty( $this->updated ) ) : ?>
	<div id="message" class="updated">
	<p><?php _e( 'Settings updated', 'tcp_max' ); ?></p>
	</div>
<?php endif; ?>
<?php global $thecartpress;
$min_price	= tcp_number_format( $thecartpress->get_setting( 'min_price', 0 ) );
$fee_price	= tcp_number_format( $thecartpress->get_setting( 'fee_price', 0 ) );
$max_price	= tcp_number_format( $thecartpress->get_setting( 'max_price', 0 ) );
$min_weight	= tcp_number_format( $thecartpress->get_setting( 'min_weight', 0 ) );
$fee_weight	= tcp_number_format( $thecartpress->get_setting( 'fee_weight', 0 ) );
$max_weight	= tcp_number_format( $thecartpress->get_setting( 'max_weight', 0 ) );
$display	= $thecartpress->get_setting( 'display_shopping_cart_fee_message', 'before' ); ?>
<form method="post" action="">
<div class="postbox">
	<div class="inside">
	<table class="form-table">
	<tbody>
	<tr valign="top">
		<th scope="row">
			<label for="min_price"><?php _e( 'Minimum price', 'tcp_max' ); ?></label>
		</th>
		<td>
			<input type="text" id="min_price" name="min_price" value="<?php echo $min_price; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="fee_price"><?php _e( 'Small Order Fee', 'tcp_max' ); ?></label>
		</th>
		<td>
			<input type="text" id="fee_price" name="fee_price" value="<?php echo $fee_price; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
			<p class="description"><?php _e( 'This fee will be applicable if the minimum price is not exceeded. If this value is zero the minimum price must exceed to proceed to order.', 'tcp_max'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="max_price"><?php _e( 'Maximum price', 'tcp_max' ); ?></label>
		</th>
		<td>
			<input type="text" id="max_price" name="max_price" value="<?php echo $max_price; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
		</td>
	</tr>
	</tbody>
	</table>
	</div><!-- .inside -->
</div><!-- .postbox -->

<div class="postbox">
	<div class="inside">
	<table class="form-table">
	<tbody>
	<tr valign="top">
		<th scope="row">
			<label for="min_weight"><?php _e( 'Minimum weight', 'tcp_max' ); ?></label>
		</th>
		<td>
			<input type="text" id="min_weight" name="min_weight" value="<?php echo $min_weight; ?>" size="10" maxlength="15" /> <?php tcp_the_unit_weight(); ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="fee_weight"><?php _e( 'Small Order Fee', 'tcp_max' ); ?></label>
		</th>
		<td>
			<input type="text" id="fee_weight" name="fee_weight" value="<?php echo $fee_weight; ?>" size="10" maxlength="15" /> <?php tcp_the_currency(); ?>
			<p class="description"><?php _e( 'This fee will be applicable if the minimum weight is not exceeded. If this value is zero the minimum weight must exceed to proceed to order.', 'tcp_max'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="max_weight"><?php _e( 'Maximum Weight', 'tcp_max' ); ?></label>
		</th>
		<td>
			<input type="text" id="max_weight" name="max_weight" value="<?php echo $max_weight; ?>" size="10" maxlength="15" /> <?php tcp_the_unit_weight(); ?>
		</td>
	</tr>
	<?php do_action( 'tcp_max_settings_page' ); ?>
	</tbody>
	</table>
	</div><!-- .inside -->
</div><!-- .postbox -->

<div class="postbox">
	<div class="inside">
	<table class="form-table">
	<tbody>
	<tr valign="top">
		<th scope="row">
			<label for="min_weight"><?php _e( 'Shopping_cart display fee message', 'tcp_max' ); ?></label>
		</th>
		<td>
			<select id="display_shopping_cart_fee_message" name="display_shopping_cart_fee_message">
				<option <?php selected( $display, 'before' ); ?> value="before"><?php _e( 'Before', 'tcp' ); ?></option>
				<option <?php selected( $display, 'after' ); ?> value="after"><?php _e( 'After', 'tcp' ); ?></option>
				<option <?php selected( $display, 'before-after' ); ?> value="before-after"><?php _e( 'Before & After', 'tcp' ); ?></option>
				<option <?php selected( $display, 'none' ); ?> value="none"><?php _e( 'None', 'tcp' ); ?></option>
			</select>
		</td>
	</tr>
	</tbody>
	</table>
</div><!-- .inside -->
</div><!-- .postbox -->

<?php wp_nonce_field( 'tcp_max_settings' ); ?>
<div class="inside">
<?php submit_button( null, 'primary', 'save-max-settings' ); ?>
</div><!-- .inside -->
</form>

</div><?php
	}

	function admin_action() {
		if ( empty( $_POST ) ) return;
		check_admin_referer( 'tcp_max_settings' );

		$settings = get_option( 'tcp_settings' );
		$settings['min_price']	= tcp_input_number( $_POST['min_price'] );
		$settings['fee_price']	= tcp_input_number( $_POST['fee_price'] );
		$settings['max_price']	= tcp_input_number( $_POST['max_price'] );
		$settings['min_weight']	= tcp_input_number( $_POST['min_weight'] );
		$settings['fee_weight']	= tcp_input_number( $_POST['fee_weight'] );
		$settings['max_weight']	= tcp_input_number( $_POST['max_weight'] );
		$settings['display_shopping_cart_fee_message'] = isset( $_POST['display_shopping_cart_fee_message'] ) ? $_POST['display_shopping_cart_fee_message'] : 'before';
		$settings = apply_filters( 'tcp_max_settings_action', $settings );
		update_option( 'tcp_settings', $settings );
		$this->updated = true;
		thecartpress()->load_settings();
	}
}

new TCPMaxSettings();
endif;