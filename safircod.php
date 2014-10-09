<?php
/**
Plugin Name: WooCommerce SafirCOD
Plugin URI: http://safircod.ir/
Description: This plugin integrates <strong>SafirCOD</strong> service with WooCommerce.
Version: 1.2.1
Author: Domanjiri
Text Domain: safircod
Domain Path: /lang/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
**/

function activate_WC_SafirCOD_plugin()
{
    wp_schedule_event(time(), 'hourly', 'update_safir_orders_state');
} 
register_activation_hook(__FILE__, 'activate_WC_SafirCOD_plugin');


function deactivate_WC_SafirCOD_plugin()
{
    wp_clear_scheduled_hook('update_safir_orders_state');
}
register_deactivation_hook(__FILE__, 'deactivate_WC_SafirCOD_plugin');


// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
	function safircod_shipping_method_init() {
            if(!class_exists('nusoap_client')) { // edit @ 02 14
                include_once(plugin_dir_path(__FILE__) . 'lib/nusoap/nusoap.php');
            }
		    
            // 
            date_default_timezone_set('Asia/Tehran');
            ini_set('default_socket_timeout', 160);
            
            // Define Pishtaz method
		    if ( ! class_exists( 'WC_Safircod_Pishtaz_Method' ) ) {
			     class WC_Safircod_Pishtaz_Method extends WC_Shipping_Method 
                 {
                        var $url            = "";
                        var $wsdl_url       = "http://ws.safircod.ir/userservice.asmx?WSDL";
                        var $username       = "";
                        var $password       = "";
                        var $debug          = 0;
                        var $w_unit         = "";
                        var $debug_file     = "";
                        var $client         = null;
				
				        public function __construct() 
                        {
					       $this->id                 = 'safircod_pishtaz'; 
					       $this->method_title       = __( 'پست پیشتاز' ); 
					       $this->method_description = __( 'ارسال توسط پست پیشتاز ' ); // Description shown in admin
 
					       $this->init();
                           $this->account_data();
				        }
 
				        function init() 
                        {
					       $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					       $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
                    
                           $this->enabled		= $this->get_option( 'enabled' );
		                   $this->title 		= $this->get_option( 'title' );
		                   $this->min_amount 	= $this->get_option( 'min_amount', 0 );
                           $this->w_unit 	    = strtolower( get_option('woocommerce_weight_unit') );
                    
					       // Save settings in admin if you have any defined
					       add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                    
				        }
                        
                        function account_data() 
                        {
                            $this->username     = $this->get_option( 'username', '' );
                            $this->password     = $this->get_option( 'password', '' );
                        }
                
                        function init_form_fields() 
                        {
   	                        global $woocommerce;

		                    if ( $this->min_amount )
		                     	$default_requires = 'min_amount';


                         	$this->form_fields = array(
	                     		'enabled' => array(
	                     						'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
						                     	'type' 			=> 'checkbox',
			                     				'label' 		=> __( 'فعال کردن پست پیشتاز', 'woocommerce' ),
			                     				'default' 		=> 'yes'
	                     					),
	                     		'title' => array(
                     	                     						'title' 		=> __( 'Method Title', 'woocommerce' ),
					                     		'type' 			=> 'text',
                     							'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					                     		'default'		=> __( 'پست پیشتاز', 'woocommerce' ),
		                     					'desc_tip'      => true,
	                     					),
	                     		'min_amount' => array(
                     							'title' 		=> __( 'Minimum Order Amount', 'woocommerce' ),
                     							'type' 			=> 'number',
		                     					'custom_attributes' => array(
	                     							'step'	=> 'any',
	                     							'min'	=> '0'
	                     						),
			                     				'description' 	=> __( 'کمترین میزان خرید برای فعال شدن این روش ارسال.', 'woocommerce' ),
				                     			'default' 		=> '0',
				                     			'desc_tip'      => true,
			                     				'placeholder'	=> '0.00'
			                     			),
                                 'username' => array(
	                     						'title' 		=> __( 'نام کاربری سرویس سفیر', 'woocommerce' ),
	                     						'type' 			=> 'text',
	                     						'description' 	=> __( 'نام کاربری شما در سرویس سفیر.', 'woocommerce' ),
	                     						'default'		=> __( '', 'woocommerce' ),
	                     						'desc_tip'      => true,
	                     					),
                                 'password' => array(
	                     						'title' 		=> __( 'رمز استفاده از وب سرویس', 'woocommerce' ),
	                     						'type' 			=> 'password',
	                     						'description' 	=> __( 'رمز عبور برای اتصال به وب سرویس سفیر.', 'woocommerce' ),
	                     						'default'		=> __( '', 'woocommerce' ),
	                     						'desc_tip'      => true,
			                     			),
		                     	);

                         }
    
    
                        public function admin_options() 
                        {
                            ?>
    	                     <h3><?php _e( 'پست پیشتاز' ); ?></h3>
                         	<table class="form-table">
                         	<?php
                         		// Generate the HTML For the settings form.
                         		$this->generate_settings_html();
                         	?>
	                     	</table>
                         	<?php
                       }
    
                      function is_available( $package ) 
                      {
    	                   global $woocommerce;

                           if ( $this->enabled == "no" ) return false;
       
                           if ( ! in_array( get_woocommerce_currency(),  array( 'IRR', 'IRT' )  ) ) return false;
        
                           if( $this->w_unit != 'g' && $this->w_unit != 'kg' )
                               return false;
        
                           if ( $this->username =="" || $this->password=="")
                               return false;
            
		                   // Enabled logic
	                   	   $has_met_min_amount = false;

	                   	   if ( isset( $woocommerce->cart->cart_contents_total ) ) {
	                   	       
			                     if ( $woocommerce->cart->prices_include_tax )
			                         	$total = $woocommerce->cart->cart_contents_total + array_sum( $woocommerce->cart->taxes );
		                      	else
				                        $total = $woocommerce->cart->cart_contents_total;

			                    if ( $total >= $this->min_amount )
				                        $has_met_min_amount = true;
		                   }


		                   if ( $has_met_min_amount ) $is_available = true;
			

		                   return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available );
                      }

		              public function calculate_shipping( $package ) 
                      {
    	                   global $woocommerce;
		                   $customer = $woocommerce->customer;

                           if( empty($package['destination']['city'])) {
                               $rate = array(
			               		'id' 		=> $this->id,
			               		'label' 	=> $this->title,
			               		'cost' 		=> 0
			               	   );
                               $this->add_rate( $rate );
                           }
                          
			               $this->shipping_total = 0;
		              	   $weight = 0;
                           $unit = ($this->w_unit == 'g') ? 1 : 1000;
            
			               $data = array();
			               if (sizeof($woocommerce->cart->get_cart()) > 0 && ($customer->get_shipping_city())) {

				              foreach ($woocommerce->cart->get_cart() as $item_id => $values) {

					              $_product = $values['data'];

					              if ($_product->exists() && $values['quantity'] > 0) {

						              if (!$_product->is_virtual()) {

							              $weight += $_product->get_weight() * $unit * $values['quantity'];
					              	  }
					             }
				              } //end foreach
                              
				              $data['weight']         = $weight;
                              $data['service_type']   = 1;  // pishtaz
				              if ($weight) {
					              $this->get_shipping_response($data, $package);
				              }
			              }
                         
                      }
        
                      function get_shipping_response($data = false, $package) 
                      {
    	                   global $woocommerce;

                           if($this->debug){
                               $this->debug_file = new WC_SafirCOD_Debug();
                           }
            
		               	$rates             = array();
		               	$customer          = $woocommerce->customer;
		               	$update_rates      = false;
		               	$debug_response    = array();

		               	$cart_items        = $woocommerce->cart->get_cart();
		               	foreach ($cart_items as $id => $cart_item) {
		               		$cart_temp[] = $id . $cart_item['quantity'];
		               	}
		               	$cart_hash         = hash('MD5', serialize($cart_temp));
            
                        $service           = $this->safir_service();
                        $total_price       = (get_woocommerce_currency() == "IRT") ? $woocommerce->cart->subtotal * 10 + $service : $woocommerce->cart->subtotal + $service;
            
                        $customer_state    = $package['destination']['state'];
                        $customer_state    = explode('-', $customer_state);
                        $customer_state    = intval($customer_state[0]);
                        if( $customer_state && $customer_state >0){
                            // nothing!
                        }else{
                             if($this->debug){
                                ob_start();
                                var_dump($customer_state);
                                $text = ob_get_contents();
                                ob_end_clean();
                    
                                $this->debug_file->write('@get_shipping_response::state is not valid:'.$text);
                             }
                    
                            return false;
                        }
            
                        $customer_city      = $package['destination']['city'];
                        $customer_city      = explode('-', $customer_city);
                        $customer_city      = intval($customer_city[0]);
                        if( $customer_city && $customer_city >0){
                            // again nothing!
                        }else{
                            if($this->debug){
                                $this->debug_file->write('@get_shipping_response::city is not valid:'.$customer_city);
                            }
                    
                            return false;
                        }
            
                        $shipping_data = array(
			                             'ZoneID'         => $customer_state,
			                             'CityID'         => $customer_city,
			                             'TotalWeight'    => $data['weight'],
                                         'TotalPrice'     => $total_price,
                                         'ServiceType'    => $data['service_type'],
                                         'COD'            => 0, // cod
                                        );

                        $cache_data         = get_transient(get_class($this));

			            if ($cache_data) 
                            if ($cache_data['cart_hash'] == $cart_hash && $cache_data['shipping_data']['CityID'] == $shipping_data['CityID'] && $cache_data['shipping_data']['TotalWeight'] == $shipping_data['TotalWeight'] && $cache_data['shipping_data']['TotalPrice'] == $shipping_data['TotalPrice'] && $cache_data['shipping_data']['ServiceType'] == $shipping_data['ServiceType'])  
					            $rates = $cache_data['rates'];
				            else
					            $update_rates = true;

			            else
				            $update_rates = true;
			            


			             if ($update_rates) {
                            $data = $this->safir_prepare($shipping_data);
				            $result = $this->safir_shipping($data);
                
                            if ($this->debug) {
                                ob_start();
                                var_dump($result);
                                $text = ob_get_contents();
                                ob_end_clean();
					           $this->debug_file->write('@get_shipping_response::everything is Ok:'.$text);
				            }
                
                            $rates = intval($result)*1.06;

				            $cache_data['shipping_data']        = $shipping_data;
				            $cache_data['cart_hash']            = $cart_hash;
				            $cache_data['rates']                = $rates;
			             }
                         
			             set_transient(get_class($this), $cache_data, 60*60*5);

			             $rate       = (get_woocommerce_currency() == "IRT") ? (int)(intval(($rates+$service) / 10)/100)*100+100  : (int)(((int)$rates+$service)/1000)*1000+1000;
			
                         $my_rate = array(
					               'id' 	=> $this->id,
					               'label' => $this->title,
					               'cost' 	=> $rate,
				         );
			             $this->add_rate($my_rate);
                         
                      }
        
                      function safir_prepare($data = false) 
                      {
			              $data['UserName'] = $this->username;
                          $data['Pass']     = $this->password;

			              return $data;
		              }

		              function safir_shipping($data = false, $cache = false) 
                      {
		                  global $woocommerce;
            
                          if ($this->debug) {
                              $this->debug_file->write('@safir_shipping::here is top of function');
                          }
			
                          $this->client                      = new nusoap_client( $this->wsdl_url, true );
                          $this->client->soap_defencoding    = 'UTF-8';
                          $this->client->decode_utf8         = true;
            
                          $response                          = $this->call("GetPostCost", $data);
            
                          if(is_array($response) && $response['error']){
                              if ($this->debug) {
                                    $this->debug_file->write('@safir_service::'.$response['message']);
							        $woocommerce->clear_messages();
							        $woocommerce->add_message('<p>Safir Error:</p> <p>'.$response['message'].'</p>');
				              }
                
                              return 30000;
                          }
            
                          mkobject($response);
                          $cost = intval($response->GetPostCostResult);
            
                          if ($this->debug) {
                              ob_start();
                              var_dump($data);
                              $text = ob_get_contents();
                              ob_end_clean();
                              $this->debug_file->write('@safir_shipping::Everything is Ok:'.$text);
                          }

		              	  return $cost;
                      }
        
                      function safir_service() 
                      {
                        // webservice dont responsible!
                         return 6000; 
                         global $woocommerce;
            
                         $cache_data = get_transient('safir_cod_service_price');
                         if ($cache_data) {
			             	if (time() - (int)$cache_data['date'] < 86400){
				                 if ($this->debug) {
                                     $this->debug_file->write('@safir_service::Everything is Ok --> return from cache');
                                 }
                                 return $cache_data['price'];
			             	}
					
			             }
         
                         $this->client                      = new nusoap_client( $this->wsdl_url, true );
                         $this->client->soap_defencoding    = 'UTF-8';
                         $this->client->decode_utf8         = true;
            
                         $response                          = $this->call("GetServiceCost", array());
            
                         if(is_array($response) && $response['error']){
                             if ($this->debug) {
                                        $this->debug_file->write('@safir_service::'.$response['message']);
		             					$woocommerce->clear_messages();
		             					$woocommerce->add_message('<p>Safir Error:</p> <p>'.$response['message'].'</p>');
			             	}
                
                            return 7000; // estimated
                         }
            
                         $service = intval($response);
            
                         $cache_data['date']        = time();
                         $cache_data['price']       = $service;
            
                         set_transient('safir_cod_service_price', $cache_data, 60*60*24);
            
                         if ($this->debug) {
                             $this->debug_file->write('@safir_service::Everything is Ok');
                         }

			             return $service;
		             }
        
                     public function call($method, $params)
	                 {
                         $result = $this->client->call($method, $params);

		             	if($this->client->fault || ((bool)$this->client->getError()))
		             	{
		             		return array('error' => true, 'fault' => true, 'message' => $this->client->getError());
		             	}

                         return $result;
                     }
        
                     public function handleError($error,$status)
                     {
                         if($status =='sendprice')
                         switch ($error)
                         {
                             case -1:
                                 return 'User name or password is wrong';
                                 break;

                             case -2:
                                 return 'Requested service is wrong';
                                 break;

                             case -3:
                                 return 'resquest is out of normal service';
                                 break;

                             case -4:
                                 return 'weight or amount is invalid';
                                 break;

                              default:
                                 return false;
                                 break;

                         }
                         if($status =='register')
                         switch ($error)
                         {
                             case -1:
                                 return 'User name or password is wrong';
                                 break;
                 
                             case -2:
                                 return 'Requested service is wrong';
                                 break;

                             case -3:
                                 return 'resquest is out of normal service';
                                 break;

                             case -4:
                                 return 'Products list is invalid';
                                 break;
                
                             case -5:
                                 return 'Error in webservice';
                                 break;
           
                              default:
                                 return false;
                                 break;

                         }
    
                     }
            } // end class
        }
        
        if ( ! class_exists( 'WC_Safircod_Sefareshi_Method' ) ) {
			class WC_Safircod_Sefareshi_Method extends WC_Safircod_Pishtaz_Method {
				
                var $username = "";
                var $password = "";
                var $w_unit   = "";
                
				public function __construct() 
                {
				    
					$this->id                 = 'safircod_sefareshi'; 
					$this->method_title       = __( 'پست سفارشی' ); 
					$this->method_description = __( 'ارسال توسط پست سفارشی ' ); // Description shown in admin
 
					$this->init();
                    $this->account_data();
				}
 
				function init() 
                {
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
 
					$this->enabled		= $this->get_option( 'enabled' );
		            $this->title 		= $this->get_option( 'title' );
		            $this->min_amount 	= $this->get_option( 'min_amount', 0 );
                    $this->w_unit      = strtolower( get_option('woocommerce_weight_unit') );
 
					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}
                
                function account_data() {
                    $ins = new WC_Safircod_Pishtaz_Method();
                    $this->username     = $ins->get_option( 'username', '' );
                    $this->password     = $ins->get_option( 'password', '' );
                    
                }
                
                function init_form_fields() 
                {
    	            global $woocommerce;

		              if ( $this->min_amount )
		          	$default_requires = 'min_amount';


    	           $this->form_fields = array(
			                     'enabled' => array(
				                     			'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
				                     			'type' 			=> 'checkbox',
				                     			'label' 		=> __( 'فعال کردن پست سفارشی', 'woocommerce' ),
				                     			'default' 		=> 'yes'
			                     			),
		                     	'title' => array(
                     				                     			'title' 		=> __( 'Method Title', 'woocommerce' ),
					                     		'type' 			=> 'text',
                     							'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                     							'default'		=> __( 'پست سفارشی', 'woocommerce' ),
                     							'desc_tip'      => true,
                     						),
                     			'min_amount' => array(
	                     						'title' 		=> __( 'Minimum Order Amount', 'woocommerce' ),
	                     						'type' 			=> 'number',
	                     						'custom_attributes' => array(
		                     						'step'	=> 'any',
	                     							'min'	=> '0'
		                     					),
		                     					'description' 	=> __( 'کمترین میزان خرید برای فعال شدن این روش ارسال.', 'woocommerce' ),
			                     				'default' 		=> '0',
			                     				'desc_tip'      => true,
			                     				'placeholder'	=> '0.00'
					                     	)
		                     	);

                }
                
                public function admin_options() 
                {
    	           ?>
    	           <h3><?php _e( 'پست سفارشی' ); ?></h3>
    	           <table class="form-table">
    	           <?php
    		          // Generate the HTML For the settings form.
    		          $this->generate_settings_html();
    	           ?>
		          </table>
                <?php
                }
 
                public function calculate_shipping( $package ) 
                {
                           global $woocommerce;
		                   $customer = $woocommerce->customer;

                           if( empty($package['destination']['city'])) {
                               $rate = array(
			               		'id' 		=> $this->id,
			               		'label' 	=> $this->title,
			               		'cost' 		=> 0
			               	   );
                               $this->add_rate( $rate );
                           }
                          
			               $this->shipping_total = 0;
		              	   $weight = 0;
                           $unit = ($this->w_unit == 'g') ? 1 : 1000;
            
			               $data = array();
			               if (sizeof($woocommerce->cart->get_cart()) > 0 && ($customer->get_shipping_city())) {

				              foreach ($woocommerce->cart->get_cart() as $item_id => $values) {

					              $_product = $values['data'];

					              if ($_product->exists() && $values['quantity'] > 0) {

						              if (!$_product->is_virtual()) {

							              $weight += $_product->get_weight() * $unit * $values['quantity'];
					              	  }
					             }
				              } //end foreach
                              
				              $data['weight']         = $weight;
                              $data['service_type']   = 0;  // sefareshi
				              if ($weight) {
					              $this->get_shipping_response($data, $package);
				              }
			              }
                         
                      }
			     } // end class
		}
	} // end function
	add_action( 'woocommerce_shipping_init', 'safircod_shipping_method_init' );
 
	function add_safircod_shipping_method( $methods ) {
		$methods[] = 'WC_Safircod_Pishtaz_Method';
        $methods[] = 'WC_Safircod_Sefareshi_Method';
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'add_safircod_shipping_method' );


