<?php
global $R34SSTR;
?>

<div class="wrap r34sstr">

	<h1>Simple Sales Tax Report for WooCommerce</h1>
	
	<div class="metabox-holder">

		<div class="postbox" id="r34sstr_options">
		
			<div class="inside">
			
				<p>This tool will provide a report of total sales tax collected by ZIP code (the most granular data available) for a given year.</p>
		
				<form id="r34sstr-report-parameters" method="post" action="">
					<?php
					wp_nonce_field('r34sstr','r34sstr-report-parameters-nonce');
					?>
					
					<label for="r34sstr_year">
						Year:
						<select name="year" id="r34sstr_year">
							<option value="">Select year...</option>
							<?php
							// WooCommerce was first released in 2011 so we don't go farther back than that
							for ($y = date('Y'); $y > 2011; $y--) {
								?>
								<option value="<?php echo intval($y); ?>"<?php
								if ($y == $R34SSTR->year) { echo ' selected="selected"'; }
								?>><?php echo intval($y); if ($y == date('Y')) { echo ' (YTD)'; }?></option>
								<?php
							}
							?>
						</select>
					</label>
					
					<label for="r34sstr_state">
						State:
						<select name="state" id="r34sstr_state">
							<option value="">ALL states and territories</option>
							<?php
							foreach ((array)$R34SSTR->states as $st_abbr => $st_name) {
								?>
								<option value="<?php echo esc_attr($st_abbr); ?>"<?php
								if ($st_abbr == $R34SSTR->state) { echo ' selected="selected"'; }
								?>><?php echo $st_name . ' (' . $st_abbr . ')'; ?></option>
								<?php
							}
							?>
						</select>
					</label>
					
					<label for="r34sstr_addr_type">
						Use:
						<select name="addr_type" id="r34sstr_addr_type">
							<option value="shipping"<?php if ($R34SSTR->addr_type == 'shipping') { echo ' selected="selected"'; } ?>>shipping address</option>
							<option value="billing"<?php if ($R34SSTR->addr_type == 'billing') { echo ' selected="selected"'; } ?>>billing address</option>
						</select>
					</label>
					
					<input type="submit" class="button button-primary" value="Get Report" />
				</form>
		
			</div>
			
		</div>
		
		<?php
		if (!empty($R34SSTR->orders_report)) {
			?>
			<div class="postbox" id="r34sstr_report">
		
				<h3 class="hndle"><span>Orders Report</span></h3>
	
				<div class="inside">
				
					<form id="r34sstr-report-parameters" method="post" action="">
						<?php
						wp_nonce_field('r34sstr','r34sstr-report-parameters-nonce');
						?>
						<input type="hidden" name="year" value="<?php echo intval($R34SSTR->year); ?>" />
						<input type="hidden" name="state" value="<?php echo intval($R34SSTR->state); ?>" />
						<input type="hidden" name="addr_type" value="<?php echo intval($R34SSTR->addr_type); ?>" />
						<input type="hidden" name="download_csv" value="1" />
						<input type="submit" class="button button-primary" value="Download CSV" />
					</form>
					
					<div class="scrollable">
						<table class="grid">
							<thead>
								<th>ZIP Code</th>
								<th>State/Territory</th>
								<th>ST Abbr</th>
								<th>City</th>
								<th>Year</th>
								<th># Orders</th>
								<th>Subtotal</th>
								<th>Shipping</th>
								<th>Discount</th>
								<th>Total</th>
								<th>Cart Tax</th>
								<th>Shipping Tax</th>
								<th>Discount Tax</th>
								<th>Total Tax</th>
								<th>Average Tax Rate<a href="#note-1-detail"><sup id="note-1">1</sup></a></th>
								<th>Total Refunded</th>
								<th>Tax Refunded</th>
							</thead>
							<tbody>
								<?php
								foreach ((array)$R34SSTR->orders_report as $st => $zips) {
									foreach ((array)$zips as $zip => $data) {
										$tag = ($zip == 'TOTAL') ? 'th' : 'td';
										$currency = ($zip == 'TOTAL') ? '$' : '';
										?>
										<tr>
											<<?php echo $tag; ?>><?php echo $zip; ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?>><?php echo $R34SSTR->states[$st]; ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?>><?php echo $st; ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?>><?php echo $data['city']; ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?>><?php echo $R34SSTR->year; ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?>><?php echo $data['orders_count']; ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['subtotal'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['shipping'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['discount'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['total'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['cart_tax'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['shipping_tax'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['discount_tax'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['total_tax'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo number_format($data['average_tax_rate'], 4); ?>%</<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['total_refunded'], 2); ?></<?php echo $tag; ?>>
											<<?php echo $tag; ?> style="text-align: right;"><?php echo $currency; ?><?php echo number_format($data['tax_refunded'], 2); ?></<?php echo $tag; ?>>
										</tr>
										<?php
									}
								}
								?>
							</tbody>
						</table>
					</div>
					
					<h4>Notes:</h4>
					
					<p id="note-1-detail"><a href="#note-1"><sup>1</sup></a> <strong>Average Tax Rate</strong> = (<em>Total Tax</em> &divide; (<em>Total</em> - <em>Total Tax</em>)) &times; 100, rounded to 4 decimal places. This does <strong>NOT</strong> indicate the correct tax rate for the given ZIP code; it is a calculation based on the actual tax amounts and order totals reported by WooCommerce on the orders placed during the reporting period. Depending on the types of merchandise and the tax laws in a given jurisdiction, different tax rates may apply to individual items within an order.</p>
					
				</div>
		
			</div>
			<?php
		}
		?>

		<div class="postbox" id="r34sstr_support">
	
			<h3 class="hndle"><span>Support</span></h3>

			<div class="inside">
			
			<p>For support please email <a href="mailto:support@room34.com">support@room34.com</a>.</p>
	
			<p><strong>PLEASE NOTE:</strong> This is a free tool, provided AS-IS with no warranty. Room 34 Creative Services, LLC accepts no liability for any inaccurate information. This plugin simply consolidates data stored by WooCommerce and cannot perform any tax calculations.</p>
		
		</div>

	</div>

</div>