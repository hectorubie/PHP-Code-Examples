<?php
	
	class publish_files{
		
		function partnership(){
			
			$pagelevel = $_GET['pagelevel'];
			$pagelang  = $_GET['pagelang'];
			$sitename  = $_GET['sitename'];
			
			$localSitePath = 'sites/247Media/partnerships/index.html';
			
			$ourFileName = '../../' . $localSitePath;
			$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
			
			$file = file_get_contents('http://{server ip }/corpcms20/sites/247Media/templates/partnerships/index.php?&pagelevel=' . $pagelevel . '&pagelang=' . $pagelang . '&sitename=' . $sitename, true);
		
			//CMS local Path (use for testing locally)
			//$file = file_get_contents('http://localhost/corpcms/sites/247Media/templates/partnerships/index1.php?&pagelevel=' . $pagelevel . '&pagelang=' . $pagelang . '&sitename=' . $sitename, true);
		
			$stringData = $file;
			fwrite($ourFileHandle, $stringData);
			fclose($ourFileHandle);
			
			//LOCAL PATH: define where the file is located on (include file)
			$localPath = "sites/247Media/partnerships/index.html";
			
			//PRODUCTION PATH: dfine where the file will be published (don't include file name)
			$prodPath = "247Media/partnerships/";
			//$localPath .' -------- '.$prodPath;
			
			exec('rsync -vvvvvprotvC /var/www/html/corpcms20/' . $localPath . ' rsync://rsync@{server ip }/www01/' . $prodPath);
		
			exec('rsync -vvvvvprotvC /var/www/html/corpcms20/' . $localPath . ' rsync://rsync@{server ip }/www02/' . $prodPath);
			
			header("location: ".$_GET['redir']."?published");
			exit;
										
		}//end of partnerships
		
		function articles(){
			include ("../includes/db_connect/db_247media_conn.php");
			
			$pageSQL = "SELECT * FROM pages WHERE page_language='$lang' AND page_title='News'";
			$pageQUERY = mysql_query($pageSQL) or die(mysql_error());
			$pageFETCH = mysql_fetch_assoc($pageQUERY);
			
			$url = explode('/',$pageFETCH['page_path']);
			$new_url = ucfirst($url[0]);
			$panel_id = rand(1,1000000);
				
			$update_url = explode('.',$new_url);
			$update_url = $update_url[0];
	
			$pageLang = getSiteLang($siteid, $pageFETCH['page_id']);
			$localSitePath = get_site_local_path();
			
			$pageName = explode(" ", $pageFETCH['page_title']);
			
			$pname = str_replace(' ', '%20', $pageFETCH['page_title']);
			
			$ourFileName = '../' . $localSitePath . '/' . $pageLang . '/' .  $pageFETCH['page_path'];
			$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file 1");
			
			 $file = file_get_contents('http://{server ip }/corpcms/' . $localSitePath . '/templates/' . $pageFETCH['page_template'] . '?&pageid=' . $pageFETCH['page_id'] . '&pagelang=' . $lang . '&pagepath=' . $pageFETCH['page_path'] . '&page_class=' . $update_url . '&pagelevel=' . $pageFETCH['page_level'] . '&page_name=' . $pname , true);
					
			//CMS local Path (use for testing locally)
			//$file = file_get_contents('http://localhost/cms_stage/corpcms/' . $localSitePath . '/templates/' . $pageFETCH['page_template'] . '?&pageid=' . $pageFETCH['page_id'] . '&pagelang=' . $lang . '&pagepath=' . $pageFETCH['page_path'] . '&page_class=' . $update_url . '&pagelevel=' . $pageFETCH['page_level']. '&page_name=' . $pname , true);
	
			$stringData = $file;
			fwrite($ourFileHandle, $stringData);
			fclose($ourFileHandle);
			mysql_query("UPDATE pages SET page_status='3' WHERE page_language='$lang' AND page_title='News'");
			
			$localPagePath = getLocalPath($siteid);
			
			$pagePagePath = getLocalPagePath($siteid, $pageFETCH['page_id']);
			$localPage = $localPagePath.'/'.$pagePagePath;
			$prodPageRoot = substr($localPagePath.'/'.$pagePagePath, 6);
			mysql_close($db_handle);
			
			return array('localpath' => $localPage, 'prodpath' => $prodPageRoot, 'pageid' => $pageFETCH['page_id']);
			
			exec('rsync -vvvvvprotvC /var/www/html/corpcms/' . $$localPage . ' rsync://rsync@{server ip }/www01/' . $prodPageRoot);
			exec('rsync -vvvvvprotvC /var/www/html/corpcms/' . $$localPage . ' rsync://rsync@{server ip }/www02/' . $prodPageRoot);
										
		}//end of partnerships
		
		
		
		
	}

?>