class WC_SafirCOD_Debug {
    var $handle = null;
    public function __construct() 
    {

    }
    
    private function open() 
    {
		if ( isset( $this->handle ) )
			return true;

		if ( $this->handle = @fopen( untrailingslashit( plugin_dir_path( __FILE__ ) ).'/log/safir_log.txt', 'a' ) )
			return true;

		return false;
	}
    
    public function write($text) 
    {
        return ;
        if ( $this->open() && is_resource( $this->handle) ) {
			$time = date_i18n( 'm-d-Y @ H:i:s -' ); //Grab Time
			@fwrite( $this->handle, $time . " " . $text . "\n" );
		}
		@fclose($this->handle);
    }
    
    public function sep() 
    {
        $this->write('------------------------------------'."\n");
    }
}     

class WC_SafirCOD {
    var $safir_carrier;
    var $debug_file = "";
    var $email_handle;
    private $client = null;
    
     public function __construct() 
     {
        // edit @ 02 14
        //if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>='))
        //add_action( 'woocommerce_order_status_processing', array( $this, 'save_order'), 10, 1);
        //add_action( 'woocommerce_order_status_on-hold', array( $this, 'save_order'), 10, 1);
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_order'), 10, 2);
        
        add_action('woocommerce_before_checkout_form', array( $this, 'calc_shipping_after_login'));
        add_action( 'woocommerce_cart_collaterals', array( $this, 'remove_shipping_calculator'));
        add_action( 'woocommerce_calculated_shipping', array( $this, 'set_state_and_city_in_cart_page'));
        add_action( 'woocommerce_cart_collaterals', array( $this, 'add_new_calculator'));
        add_action( 'woocommerce_before_cart', array( $this, 'remove_proceed_btn'));
        add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'add_proceed_btn'));
        
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'get_available_payment_gateways'), 10, 1);
        add_filter( 'woocommerce_locate_template', array( $this, 'new_template'), 50, 3); // edit @ 02 14
        add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'remove_free_text'), 10, 2);
        add_filter( 'woocommerce_default_address_fields', array( $this, 'remove_country_field'), 10, 1);
        add_action( 'woocommerce_admin_css', array( $this, 'add_css_file'));
        add_action('admin_enqueue_scripts', array( $this, 'overriade_js_file'), 11);
        
        add_action( 'update_safir_orders_state', array( $this, 'update_safir_orders_state'));
        
        add_filter( 'woocommerce_currencies', array( $this, 'check_currency'), 20 );
        add_filter('woocommerce_currency_symbol', array( $this, 'check_currency_symbol'), 20, 2);
        
        
        if(!class_exists('WC_Safircod_Pishtaz_Method') && function_exists('safircod_shipping_method_init') && class_exists('WC_Shipping_Method'))
            safircod_shipping_method_init();
        
    }
    
    public function get_available_payment_gateways( $_available_gateways)
    {
        global $woocommerce;
        
        $shipping_method = $woocommerce->session->chosen_shipping_method;
        if(in_array( $shipping_method, array('safircod_pishtaz' ,'safircod_sefareshi' ))){   
            foreach ( $_available_gateways as $gateway ) :

			     if ($gateway->id == 'cod') $new_available_gateways[$gateway->id] = $gateway;

		    endforeach;
        
        return $new_available_gateways;
        }
        
        return $_available_gateways;
    }
    
    public function new_template( $template, $template_name, $template_path)
    {
        global $woocommerce;
        
        $shipping_method = $woocommerce->session->chosen_shipping_method;
        /*
        @ edit 02 14
        if(!in_array( $shipping_method, array('safircod_pishtaz' ,'safircod_sefareshi' )))
            return $template;*/
        
        if( $template_name =='checkout/form-billing.php' OR $template_name =='checkout/form-shipping.php')
            return untrailingslashit( plugin_dir_path( __FILE__ ) ). '/'. $template_name;
        
        return $template;
    }
    
    public function save_order($id, $posted)
    {
        global $woocommerce;

        $this->email_handle =  $woocommerce->mailer();
      
        $order = new WC_Order($id);
        if(!is_object($order))
            return;
            
        // edit @ 02 14   
        $is_safir = false; 
        if ( $order->shipping_method ) {
            if( in_array($order->shipping_method, array('safircod_pishtaz' ,'safircod_sefareshi' )) ) {
                $is_safir = true;
                $shipping_methods = $order->shipping_method;
            }
            
		} else {
            $shipping_s = $order->get_shipping_methods();

			foreach ( $shipping_s as $shipping ) {
			    if( in_array($shipping['method_id'], array('safircod_pishtaz' ,'safircod_sefareshi' )) ) {
                    $is_safir = true;
                    $shipping_methods = $shipping['method_id'];
                    break;
                }
			}
        }
        if( !$is_safir || $order->payment_method != 'cod' )
            return;
           
        $this->safir_carrier      = new WC_Safircod_Pishtaz_Method();
        $service_type             = ($shipping_methods == 'safircod_pishtaz') ? 1 : 0;
        if($this->safir_carrier->debug){
           $this->debug_file = new WC_SafirCOD_Debug();
           $this->debug_file->sep();
         }
        
        $unit = ($this->safir_carrier->w_unit == 'g') ? 1 : 1000;
        
        $orders = '';
        foreach ( $order->get_items() as $item ) {

				if ($item['product_id']>0) {
					$_product = $order->get_product_from_item( $item );
                    $productName = str_ireplace('^', '', $_product->get_title()); // edit @ 02 14
                    $productName = str_ireplace(';', '', $productName);
                    $orders .= $productName.'^';
                    $orders .= intval($_product->weight * $unit).'^';
                    $orders .= (int)$item['qty'].'^';
                    $price  = $order->get_item_total( $item); 
                    $orders .= (get_woocommerce_currency() == "IRT") ? (int)$price*10: (int)$price;
                    $orders .= ';';
				}

			}
            
            $customer_city = $order->shipping_city;
            $customer_city = explode('-', $customer_city);
            $customer_city = intval($customer_city[0]);
            if( $customer_city && $customer_city >0){
                
            }else{
                if($this->safir_carrier->debug){
                    $this->debug_file->write('@save_order::city is not valid');
                    die('city is not valid');
                }
                    
                return false;
            }
        
        $params = array(
         'UserName'         =>  $this->safir_carrier->username,
         'Pass'             =>  $this->safir_carrier->password,
         'COD'              =>  0, // cod
         'ServiceType'      =>  $service_type,
         'ShopperIP'        =>  $this->getIp(),
         'ShopperCityID'    =>  $customer_city,
         'ShopperInfo'      =>  $order->billing_first_name.' '.$order->billing_last_name,
         'ShopperPhone'     =>  $order->billing_phone,
         'ShopperAddress'   =>  $order->billing_address_1 . ' - '. $order->billing_address_2,
         'ShopperEmail'     =>  $order->billing_email,
         'ShopperPostCode'  =>  $order->billing_postcode,
         'ShopperDescription'=> $order->customer_note,
         'ProductList'      =>  trim($orders, ';')
         ); 
         
         list($res, $response) = $this->add_order( $params, $order );
        
         if ($res === false) {
                    if ($this->safir_carrier->debug) {
                            ob_start();
                            var_dump($params);
                            $text = ob_get_contents();
                            ob_end_clean();
                            $this->debug_file->write('@save_order::error in registering by webservice:'.$response.'::'.$text);
					}
                    $order->update_status( 'pending', 'Safir : '.$response );
                    $this->trigger($order->id, $order, '::سفارش در سیستم سفیر ثبت نشد::');

         } elseif($res === true) {
            
            if ($this->safir_carrier->debug) {
                            $this->debug_file->write('@save_order::everything is Ok');
							$woocommerce->clear_messages();
							$woocommerce->add_message('<p>Safir:</p> <p>Everthing is Ok!</p>');
			}
            $this->trigger($order->id, $order, true);
            update_post_meta($id, '_safir_tracking_code', $response->RegisterNewOrderResult);
 
         } else {
            $order->update_status( 'pending', 'Safir : error in webservice, Order not register!' );
            $this->trigger($order->id, $order, false);    
         }
        
    }
    
    public function add_order( $data, $order )
    {
        global $woocommerce;
        
        if ($this->safir_carrier->debug) {
			$this->debug_file->write('@add_order::here is top of function');
        }
        
        $this->safir_carrier->client = new nusoap_client( $this->safir_carrier->wsdl_url, true );
        $this->safir_carrier->client->soap_defencoding = 'UTF-8';
        $this->safir_carrier->client->decode_utf8 = true;
            
        $response  = $this->safir_carrier->call("RegisterNewOrder", $data);
            
        if(is_array($response) && $response['error']){
            if ($this->safir_carrier->debug) {
                            $this->debug_file->write('@safir_service::'.$response['message']);
							$woocommerce->clear_messages();
							$woocommerce->add_message('<p>Safir Error:</p> <p>'.$response['message'].'</p>');
				}
                mkobject($response);
                return array(false, $this->safir_carrier->handleError($response->RegisterNewOrderResult,'register'));
        }
            
        mkobject($response);
        if ($this->safir_carrier->debug) {
                ob_start();
                var_dump($response);
                $text = ob_get_contents();
                ob_end_clean();
                
			   $this->debug_file->write('@add_order::everything is Ok: '.$text);
      }

      return array(true, $response);
        
    }
    
    function trigger( $order_id, $order, $subject= false ) 
    {
		global $woocommerce;
        if(!$subject) {
            $message = $this->email_handle->wrap_message(
		            		'سفارش در سیستم سفیر ثبت نشد',
		            		sprintf( 'سفارش  %s در سیستم سفیر ثبت نشد، لطفن بصورت دستی اقدام به ثبت سفارش در پنل شرکت سفیر نمایید.', $order->get_order_number() )
						);

		  $this->email_handle->send( get_option( 'admin_email' ), sprintf('سفارش  %s در سیستم سفیر ثبت نشد', $order->get_order_number() ), $message );
        }else{
            $message = $this->email_handle->wrap_message(
		            		'سفارش با موفقیت در سیستم سفیر ثبت گردید',
		            		sprintf( 'سفارش  %s با موفقیت در سیستم سفیر ثبت گردید.', $order->get_order_number() )
						);

		  $this->email_handle->send( get_option( 'admin_email' ), sprintf( 'سفارش %s در سیستم سفیر با موفقیت ثبت گردید', $order->get_order_number() ), $message );
        }
	}
    
    public function calc_shipping_after_login( $checkout ) 
    {
        global $woocommerce;
        
        $state 		= $woocommerce->customer->get_shipping_state() ;
		$city       = $woocommerce->customer->get_shipping_city() ;
        
        if( $state && $city ) {
            $woocommerce->customer->calculated_shipping( true );
        } else {
  
            wc_add_notice( 'پیش از وارد کردن مشخصات و آدرس، لازم است استان و شهر خود را مشخص کنید.');
            $cart_page_id 	= get_option('woocommerce_cart_page_id' );//wc_get_page_id( 'cart' );
			wp_redirect( get_permalink( $cart_page_id ) );
        }

    }
    
    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
             $ip = $_SERVER['HTTP_CLIENT_IP'];
        } 
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } 
        else 
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public function remove_shipping_calculator()
    {
        if( get_option('woocommerce_enable_shipping_calc')!='no' )
            update_option('woocommerce_enable_shipping_calc', 'no');
    }
    
    public function remove_free_text( $full_label, $method)
    {
        global $woocommerce;
        
        $shipping_city = $woocommerce->customer->city;
        if(!in_array( $method->id, array('safircod_pishtaz' ,'safircod_sefareshi' )))
            return $full_label;

        if(empty($shipping_city))
            return $method->label;
        
        return $full_label;
        
    }
    
    public function remove_country_field( $fields )
    {
        unset( $fields['country'] );
        
        return $fields;
    }
    
    public function add_css_file()
    {
        global $typenow;
        
        if ( $typenow == '' || $typenow == "product" || $typenow == "service" || $typenow == "agent" ) {
             wp_enqueue_style( 'woocommerce_admin_override', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/css/override.css', array('woocommerce_admin_styles') );
        }
    }
    
    public function overriade_js_file()
    {
        global $woocommerce;
        
        wp_deregister_script( 'jquery-tiptip' );
        wp_register_script( 'jquery-tiptip', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/js/jquery.tipTip.min.js', array( 'jquery' ), $woocommerce->version, true );
    }
    
    public function set_state_and_city_in_cart_page()
    {
        global $woocommerce;
        // edit @ 02 14
        $state 		= (woocommerce_clean( $_POST['calc_shipping_state'] )) ? woocommerce_clean( $_POST['calc_shipping_state'] ) : $woocommerce->customer->get_shipping_state() ;
		$city       = (woocommerce_clean( $_POST['calc_shipping_city'] )) ? woocommerce_clean( $_POST['calc_shipping_city'] ) : $woocommerce->customer->get_shipping_city() ;

        if ( $city && $state) {
				$woocommerce->customer->set_location( 'IR', $state, '', $city );
				$woocommerce->customer->set_shipping_location( 'IR', $state, '', $city );
			}else{
                $woocommerce->clear_messages();
                $woocommerce->add_error('استان و شهر را انتخاب کنید. انتخاب هر دو فیلد الزامی است.');
			}
    }
    
    public function add_new_calculator()
    {
        global $woocommerce;
        
        $have_city = true;
        if( ! $woocommerce->customer->get_shipping_city()){
            echo '<style> div.cart_totals{display:none!important;}
                          p.selectcitynotice {display:block;}
                    </style>';
            
            $have_city = false;
        }
    
        include('cart/shipping-calculator.php');
    }
    
    public function remove_proceed_btn()
    {
        echo '<style>input.checkout-button{ display:none!important;}
                    .woocommerce .cart-collaterals .cart_totals table, .woocommerce-page .cart-collaterals .cart_totals table { border:0px; }
              </style>';
    }
    
    public function add_proceed_btn()
    {
        
        echo '<tr style="border:0px;"><td colspan="2" style="padding:15px 0px;border:0px;">
              <input onclick="submitchform();" type="submit" style="padding:10px 15px;" class="button alt" id="temp_proceed" name="temp_proceed" value=" &rarr; اتمام خرید و وارد کردن آدرس و مشخصات" />
              </td></tr>';
    }
    
    public function update_safir_orders_state()
    {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdab->prepare("		SELECT meta.meta_value, posts.ID FROM {$wpdb->posts} AS posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )

		WHERE 	meta.meta_key 		= '_safir_tracking_code'
        AND     meta.meta_value     != ''
		AND 	posts.post_type 	= 'shop_order'
		AND 	posts.post_status 	= 'publish'
		AND 	tax.taxonomy		= 'shop_order_status'
		AND		term.slug			IN ('processing', 'on-hold', 'pending')
	   "));
       
       if ( $results ) {
            $tracks = array();
	        foreach( $results as $result ) {
	           $tracks['code'][] = $result->meta_value;
               $tracks['id'][]   = $result->ID;

		    }
	   }
       
       if( empty($tracks))
            return ;

        if(!is_object($this->safir_carrier))
            $this->safir_carrier      = new WC_Safircod_Pishtaz_Method();
        
        $this->safir_carrier->client = new nusoap_client( $this->safir_carrier->wsdl_url, true );
        $this->safir_carrier->client->soap_defencoding = 'UTF-8';
        $this->safir_carrier->client->decode_utf8 = true;
        
        for($i = 0; $i < 5; $i++)
        {  
            $data = array(
                'UserName'         =>  $this->safir_carrier->username,
                'Pass'             =>  $this->safir_carrier->password,
                'OrderNumber'      =>  $tracks['code'][$i]); 
            $response  = $this->safir_carrier->call("GetOrderState", $data);
            
            if(is_array($response) && $response['error']){
                if ($this->safir_carrier->debug) {
                            $this->debug_file->write('@update_safir_orders_state::'.$response['message']);
				}
                return;
            }
            
            mkobject($response);
            
            if ($this->safir_carrier->debug) {
                ob_start();
                var_dump($response);
                $text = ob_get_contents();
                ob_end_clean();
                
			   $this->debug_file->write('@update_safir_orders_state::everything is Ok: '.$text);
            }
            
            $res  = explode(';', $response->GetOrderStateResult);
            
            $status = false;
            switch($res[1]) {
                /*case '0': // سفارش جدید
                       $status = 'pending';
                       break; */
                case '1': // آماده به ارسال
                case '2': // ارسال شده
                case '3':  //توزیع شده
                       /*$status = 'processing';
                       break; */
                case '4': // وصول شده
                       $status = 'completed';
                       break; 
                case '5': // برگشتی اولیه
                case '6': //برگشتی نهایی
                       $status = 'refunded';
                       break; 
                case '7': // انصرافی
                       $status = 'cancelled';
                       break; 
            }
            if ( $status )
            {
                $order = new WC_Order( $tracks['id'][$i] );
	            $order->update_status( $status, 'سیستم سفیر @ '.$res[0] );
            }
            
            
         }// end for   
            
    }
    
    // thanks to  woocommerce parsi
    public function check_currency( $currencies ) 
    {
        if(empty($currencies['IRR'])) 
            $currencies['IRR'] = __( 'ریال', 'woocommerce' );
        if(empty($currencies['IRT'])) 
            $currencies['IRT'] = __( 'تومان', 'woocommerce' );
        
        return $currencies;
    }
    
    public function check_currency_symbol( $currency_symbol, $currency ) {

        switch( $currency ) {
            case 'IRR': $currency_symbol = 'ریال'; break;
            case 'IRT': $currency_symbol = 'تومان'; break;
        }
        
        return $currency_symbol;
          
    }
}
     
    $GLOBALS['SafirCOD'] = new WC_SafirCOD();

    function mkobject(&$data) 
    {
		$numeric = false;
		foreach ($data as $p => &$d) {
			if (is_array($d))
				mkobject($d);
			if (is_int($p))
				$numeric = true;
		}
		if (!$numeric)
			settype($data, 'object');
	} 
}