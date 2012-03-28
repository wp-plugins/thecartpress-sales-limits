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

class TCPMAXSettings {

	function admin_init() {
		register_setting( 'tcp_max_options', 'tcp_max_settings', array( $this, 'validate' ) );
		add_settings_section( 'tcp_max_main_section', __( 'Limits settings', 'tcp_max' ) , array( $this, 'show_tcp_max_section' ), __FILE__ );
		add_settings_field( 'min_price', __( 'Minimum price', 'tcp_max' ), array( $this, 'show_min_price' ), __FILE__ , 'tcp_max_main_section' );
		add_settings_field( 'max_price', __( 'Maximum price', 'tcp_max' ), array( $this, 'show_max_price' ), __FILE__ , 'tcp_max_main_section' );
		add_settings_field( 'min_weight', __( 'Minimum weight', 'tcp_max' ), array( $this, 'show_min_weight' ), __FILE__ , 'tcp_max_main_section' );
		add_settings_field( 'max_weight', __( 'Maximum weight', 'tcp_max' ), array( $this, 'show_max_weight' ), __FILE__ , 'tcp_max_main_section' );
	}

	function admin_menu() {
		global $thecartpress;
		if ( $thecartpress ) {
			$base = $thecartpress->get_base();
			add_submenu_page( $base, __( 'Limits settings', 'tcp_max' ), __( 'Limits Settings', 'tcp_max' ), 'tcp_edit_settings', 'tcp_max_settings_page', array( $this, 'show_settings' ) );
		}
	}

	function show_settings() {?>
	<div class="wrap">
		<h2><?php _e( 'Maximum-Minimum Settings', 'tcp_max' );?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'tcp_max_options' ); ?>
			<?php do_settings_sections( __FILE__ ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'tcp_max' ) ?>" />
			</p>
		</form>
	</div><?php
	}

	function validate( $input ) {
		$input['min_price']		= isset( $input['min_price'] ) ? (float)$input['min_price'] : 0;
		$input['max_price']		= isset( $input['max_price'] ) ? (float)$input['max_price'] : 0;
		$input['min_weight']	= isset( $input['min_weight'] ) ? (float)$input['min_weight'] : 0;
		$input['max_weight']	= isset( $input['max_weight'] ) ? (float)$input['max_weight'] : 0;
		return $input;
	}

	function show_tcp_max_section() {
	}

	function show_min_price() {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$min_price = isset( $max_settings['min_price'] ) ? (float)$max_settings['min_price'] : 0;?>
		<input type="text" id="min_price" name="tcp_max_settings[min_price]" value="<?php echo $min_price;?>" size="10" maxlength="15" /> <?php tcp_the_currency();
	}

	function show_max_price() {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$max_price = isset( $max_settings['max_price'] ) ? (float)$max_settings['max_price'] : 0;?>
		<input type="text" id="max_price" name="tcp_max_settings[max_price]" value="<?php echo $max_price;?>" size="10" maxlength="15" /> <?php tcp_the_currency();
	}

	function show_min_weight() {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$min_weight = isset( $max_settings['min_weight'] ) ? (float)$max_settings['min_weight'] : 0;?>
		<input type="text" id="min_weight" name="tcp_max_settings[min_weight]" value="<?php echo $min_weight;?>" size="10" maxlength="15" /> <?php tcp_the_unit_weight();
	}

	function show_max_weight() {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$max_weight = isset( $max_settings['max_weight'] ) ? (float)$max_settings['max_weight'] : 0;?>
		<input type="text" id="max_weight" name="tcp_max_settings[max_weight]" value="<?php echo $max_weight;?>" size="10" maxlength="15" /> <?php tcp_the_unit_weight();
	}
	
	function __construct() {
		if ( is_admin() ) {
			add_action('admin_init', array( $this, 'admin_init' ) );
			add_action('admin_menu', array( $this, 'admin_menu' ), 99 );
		}
	}
}

new TCPMAXSettings();
?>
