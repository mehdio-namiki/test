<?php

	error_reporting(E_ALL); // Error engine - always TRUE!
	ini_set('ignore_repeated_errors', TRUE); // always TRUE
	ini_set('display_errors', FALSE); // Error display - FALSE only in production environment or real server
	//ini_set('log_errors', TRUE); // Error logging engine
	ini_set('log_errors', 'On');
	ini_set('error_log', '/home/arrondis/public_html/arrondissementgueliz.ma/tvapp/admin/errors.log'); // Logging file path
	ini_set('log_errors_max_len', 1024); // Logging file size
    //echo $_SERVER['SCRIPT_FILENAME'];
	
	
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	function Saverequestdata($rqtabledata)
	{
		$myFile = "../data/requestsdata.json";
		file_put_contents($myFile, $rqtabledata);
		SaveRereshstatus("true");
	}
	
	function Saveresultsdata($rttabledata)
	{
		$myFile = "../data/resultsdata.json";
		file_put_contents($myFile, $rttabledata);
		SaveRereshstatus("true");
	}

	function Savenotificationsdata($notificationsdata)
	{
		$myFile = "../data/notificationsdata.json";
		file_put_contents($myFile, $notificationsdata);
		SaveRereshstatus("true");
	}

	function Savestatisticsdata ($statisticsdata)
	{
		$refreshdataarr = json_decode(file_get_contents('../data/refreshdata.json'), true);
		$statisticsjson =  json_decode($statisticsdata, true);
		//var_dump( $statisticsjson);
		
		foreach ($refreshdataarr as $key => &$value ) 
		{
			
			if ($value['LocationName'] != $statisticsjson[$key]['LocationName'] && isset($value['UserAgent']))
			{	
				$value['LocationName'] = $statisticsjson[$key]['LocationName'];
			}
		}
		
		file_put_contents('../data/refreshdata.json',json_encode($refreshdataarr));

		SaveRereshstatus("true");
	}
	
	function Saveslidersdata($slidesdata)
	{
		$myFile = "../data/slidersdata.json";
		file_put_contents($myFile, $slidesdata);
		SaveRereshstatus("true");
	}

	function IsRefresh($isrefreshfile)
	{
		$refreshdataarr = json_decode(file_get_contents('../data/refreshdata.json'), true);
		
		$randontvid = bin2hex(openssl_random_pseudo_bytes(30));
		
		if(!isset($_COOKIE['tvid'])) {
			setcookie('tvid', $randontvid, time() + (10 * 365 * 24 * 60 * 60) );
			$_COOKIE['tvid'] = $randontvid;
		}
		else 
		{
			$randontvid = $_COOKIE['tvid'];
		}	
		
		foreach ($refreshdataarr as $key => &$value ) 
		{

			if ($value['tvid'] == $randontvid)
			{
				$tvkey = $key;
			}
		}
		
		if ( !isset($tvkey) || trim($tvkey) === '' )
		{
			$newarr = array('tvid' => $randontvid );
			$refreshdataarr[] = $newarr;
			$tvkey = count($refreshdataarr) - 1;
			//$tvkey = array_push($refreshdataarr, array('tvid' => $randontvid ));
		}	
		
		echo $refreshdataarr[$tvkey]['status'];
		
		if ($refreshdataarr[$tvkey]['status'] == 'true')
			$refreshdataarr[$tvkey]['LastRefreshed'] = date("M,d,Y h:i:s A");
		
		$locationname = $refreshdataarr[$tvkey]['LocationName'];
		//var_dump($locationname);
		//echo "\n<br>";
		//var_dump($_COOKIE['LocationName']);
		//echo "\n<br>";
		
		if(isset($_COOKIE['LocationName']) && !isset($locationname) )
		{	
			$refreshdataarr[$tvkey]['LocationName'] = $_COOKIE['LocationName'];
			//echo "it's set 0";
		}
		else if (!isset($_COOKIE['LocationName']) && isset($locationname) &&  !empty($locationname)
					&& $_COOKIE['LocationName'] != $locationname )
		{
			setcookie('LocationName', $refreshdataarr[$tvkey]['LocationName'], time() + (10 * 365 * 24 * 60 * 60), dirname($_SERVER['REQUEST_URI']) );
			//echo "it's set 1";
		}
		else if ( isset($_COOKIE['LocationName']) && isset($locationname) && !empty($locationname)
					&& $_COOKIE['LocationName'] != $locationname )
		{
			setcookie('LocationName', $refreshdataarr[$tvkey]['LocationName'], time() + (10 * 365 * 24 * 60 * 60), dirname($_SERVER['REQUEST_URI']) );
			//echo "it's set 2";
		}
			
		$refreshdataarr[$tvkey]['status'] = 'false';
		$refreshdataarr[$tvkey]['LastChecked'] = date("M,d,Y h:i:s A");
		$refreshdataarr[$tvkey]['UserAgent'] = $_SERVER['HTTP_USER_AGENT'];
		
		file_put_contents('../data/refreshdata.json',json_encode($refreshdataarr)); 
	}

	function Uploadimages($imagefile)
	{
		/* Getting file name */
		$filename = $imagefile['name'];

		/* Location */
		$location = "../assets/images/banner/".$filename;
		$uploadOk = 1;
		$imageFileType = pathinfo($location,PATHINFO_EXTENSION);

		/* Valid Extensions */
		$valid_extensions = array("jpg","jpeg","png");
		/* Check file extension */
		if( !in_array(strtolower($imageFileType),$valid_extensions) ) {
		   $uploadOk = 0;
		}

		if($uploadOk == 0){
		   echo 0;
		}else{
		   /* Upload file */
		   if(move_uploaded_file( $imagefile['tmp_name'],$location)){
			  echo $location;
		   }else{
			  echo 0;
		   }
		}
		SaveRereshstatus("true");
	}
	
	function SaveRereshstatus($status)
	{
		$refreshdataarr = json_decode(file_get_contents('../data/refreshdata.json'), true);
		
		foreach ($refreshdataarr as $key => &$value ) 
		{
			$refreshdataarr[$key]['status'] = $status;
		}
		
		file_put_contents('../data/refreshdata.json',json_encode($refreshdataarr));		
	}
	
    if(isset($_GET['Saverequestdata']) && isset($_POST['rqtabledata']))
	{
        die(Saverequestdata($_POST['rqtabledata']));
    }

    if(isset($_GET['Saveresultsdata']) && isset($_POST['rttabledata']))
	{
        die(Saveresultsdata($_POST['rttabledata']));
    }

    if(isset($_GET['Savestatisticsdata']) && isset($_POST['statisticsdata']))
	{
        die(Savestatisticsdata($_POST['statisticsdata']));
    }

    if(isset($_GET['Savenotificationsdata']) && isset($_POST['notificationsdata']))
	{
        die(Savenotificationsdata($_POST['notificationsdata']));
    }
	
    if(isset($_GET['Removestatisticsdata']) )
	{
        unlink('../data/refreshdata.json');
    }	
	
    if(isset($_GET['Saveslidersdata']) && isset($_POST['slidesdata']))
	{
        die(Saveslidersdata($_POST['slidesdata']));
    }	

    if(isset($_GET['IsRefresh']))
	{
        die(IsRefresh($isrefreshfile));
    }	
	
    if(isset($_GET['Uploadimages']) && isset($_FILES['file']))
	{
        die(Uploadimages($_FILES['file']));
    }	
	
?>