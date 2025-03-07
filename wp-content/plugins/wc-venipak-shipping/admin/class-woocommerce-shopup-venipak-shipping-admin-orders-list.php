<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://shopup.lt/
 * @since      1.0.0
 *
 * @package    Woocommerce_Shopup_Venipak_Shipping
 * @subpackage Woocommerce_Shopup_Venipak_Shipping/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Shopup_Venipak_Shipping
 * @subpackage Woocommerce_Shopup_Venipak_Shipping/admin
 * @author     ShopUp <info@shopup.lt>
 */
class Woocommerce_Shopup_Venipak_Shipping_Admin_Orders_List {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	private $settings;

	/**
   *
   *
   * @since    1.13.0
   */
	private $shopup_venipak_shipping_field_forcedispatch;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $settings ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = $settings;
		$this->shopup_venipak_shipping_field_forcedispatch = $settings->get_option_by_key('shopup_venipak_shipping_field_forcedispatch');
	}

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public function add_venipak_shipping_bulk_action( $bulk_actions  ) {
		$bulk_actions['shopup_venipak_shipping_dispatch'] = __( 'Venipak dispatch selected', 'woocommerce-shopup-venipak-shipping' );
		$bulk_actions['shopup_venipak_shipping_labels'] = __( 'Get labels PDF', 'woocommerce-shopup-venipak-shipping' );
    	return $bulk_actions;
	}

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public function add_venipak_shipping_orders_list_columns( $columns ) {
		$reordered_columns = array();
	    foreach( $columns as $key => $column) {
	      $reordered_columns[$key] = $column;
	      if ( $key == 'order_status' ) {
	        $reordered_columns['shopup_venipak_shipping_status'] = __( 'Venipak status','woocommerce-shopup-venipak-shipping');
	      }
	    }
	    return $reordered_columns;
	}

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public function add_venipak_shipping_orders_list_columns_content( $column, $post_id  ) {
		if ($column !== 'shopup_venipak_shipping_status') return;

		if (is_numeric($post_id)) {
			$order = wc_get_order($post_id);
		} else {
			$order = $post_id;
			$post_id = $order->get_id();
		}

		$shipping_method = @array_shift($order->get_shipping_methods());
		$shipping_method_id = $shipping_method ? $shipping_method['method_id'] : null;

		if (!$this->shopup_venipak_shipping_field_forcedispatch && $shipping_method_id !== 'shopup_venipak_shipping_courier_method' && $shipping_method_id !== 'shopup_venipak_shipping_pickup_method') {
			return;
		}

		$venipak_shipping_order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);

		if ($venipak_shipping_order_data) {
			$status = $venipak_shipping_order_data['status'];
			$pack_numbers = $venipak_shipping_order_data['pack_numbers'];
			$error_message = $venipak_shipping_order_data['error_message'];
			$manifest = $venipak_shipping_order_data['manifest'];
		} else {
			return;
		}

        $tracking_data =  isset($pack_numbers) ? $this->get_order_tracking_data($pack_numbers, $order) : '';
		$content = '<div id="shopup_venipak_shipping_wrapper_order_' . $post_id . '">';
		$pack_numbers_string = isset($pack_numbers) ? implode(', ', $pack_numbers) : '';
		$content .= isset($tracking_data) ? '<span><strong>'.$tracking_data.'</strong></span>' : "";
		if ($status === 'sent' && isset($pack_numbers)) {
			$content .= '<div>' . $pack_numbers_string . '</div>';
			$content .= '<div class="venipak-shipping-pack" style="display: flex; gap: 5px;">
    <a class="button button-primary btn-sm" style="font-size: 20px; display: flex; align-items: center; gap: 5px;" title="' . esc_attr($pack_numbers_string) . '" target="_blank" href="' . esc_url(admin_url('admin-ajax.php') . '?action=woocommerce_shopup_venipak_shipping_get_label_pdf&order_id=' . $post_id) . '">
        <span class="dashicons dashicons-tag"></span>
    </a>
    <a class="button button-primary" style="display: flex; align-items: center; gap: 5px;" title="' . esc_attr($manifest) . '" target="_blank" href="' . esc_url(admin_url('admin-ajax.php') . '?action=woocommerce_shopup_venipak_shipping_get_manifest_pdf&order_id=' . $post_id) . '">
        <span class="dashicons dashicons-media-document"></span>
    </a>
</div>';
		} elseif ($status === 'error') {
			$content .= '<div class="venipak-shipping-error">' . $error_message . '</div>';
			$content .= '<span class="button button-primary" onclick="event.stopPropagation(); shopup_venipak_shipping_dispatch_order_by_id({ id: ' . $post_id . ' });">' . __( 'Dispatch', 'woocommerce-shopup-venipak-shipping' ) . '</span>';
		} elseif ($order->get_status() === 'processing') {
			$content .= '<div>' . $pack_numbers_string . '</div>';
			$content .= '<span class="button button-primary" onclick="event.stopPropagation(); shopup_venipak_shipping_dispatch_order_by_id({ id: ' . $post_id . ' });">' . __( 'Dispatch', 'woocommerce-shopup-venipak-shipping' ) . '</span>';
		}

		$content .= '</div>';
		echo $content;
	}

	public function get_order_tracking_data($pack_number, $order) {    
		$data = $order->get_meta('venipak_shipping_order_data');
		if(!empty($data)){
			$data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
			if (isset($data['pack_status_text']) && is_array($data['pack_status_text'])) {
				$lastIndex = count($data['pack_status_text']) - 1; // Get last index
				if ($lastIndex >= 0) {
				 return $data['pack_status_text'][$lastIndex];
				}
			} 				
		}  
	}  
}
