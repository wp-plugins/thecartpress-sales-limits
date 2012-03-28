<?php
/*
Plugin Name: TheCartPress Sales Limits
Plugin URI: http://extend.thecartpress.com/ecommerce-plugins/limits/
Description: sales Limits for TheCartPress
Version: 1.0.1
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

class TCPSalesLimits {

	function init() {
		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'thecartpress/TheCartPress.class.php' ) )  {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
		if ( function_exists( 'load_plugin_textdomain' ) )
			load_plugin_textdomain( 'tcp_max', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	function admin_notices() {
		echo '<div class="error">
			<p>', __( '<strong>Sales Limits for TheCartPress</strong> requires TheCartPress plugin activated.', 'tcp_max' ), '</p>
		</div>';
	}

	function tcp_get_shopping_cart_summary( $html, $args ) {
		$out = '';
		if ( isset( $args['see_maximum_msg'] ) && $args['see_maximum_msg'] ) {
			$shoppingcart = TheCartPress::getShoppingCart();
			$max_settings = get_option( 'tcp_max_settings', array() );
			$min_price = isset( $max_settings['min_price'] ) ? (float)$max_settings['min_price'] : 0;
			$max_price = isset( $max_settings['max_price'] ) ? (float)$max_settings['max_price'] : 0;
			$min_weight = isset( $max_settings['min_weight'] ) ? (float)$max_settings['min_weight'] : 0;
			$max_weight = isset( $max_settings['max_weight'] ) ? (float)$max_settings['max_weight'] : 0;
			$weight = $shoppingcart->getWeight();
			$total = $shoppingcart->getTotalToShow();
			if ( $max_price > 0 && $total > $max_price ) {
				$out .= '<li class="tcp_max_error exceed_price">' . $this->exceed_price() . '</li>';
			}
			if ( $total < $min_price ) {
				$out .= '<li class="tcp_min_error not_reach_price">' . $this->not_reach_price() . '</li>';
			}
			if ( $max_weight > 0 && $weight > $max_weight ) {
				$out .= '<li class="tcp_max_error exceed_weight">' . $this->exceed_weight() . '</li>';
			}
			if ( $weight < $min_weight ) {
				$out .= '<li class="tcp_min_error not_reach_weight">' . $this->not_reach_weight() . '</li>';
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
		$max_settings = get_option( 'tcp_max_settings', array() );
		$min_price = isset( $max_settings['min_price'] ) ? (float)$max_settings['min_price'] : 0;
		$max_price = isset( $max_settings['max_price'] ) ? (float)$max_settings['max_price'] : 0;
		$min_weight = isset( $max_settings['min_weight'] ) ? (float)$max_settings['min_weight'] : 0;
		$max_weight = isset( $max_settings['max_weight'] ) ? (float)$max_settings['max_weight'] : 0;
		$weight = $shoppingcart->getWeight();
		$total = $shoppingcart->getTotal();
		if ( $max_price > 0 && $total > $max_price ) {
			$param['msg'][] = $this->exceed_price();
			$param['validate'] = false;
		}
		if ( $total < $min_price ) {
			$param['msg'][] = $this->not_reach_price();
			$param['validate'] = false;
		}
		if ( $max_weight > 0 && $weight > $max_weight ) {
			$param['msg'][] = $this->exceed_weight();
			$param['validate'] = false;
		}
		if ( $weight < $min_weight ) {
			$param['msg'][] = $this->not_reach_weight();
			$param['validate'] = false;
		}
		return $param;
	}

	function exceed_weight( $html = '' ) {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$max_weight = isset( $max_settings['max_weight'] ) ? (float)$max_settings['max_weight'] : 0;
		return $html . '<span class="tcp_max_error excess_weight">' . sprintf( __( 'Sorry, orders over %1$s %2$s cannot be accepted', 'tcp_max' ), $max_weight, tcp_get_the_unit_weight() ) . '</span>';
	}

	function exceed_price( $html = '' ) {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$max_price = isset( $max_settings['max_price'] ) ? (float)$max_settings['max_price'] : 0;
		return $html . '<span class="tcp_max_error excess_price">' . sprintf( __( 'Sorry, orders over %1$s cannot be accepted', 'tcp_max' ), tcp_format_the_price( $max_price ) ) . '</span>';

	}

	function not_reach_weight( $html = '' ) {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$min_weight = isset( $max_settings['min_weight'] ) ? (float)$max_settings['min_weight'] : 0;
		return $html . '<span class="tcp_max_error not_reach_weight">' . sprintf( __( 'Sorry, orders under %1$s %2$s cannot be accepted', 'tcp_max' ), $min_weight, tcp_get_the_unit_weight() ) . '</span>';
	}

	function not_reach_price( $html = '' ) {
		$max_settings = get_option( 'tcp_max_settings', array() );
		$min_price = isset( $max_settings['min_price'] ) ? (float)$max_settings['min_price'] : 0;
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

/*	function tcp_add_to_shopping_cart( $shopping_cart_item ) {
		$shoppingcart = TheCartPress::getShoppingCart();
		$max_settings = get_option( 'tcp_max_settings', array() );
		$max_price = isset( $max_settings['max_price'] ) ? (float)$max_settings['max_price'] : 0;
		$max_weight = isset( $max_settings['max_weight'] ) ? (float)$max_settings['max_weight'] : 0;
		if ( $shoppingcart->getWeight() + $shopping_cart_item->getWeight() > $max_weight ) {
			add_filter( 'tcp_buy_button_add_button', array( $this, 'exceed_weight' ) );
		} elseif ( $shoppingcart->getTotal() + $shopping_cart_item->getTotal() > $max_price ) {
			add_filter( 'tcp_buy_button_add_button', array( $this, 'exceed_price' ) );
		}
		return $shopping_cart_item;
	}*/
	
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		if ( is_admin() ) {
			require_once( dirname( __FILE__ ) .'/admin/MAXSettings.class.php' );
			add_action( 'tcp_shopping_cart_summary_widget_form', array( $this, 'tcp_shopping_cart_summary_widget_form' ), 10, 2 );
			add_filter( 'tcp_shopping_cart_summary_widget_update', array( $this, 'tcp_shopping_cart_summary_widget_update' ), 10, 2 );
			add_action( 'tcp_shopping_cart_widget_form', array( $this, 'tcp_shopping_cart_summary_widget_form' ), 10, 2 );
			add_filter( 'tcp_shopping_cart_widget_update', array( $this, 'tcp_shopping_cart_summary_widget_update' ), 10, 2 );
			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		} else {
			add_filter( 'tcp_checkout_validate_before_enter', array( $this, 'tcp_checkout_validate_before_enter' ) );
			add_filter( 'tcp_get_shopping_cart_summary', array( $this, 'tcp_get_shopping_cart_summary' ), 10, 2 );
			add_filter( 'tcp_get_shopping_cart_widget', array( $this, 'tcp_get_shopping_cart_widget' ) );
			//add_filter( 'tcp_add_to_shopping_cart', array( $this, 'tcp_add_to_shopping_cart' ) );
		}
	}
}

new TCPSalesLimits();
?>