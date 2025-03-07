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
class Woocommerce_Shopup_Venipak_Shipping_Admin_Order_Edit {

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
	 * @since    1.2.0
	 */
	private $pickup_type;

	private $shopup_venipak_shipping_field_forcedispatch;

	private $shopup_venipak_shipping_field_maxpackproducts;

	private $iseventoptiondisabled ;

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
		$this->pickup_type = $settings->get_option_by_key('shopup_venipak_shipping_field_pickuptype');
		$this->shopup_venipak_shipping_field_forcedispatch = $settings->get_option_by_key('shopup_venipak_shipping_field_forcedispatch');
		$optionValue = $settings->get_option_by_key('shopup_venipak_shipping_field_maxpackproducts');
		if ($optionValue !== null) {
			$this->shopup_venipak_shipping_field_maxpackproducts = $optionValue;
		} else {
			$this->shopup_venipak_shipping_field_maxpackproducts = 1000;
		}
		$iseventoptiondisabled = $settings->get_option_by_key('shopup_venipak_shipping_field_iseventoptiondisabled'); 
		if ($iseventoptiondisabled !== null) {
			$this->iseventoptiondisabled = 1; 
		} else { 
			$this->iseventoptiondisabled = 0;
		}
	}

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public function add_venipak_shipping_order_edit( $order ) {

		$shipping_method = @array_shift($order->get_shipping_methods());

		if ($shipping_method !== null && isset($shipping_method['method_id'])) {
			$shipping_method_id = $shipping_method['method_id'];
		} else {
			// Handle the case where no shipping method is available
			$shipping_method_id = null;
		}
    	
		if (!$this->shopup_venipak_shipping_field_forcedispatch && $shipping_method_id !== 'shopup_venipak_shipping_courier_method' && $shipping_method_id !== 'shopup_venipak_shipping_pickup_method') return;

		$venipak_pickup_point_id = $order->get_meta('venipak_pickup_point', true );
		$venipak_pickup_point_title = $this->get_venipak_point_title_by_id($venipak_pickup_point_id);
		$status = $this->get_venipak_status($order);
		$status_title = $this->get_venipak_status_title($status);
		$tracking_code = $this->get_venipak_tracking_code($order);
		$manifest = $this->get_venipak_manifest($order);
		$pack_collection = $this->get_venipak_packs($order);
		$pack_count = sizeof($pack_collection);
		$weight = $this->get_venipak_weight($order);
		$product_count = $this->get_product_count($order);
		$error_message = $this->get_venipak_error($order);
		$is_event_option_disabled = $this->iseventoptiondisabled;
		$order_event_detail = $this->get_order_event_detail($order);  
		$pack_number = $this->get_order_pack_no($order);

		if(isset($pack_number) && $pack_number != '')
		{
            $order_tracking_data = $this->get_order_tracking_data($pack_number , $order, $order_event_detail); 
		}
		 
		$get_venipak_shipping_order_data = $this->get_venipak_shipping_order_data($order); 

		?>
		<br class="clear" />
		<h4><?php echo __( 'Venipak shipping', 'woocommerce-shopup-venipak-shipping' ); ?> <a href="#" class="edit_address"></a></h4>
		<div class="address">
			<p><strong><?php echo __( 'Total weight', 'woocommerce-shopup-venipak-shipping' ); ?></strong><?php echo $weight . ' ' . __( 'kg.', 'woocommerce-shopup-venipak-shipping' ) ?></p>
			<p><strong><?php echo __( 'Products count', 'woocommerce-shopup-venipak-shipping' ); ?></strong><?php echo $product_count . ' ' . __( 'vnt.', 'woocommerce-shopup-venipak-shipping' ) ?></p>
			<p><strong><?php echo __( 'Packages count', 'woocommerce-shopup-venipak-shipping' ); ?></strong><?php echo $pack_count . ' ' . __( 'vnt.', 'woocommerce-shopup-venipak-shipping' )?></p>
		<?php if ($venipak_pickup_point_title) { ?>
			<p><strong><?php echo __( 'Pickup point', 'woocommerce-shopup-venipak-shipping' ); ?></strong><?php echo $venipak_pickup_point_title ?></p>
		<?php } ?>
		<?php if ($tracking_code) { ?>
			<p><strong><?php echo __( 'Tracking number', 'woocommerce-shopup-venipak-shipping' ); ?></strong><?php echo $tracking_code ?></p>
		<?php } ?>

		<?php if($pack_number && $is_event_option_disabled == 0 && ($order_event_detail == '' || $order_event_detail != 'Delivered')){ 
				if(!empty($order_tracking_data) )  {
				foreach($order_tracking_data as $data)
				{  
					?>
					<span><?php echo isset($data['date']) ? $data['date'] : '';?><br><strong><?php echo isset($data['pack_status_text']) ? $data['pack_status_text'] : ''?></strong></span> 
					<?php
				}
			}
			}
			elseif(isset($order_event_detail) && $order_event_detail == 'Delivered'  && $is_event_option_disabled == 0){
				foreach($get_venipak_shipping_order_data['pack_status_text'] as $key => $pack_status_text)
				{  
					?>
					<span><?php echo isset($get_venipak_shipping_order_data['date'][$key]) ? $get_venipak_shipping_order_data['date'][$key] : '';?><br><strong><?php echo isset($pack_status_text) ? $pack_status_text : ''?></strong></span> 
					<?php
				} 
			}?> 
			<p><strong><?php echo __( 'Dispatch status', 'woocommerce-shopup-venipak-shipping' ); ?></strong><?php echo $status_title ?></p>
			<div>
				<p><?php echo __( 'Packages count', 'woocommerce-shopup-venipak-shipping' ); ?>: <button onclick="addPackage();" type="button"><?php echo __( 'Add package', 'woocommerce-shopup-venipak-shipping' ); ?></button></p>
				<table id="packages-table">
					<tr>
						<th><?php echo __( 'Width', 'woocommerce-shopup-venipak-shipping' ); ?></th>
						<th><?php echo __( 'Height', 'woocommerce-shopup-venipak-shipping' ); ?></th>
						<th><?php echo __( 'Length', 'woocommerce-shopup-venipak-shipping' ); ?></th>
						<th><?php echo __( 'Weight', 'woocommerce-shopup-venipak-shipping' ); ?></th>
						<th><?php echo __( 'Description', 'woocommerce-shopup-venipak-shipping' ); ?></th>
						<th><?php echo __( 'Remove', 'woocommerce-shopup-venipak-shipping' ); ?></th>
					</tr>
					<?php for ($i = 0; $i < $pack_count; $i++) { ?>
					<tr class="venipak-pack">
						<td><input class="venipak-pack-width" style="width: 70px;" type="text" name="width[]" value="<?php echo $pack_collection[$i]['width']; ?>" /></td>
						<td><input class="venipak-pack-height" style="width: 70px;" type="text" name="height[]" value="<?php echo $pack_collection[$i]['height']; ?>" /></td>
						<td><input class="venipak-pack-length" style="width: 70px;" type="text" name="length[]" value="<?php echo $pack_collection[$i]['length']; ?>" /></td>
						<td><input class="venipak-pack-weight" style="width: 70px;" type="text" name="weight[]" value="<?php echo $pack_collection[$i]['weight']; ?>" /></td>
						<td><textarea class="venipak-pack-description" name="description[]"><?php echo $pack_collection[$i]['description']; ?></textarea></td>
						<td><button onclick="removePackage(this)" type="button"><?php echo __( 'Remove package', 'woocommerce-shopup-venipak-shipping' ); ?></button></td>
					</tr>
					<?php }	?>
				</table>
				<div>
					<input id="shopup_venipak_shipping_global" type="checkbox" name="is_global" />
					<label for="shopup_venipak_shipping_global"><?php echo __( 'Global shipment', 'woocommerce-shopup-venipak-shipping' ) ?></label>
				</div><br />
			<?php if ($status !== 'sent') { ?>
				<span class="button button-primary" onclick="event.stopPropagation(); shopup_venipak_shipping_dispatch_order_by_id({ id: <?php echo $order->get_id(); ?> });"><?php echo __( 'Dispatch', 'woocommerce-shopup-venipak-shipping' ) ?></span>
			<?php } ?>
			<?php if ($status === 'sent') { ?>
				<span class="button button-primary" onclick="event.stopPropagation(); shopup_venipak_shipping_dispatch_order_by_id({ id: <?php echo $order->get_id(); ?>, newDispatch: true });"><?php echo __( 'Dispatch one more time', 'woocommerce-shopup-venipak-shipping' ) ?></span>
			<?php } ?>
			</div>
			<div id="shopup_venipak_shipping_wrapper_order_<?php echo $order->get_id(); ?>" style="margin-top: 10px;">
			<?php if ($status === 'error') { ?>
				<p style="color: red;"><?php echo $error_message ?></p>
			<?php } ?>
			<?php if ($status === 'sent') { ?>
				<div>
					<a class="button button-primary" target="_blank" href="<?php echo admin_url('admin-ajax.php'); ?>?action=woocommerce_shopup_venipak_shipping_get_label_pdf&order_id=<?php echo $order->get_id(); ?>"><?php echo __( 'Print labels', 'woocommerce-shopup-venipak-shipping' ) ?></a>
					<a class="button button-primary" target="_blank" href="<?php echo admin_url('admin-ajax.php'); ?>?action=woocommerce_shopup_venipak_shipping_get_manifest_pdf&order_id=<?php echo $order->get_id(); ?>"><?php echo sprintf( __( 'Print manifest (%s)', 'woocommerce-shopup-venipak-shipping' ), $manifest) ?></a>
				</div>
			<?php } ?>
			</div>
		</div>
		<div class="edit_address">
		<?php
		$default_options = $venipak_pickup_point_id ? [$venipak_pickup_point_id => $venipak_pickup_point_title] : [];
		woocommerce_wp_select( array(
			'id' => 'venipak_pickup_point',
			'label' => __( 'Pickup point', 'woocommerce-shopup-venipak-shipping' ),
			'class' => 'venipak_pickup_point',
			'wrapper_class' => 'form-field-wide',
			'options' => $default_options
		) );
		?>
			<button type="button" onclick="jQuery('.venipak_pickup_point').val(null).trigger('change');"><?php echo __( 'Remove', 'woocommerce-shopup-venipak-shipping' ); ?></button>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$.get('admin-ajax.php', { 'action': 'woocommerce_venipak_shipping_pickup_points' }, function(data) {
			$('.venipak_pickup_point').select2({
				data: data.map(value => ({
					id: value.id,
					text: `${value.name}, ${value.address}, ${value.city}, ${value.zip}`,
				})),
		    });
	      	$('.venipak_pickup_point').val("<?php echo $venipak_pickup_point_id; ?>").trigger('change');
      }, 'json');
  	});

		function addPackage() {
			jQuery('#packages-table tr:last').after('<tr class="venipak-pack"><td><input class="venipak-pack-width" style="width: 70px;" type="text" name="width[]" /></td><td><input class="venipak-pack-height" style="width: 70px;" type="text" name="height[]" /></td><td><input class="venipak-pack-length" style="width: 70px;" type="text" name="length[]" /></td><td><input class="venipak-pack-weight" style="width: 70px;" type="text" name="weight[]" /></td><td><textarea class="venipak-pack-description" name="description[]"></textarea></td><td><button onclick="removePackage(this)" type="button"><?php echo __( "Remove package", "woocommerce-shopup-venipak-shipping" ); ?></button></td></tr>');
		}

		function removePackage(cb) {
			jQuery(cb).closest('tr').remove();
		}
		</script>
		<?php
	}

	/**
	 *
	 *
	 * @since    1.0.0
	 */
	public function add_venipak_shipping_order_save( $order_id ) {
		$order = wc_get_order($order_id);
		if ( isset( $_POST['venipak_pickup_point'] )) {
			$order->update_meta_data('venipak_pickup_point', wc_clean( $_POST[ 'venipak_pickup_point' ] ) );
		} else {
			$order->delete_meta_data('venipak_pickup_point');
		}
		$order->save();
	}

	public function get_venipak_point_title_by_id($point_id) {
		if (!$point_id) {
			return null;
		}
		$collection = venipak_fetch_pickups();
		$venipak_pickup_entity = null;
		
		foreach ($collection as $key => $value) {
			if ($value['id'] == $point_id) {
				$venipak_pickup_entity = $value;
				break;
			}
		}
		if (!$venipak_pickup_entity) {
			return null;
		}
		return $venipak_pickup_entity['name'] . ' ' . $venipak_pickup_entity['address'] . ' ' . $venipak_pickup_entity['city'];
	}

	public function get_venipak_status($order) {
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data) {
			return $order_data['status'];
		}
		return null;
	}

	public function get_venipak_tracking_code($order) {
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data) {
			return $order_data['pack_numbers'] ? implode('<br/>', $order_data['pack_numbers']) : '';
		}
		return null;
	}

	public function get_venipak_manifest($order) {
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data) {
			return $order_data['manifest'];
		}
		return null;
	}

	public function get_venipak_error($order) {
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data) {
			return $order_data['error_message'];
		}
		return null;
	}

	public function get_order_event_detail($order) {
	    $order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);

	    if ($order_data && isset($order_data['event']) && is_array($order_data['event'])) {
	        return end($order_data['event']);
	    }

	    return null;
	}


	public function get_order_tracking_data($pack_number, $order, $order_event_detail) { 

		if( $order_event_detail != "Delivered")
		{
			$url = "https://tracking.venipak.com/api/v1/events?pack_no=" . $pack_number;

        // WordPress API request
        $response = wp_remote_get($url, [
            'headers' => [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        ]);


        // Check for errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("API Request failed: $error_message");
            return null;
        }

        // Extract response body and decode JSON
        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);

        if ($status !== 200) {
            error_log("API returned status code $status: $body");
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            error_log("Failed to decode API response.");
            return null;
        }

		$order_meta = json_decode($order->get_meta('venipak_shipping_order_data'), true); 
		$ischanged = false;
		if (is_array($order_meta) && isset($order_meta['pack_numbers']) && is_array($order_meta['pack_numbers'])) {
		$order_meta['event'] = [];
		$order_meta['date'] = [];
		$order_meta['pack_status_text'] = [];
		}

		foreach ($data as $a) { 
			if (is_array($order_meta) && isset($order_meta['pack_numbers']) && is_array($order_meta['pack_numbers'])) {  
				$pack_numbers = $order_meta['pack_numbers'];
				$ischanged = false; 
				if (!empty($pack_numbers[0]) && isset($a['pack_no']) && !empty($a['pack_no']) ) {
					$ischanged = true;
					$order_meta['pack_numbers'][0] = $a['pack_no'];
				} 
				if (isset($a['event']) && !empty($a['event'])) {
					$ischanged = true;
					$order_meta['event'][] = $a['event'];
				} 
				if (isset($a['date']) && !empty($a['date'])) {
					$ischanged = true;
					$order_meta['date'][] = $a['date'];
				}
		 
				if (isset($a['pack_status_text']) && !empty($a['pack_status_text'])) {
					$ischanged = true;
					$order_meta['pack_status_text'][] = $a['pack_status_text'];
				} 
				if ($ischanged) {
					$order->update_meta_data('venipak_shipping_order_data', json_encode($order_meta));
					$order->save();
		 
					$order_meta = json_decode($order->get_meta('venipak_shipping_order_data', true), true);
				}
				} 	 
		} 
		return $data;
		} 
		else{
			return [];
		}
	}
	

	public function get_venipak_shipping_order_data($order)
	{
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data) {
			return $order_data;;
		}
		return null;
	}
	
	public function get_order_pack_no($order)
	{
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true); 
		if ($order_data) { 
			return $order_data['pack_numbers'][0];
		}
		return null;
	} 
	


	public function get_venipak_packs($order) {
		$order_products = [];
		$order_description = '';
		$weight = 0;
		foreach ( $order->get_items() as $item_id => $product_item ) {
			$product = $product_item->get_product();
			if (!$product) continue;
			$product_weight = $this->get_product_weight($product);
			$product_quantity = $product_item->get_quantity();
			$order_description .= $product_item->get_product()->get_title() . PHP_EOL;
			for ($i = 0; $i < $product_quantity; $i++) {
				$order_products[] = $product;
				$weight += $product_weight;
			}
		}
		$weight = wc_get_weight($weight, 'kg');
		$pack_collection = array();
		if ($order->get_meta('venipak_pickup_point', true )) {
			$pack_collection[] = array(
				'length' => 0,
				'width' => 0,
				'height' => 0,
				'weight' => $weight,
				'description' => $order_description,
			);
			return $pack_collection;
		}

		$products_with_shipments = [];
		$products_without_shipments = [];

		foreach ($order_products as $product) {
			$product_total_shipments = $product->get_meta('_venipak_total_shipments', true) ?: $product->get_meta('_omnivalt_total_shipments', true);
			if ($product_total_shipments) {
				$products_with_shipments[] = $product;
			} else {
				$products_without_shipments[] = $product;
			}
		}

		foreach ($products_with_shipments as $product) {
			$product_total_shipments = $product->get_meta('_venipak_total_shipments', true) ?: $product->get_meta('_omnivalt_total_shipments', true);
			$pack_weight = $this->get_product_weight($product);
			$pack_description = $product->get_title() . PHP_EOL;
			$pack_weight = max(wc_get_weight($pack_weight, 'kg'), 0.1);
			for ($j = 0; $j < $product_total_shipments; $j++) {
				$pack_collection[] = array(
					'weight' => $pack_weight / $product_total_shipments,
					'description' => $pack_description,
				);
			}
		}

		$pack_count = ceil(sizeof($products_without_shipments) / $this->shopup_venipak_shipping_field_maxpackproducts);


		for ($i = 0; $i < $pack_count; $i++) {
			$range_from = $i * $this->shopup_venipak_shipping_field_maxpackproducts;
			$range_to = $range_from + $this->shopup_venipak_shipping_field_maxpackproducts;
			$pack_weight = 0;
			$pack_description = '';
			$prev_title = 'no-repeat';

			for ($y = $range_from; $y < $range_to; $y++) {
				if (!array_key_exists($y, $products_without_shipments)) break;
				$pack_weight += $this->get_product_weight($products_without_shipments[$y]);
				if ($prev_title !== $products_without_shipments[$y]->get_title()) {
					$pack_description .= $products_without_shipments[$y]->get_title() . PHP_EOL;
					$prev_title = $products_without_shipments[$y]->get_title();
				}
			}
			$pack_weight = wc_get_weight($pack_weight, 'kg');
			$pack_collection[] = array(
				'length' => 0,
				'width' => 0,
				'height' => 0,
				'weight' => $pack_weight,
				'description' => $pack_description,
			);
		}

		return $pack_collection;
	}

	public function get_venipak_weight($order) {
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data && $order_data['status'] === 'sent') {
			return $order_data['weight'];
		}

		$weight = 0;
		foreach ( $order->get_items() as $item_id => $product_item ) {
			$product = $product_item->get_product();
			if (!$product) continue;
			$weight += $this->get_product_weight($product) * $product_item->get_quantity();
		}

		return wc_get_weight($weight, 'kg');
	}

	public function get_product_weight($product) {
		if (!$product->get_virtual()) {
			$weight = $product->get_weight();
			if ($weight) {
				return $weight;
			}
		}

		return 0;
	}

	public function get_venipak_status_title($status) {
		switch($status) {
			case "waiting":
				return __( 'Waiting', 'woocommerce-shopup-venipak-shipping' );
			case "sent":
				return __( 'Sent', 'woocommerce-shopup-venipak-shipping' );
			case "error":
				return __( 'Error', 'woocommerce-shopup-venipak-shipping' );
		}
		return null;
	}

	public function get_product_count($order) {
		$order_data = json_decode($order->get_meta('venipak_shipping_order_data'), true);
		if ($order_data) {
			return $order_data['products_count'];
		}
		$count = 0;
		foreach ( $order->get_items() as $item_id => $product_item ) {
			$product_quantity = $product_item->get_quantity();
			$count += $product_quantity;
		}
		return $count;
	}
}
