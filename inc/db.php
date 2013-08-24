<?php

/**
 * SugarDB is a set of helper classes that abstract away the inner workings of different
 * PHP database connections in order to simply query or update MySQL databases.
 */


/**
 * Sets up the main interactions found within each class
 */
interface iSugarDB {
	public function query($sql, $vars); //Queries a database, used for select statements, must return row-based output
	public function update($sql, $vars); //Updates a database, should not return any output
}

/**
* A set of abstractions for working with a DB with a PDO connection
*/
class sugarPDO implements iSugarDB {
	private $handle;

	public function __construct($dbinfo) {
		// try to connect to database
		if (!isset($this->handle)) {
			try {
				// connect to database
				$this->handle = new PDO("mysql:dbname=" . $dbinfo['database'] . ";host=" . 
					$dbinfo['server'], $dbinfo['username'], $dbinfo['password']);

				// ensure that PDO::prepare returns false when passed invalid SQL
				$this->handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
			}
			catch (Exception $e){
				// trigger error
				trigger_error($e->getMessage(), E_USER_ERROR);
				exit;
			}
		}
	}

	public function query($sql, $vars) {
		// prepare SQL statement
		$statement = $this->handle->prepare($sql);
		if ($statement === false){
			// trigger error
			$error = $this->handle->errorInfo();
			trigger_error($error[2], E_USER_ERROR);
			exit;
		}

		// execute SQL statement
		$results = $statement->execute($parameters);

		// return result set's rows, if any
		if ($results !== false){
			return $statement->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return false;
		}
	}

	public function update($sql, $vars) {
		return query($sql,$vars);;
	}
}


/**
* A set of abstractions for working with a DB with a mysqli connection
*/
//Update hasn't been tested yet
class sugarMysqli implements iSugarDB {
	private $conn;
	
	public function __construct($dbinfo) {
		$this->conn = new mysqli($dbinfo['server'], $dbinfo['username'], $dbinfo['password'], $dbinfo['database']);
        if(mysqli_connect_errno()) {
            echo("Database connect Error : ".mysqli_connect_error($this->conn));
        }  
	}
	
	public function query($sql, $vars) {
		$stmt = $this->runPreparedQuery($sql, $vars);
		$res = $stmt->get_result();
		$output = array();
		for ($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
			$res->data_seek($row_no);
			array_push($output,$res->fetch_assoc());
		}
		$stmt->close();
		return $output;
	}
	
	public function update($sql, $vars) {
		$stmt = $this->runPreparedQuery($sql, $vars);
		$res = $stmt->get_result();
		$stmt->close();
	}
	
	//Gets the datatypes of the given parameters, in order to build query string
	private function getTypes($vars) {
		$types = '';
		foreach ($vars as $var) {
			switch(gettype($var)){
				case "integer": $types .= 'i'; break;
				case "double": $types .= 'd'; break;
				case "string": $types .= 's'; break;
				default: 
					throw new Exception('Unrecognized datatype in parameters'); 
					break;
			}
		}
		return $types;
	}
	
	private function runPreparedQuery($sql, $vars) {
		$stmt = $this->conn->prepare($sql);
		$this->bindParameters($stmt, $vars);
		
		if ($stmt->execute()) {
			return $stmt;
		} else {
			echo("Error in statement: ".mysqli_error($this->conn));
			return 0;
		}
	}
	
	//References to arguments is required for PHP 5.3+
	private function refValues($arr){ 
		if (strnatcmp(phpversion(),'5.3') >= 0) { 
			$refs = array(); 
			foreach($arr as $key => $value) 
				$refs[$key] = &$arr[$key]; 
			return $refs; 
		}
		return $arr; 
	} 
	
    private function bindParameters($obj, $vars) {
		$types = $this->getTypes($vars);
		$params = array_merge(array($types),$vars);
        call_user_func_array(array($obj, "bind_param"), $this->refValues($params));
    }
    
    private function bindResult(&$obj, &$bind_result_r) {
        call_user_func_array(array($obj, "bind_result"), $bind_result_r);
    }
}

/**
 * Allows easy access to Access databases via an ODBC connection
 * Note that ODBC does not have any built-in way to sanitize inputs easily,
 * so steps are taken to do this in PHP before executing anything.
 */
class sugarAccessOdbc implements iSugarDB {
	private $conn;
	
	public function __construct($file, $username, $pass) {
		$this->conn = 
			odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$file", $username, $pass);
			set_error_handler(function($errno, $errstr, $errfile, $errline ) {
				throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
			});
	}

	public function query($sql, $vars) {
		$stmt = $this->prepare($sql,$vars);
		$rs = odbc_exec($this->conn, $stmt);
		$cols = odbc_num_fields($rs);
		$output = array();
		while (odbc_fetch_row($rs)){
			$row = array();
			for($i=1; $i<=$cols; $i++) {
				$fname = odbc_field_name($rs,$i);
				$row[$fname] = odbc_result($rs,$i);
			}
			array_push($output,$row);
		}
		return $output;
	}
	
	public function update($sql, $vars) {
		$stmt = $this->prepare($sql,$vars);
		$rs = odbc_exec($this->conn, $stmt);
		if (odbc_error()) {
			return odbc_errormsg($this->conn);
        } else {
			return $rs;
		}
	}

	//odbc_prepare and odbc_execute are not supported by Microsoft Access drivers. Of course.
	//Instead, I'll be building the statement myself, by first sanitizing the inputs
	private function clean($data) {
		$out = array();
		foreach($data as $k=>$v){
			$res = get_magic_quotes_gpc() ? stripslashes($v) : $v;
			$res = strip_tags($res);
			$res = str_replace ("'", "''", $res);
			$out[$k] = $res;
		}
		return $out;
	}
	
	private function prepare($sql, $vars) {
		$params = $this->clean($vars);
		$exp = explode('?',$sql);
		
		$longest = max( count($exp), count($vars) );
		$stmt = '';
		for ($i = 0; $i < $longest; $i++){
			if (isset($exp[$i]))
				$stmt .= $exp[$i];
			if (isset($vars[$i]))
				//Add quotes if string
				if (gettype($vars[$i]) == 'string') {
					$stmt .= "'".$vars[$i]."'";
				} else {
					$stmt .= $vars[$i];
				}
		}
		return $stmt;
	}	
}
?>