<?php
// http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
/*
 * 
CREATE TABLE IF NOT EXISTS `updates` (
  `update_id` int(11) NOT NULL AUTO_INCREMENT,
  `house_id` int(11) NOT NULL,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `submitted` datetime NOT NULL,
  PRIMARY KEY (`update_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

 */
class Update
{
	private $_dbh;
	
	private $_id;
	private $_params = array();
	
	private $_countSelect = 'count(*) as total';
	private $_select = '*';
	
	private $_whereArray = array();
	
	private $_having;
	
	private $_order = 'date_of_sale';
	
	private $_limit = 10;
	private $_offset = 0;
	
	private $_urlArray = array();
	private $_sqlParams = array();
	
	public function __construct()
	{
		global $dbh;
	
		$this->_dbh = $dbh;
	}
	
	private function buildQuery()
	{
		$sql = "INSERT INTO updates (house_id,lat,lng,submitted) VALUE (:id,  :lat, :lng, NOW())";
			
		return $sql;
	}
	
	private function prepareQuery($query)
	{
		$prepared = $this->_dbh->prepare($query);
	
		foreach ($this->_params as $name => $param)
		{
			$prepared->bindValue($name, $param);
		}
	
		return $prepared;
	}
	
	public function hydrate($details)
	{
		$this->_params['id'] = $details['house_id'];
		$this->_params['lat'] = $details['lat'];
		$this->_params['lng'] = $details['lng'];
	}
	
	public function update()
	{
		$selectQuery = $this->prepareQuery($this->buildQuery());
		if ($selectQuery->execute())
		{
			$return['results'] =  $this->_dbh->lastInsertId();
		}
		else
		{
			$return['results'] = "Error";
		}
		
		return $return;
	}
}
