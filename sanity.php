<?php
include 'boot.php';

/*
 * Boundaries of Ireland box:
 * lat Top: 55.491304
 * lng Left: -10.696106
 * lng Right: -5.396118
 * lat Bottom: 51.310013
 */

$url = 'sanity.php?';

$upperlat = 55.491304;
$lowerlat = 51.310013;
$leftlng = -10.696106;
$rightlng = -5.396118;

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0) ? $_GET['limit'] : 10;
$offset = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? $limit * ($_GET['page'] - 1) : 0;
	
$whereString = 'WHERE (lat > '.$upperlat.' OR lat < '.$lowerlat.') OR (lng > '.$rightlng.' OR lng < '.$leftlng.')';

$countQuery = $dbh->prepare("SELECT count(*) as total FROM property $whereString ORDER BY id");
$selectQuery = $dbh->prepare("SELECT * FROM property $whereString ORDER BY id ASC LIMIT ? OFFSET ?");
$selectQuery->bindValue(1, (int)$limit, PDO::PARAM_INT);
$selectQuery->bindValue(2, (int)$offset, PDO::PARAM_INT);

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

		<link rel="author" href="humans.txt" />

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
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
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
						<caption>Showing results <?php echo $offset + 1; ?> to <?php echo $offset + $limit; ?> of <?php echo number_format($counts['total']);?></caption>
						<?php
						}
						else
						{
						?>
						<caption>No results found: all geolocation data is within acceptable limits</caption>
						<?php
						}
						?>
						<?php
						if (isset($results) && $results)
						{
						?>
						<thead>
							<tr>
								<th>ID</th>
								<th>Address</th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach ($results as $row)
							{
						?>
							<tr>
								<td><a href="house.php?id=<?php echo $row['id'];?>"><?php echo $row['id']; ?></a></td>
								<td>
									<a href="house.php?id=<?php echo $row['id'];?>">
									<?php echo $row['address'] . ', Co. ' . $row['county']; ?><br />
									<?php echo $row['description_of_property']; ?><br />
									<?php echo $row['property_size_description']; ?>
									</a>
								</td>
							</tr>
						<?php
							}
						?>
						</tbody>
						<?php
						}
						?>
						
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