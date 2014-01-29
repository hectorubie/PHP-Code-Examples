<?php
	
	class user_authentication{
		
		private $db;
		private $database;

    	function __construct($dbhost,$dbuser,$dbpass,$dbname){
        	$this->dbhost = $dbhost;
        	$this->dbname = $dbname;
        	$this->dbuser = $dbuser;
        	$this->dbpass = $dbpass;
    	}

	 	//Connect to the Database
		private function dbconnect()
		{
			$this->database = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass, true) or die("MySQL Error: " . mysql_error());
			mysql_select_db($this->dbname, $this->database) or die("MySQL Error: " . mysql_error());
			
			if(!$this->database){ die('Could not connect: ' . mysql_error()); }
		}
		
		function dbconnect_close()
		{
			mysql_close($this->database);
		}
		
		//sanitize values
		function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
		{
		  if (PHP_VERSION < 6) {
			$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
		  }
		
		  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
		
		  switch ($theType) {
			case "text":
			  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
			  break;    
			case "long":
			case "int":
			  $theValue = ($theValue != "") ? intval($theValue) : "NULL";
			  break;
			case "double":
			  $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
			  break;
			case "date":
			  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
			  break;
			case "defined":
			  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
			  break;
		  }
		  return $theValue;
		}
		
		
		//login user
		public function authenticate($uname, $upass)
		{
			$username  = $this->GetSQLValueString($uname,"text");
			$hash_pass = $this->GetSQLValueString(md5($upass), "text");
			
			$db = $this->dbconnect();
			
			$sql = mysql_query("SELECT * FROM corpusers WHERE user_screenid=$username AND user_passcode=$hash_pass") or die("Authentication Error: ".mysql_error());
			
			$num_row = mysql_num_rows($sql);
			
			if($num_row == 1)
			{
				$data = mysql_fetch_assoc($sql);
				$_SESSION['logged_in']		  = true;
				$_SESSION['cur_user_id']       = $data['user_id'];
				$_SESSION['cur_user_fname']    = $data['user_fname'];
				$_SESSION['cur_user_lname']    = $data['user_lname'];
				$_SESSION['cur_user_screenid'] = $data['user_screenid'];
				$_SESSION['userAdmin']         = $data['user_corpadmin'];
				unset($_SESSION['login_error_class']);
			}
			else
			{
				$_SESSION['login_error_class'] = 'login-error';
			}
			
		}
		
		
		//logs out users
		public function logout()
		{
			unset($_SESSION['logged_in']);
		    unset($_SESSION['cur_user_id']);
			unset($_SESSION['cur_user_fname']);
		    unset($_SESSION['cur_user_lname']);
		    unset($_SESSION['cur_user_screenid']);
			unset($_SESSION['login_error_class']);
			unset($_SESSION['cur_selected_site']);
			unset($_SESSION['cur_site_url']);
			unset($_SESSION['cur_site_local_url']);
			unset($_SESSION['userAdmin']);
			
			session_destroy();
		}
		
		
		//Gets site list from the DB
		public function getSiteList()
		{
			  $db = $this->dbconnect();
			  $SQL = "SELECT * FROM corpsites";
			  $result = mysql_query($SQL);
			  echo '<ul class="dashboard_icons">';
			  while ($db_field = mysql_fetch_assoc($result)) 
			  {
				      $s_id = $db_field['site_abbreviation'];
					  if($this->return_site_access_val($s_id) == 1)
					  {
						  echo '<li class="'.$db_field['dashboard_class'].' white_bg border">';
						  echo '<a href="includes/core_files/changeSite.php?site_id=' . $db_field['site_abbreviation'] . '&redir=../managesites.php&url=' . $db_field['site_url'] .'&local=' . $db_field['site_local_path'] . '"></a>';
						  echo '</li>';
				  	  }
		      }
			  echo '</ul>';
		}
		
		
		//Gets users site access from DB
		public function getUsersSiteAccess()
		{
			  $db = $this->dbconnect();
			  $SQL = "SELECT * FROM corpsites";
			  $result = mysql_query($SQL);
			  while ($db_field = mysql_fetch_assoc($result)) 
			  {
				      $s_id = $db_field['site_abbreviation'];
					  if($this->return_site_access_val($s_id) == 1)
					  {
						  return 1;
				  	  }
					 
		      }
		}
		
		
		//Gets the site logos
		function getSiteLogo()
		{
			$db = $this->dbconnect();
			$SQL = "SELECT * FROM corpsites";
			$result = mysql_query($SQL);
			while ($db_field = mysql_fetch_assoc($result)) {
				if($db_field['site_abbreviation'] == $_SESSION['cur_selected_site']){
					echo $db_field['site_logo_path'];	
				}
			}
		}
		
		
		//Returns site access value
		public function return_site_access_val($num)
		{
			$db = $this->dbconnect();
			$SQL3 = "SELECT * FROM corpusers";
			$result3 = mysql_query($SQL3);	
			while ($db_field3 = mysql_fetch_assoc($result3)) 
			{
				if($_SESSION['cur_user_id'] == $db_field3['user_id'])
				{
				   return ($db_field3['site_access_' . $num]);
				}
			}
		}
		 
		 
		 //gets site access role value
		 function get_site_access_role_val($num, $id){
			$db = $this->dbconnect();
			$SQL5 = "SELECT * FROM corpusers";
			$result5 = mysql_query($SQL5);	
			while ($db_field5 = mysql_fetch_assoc($result5)) {
				if($id == $db_field5['user_id']){
				  return ($db_field5['site_role_' . $num]);
				}
			};
			
		}
		
		
		//Returns site access role value
		function return_site_access_role_val($num){
			  $db = $this->dbconnect();
			  $SQL3 = "SELECT * FROM corpusers";
			  $result3 = mysql_query($SQL3);	
			  while ($db_field3 = mysql_fetch_assoc($result3)) {
				  if($_SESSION['cur_user_id'] == $db_field3['user_id']){
					return $db_field3['site_role_' . $num];
				  }
			  }
		 }
		
		
		function get_site_access($num, $id){
			 $db = $this->dbconnect();
			  $SQL5 = "SELECT * FROM corpusers";
			  $result5 = mysql_query($SQL5);	
			  while ($db_field5 = mysql_fetch_assoc($result5)) {
				  if($id == $db_field5['user_id']){
					return ($db_field5['site_access_' . $num]);
				  }
			  };
		}
		
		
		//Gets site list Options
		function getSiteListOptions()
		{
			  $db = $this->dbconnect();
			  $SQL = "SELECT * FROM corpsites";
			  $result = mysql_query($SQL);
			  while ($db_field = mysql_fetch_assoc($result)) {
				  $s_id = $db_field['site_abbreviation'];
				  
				  if($db_field['site_abbreviation'] == $_SESSION['cur_selected_site']){
					  echo '<option value="php/changeSite.php?site_id=' . $db_field['site_abbreviation'] . '" selected="selected">' . $db_field['site_title'] . '</option>';
				  }else{
					  
					  if($this->return_site_access_val($s_id) == 1){
						  echo '<option value="php/changeSite.php?site_id=' . $db_field['site_abbreviation'] . '&redir=managecontent.php&url=' . $db_field['site_url'] .'&local=' . $db_field['site_local_path'] . '">' . $db_field['site_title'] . '</option>';
						  
					  }
				  }
			  }
		  }
		
		
		//gets utility list from DB
		public function getUtilityList()
		{
			  $db = $this->dbconnect();
			  $SQL = "SELECT * FROM corputility";
			  $result = mysql_query($SQL);
			  $row = mysql_num_rows($result);
			  
			  echo '<ul class="dashboard_utility_icons">';
			  while ($db_field = mysql_fetch_assoc($result)) {
				  $s_id = $db_field['utility_abbreviation'];
					  if($this->return_site_access_val($s_id) == 1)
					  {
						  switch ($s_id) 
						  {
							  
							  case 'NEWS';
								  echo '<li class="utility_news white_bg border"><a href="manage_articles.php"></a></li>';
							  break;
							  
							  case 'ACCOUNT';
								  echo '<li class="utility_account white_bg border"><a href="manageusers.php"></a></li>';
							  break;
							  
							  case 'PUBLISH';
								  echo '<li class="utility_publish white_bg border"><a href="managepublisher.php"></a></li>';
							  break;
							  
							  case 'CAMPAIGN';
								  echo '<li class="utility_campaign white_bg border"><a href="managepublisher.php"></a></li>';
							  break;
							  
							  case 'CAREERS';
								  echo '<li class="utility_careers white_bg border"><a href="manage_247M_careers.php"></a></li>';
							  break;
							  
							  case 'TEAMS';
								  echo '<li class="utility_teams white_bg border"><a href="manageteams.php?lang=en_us"></a></li>';
							  break;
							  
							  case 'PARTNERS':
								  echo '<li class="utility_partners white_bg border"><a href="managepartners.php?lang=en_us&partner=main_partner"></a></li>';
							  break;
							  
							  case 'SALESDOCS';
								  echo '<li class="utility_salesdocs white_bg border"><a href="managesalesdocs.php"></a></li>';
							  break;
							  
						  }
					  }
			  }
			  echo '</ul>';

		}		
		
		//gets user utility access
		public function getUsersUtilityAccess(){
			$db = $this->dbconnect();
			  $SQL = "SELECT * FROM corputility";
			  $result = mysql_query($SQL);
			  $row = mysql_num_rows($result);
			  
			  while ($db_field = mysql_fetch_assoc($result)) {
				  $s_id = $db_field['utility_abbreviation'];
					  if($this->return_site_access_val($s_id) == 1)
					  {
						  switch ($s_id) 
						  {
							  
							  case 'NEWS':
							  case 'ACCOUNT':
							  case 'PUBLISH':
							  case 'CAMPAIGN':
							  case 'CAREERS':
							  case 'TEAMS':
							  case 'PARTNERS':
							  case 'SALESDOCS':
								  return 1;
							  break;
							  
							  default:
							  	return 0;
							  break;
							  
						  }
					  }
			     }
		
	         }
		
		
		
		
		
		
	}//end of login class


?>