<?php
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

/**
 * Plugin Name:       Discount For Product
 * Plugin URI:        https://prozoned.com/
 * Description:       Discount For Product
 * Version:           1.0.0
 * Author:            Prozoned Technologies
 * Author URI:        https://prozoned.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/

	global $post, $product;
	// create custom plugin settings menu
	add_action('admin_menu', 'discount_create_menu');

	function discount_create_menu() {

		//create new top-level menu
		add_menu_page('Discount Plugin Settings', 'Discount', 'administrator', __FILE__, 'my_cool_plugin_settings_page' , plugins_url('/images/icon.png', __FILE__) );

		//call register settings function
		add_action( 'admin_init', 'register_discount_settings' );
	}

	function register_discount_settings() {
		//register our settings
		register_setting( 'discount-plugin-setting', 'cst_discounted_product' );
		register_setting( 'discount-plugin-setting', 'cst_discount' );
	}

	function my_cool_plugin_settings_page() {
	?>
	<div class="wrap">
	<h1>Add Discount For Product</h1>
	<form method="post" action="">
		<?php settings_fields( 'discount-plugin-setting' ); ?>
		<?php do_settings_sections( 'discount-plugin-setting' ); ?>
		
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Product</th>
				<td>
				<select name="cst_discounted_product">
				<option value="">Please Select Product</option>
				<?php
				$args = array('post_status' => "publish");
				$results = wc_get_products( $args );

				if($results){
					foreach($results as $result){ ?>
					<option value="<?php echo $result->get_id(); ?>" <?php if(esc_attr( get_option('product') ) == $result->get_id()){ echo "selected"; }?>><?php echo  $result->get_name(); ?></option>
				<?php } ?>
				</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Discount</th>
				<td><input type="text" name="cst_discount" value="<?php echo esc_attr( get_option('discount') ); ?>" /></td>
			</tr>
		</table>
		<button name='add_discount_coupon' style='color:#fff;background:#0071a1;padding:6px 20px 6px 20px;border:none;'>Submit</button>
	</form>
	</div>
	<?php 
	}
		if(isset($_POST['add_discount_coupon'])){
			
			global $wpdb;
			$product_id = $_POST['cst_discounted_product'];
			$amount = $_POST['cst_discount'];
				
			update_option( 'product', $product_id );
			update_option( 'discount', $amount );
			
			$table1 = $wpdb->prefix . 'posts';
			$table2 = $wpdb->prefix . 'postmeta';
	
			$post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'shop_coupon' and post_status='publish'");
			$nm_cp = $wpdb->num_rows;
			$to_generate = 1000;
			if($nm_cp!=0){	
				$post = $wpdb->get_results("SELECT p.* FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm ON (p.ID=pm.post_id) WHERE pm.meta_key = 'usage_count' AND pm.meta_value = '0' AND p.post_status='publish'");
				$unused_coupon = $wpdb->num_rows;
				if($unused_coupon>0){
					$to_generate = 1000-($unused_coupon);
				}
			}
			
			for($i=1;$i<=$to_generate;$i++){
				$amount = $_POST['cst_discount'];
				$rand = rand(111111,999999);
				$coupon_code = 'UNIQUECODE_'.$rand;
				$coupon[$i] = array(
				'post_title' => $coupon_code,
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'shop_coupon');

				$new_coupon_id[$i] = wp_insert_post( $coupon[$i] );

				// Add meta
				update_post_meta( $new_coupon_id[$i], 'discount_type', $discount_type[$i] );
				update_post_meta( $new_coupon_id[$i], 'coupon_amount', $amount );
				update_post_meta( $new_coupon_id[$i], 'individual_use', 'no' );
				update_post_meta( $new_coupon_id[$i], 'product_ids', $product_id );
				update_post_meta( $new_coupon_id[$i], 'exclude_product_ids', '' );
				update_post_meta( $new_coupon_id[$i], 'usage_limit', '1' );
				update_post_meta( $new_coupon_id[$i], 'usage_count', '0' );
				update_post_meta( $new_coupon_id[$i], 'expiry_date', '' );
				update_post_meta( $new_coupon_id[$i], 'apply_before_tax', 'yes' );
				update_post_meta( $new_coupon_id[$i], 'free_shipping', 'no' );
			}
			header("Refresh:0");
		}
	}
}
?>