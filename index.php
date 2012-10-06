<?php
include 'boot.php';
include 'search.class.php';

$results = array();

if (count($_GET))
{
	$search = new Search();
	
	$search->hydrate($_GET);
	
	$results = $search->search();
	
	$url = $search->getURL();
	$unordered = $search->getUnorderedURL();
}

include 'header.php';
?>
			<div class="row-fluid">
				<div class="span6">
					<div id="map_canvas"></div>
				</div>
				<div class="span6">
					<table id="results-table" class="table table-striped">
						<?php
						if (isset($results['total']))
						{
						?>
						<caption>Showing results <?php echo number_format($results['pagination']['start'] + 1); ?> to <?php echo number_format($results['pagination']['start'] + count($results['results'])); ?> of <?php echo number_format($results['total']);?></caption>
						<?php
						}
						?>
						<thead>
							<tr>
								<th style="min-width: 94px;"><a href="<?php echo $unordered . '&order=date'; ?>"><i class="icon-arrow-up"></i></a>Date Sold<a href="<?php echo $unordered . '&order=date&desc=1'; ?>"><i class="icon-arrow-down"></i></a></th>
								<th>Address</i></th>
								<th style="min-width: 63px;"><a href="<?php echo $unordered . '&order=price'; ?>"><i class="icon-arrow-up"></i></a>Price<a href="<?php echo $unordered . '&order=price&desc=1'; ?>"><i class="icon-arrow-down"></i></a></th>
								<th>Market Price?</th>
							</tr>
						</thead>
						<tbody>
						<?php
						if (isset($results['total']))
						{
							foreach ($results['results'] as $row)
							{
						?>
							<tr>
								<td><?php echo $row['date_of_sale']; ?></td>
								<td>
									<a href="house.php?id=<?php echo $row['id'];?>">
									<?php echo $row['address'] . ', Co. ' . $row['county']; ?></a><br />
									<?php echo $row['description_of_property']; ?><br />
									<?php echo $row['property_size_description']; ?>
									
								</td>
								<td>&euro;<?php echo number_format($row['price']); ?></td>
								<td><?php echo ($row['not_full_market_price']) ? 'No' : 'Yes'; ?></td>
							</tr>
						<?php
							}
						}
						?>
						</tbody>
					</table>
    			</div>
    		</div>
			<?php
			if (isset($results['total']) && ($results['pagination']['totalPages'] > 1))
			{
			?>
    		<div class="pagination pagination-centered">
    			<ul>
    				<li <?php if ($results['pagination']['currentPage'] == 1) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=1'; ?>">First</a></li>
    				<li <?php if ($results['pagination']['currentPage'] == 1) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=' . ($currentPage - 1); ?>">Prev</a></li>
					<?php for ($i = 0; $i < $results['pagination']['totalPagesToShow']; $i++)
					{
					?>
					<li <?php if (($i + $results['pagination']['startPage']) == $results['pagination']['currentPage']) echo 'class="active"'; ?>><a href="<?php echo $url . '&page=' . ($i + $results['pagination']['startPage']); ?>"><?php echo $i + $results['pagination']['startPage']; ?></a></li>
					<?php
					}
					?>
					<li <?php if ($results['pagination']['currentPage'] == $results['pagination']['totalPages']) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=' . ($results['pagination']['currentPage'] + 1); ?>">Next</a></li>
					<li <?php if ($results['pagination']['currentPage'] == $results['pagination']['totalPages']) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=' . ($results['pagination']['totalPages']); ?>">Last</a></li>
    			</ul>
    		</div>
			<?php
			}
			?>
			
			<div class="form">
				<form class="form-horizontal">
				    <div class="control-group">
	    				<label class="control-label" for="from_price">From price</label>
	    				<div class="controls">
	    					<input type="text" id="from_price" name="from_price" placeholder="From price" <?php if(isset($_GET['from_price'])){?>value="<?php echo $_GET['from_price']; ?>"<?php }?>><span class="help-block">This should be a number like 200000 rather than 200,000 or &euro;200,000.</span>
	    				</div>
	    			</div>
	    			<div class="control-group">
	    				<label class="control-label" for="to_price">To Price</label>
	    				<div class="controls">
	    					<input type="text" id="to_price" name="to_price" placeholder="To price" <?php if(isset($_GET['to_price'])){?>value="<?php echo $_GET['to_price']; ?>"<?php }?>><span class="help-block">This should be a number like 200000 rather than 200,000 or &euro;200,000.</span>
	    				</div>
	    			</div>
	    			<div class="control-group">
	    				<label class="control-label" for="address">Address</label>
	    				<div class="controls">
	    					<input type="text" id="address" name="address" placeholder="address" <?php if(isset($_GET['address'])){?>value="<?php echo $_GET['address']; ?>"<?php }?>><span class="help-block">Note that an exact address may not match and you're better off just trying street or town names.</span>
	    				</div>
	    			</div>
					<div class="control-group">
	    				<label class="control-label" for="postal_code[]">Postal Code</label>
	    				<div class="controls">
	    					<select id="postal_code[]" name="postal_code[]" multiple="multiple">
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 1', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 1">Dublin 1</option>
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 2', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 2">Dublin 2</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 3', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 3">Dublin 3</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 4', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 4">Dublin 4</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 5', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 5">Dublin 5</option>
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 6', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 6">Dublin 6</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 6w', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 6w">Dublin 6w</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 7', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 7">Dublin 7</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 8', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 8">Dublin 8</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 9', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 9">Dublin 9</option>
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 10', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 10">Dublin 10</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 11', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 11">Dublin 11</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 12', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 12">Dublin 12</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 13', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 13">Dublin 13</option>
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 14', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 14">Dublin 14</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 15', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 15">Dublin 15</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 16', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 16">Dublin 16</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 17', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 17">Dublin 17</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 18', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 18">Dublin 18</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 20', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 20">Dublin 20</option>
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 22', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 22">Dublin 22</option> 
								<option <? if (isset($_GET) && isset($_GET['postal_code']) && in_array('Dublin 24', $_GET['postal_code'])) echo 'selected '; ?>value="Dublin 24">Dublin 24</option> 
							</select>
	    				</div>
	    			</div>    			
					<div class="control-group">
	    				<label class="control-label" for="county[]">County</label>
	    				<div class="controls">
	    					<select id="county[]" name="county[]" multiple="multiple">
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Carlow', $_GET['county'])) echo 'selected '; ?>value="Carlow">Carlow</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Cavan', $_GET['county'])) echo 'selected '; ?>value="Cavan">Cavan</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Clare', $_GET['county'])) echo 'selected '; ?>value="Clare">Clare</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Cork', $_GET['county'])) echo 'selected '; ?>value="Cork">Cork</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Donegal', $_GET['county'])) echo 'selected '; ?> value="Donegal">Donegal</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Dublin', $_GET['county'])) echo 'selected '; ?>value="Dublin">Dublin</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Galway', $_GET['county'])) echo 'selected '; ?>value="Galway">Galway</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Kerry', $_GET['county'])) echo 'selected '; ?>value="Kerry">Kerry</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Kildare', $_GET['county'])) echo 'selected '; ?>value="Kildare">Kildare</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Kilkenny', $_GET['county'])) echo 'selected '; ?>value="Kilkenny">Kilkenny</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Laois', $_GET['county'])) echo 'selected '; ?>value="Laois">Laois</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Leitrim', $_GET['county'])) echo 'selected '; ?>value="Leitrim">Leitrim</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Limerick', $_GET['county'])) echo 'selected '; ?>value="Limerick">Limerick</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Longford', $_GET['county'])) echo 'selected '; ?>value="Longford">Longford</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Louth', $_GET['county'])) echo 'selected '; ?>value="Louth">Louth</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Mayo', $_GET['county'])) echo 'selected '; ?>value="Mayo">Mayo</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Meath', $_GET['county'])) echo 'selected '; ?>value="Meath">Meath</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Monaghan', $_GET['county'])) echo 'selected '; ?>value="Monaghan">Monaghan</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Offaly', $_GET['county'])) echo 'selected '; ?>value="Offaly">Offaly</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Roscommon', $_GET['county'])) echo 'selected '; ?>value="Roscommon">Roscommon</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Sligo', $_GET['county'])) echo 'selected '; ?>value="Sligo">Sligo</option>
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Tipperary', $_GET['county'])) echo 'selected '; ?>value="Tipperary">Tipperary</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Waterford', $_GET['county'])) echo 'selected '; ?>value="Waterford">Waterford</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Westmeath', $_GET['county'])) echo 'selected '; ?>value="Westmeath">Westmeath</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Wexford', $_GET['county'])) echo 'selected '; ?>value="Wexford">Wexford</option> 
								<option <? if (isset($_GET) && isset($_GET['county']) && in_array('Wicklow', $_GET['county'])) echo 'selected '; ?>value="Wicklow">Wicklow</option>
							</select>
	    				</div>
	    			</div>
	    			<div class="control-group">
	    				<label class="control-label" for="house_type">Type of property</label>
	    				<div class="controls">
	    					<select id="house_type" name="house_type">
								<option value="0">All properties</option>
								<option <? if (isset($_GET) && isset($_GET['house_type']) && ($_GET['house_type'] == 1)) echo 'selected '; ?>value="1">New dwelling house or apartment</option>
								<option <? if (isset($_GET) && isset($_GET['house_type']) && ($_GET['house_type'] == 2)) echo 'selected '; ?>value="2">Second-hand dwelling house or apartment</option>
							</select>
						</div>
	    			</div>
					<div class="control-group">
	    				<div class="controls">
	    					<label class="checkbox">
								<input type="checkbox" value="1" id="full_price" name="full_price" <? if (isset($_GET) && isset($_GET['full_price'])) echo 'checked '; ?>>
								Only houses that reached full market price
							</label>
						</div>
	    			</div>
	    			<div class="control-group">
	    				<label class="control-label">Sold after</label>
	    				<div class="controls">
	    					<div class="input-append date" id="start_date" data-date="<?php if (isset($_GET) && isset($_GET['start_date'])){ echo $_GET['start_date'];} else {echo '01-01-2010';}; ?>" data-date-format="dd-mm-yyyy">
								<input class="span2" size="16" name="start_date" type="text" value="01-01-2010" readonly>
								<span class="add-on"><i class="icon-calendar"></i></span>
				  			</div>
				  		</div>
	    				<label class="control-label">Sold before</label>
	    				<div class="controls">
	    					<div class="input-append date" id="end_date" data-date="<?php if (isset($_GET) && isset($_GET['end_date'])) {echo $_GET['end_date'];} else {echo date('d-m-Y');}; ?>" data-date-format="dd-mm-yyyy">
								<input class="span2" size="16" type="text" name="end_date" value="<?php if (isset($_GET) && isset($_GET['end_date'])) {echo $_GET['end_date'];} else {echo date('d-m-Y');}; ?>" readonly>
								<span class="add-on"><i class="icon-calendar"></i></span>
				  			</div>
				  		</div>
	    			</div>
	    			
	    			<div class="control-group">
	    				<div class="controls">
							<button type="submit" class="btn btn-primary">Search</button>
							<button type="reset" class="btn">Reset</button>
	    				</div>
	    			</div>
	    		</form>
   			</div>
<?php
include 'footer.php';