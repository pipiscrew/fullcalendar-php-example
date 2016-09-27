<?php
    $is_admin=false;
	@session_start();
	if (!isset($_SESSION["id"])) {
		//header("Location: index.php");
		//exit ;
	}
	else {
		date_default_timezone_set("UTC");
		
		if ($_SESSION["login_expiration"] != date("Y-m-d"))
		{	
		var_dump($is_admin);
			session_destroy();
			header("Location: login.php");
			exit ;
		} else {
             $is_admin=true;
        }
	}
	
	
?>

<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">



<script src="assets/jquery-3.1.0.min.js"></script>
<script src="assets/moment.min.js"></script>
<script src="assets/fullcalendar.min.js"></script> <!--    https://fullcalendar.io/docs/event_ui/Requirements/ -->
<script src="assets/jquery-ui.min.js"></script>
<script src="assets/bootstrap.min.js"></script>
     
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="assets/fullcalendar.min.css">


<script>
<?php if ($is_admin) { ?>
     //hold the user selection from modal
    var modal_event_html_element; //
    var modal_event_date; //

<?php } ?>
    
    $(function() {
    	
<?php if ($is_admin) { ?>
        //inputs effect
        $('.inputer>.input-wrapper>.form-control:disabled').parents('.input-wrapper').addClass('disabled');
        $('.inputer>.input-wrapper>.form-control[readonly]').parents('.input-wrapper').addClass('readonly');
        $('.inputer>.input-wrapper>.form-control').on('focus', function() {
            $('.input-wrapper.active').removeClass('active');
            $(this).parents('.input-wrapper').addClass('active');
        });
        $('.inputer>.input-wrapper>.form-control').on('blur', function() {
            $('.input-wrapper.active').removeClass('active');
        });
        
         //convert each HTML element to droppable
        $('#external-events .fc-event').each(function() {
            var eventObject = {
                title: $.trim($(this).find('span').text())
            };
            
            $(this).data('eventObject', eventObject);
            
            $(this).draggable({
                revert: true,
                revertDuration: 0,
                zIndex: 1999
            });
        });

        ////////////// CONTEXT MENU [START]
        $(document).on("contextmenu", ".fc-event", function(e){
            //http://api.jquery.com/is/
            
            //make it work, only to draggable divs and no to calendar
            if ($(this).parent().is("div") && $(this).data("userid"))
            {
               $("#modaloCONTEXT_title").html($(this)[0].innerText.replace("×","").trim());
               $("#menu_context_userid").val($(this).data("userid"));

               $("#modaloCONTEXT").modal('toggle');
            }
           return false;
            
        });
        
        //when modal closed, reset inputbox
        $('#modaloCONTEXT').on('hidden.bs.modal', function() {
            $("menu_context_userid").val('');
        });
        
        $('#menu_context_btn_update').on('click', function(e) {
            e.preventDefault();
            
            var user_id = $("#menu_context_userid").val();
            
            var s = prompt("Will affect all previous/future events, please enter new name. ", $("#modaloCONTEXT_title").html());
            if (s)
            {
                $.ajax({
                    url : "update_user.php",
                    type: "POST",
                    data : {user_id:user_id,new_name:s},
                    dataType : "json",
                    success:function(data, textStatus, jqXHR)
                    {
                        if (data!=100){ 
                            alert("ERROR");
                        }
                        else 
                        {
                            //enumerate draggable elements, find the one contains HTML5 attribute equal with user_id
                            $(".fc-event").each(function( index ) {
                                if ($(this).data("userid") == user_id){
                                    
                                    //find the span inside div and replace the html content!
                                    $(this).find("span").html(s);
                                    
                                    //break the each enumeration
                                    return false;
                                }
                            });

                            //refetch timebars from source
                            $('#calendar').fullCalendar('refetchEvents');

                            //close modal
                            $("#modaloCONTEXT").modal('toggle');
                        }


                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        alert("ERROR - connection error");
                    }
                });  
                
                
            }
        });
        
        $('#menu_context_btn_delete').on('click', function(e) {
            e.preventDefault();
            
            var user_id = $("#menu_context_userid").val();
            
            if (confirm("Do you want to delete the user\r\n" + $("#modaloCONTEXT_title").html() + " ??")) {
                var del_confirm = prompt("Keep in mind, all the user events will be deleted also!\r\nPlease write the world : delete", "PipisCrew");
                if (del_confirm != null) {
                    if (del_confirm=="delete"){
                        
                        $.ajax({
                            url : "del_user.php",
                            type: "POST",
                            data : {user_id:user_id},
                            dataType : "json",
                            success:function(data, textStatus, jqXHR)
                            {
                                
                                if (data!=100){ 
                                    alert("ERROR");
                                }
                                else 
                                {
                                    //enumerate draggable elements, find the one contains HTML5 attribute equal with user_id
                                    $(".fc-event").each(function( index ) {
                                        if ($(this).data("userid") == user_id){
                                            $(this).remove();
                                            return false;
                                        }
                                    });
                                    
                                    //refetch timebars from source
                                    $('#calendar').fullCalendar('refetchEvents');
                                    
                                    //close modal
                                    $("#modaloCONTEXT").modal('toggle');
                                }


                            },
                            error: function(jqXHR, textStatus, errorThrown)
                            {
                                alert("ERROR - connection error");
                            }
                        });   
                    }
                }
            }
        });
        ////////////// CONTEXT MENU [END]
        
        //modal - event button click
        $(document).on("click", "#btn_dayoff_type", function(e) {
            e.preventDefault();
            
            //take button properties > assign to public vars
            var modal_selected_type = $(this).data("eventid"); //eventtype id-is hardcoded to HTMLelement - needed to store it on the record
            var modal_selected_color = $(this).css("background-color"); //get the color from HTMLelement - needed for calendar draw

            console.log("eventid :", modal_selected_type);
            console.log("color :", modal_selected_color);

            //get HTML5 attribute - is the dbase user_id (var assigned at drop callback)
            var user_id = modal_event_html_element.data("userid");
            

            
            var comment = $("#event_comment").val();
            
            console.log("userid :", user_id);
            console.log("comment :", comment);
            
            store_day(user_id, modal_selected_type, modal_event_date, comment);
        });
        
        //form button
        $('#create-event').submit(function(e) {
            e.preventDefault();

            if ($("#event-title").val().length==0)
            {
                alert("Name cant be zero length.");
                return;
            }
            
            var postData = $(this).serializeArray();
            var formURL = $(this).attr("action");
            
            $.ajax(
            {
                url : formURL,
                type: "POST",
                data : postData,
                dataType : "json",
                success:function(data, textStatus, jqXHR)
                {

                    console.log(data);
                    
                    if (data==100){
                        location.reload(true);
                    }
                    else
                        alert("ERROR");
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("ERROR - connection error");
                }
            });
        });
<?php } ?>
        
         Calendar.init();
        
    }); //jQuery ends


        var Calendar = {
            createCalendar: function() {
                var date = new Date(),
                    m = date.getMonth(),
                    d = date.getDate(),
                    y = date.getFullYear();
                $('#calendar').fullCalendar({
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    events: {
                        url: 'get_events.php',
                        error: function() {
                            alert("ERROR - Reading the dbase.");
                        }
                    },
                    firstDay: 0,
                    isRTL: false,
                    eventLimit: true,
                    weekends : false,
                    eventDurationEditable  : false, //disable resize
<?php if ($is_admin) { ?>
                    editable: true,
                    droppable: true,
                    drop: function(date) {
                        //https://fullcalendar.io/docs1/dropping/drop/ -- function( date, allDay, jsEvent, ui ) { }
                        //the object is
                        console.log(date);

                        if ($('#drop-type').is(':checked')) {

                        } else {
                            $("#modaloSUBSECTOR").modal('toggle');
                        }

                        //store to public variables - use it on modal button-click event + add_to_calendar procedure
                        modal_event_html_element = $(this);
                        //modal_event_html_element.data("userid");
                        
                        //get the dropped date (var assigned at drop callback)
                        var date_dropped = date['_d']; //ex. Mon Sep 05 2016 02:00:00 GMT+0200 (Central Europe Daylight Time)
                        modal_event_date = moment(date_dropped).format('YYYY-MM-DD'); //moment.min.js - 2016-09-05
                    },
                    eventDrop: function(event, delta, revertFunc) {
                        //https://fullcalendar.io/docs/event_ui/eventDrop/
                        console.log(event);
                        
                        var date_dropped = event.start['_d']; 
                        date_dropped = moment(date_dropped).format('YYYY-MM-DD');

                        $.ajax({
                            url : "update_dayoff.php",
                            type: "POST",
                            data : {day_off_id:event.id, eventdate : date_dropped},
                            dataType : "json",
                            success:function(data, textStatus, jqXHR)
                            {

                                console.log(data);

                                if (data!=100){ 
                                    revertFunc(); //everts the event's start/end date to the values before the drag. This is useful if an ajax call should fail.
                                    alert("ERROR");
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown)
                            {
                                revertFunc();
                                alert("ERROR - connection error");
                            }
                        }); 
                        
                    },
                    eventClick: function(calEvent, jsEvent, view) {
                        //delete the event
                        var rec_id = calEvent.id;
                        console.log(rec_id);
                        
                        if (confirm(calEvent.title + "\r\n\r\nDo you want to delete this event ??")) {
                            var del_confirm = prompt("Please write the world : delete", "PipisCrew");
                            if (del_confirm != null) {
                                if (del_confirm=="delete"){
                                    $.ajax({
                                        url : "del_dayoff.php",
                                        type: "POST",
                                        data : {day_off_id:rec_id},
                                        dataType : "json",
                                        success:function(data, textStatus, jqXHR)
                                        {

                                            console.log(data);

                                            if (data!=100){ 
                                                alert("ERROR");
                                            }
                                            else 
                                                //refetch timebars from source
                                                $('#calendar').fullCalendar('refetchEvents');
                                                
                                                
                                        },
                                        error: function(jqXHR, textStatus, errorThrown)
                                        {
                                            alert("ERROR - connection error");
                                        }
                                    });   
                                }
                            }
                        }
                        
//                        alert('Event: ' + calEvent.title);
//                        alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
//                        alert('View: ' + view.name);

                        // change the border color just for fun
                        $(this).css('border-color', 'red');

                    }
<?php } ?>
                });

            },
            handleEventDragging: function(obj) {
                var eventObject = {
                    title: $.trim(obj.find('span').text())
                };
                obj.data('eventObject', eventObject);
                obj.draggable({
                    revert: true,
                    revertDuration: 0,
                    zIndex: 1999
                });
            },
            addEvent: function(title) {
                title = title.length === 0 ? "Missing Event Title" : title;
                var html = $('<div class="fc-event"><span>' + title + '</span> <button type="button" class="close">&times;</button></div>');
                $('.draggable-events').append(html);
                Calendar.handleEventDragging(html);
            },
            init: function() {
                this.createCalendar();
            }
        }
        
        
<?php if ($is_admin) { ?>
        function store_day(userid, typeid, eventdate, comment){
            console.log(userid,typeid,eventdate,comment);
            $.ajax({
                url : "add_dayoff.php",
                type: "POST",
                data : {userid:userid, eventdate : eventdate, typeid: typeid, comment: comment},
                dataType : "json",
                success:function(data, textStatus, jqXHR)
                {

                    console.log(data);

                    if (data==100){

                        //close modal
                        $("#modaloSUBSECTOR").modal('toggle');


                        add_to_calendar();
                    }
                    else
                        alert("ERROR");
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("ERROR - connection error");
                }
            });    
        }


        function update_day(day_off_id,eventdate){
            console.log(day_off_id,eventdate);
            $.ajax({
                url : "update_dayoff.php",
                type: "POST",
                data : {day_off_id:day_off_id, eventdate : eventdate},
                dataType : "json",
                success:function(data, textStatus, jqXHR)
                {

                    console.log(data);

                    if (data!=100){
                        add_to_calendar();
                    }
                    else
                        alert("ERROR");
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("ERROR - connection error");
                }
            });    
        }
     
        function add_to_calendar()
        {
            //remove from event list, when option is checked
            if ($('#drop-remove').is(':checked')) {
                modal_event_html_element.remove();
            }

            //clear global vars 
            modal_event_html_element = "";
            modal_event_date = "";
            $("#event_comment").val('');
            
            //refetch timebars from source
            $('#calendar').fullCalendar('refetchEvents');
        }
    
<?php } ?>
    
</script>
</head>
<body>
    <div class="container-fluid">
    <div class="page-header full-content">
    <div class="row">
    <div class="col-sm-6">
    <h1>Days Off <small>by PipisCrew</small></h1>
    </div> 
    <div class="col-sm-6">
    <?php if (!$is_admin) { ?>
        <ol class="breadcrumb">
            <li><a href="login.php"><i class="tiny-icon icon-plug">&#xf1e6;</i></a></li>
        </ol>
    <?php } else { ?>
        <ol class="breadcrumb">
            <li><a href="logout.php"><i class="tiny-icon icon-eject">&#xe800;</i></a></li>
        </ol>
    <?php } ?>
    </div> 
    </div> 
    </div> 
    <div class="row">
    <div class="col-md-12">
    <div class="panel">
    <div class="panel-heading">
    <div class="panel-title"><h4>CALENDAR</h4></div>
    </div> 
    <div class="panel-body">
    <div class="row">
<?php if ($is_admin) { ?>
    <div class="col-md-3">
    <div id="external-events">
    <div class="legend">Create Event</div>

    <form action="add_user.php" id="create-event">
        <div class="input-group">
            <div class="inputer">
                <div class="input-wrapper">
                    <input id="event-title" name="event-title" type="text" class="form-control">
                </div>
            </div>
            <div class="input-group-btn">
                <button id="add-event" type="submit" class="btn btn-flat btn-default">Add</button>
            </div> 
        </div> 
    </form>

    <div class="legend">Draggable Events</div>
    <div class="draggable-events">
        <?php 
            include 'config.php';

            $db = connect();

            $r = getSet($db,"select * from users order by user_mail", null);

            $elements = "";
            foreach($r as $row) {
                if (strpos($row["user_mail"], '@') == FALSE )
                    echo "<div class=\"fc-event\" data-userid=\"{$row["user_id"]}\"><span>{$row["user_mail"]}</span> <button type=\"button\" class=\"close\">&times;</button></div>\r\n";
            }
        ?>
    </div> 
    <p>
    <input type="checkbox" id="drop-remove" checked/>
    <label for="drop-remove">Remove After Drop</label>
    </p>
    <p>
    <input type="checkbox" id="drop-type"/>
    <label for="drop-type">Add as day off</label>
    </p>
    </div> 
    </div> 
    <div class="col-md-9">
<?php } else {  ?>
    <div class="col-md-12">
<?php }?>
    
    <div id="calendar"></div> 
    </div> 
    </div> 
    </div> 
    </div> 
    </div> 
    </div> 
</div> 
</div> 

<?php if ($is_admin) { ?>
		<div class="modal scale fade" id="modaloSUBSECTOR" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title">Please select type</h4>
					</div>
					<div class="modal-body">
		                <button type="button" id="btn_dayoff_type" data-eventid="1" class="btn btn-purple btn-block btn-ripple">home office</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="2" class="btn btn-blue btn-block btn-ripple">vacation</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="3" class="btn btn-teal btn-block btn-ripple">half-day vacation</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="4" class="btn btn-deep-orange btn-block btn-ripple">left</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="5" class="btn btn-success btn-block btn-ripple">compensate</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="6" class="btn btn-yellow btn-block btn-ripple">sickness/doctor</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="7" class="btn btn-brown btn-block btn-ripple">training</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="8" class="btn btn-blue-grey btn-block btn-ripple">public holiday</button>
		                <button type="button" id="btn_dayoff_type" data-eventid="9" class="btn btn-inverted btn-block btn-ripple">personal leave</button>
		            
		                <div class="input-group">
		                    <div class="inputer">
		                        <div class="input-wrapper">
		                            <input id="event_comment" name="event_comment" type="text" class="form-control">
		                        </div>
		                    </div>
		                </div> 
					</div>
				</div><!--.modal-content-->
			</div><!--.modal-dialog-->
		</div><!--.modal-->
    
    
		<div class="modal scale fade" id="modaloCONTEXT" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
						<h4 id="modaloCONTEXT_title" class="modal-title">Please choose action</h4>
					</div>
					<div class="modal-body">
		                <button type="button" id="menu_context_btn_update" data-eventid="1" class="btn btn-success btn-block btn-ripple">Update</button>
		                <button type="button" id="menu_context_btn_delete" data-eventid="2" class="btn btn-deep-orange btn-block btn-ripple">Delete</button>
                        <input id="menu_context_userid" type="hidden">
					</div>
				</div><!--.modal-content-->
			</div><!--.modal-dialog-->
		</div><!--.modal-->
<?php } ?>    
</body>
</html>
