<?php
include 'boot.php';

if (count($_GET))
{
//print_r($_GET);
	$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0) ? $_GET['limit'] : 10;
	$offset = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? $limit * ($_GET['page'] - 1) : 0;

	$params = array();

	$whereArray = array();
	
	$urlArray = array();
	
	if (isset($_GET['address']) && trim($_GET['address']))
	{
		$params[] = '%' . trim($_GET['address']) . '%';
		$whereArray[] = ' address LIKE ?';
		
		$urlArray[] = 'address=' . trim($_GET['address']);
	}
	
	if (isset($_GET['from_price']) && (is_numeric($_GET['from_price'])))
	{
		$params[] = $_GET['from_price'];
		$whereArray[] = ' price >= ?';
		
		$urlArray[] = 'from_price=' . trim($_GET['from_price']);
	}
	
	if (isset($_GET['to_price']) && (is_numeric($_GET['to_price'])))
	{
		$params[] = $_GET['to_price'];
		$whereArray[] = ' price < ?';
		
		$urlArray[] = 'to_price=' . trim($_GET['to_price']);
	}
	
	if (isset($_GET['house_type']))
	{
		$whereArray[] = ' description_of_property = ' . (($_GET['house_type'] == 1) ? '"New Dwelling house /Apartment"' : '"Second-Hand Dwelling house /Apartment"') ;
		
		$urlArray[] = 'house_type=' . trim($_GET['house_type']);
	}
	
	if (isset($_GET['postal_code']) && count($_GET['postal_code']))
	{
		$postalCode = ' postal_code IN (';
		
		foreach ($_GET['postal_code'] as $code)
		{
			$postalCode .= '?,';
			$params[] = $code;
			
			$urlArray[] = 'postal_code[]=' . $code;
		}
		
		$postalCode = rtrim($postalCode, ',');
		$postalCode .= ')';
		
		$whereArray[] = $postalCode;
	}
	else if (isset($_GET['county']) && count($_GET['county']))
	{
		$county = ' county IN (';
		
		foreach ($_GET['county'] as $code)
		{
			$county .= '?,';
			$params[] = $code;
			$urlArray[] = 'county[]=' . $code;
		}
		
		$county = rtrim($county, ',');
		$county .= ')';	
		
		$whereArray[] = $county;
	}
	
	if (isset($_GET['full_price']))
	{
		$whereArray[] = ' not_full_market_price = 0';
		
		$urlArray[] = 'full_price=1';
	}
	
	if (isset($_GET['start_date']))
	{
		$parts = explode('-', $_GET['start_date']);
		
		$params[] = "$parts[2]-$parts[1]-$parts[0]";
		$whereArray[] = 'date_of_sale >= ?';
		
		$urlArray[] = 'start_date=' . trim($_GET['start_date']);
	}
	
	if (isset($_GET['end_date']))
	{
		$parts = explode('-', $_GET['end_date']);
		
		$params[] = "$parts[2]-$parts[1]-$parts[0]";
		$whereArray[] = 'date_of_sale <= ?';
		
		$urlArray[] = 'start_date=' . trim($_GET['start_date']);
	}
	
	if(isset($_GET['id']))
	{
		$whereArray = array();
		$params = array();
		$urlArray = array();
		
		$params[] = $_GET['id'];
		$whereArray[] = 'id = ?';
		
		$urlArray[] = 'id=' . trim($_GET['id']);
	}
	
	$url = 'index.php?' . implode('&', $urlArray);
	$whereString = (count($whereArray)) ? ' WHERE ' . implode(' AND ', $whereArray) : '';
	$countQuery = $dbh->prepare("SELECT count(*) as total FROM property $whereString ORDER BY date_of_sale");
	$selectQuery = $dbh->prepare("SELECT * FROM property $whereString ORDER BY date_of_sale DESC LIMIT ? OFFSET ?");

	$count = 1;
	
	foreach ($params as $param)
	{
		$countQuery->bindValue($count, $param);
		$selectQuery->bindValue($count, $param);
		
		$count++;
	}
	
	$selectQuery->bindValue($count, (int)$limit, PDO::PARAM_INT);
	$selectQuery->bindValue($count + 1, (int)$offset, PDO::PARAM_INT);
	
	if ($countQuery->execute())
	{
		$counts = $countQuery->fetch(PDO::FETCH_ASSOC); 
	};
	if ($counts['total'])
	{
		$totalPages = ceil($counts['total'] / $limit);
		$currentPage = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
		
		$totalPagesToShow = ($totalPages < 10) ? $totalPages : 10; 
		$startPage = ($totalPages < 10) ? 1 : (($currentPage < 5) ? 1 : $currentPage - 5);
		
		if ($selectQuery->execute())
		{
			$results = $selectQuery->fetchAll();
		};
	}
}
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title>Yet Another Searchable Property Price Register</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/datepicker.css">
        <link rel="stylesheet" href="css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="css/main.css">

        <!--[if lt IE 9]>
            <script src="js/vendor/html5-3.6-respond-1.1.0.min.js"></script>
        <![endif]-->
    </head>
    <body>
    	<center>
			<script type="text/javascript"><!--
				google_ad_client = "ca-pub-1189639444988756";
				/* karlmonaghan.com leaderboard */
				google_ad_slot = "3806467395";
				google_ad_width = 728;
				google_ad_height = 90;
			//-->
			</script>
				<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
    	</center>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
        <![endif]-->

        <!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->

		<header id="overview" class="jumbotron subhead">
			<div class="container">
				<h1>Yet Another Searchable Property Price Register</h1>
				<p class="lead">A mapped version of the Property Price Register.</p>
			</div>
		</header>
        <div class="container-fluid">        	
			<div class="row-fluid">
				<div class="span6">
					<div id="map_canvas"></div>
				</div>
				<div class="span6">
					<table id="results-table" class="table table-striped">
						<?php
						if (isset($results) && $results)
						{
						?>
						<caption>Showing results <?php echo $offset + 1; ?> to <?php echo $offset + count($results); ?> of <?php echo number_format($counts['total']);?></caption>
						<?php
						}
						?>
						<thead>
							<tr>
								<th>Date Sold</th>
								<th>Address</th>
								<th>Price</th>
								<th>Market Price?</th>
							</tr>
						</thead>
						<tbody>
						<?php
						if (isset($results) && $results)
						{
							foreach ($results as $row)
							{
						?>
							<tr>
								<td><?php echo $row['date_of_sale']; ?></td>
								<td>
									<a href="?id=<?php echo $row['id'];?>">
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
			if (isset($results) && $results)
			{
			?>
    		<div class="pagination pagination-centered">
    			<ul>
    				<li <?php if ($currentPage == 1) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=1'; ?>">First</a></li>
    				<li <?php if ($currentPage == 1) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=' . ($currentPage - 1); ?>">Prev</a></li>
					<?php for ($i = 0; $i < $totalPagesToShow; $i++)
					{
					?>
					<li <?php if (($i + $startPage) == $currentPage) echo 'class="active"'; ?>><a href="<?php echo $url . '&page=' . ($i + $startPage); ?>"><?php echo $i + $startPage; ?></a></li>
					<?php
					}
					?>
					<li <?php if ($currentPage == $totalPages) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=' . ($currentPage + 1); ?>">Next</a></li>
					<li <?php if ($currentPage == $totalPages) echo 'class="disabled"'; ?>><a href="<?php echo $url . '&page=' . ($totalPages); ?>">Last</a></li>
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
	    				</div>
	    			</div>
	    		</form>
   			</div>
            <hr>

            <footer>
                <p>By <a href="http://www.karlmonaghan.com/">Karl Monaghan</a> &amp; <a href="https://twitter.com/jymian">Mike McHugh</a>&nbsp;|&nbsp;Data provided by <a href="http://propertypriceregister.ie">Residential Property Price Register</a>&nbsp;|&nbsp;<a href="http://www.karlmonaghan.com/contact">Get in touch</a></p>
            </footer>

        </div> <!-- /container -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
		
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.1.min.js"><\/script>')</script>

		<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>

        <script src="js/vendor/bootstrap.min.js"></script>
        <script src="js/vendor/bootstrap-datepicker.js"></script>
        <script src="js/main.js?v=2"></script>

        <script>  
            var _gaq=[['_setAccount','UA-5653857-4'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
<?php
if (isset($results) && $results)
{
?>
			results = <?php echo json_encode($results); ?>;
<?php
}
?>

        </script>
    </body>
</html>