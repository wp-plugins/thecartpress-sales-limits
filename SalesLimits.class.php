<?php
/*
Plugin Name: TheCartPress Sales Limits
Plugin URI: http://extend.thecartpress.com/ecommerce-plugins/limits/
Description: Sales Limits for TheCartPress
Version: 1.0.5
Author: TheCartPress team
Author URI: http://thecartpress.com
License: GPL
Parent: thecartpress
*/

/**
 * This file is part of TheCartPress-Sales-Limits.
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

define( 'TCP_LIMITS_FOLDER',		dirname( __FILE__ ) . '/' );
define( 'TCP_LIMITS_ADMIN_FOLDER',	TCP_LIMITS_FOLDER . 'admin/' );

define( 'TCP_LIMITS_WEIGHT_COST',	'TCP_LIMITS_WEIGHT_COST' );
define( 'TCP_LIMITS_PRICE_COST',	'TCP_LIMITS_PRICE_COST' );

class SalesLimits {
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		if ( is_admin() ) {
			require_once( TCP_LIMITS_ADMIN_FOLDER .'MAXSettings.class.php' );
			add_action( 'tcp_shopping_cart_summary_widget_form', array( &$this, 'tcp_shopping_cart_summary_widget_form' ), 10, 2 );
			add_filter( 'tcp_shopping_cart_summary_widget_update', array( &$this, 'tcp_shopping_cart_summary_widget_update' ), 10, 2 );
			add_action( 'tcp_shopping_cart_widget_form', array( &$this, 'tcp_shopping_cart_summary_widget_form' ), 10, 2 );
			add_filter( 'tcp_shopping_cart_widget_update', array( &$this, 'tcp_shopping_cart_summary_widget_update' ), 10, 2 );
			add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
		}
		add_filter( 'tcp_checkout_validate_before_enter', array( &$this, 'tcp_checkout_validate_before_enter' ) );
		add_filter( 'tcp_get_shopping_cart_summary', array( &$this, 'tcp_get_shopping_cart_summary' ), 10, 2 );
		add_filter( 'tcp_get_shopping_cart_widget', array( &$this, 'tcp_get_shopping_cart_widget' ) );
		add_filter( 'tcp_add_to_shopping_cart', array( &$this, 'tcp_add_to_shopping_cart' ) );
		add_filter( 'tcp_modify_to_shopping_cart', array( &$this, 'tcp_add_to_shopping_cart' ) );
	}

	function init() {
		if ( ! function_exists( 'is_plugin_active' ) ) require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'thecartpress/TheCartPress.class.php' ) ) add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		if ( function_exists( 'load_plugin_textdomain' ) ) load_plugin_textdomain( 'tcp_max', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function admin_notices() {
		echo '<div class="error">
			<p>', __( '<strong>Limits for TheCartPress</strong> requires TheCartPress plugin is activated.', 'tcp_max' ), '</p>
		</div>';
	}

	function tcp_get_shopping_cart_summary( $html, $args ) {
		$out = '';
		if ( isset( $args['see_maximum_msg'] ) && $args['see_maximum_msg'] ) {
			$shoppingcart = TheCartPress::getShoppingCart();
			global $thecartpress;
			$min_price = (float)$thecartpress->get_setting( 'min_price', 0 );
			$max_price = (float)$thecartpress->get_setting( 'max_price', 0 );
			$fee_price = (float)$thecartpress->get_setting( 'fee_price', 0 );
			$min_weight = (float)$thecartpress->get_setting( 'min_weight', 0 );
			$max_weight = (float)$thecartpress->get_setting( 'max_weight', 0 );
			$fee_weight = (float)$thecartpress->get_setting( 'fee_weight', 0 );
			$weight = $shoppingcart->getWeight();
			$total = $shoppingcart->getTotal();
			if ( $max_weight > 0 && $weight > $max_weight && $fee_price == 0 ) {
				$out .= '<li class="tcp_max_error exceed_weight">' . $this->exceed_weight() . '</li>';
			}
			if ( $weight < $min_weight ) {
				$out .= '<li class="tcp_min_fee not_reach_weight">' . $this->fee_weight() . '</li>';
			}
			if ( $max_price > 0 && $total > $max_price && $fee_weight == 0 ) {
				$out .= '<li class="tcp_max_error exceed_price">' . $this->exceed_price() . '</li>';
			}
			if ( $total > 0 && $total < $min_price && $fee_price > 0 ) {
				$out .= '<li class="fee_error not_reach_price">' . $this->fee_price() . '</li>';
			}
		}
		return $html . $out;
	}

	function tcp_get_shopping_cart_widget ( $args ) {
		echo $this->tcp_get_shopping_cart_summary( '', $args );
	}

	/**
	 * @param $param = array('validate' => true/false, 'msg' => 'error message' );
	 */
	function tcp_checkout_validate_before_enter( $param ) {
		$shoppingcart = TheCartPress::getShoppingCart();
		global $thecartpress;
		$min_price = (float)$thecartpress->get_setting( 'min_price', 0 );
		$max_price = (float)$thecartpress->get_setting( 'max_price', 0 );
		$fee_price = (float)$thecartpress->get_setting( 'fee_price', 0 );
		$min_weight = (float)$thecartpress->get_setting( 'min_weigt', 0 );
		$max_weight = (float)$thecartpress->get_setting( 'max_weight', 0 );
		$fee_weight = (float)$thecartpress->get_setting( 'fee_weight', 0 );
		$weight = $shoppingcart->getWeight();
		$total = $shoppingcart->getTotalToShow();
		if ( $max_weight > 0 && $weight > $max_weight ) {
			$param['msg'] = $this->exceed_weight();
			$param['validate'] = false;
		} elseif ( $weight < $min_weight && $fee_weight == 0 ) {
			$param['msg'] = $this->not_reach_weight();
			$param['validate'] = false;
		/*} elseif ( $weight < $min_weight && $fee_weight > 0 ) {
			$shoppingcart->addOtherCost( TCP_LIMITS_WEIGHT_COST, $fee_weight );
			$shoppingcart->deleteOtherCost( TCP_LIMITS_PRICE_COST );*/
		} elseif ( $max_price > 0 && $total > $max_price ) {
			$param['msg'] = $this->exceed_price();
			$param['validate'] = false;
		} elseif ( $total < $min_price  && $fee_price == 0 ) {
			$param['msg'] = $this->not_reach_price();
			$param['validate'] = false;
		/*} elseif ( $weight < $min_weight && $fee_weight > 0) {
			$shoppingcart->addOtherCost( TCP_LIMITS_PRICE_COST, $fee_price );
			$shoppingcart->deleteOtherCost( TCP_LIMITS_WEIGHT_COST );*/
		}
		return $param;
	}

	function exceed_weight( $html = '' ) {
		global $thecartpress;
		$max_weight = (float)$thecartpress->get_setting( 'max_weight', 0 );
		return $html . '<span class="tcp_max_error excess_weight">' . sprintf( __( 'Sorry, orders over %1$s %2$s cannot be accepted', 'tcp_max' ), $max_weight, tcp_get_the_unit_weight() ) . '</span>';
	}

	function exceed_price( $html = '' ) {
		global $thecartpress;
		$max_price = (float)$thecartpress->get_setting( 'max_price', 0 );
		return $html . '<span class="tcp_max_error excess_price">' . sprintf( __( 'Sorry, orders over %1$s cannot be accepted', 'tcp_max' ), tcp_format_the_price( $max_price ) ) . '</span>';

	}

	function fee_weight( $html = '' ) {
		global $thecartpress;
		$min_weight = (float)$thecartpress->get_setting( 'min_weight', 0 );
		$fee_weight = (float)$thecartpress->get_setting( 'fee_weight', 0 );
		return $html . '<span class="tcp_fee_weight">' . sprintf( __( 'Sorry, orders under %1$s %2$s will be recharged with %3$s', 'tcp_max' ), $min_weight, tcp_get_the_unit_weight(), tcp_format_the_price( $fee_weight ) ) . '</span>';
	}

	function fee_price( $html = '' ) {
		global $thecartpress;
		$min_price = (float)$thecartpress->get_setting( 'min_price', 0 );
		$fee_price = (float)$thecartpress->get_setting( 'fee_price', 0 );
		return $html . '<span class="tcp_fee_weight">' . sprintf( __( 'Sorry, orders under %1$s will be recharged with %2$s', 'tcp_max' ), tcp_format_the_price( $min_price ), tcp_format_the_price( $fee_price ) ) . '</span>';
	}

	/*function not_reach_weight( $html = '' ) {
		global $thecartpress;
		$min_weight = (float)$thecartpress->get_setting( 'min_weight', 0 );
		return $html . '<span class="tcp_max_error not_reach_weight">' . sprintf( __( 'Sorry, orders under %1$s %2$s cannot be accepted', 'tcp_max' ), $min_weight, tcp_get_the_unit_weight() ) . '</span>';
	}*/

	function not_reach_price( $html = '' ) {
		global $thecartpress;
		$min_price = (float)$thecartpress->get_setting( 'min_price', 0 );
		return $html . '<span class="tcp_max_error not_reach_price">' . sprintf( __( 'Sorry, orders under %1$s cannot be accepted', 'tcp_max' ), tcp_format_the_price( $min_price ) ) . '</span>';
	}

	function tcp_shopping_cart_summary_widget_form( $widget, $instance ) { 
		$see_maximum_msg = isset( $instance['see_maximum_msg'] ) ? (bool)$instance['see_maximum_msg'] : false;?>
		<br />
		<input type="checkbox" class="checkbox" id="<?php echo $widget->get_field_id( 'see_maximum_msg' ); ?>" name="<?php echo $widget->get_field_name( 'see_maximum_msg' ); ?>"<?php checked( $see_maximum_msg ); ?> />
		<label for="<?php echo $widget->get_field_id( 'see_maximum_msg' ); ?>"><?php _e( 'See limits notices', 'tcp_max' ); ?></label><?php
	}

	function tcp_shopping_cart_summary_widget_update( $instance, $new_instance ) {
		$instance['see_maximum_msg'] = isset( $new_instance['see_maximum_msg'] );
		return $instance; 
	}

	function plugin_action_links( $links, $file ) {
		if ( $file == 'thecartpress-limits/Limits.class.php' && function_exists( 'admin_url' ) ) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page=tcp_max_settings_page' ). '">' . __( 'Settings', 'tcp_max' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	function tcp_add_to_shopping_cart( $shopping_cart_item ) {
		$shoppingcart = TheCartPress::getShoppingCart();
		global $thecartpress;
		$min_weight = (float)$thecartpress->get_setting( 'min_weigt', 0 );
		$fee_weight = (float)$thecartpress->get_setting( 'fee_weight', 0 );
		$weight = $shoppingcart->getWeight();
		if ( $weight < $min_weight && $fee_weight > 0 ) {
			$shoppingcart->addOtherCost( TCP_LIMITS_WEIGHT_COST, $fee_weight, __( 'Minimum weight fee', 'tcp-limits' ) );
		} else {
			$shoppingcart->deleteOtherCost( TCP_LIMITS_WEIGHT_COST );
		}
		$min_price = (float)$thecartpress->get_setting( 'min_price', 0 );
		$fee_price = (float)$thecartpress->get_setting( 'fee_price', 0 );
		$total = $shoppingcart->getTotalToShow();
		if ( $total < $min_price && $fee_price > 0) {
			$shoppingcart->addOtherCost( TCP_LIMITS_PRICE_COST, $fee_price, __( 'Minimum Price fee', 'tcp-limits' ) );
		} else {
			$shoppingcart->deleteOtherCost( TCP_LIMITS_PRICE_COST );
		}
		TheCartPress::saveShoppingCart();
		return $shopping_cart_item;
	}
}

new SalesLimits();
?>