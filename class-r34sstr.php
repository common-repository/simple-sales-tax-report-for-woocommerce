<?php

class R34SSTR {

	public $version = '1.1.0';
	
	public $orders_report = null;
	public $refunds_report = null;
	public $year = null;
	public $state = null;
	public $addr_type = null;
	
	public $states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AS' => 'American Samoa',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'AA' => 'Armed Forces Americas',
		'AE' => 'Armed Forces Europe',
		'AP' => 'Armed Forces Pacific',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FM' => 'Federated States of Micronesia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'GU' => 'Guam',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MH' => 'Marshall Islands',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'MP' => 'Northern Mariana Islands',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PW' => 'Palau',
		'PA' => 'Pennsylvania',
		'PR' => 'Puerto Rico',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'VI' => 'U.S. Virgin Islands',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	);


	public function __construct() {

		// Enqueue admin scripts
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
			
		// Set up admin menu
		add_action('admin_menu', array(&$this, 'admin_menu'));
		
		// Process report
		add_action('admin_init', array(&$this, 'admin_process_report'));
			
	}


	public function admin_enqueue_scripts() {
		wp_enqueue_style('r34sstr-admin', plugin_dir_url(__FILE__) . 'assets/admin-style.css', false, $this->version);
	}
	
	
	public function admin_menu() {
		add_submenu_page(
			'woocommerce',
			'Simple Sales Tax Report',
			'Sales Tax Report',
			'edit_others_posts',
			'r34sstr',
			array(&$this, 'admin_page')
		);
	}

	
	public function admin_page() {
		include_once(plugin_dir_path(__FILE__) . 'templates/admin.php');
	}
	
	
	public function admin_process_report() {
		if (wp_verify_nonce($_POST['r34sstr-report-parameters-nonce'],'r34sstr')) {
			$this->year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);
			$this->state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
			$this->addr_type = ($_POST['addr_type'] == 'billing') ? 'billing' : 'shipping';
			
			// Get report data
			if (!empty($this->year)) {
			
				// Get orders
				$orders_params = array(
					'date_paid' => $this->year . '-01-01...' . $this->year . '-12-31',
					'limit' => -1,
					'status' => 'completed',
				);
				if (!empty($this->state)) {
					$orders_params['meta_key'] = '_' . $this->addr_type . '_state';
					$orders_params['meta_value'] = $this->state;
				}
				$orders = wc_get_orders($orders_params);
				
				// Assemble report data
				$orders_report = array();
				
				foreach ((array)$orders as $order) {
					
					// Skip if no tax collected
					if ($order->get_total_tax() == 0) { continue; }
				
					// Get order data
					switch ($this->addr_type) {
						case 'shipping':
							$order_city = !empty($order->get_shipping_city()) ? $order->get_shipping_city() : $order->get_billing_city();
							$order_state = !empty($order->get_shipping_state()) ? $order->get_shipping_state() : $order->get_billing_state();
							$order_zip = !empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : $order->get_billing_postcode();
							break;
						case 'billing':
						default:
							$order_city = $order->get_billing_city();
							$order_state = $order->get_billing_state();
							$order_zip = $order->get_billing_postcode();
							break;
					}
					if (!isset($orders_report[$order_state][$order_zip])) {
						$orders_report[$order_state][$order_zip] = array(
							'city' => null,
							'orders_count' => 0,
							'subtotal' => 0,
							'shipping' => 0,
							'discount' => 0,
							'total' => 0,
							'cart_tax' => 0,
							'shipping_tax' => 0,
							'discount_tax' => 0,
							'total_tax' => 0,
							'total_refunded' => 0,
							'tax_refunded' => 0,
						);
					}
					$orders_report[$order_state][$order_zip]['city'] = $order_city;
					$orders_report[$order_state]['TOTAL']['city'] = null;
					$orders_report[$order_state][$order_zip]['orders_count']++;
					$orders_report[$order_state]['TOTAL']['orders_count']++;
					$orders_report[$order_state][$order_zip]['subtotal'] = $orders_report[$order_state][$order_zip]['subtotal'] + $order->get_subtotal();
					$orders_report[$order_state]['TOTAL']['subtotal'] = $orders_report[$order_state]['TOTAL']['subtotal'] + $order->get_subtotal();
					$orders_report[$order_state][$order_zip]['shipping'] = $orders_report[$order_state][$order_zip]['shipping'] + $order->get_shipping_total();
					$orders_report[$order_state]['TOTAL']['shipping'] = $orders_report[$order_state]['TOTAL']['shipping'] + $order->get_shipping_total();
					$orders_report[$order_state][$order_zip]['discount'] = $orders_report[$order_state][$order_zip]['discount'] + $order->get_discount_total();
					$orders_report[$order_state]['TOTAL']['discount'] = $orders_report[$order_state]['TOTAL']['discount'] + $order->get_discount_total();
					$orders_report[$order_state][$order_zip]['total'] = $orders_report[$order_state][$order_zip]['total'] + $order->get_total();
					$orders_report[$order_state]['TOTAL']['total'] = $orders_report[$order_state]['TOTAL']['total'] + $order->get_total();
					$orders_report[$order_state][$order_zip]['cart_tax'] = $orders_report[$order_state][$order_zip]['cart_tax'] + $order->get_cart_tax();
					$orders_report[$order_state]['TOTAL']['cart_tax'] = $orders_report[$order_state]['TOTAL']['cart_tax'] + $order->get_cart_tax();
					$orders_report[$order_state][$order_zip]['shipping_tax'] = $orders_report[$order_state][$order_zip]['shipping_tax'] + $order->get_shipping_tax();
					$orders_report[$order_state]['TOTAL']['shipping_tax'] = $orders_report[$order_state]['TOTAL']['shipping_tax'] + $order->get_shipping_tax();
					$orders_report[$order_state][$order_zip]['discount_tax'] = $orders_report[$order_state][$order_zip]['discount_tax'] + $order->get_discount_tax();
					$orders_report[$order_state]['TOTAL']['discount_tax'] = $orders_report[$order_state]['TOTAL']['discount_tax'] + $order->get_discount_tax();
					$orders_report[$order_state][$order_zip]['total_tax'] = $orders_report[$order_state][$order_zip]['total_tax'] + $order->get_total_tax();
					$orders_report[$order_state]['TOTAL']['total_tax'] = $orders_report[$order_state]['TOTAL']['total_tax'] + $order->get_total_tax();
					$orders_report[$order_state][$order_zip]['total_refunded'] = $orders_report[$order_state][$order_zip]['total_refunded'] + $order->get_total_refunded();
					$orders_report[$order_state]['TOTAL']['total_refunded'] = $orders_report[$order_state]['TOTAL']['total_refunded'] + $order->get_total_refunded();
					$orders_report[$order_state][$order_zip]['tax_refunded'] = $orders_report[$order_state][$order_zip]['tax_refunded'] + $order->get_total_tax_refunded();
					$orders_report[$order_state]['TOTAL']['tax_refunded'] = $orders_report[$order_state]['TOTAL']['tax_refunded'] + $order->get_total_tax_refunded();
										
				}
				
				if (!empty($orders_report)) {
				
					// Additional calculations
					foreach ((array)$orders_report as $st => $zips) {
						foreach ((array)$zips as $zip => $data) {
							$orders_report[$st][$zip]['average_tax_rate'] = round($data['total_tax'] / ($data['total'] - $data['total_tax']), 6) * 100;
						}
					}
				
					// Sort data
					ksort($orders_report);
					foreach (array_keys((array)$orders_report) as $st) {
						ksort($orders_report[$st]);
					}
					$this->orders_report = $orders_report;
					
					// Download CSV
					if (!empty($_POST['download_csv'])) {
					
						$fh = @fopen('php://output', 'w');
						ob_start();
				
						$csv_header_row = array(
							'ZIP Code',
							'State/Territory',
							'ST Abbr',
							'City',
							'Year',
							'# Orders',
							'Subtotal',
							'Shipping',
							'Discount',
							'Total',
							'Cart Tax',
							'Shipping Tax',
							'Discount Tax',
							'Total Tax',
							'Average Tax Rate',
							'Total Refunded',
							'Tax Refunded',
						);
						
						fputcsv($fh, $csv_header_row);

						foreach ((array)$this->orders_report as $st => $zips) {
							foreach ((array)$zips as $zip => $data) {
								$currency = ($zip == 'TOTAL') ? '$' : '';
								fputcsv($fh, array(
									$zip,
									$this->states[$st],
									$st,
									$data['city'],
									$this->year,
									$data['orders_count'],
									$currency . number_format($data['subtotal'], 2),
									$currency . number_format($data['shipping'], 2),
									$currency . number_format($data['discount'], 2),
									$currency . number_format($data['total'], 2),
									$currency . number_format($data['cart_tax'], 2),
									$currency . number_format($data['shipping_tax'], 2),
									$currency . number_format($data['discount_tax'], 2),
									$currency . number_format($data['total_tax'], 2),
									number_format($data['average_tax_rate'], 4) . '$',
									$currency . number_format($data['total_refunded'], 2),
									$currency . number_format($data['tax_refunded'], 2),
								));
							}
						}
						
						$csv_data = ob_get_clean();
						
						$csv_filename = 'Sales-Tax-Report-' . $this->year . $this->state . '-' . wp_date('YmdHis') . '.csv';
						
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Content-Disposition: attachment; filename=' . $csv_filename);
						header('Content-Type: text/csv');
						header('Expires: 0');
						header('Pragma: public');

						echo $csv_data;
						
						fclose($fh);
						exit;

					}
				
				}
				else {
					?>
					<div class="notice notice-warning"><p>No order data matched your search criteria. Please try again.</p></div>
					<?php
				}
												
			}
			else {
				?>
				<div class="notice notice-error"><p>Please select a year and state.</p></div>
				<?php
			}
			
		}
	}


}
