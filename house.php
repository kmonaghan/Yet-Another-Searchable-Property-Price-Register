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
									<a href="?id=<?php echo $row['id'];?>">
									<?php echo $row['address'] . ', Co. ' . $row['county']; ?></a><br />
									<?php echo $row['description_of_property']; ?><br />
									<?php echo $row['property_size_description']; ?>
									<span class="update location" id="ref<?php echo $row['id'];?>">Update location</span>
									<?php /* TODO: add a geo search box here */?>
									<div class="update location hidden">
										<p>Right-click on the map to select the correct location for this house.</p>
										<form name="updateloc">
											<input type="hidden" id="house_id" name="house_id" placeholder="house_id" value="<?php echo $row['id'];?>">
											<input type="text" id="lat" name="lat" placeholder="lat" readonly><br/>
											<input type="text" id="lng" name="lng" placeholder="lat" readonly>
											<button type="submit" class="btn btn-primary">Update</button>
										</form>
									</div>
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
include 'footer.php';