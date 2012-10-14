            <hr>

            <footer>
                <p>By <a href="http://www.karlmonaghan.com/">Karl Monaghan</a> &amp; <a href="https://twitter.com/jymian">Mike McHugh</a>&nbsp;|&nbsp;Data provided by <a href="http://propertypriceregister.ie">Residential Property Price Register</a>&nbsp;|&nbsp;<a href="http://www.karlmonaghan.com/contact">Get in touch</a>&nbsp;|&nbsp;<a href="http://www.karlmonaghan.com/2012/10/07/yet-another-searchable-property-price-register/">About</a>&nbsp;|&nbsp;<a href="https://github.com/kmonaghan/Yet-Another-Searchable-Property-Price-Register">Code</a></p>
            </footer>

        </div> <!-- /container -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
		
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.1.min.js"><\/script>')</script>

		<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>

        <script src="js/vendor/bootstrap.min.js"></script>
        <script src="js/vendor/bootstrap-datepicker.js"></script>
        <script src="js/main.js?v=4"></script>

        <script>  
            var _gaq=[['_setAccount','UA-5653857-4'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
<?php
if (isset($results['results']))
{
?>
			results = <?php echo json_encode($results['results']); ?>;
<?php
}
?>

        </script>
    </body>
</html>