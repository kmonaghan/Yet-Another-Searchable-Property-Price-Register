<?php
class Search
{
	private $_dbh;
	
	private $_countSelect = 'count(*) as total';
	private $_select = '*';
	
	private $_whereArray = array();
	
	private $_having;
	
	private $_order = 'date_of_sale DESC';
	
	private $_limit = 10;
	private $_offset = 0;
	
	private $_params = array();
	private $_urlArray = array();
	private $_sqlParams = array();
	
	public function __construct()
	{
		global $dbh;
		
		$this->_dbh = $dbh;
	}
	
	public function getUnorderedURL()
	{
		$url = $this->_urlArray;
		
		array_pop($url);
		array_pop($url);
		
		return '?' . implode('&', $url);
	}
	
	public function getURL()
	{
		return '?' . implode('&', $this->_urlArray);
	}
	
	public function getCount()
	{
		$countQuery = $this->prepareQuery($this->buildQuery($this->_countSelect, ''));

		if ($countQuery->execute())
		{
			$count = $countQuery->fetch(PDO::FETCH_ASSOC); 
		};

		return $count['total'];
	}
	
	public function getPagination($total)
	{
		$pagination['totalPages'] = ceil($total / $this->_limit);
		$pagination['currentPage'] = floor($this->_offset / $this->_limit);
		if (!$pagination['currentPage']) $pagination['currentPage'] = 1;
		
		$pagination['totalPagesToShow'] = ($pagination['totalPages'] < 10) ? $pagination['totalPages'] : 10; 
		$pagination['startPage'] = ($pagination['totalPages'] < 10) ? 1 : (($pagination['currentPage'] < 5) ? 1 : $pagination['currentPage'] - 5);
					
		$pagination['start'] = $this->_offset;
		
		return $pagination;
	}
	
	private function buildQuery($select, $limit, $having = '')
	{
		$whereString = (count($this->_whereArray)) ? ' WHERE ' . implode (' AND ', $this->_whereArray) : '';
		
		$sql = "SELECT {$select} FROM property $whereString {$having} ORDER BY {$this->_order} $limit";

		return $sql;
	}
	
	private function prepareQuery($query)
	{
		$prepared = $this->_dbh->prepare($query);
		$count = 1;
		
		foreach ($this->_params as $param)
		{
			$prepared->bindValue($count, $param);
			
			$count++;
		}
		
		return $prepared;	
	}
	
	public function search()
	{
		$selectQuery = $this->prepareQuery($this->buildQuery($this->_select, 'LIMIT ? OFFSET ?', $this->_having));
		
		$count = count($this->_params);

		$selectQuery->bindValue($count + 1, (int)$this->_limit, PDO::PARAM_INT);
		$selectQuery->bindValue($count + 2, (int)$this->_offset, PDO::PARAM_INT);
		
		$count = $this->getCount();
		
		$return = array('total' => $count);
		
		if ($count)
		{
			if ($selectQuery->execute())
			{
				$return['results'] = $selectQuery->fetchAll(PDO::FETCH_ASSOC);
			};
			
			$return['pagination'] = $this->getPagination($count);
		}
		
		return $return;
	}
	
	public function hydrate($details)
	{
		$this->_limit = (isset($details['limit']) && is_numeric($details['limit']) && $details['limit'] > 0) ? $details['limit'] : 10;
		$this->_offset = (isset($details['page']) && is_numeric($details['page']) && ((int)$details['page'] > 0)) ? $this->_limit * ((int)$details['page'] - 1) : 0;

		if (isset($details['lat']))
		{
			$this->_countSelect .= ', ( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) as distance ';
			$this->_select .= ', ( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) as distance ';
			$this->_params[] = $details['lat'];
			$this->_params[] = $details['lng'];
			$this->_params[] = $details['lat'];
			
			$this->_having = ' having distance < 10';
		}
	
		if (isset($details['address']) && trim($details['address']))
		{
			$this->_params[] = '%' . trim($details['address']) . '%';
			$this->_whereArray[] = ' address LIKE ?';
			
			$this->_urlArray[] = 'address=' . trim($details['address']);
		}
		
		if (isset($details['from_price']) && (is_numeric($details['from_price'])))
		{
			$this->_params[] = $details['from_price'];
			$this->_whereArray[] = ' price >= ?';
			
			$this->_urlArray[] = 'from_price=' . trim($details['from_price']);
		}
		
		if (isset($details['to_price']) && (is_numeric($details['to_price'])))
		{
			$this->_params[] = $details['to_price'];
			$this->_whereArray[] = ' price < ?';
			
			$this->_urlArray[] = 'to_price=' . trim($details['to_price']);
		}
		
		if (isset($details['house_type']) && ($details['house_type'] > 0))
		{
			$this->_whereArray[] = ' description_of_property = ' . (($details['house_type'] == 1) ? '"New Dwelling house /Apartment"' : '"Second-Hand Dwelling house /Apartment"') ;
			
			$this->_urlArray[] = 'house_type=' . trim($details['house_type']);
		}
		
		if (isset($details['postal_code']) && count($details['postal_code']))
		{
			$postalCode = ' postal_code IN (';
			
			foreach ($details['postal_code'] as $code)
			{
				$postalCode .= '?,';
				$this->_params[] = $code;
				
				$this->_urlArray[] = 'postal_code[]=' . $code;
			}
			
			$postalCode = rtrim($postalCode, ',');
			$postalCode .= ')';
			
			$this->_whereArray[] = $postalCode;
		}
		else if (isset($details['county']) && count($details['county']))
		{
			$county = ' county IN (';
			
			foreach ($details['county'] as $code)
			{
				$county .= '?,';
				$this->_params[] = $code;
				$this->_urlArray[] = 'county[]=' . $code;
			}
			
			$county = rtrim($county, ',');
			$county .= ')';	
			
			$this->_whereArray[] = $county;
		}
		
		if (isset($details['full_price']))
		{
			$this->_whereArray[] = ' not_full_market_price = 0';
			
			$this->_urlArray[] = 'full_price=1';
		}
		
		if (isset($details['start_date']))
		{
			$parts = explode('-', $details['start_date']);
			
			$this->_params[] = "$parts[2]-$parts[1]-$parts[0]";
			$this->_whereArray[] = 'date_of_sale >= ?';
			
			$this->_urlArray[] = 'start_date=' . trim($details['start_date']);
		}
		
		if (isset($details['end_date']))
		{
			$parts = explode('-', $details['end_date']);
			
			$this->_params[] = "$parts[2]-$parts[1]-$parts[0]";
			$this->_whereArray[] = 'date_of_sale <= ?';
			
			$this->_urlArray[] = 'end_date=' . trim($details['end_date']);
		}
		
		if (isset($details['order']))
		{
			$this->_order = ($details['order'] == 'price') ? 'price' : 'date_of_sale';
			
			$this->_urlArray[] = 'order=' . $this->_order;
			
			if (isset($details['desc']))
			{
				$this->_order .= ' DESC';
				
				$this->_urlArray[] = 'desc=1';
			}
		}	
		else
		{
			$this->_urlArray[] = 'order=date_of_sale';
			$this->_urlArray[] = 'desc=1';
		}
		
		if (isset($details['id']))
		{
			$this->_params[] = $details['id'];
			$this->_whereArray[] = 'id = ?';
		}
	}
}