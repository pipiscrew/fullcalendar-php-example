<?php
      $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
      if(!$isAjax) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Python - SyntaxError: invalid syntax, visit pipiscrew.com', true, 500);
        exit;
      }

    if (!isset($_GET["start"]) || !isset($_GET["end"]) || !isset($_GET["_"])) {
        echo json_encode(3);
        exit;
    } 
	

    include 'config.php';

    $db = connect();
	
	$rows = getSet($db, "select * from day_offs left join users on users.user_id=day_offs.user_id where date_occur between ? and ?",array($_GET["start"],$_GET["end"]));
     
	//create an array
	$record = array();
	
	//for each record
	foreach($rows as $row) {
		$datetime = new DateTime($row['date_occur']);
	 
		$event_type = $row['day_off_type'];
		
		//give to calendar bar the proper color
		switch ($event_type) {
			case 1 :
				$color = "#9B26AF";
				break;
			case 2 :
				$color = "#2095F2";
				break;
			case 3 :
				$color = "#009587";
				break;
			case 4 :
				$color = "#FE5621";
				break;
			case 5 :
				$color = "#5CB85C";
				break;
			case 6 :
				$color = "#FEEA3A";
				break;
			case 7 :
				$color = "#785447";
				break;
			case 8 :
				$color = "#5F7C8A";
				break;
			case 9 :
				$color = "#212121";
				break;
			 default:
				$color = "#212121";
			}
		
        //https://fullcalendar.io/docs/agenda/allDaySlot/
        //https://fullcalendar.io/docs/event_data/Event_Object/
		//convert datetime to ISO8601 format FullCalendar compatible
		$record[] = array("id" => $row['day_off_id'],"title" => $row['user_mail'].$row['comment'],"color" => $color, "allDay" => true, "start" => $datetime->format(DateTime::ISO8601));
		//add it to array
	}

	echo json_encode($record);
?>
