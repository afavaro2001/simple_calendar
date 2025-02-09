<?php require_once( '../config.php' );
    global $calendar, $user;
    $user->checkUserLogged( );
    if( ! empty( $_POST['ajax_request'] ) ){
        $calendar->routeAjaxRequest( );
    }    
    if( ! $user->roleCan( 'mng_calendar' ) ){
        echo "You are not allowed to view this page.";
        exit;
    }
    $categories = $calendar->getCategories( );
    $default_category = 1;
    if( ! empty( $_GET['category_id' ] ) && is_numeric( $_GET['category_id' ] ) ){
      $default_category = $_GET['category_id' ];
    }
    $categories = $calendar->getCategories( );
    $default_color = $categories[ $default_category ][ 'category_color' ];
    $week_starters = $calendar->getWeekStarts( );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendars Administration</title>
    <?php require_once( '../templates/header.php' ); ?>
     <script src="../assets/js/fullcalendar.6.1.9/index.global.min.js"></script>
     <script src="../assets/js/moment.2.29.4/moment.js"></script>
     <style>
        .fc .fc-button{
          padding: 0 0.5em;
        }
        .fc .fc-toolbar.fc-header-toolbar{
          margin-bottom: 0;
        }
        .fc .fc-toolbar-title{
          font-size: 1.5em;
        }
        .fc-event-main{
          overflow: hidden;
        }
        .customEvent .fc-event-main{ 
          border-top: 2px solid gray;
          font-size: 12px;
          padding-left: 3px;
        }
      </style>
</head>
<body>
    <?php require_once( 'templates/navigation.php' ); ?>
    <div class="container admin-page calendar"> 
        <?php require_once( 'templates/menu-calendars.php' ); ?>
        <div id="liveAlertPlaceholder"></div>
        <div class="row">
          <div class="col-3"><h4>CALENDARS</h4></div>
          <div class="col-3 text-end"><button class="btn btn-danger btn-sm" id="clone-week-events" data-bs-toggle="modal" data-bs-target="#clone-week-modal" >Clone Week</button>&nbsp;<button class="btn btn-success btn-sm" id="reload-homepage-tabs">Reload Public Dashboard</button></div>
          <div class="col-2 text-end">Category:</div>
          <div class="col-4 text-end">
            <select class="form-select form-select-sm" id="category-id" name="category-id">
            <?php foreach( $categories AS $row ){ ?>
              <option value="<?php echo $row['category_id'] ?>" <?php if( $row['category_id'] == $default_category ) { echo "SELECTED"; } ?>><?php echo $row['category_name'] ?></option>
            <?php } ?>
            </select>
          </div>
        </div> 
        <div id='calendar'></div> 
    </div> 
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="eventModalLabel">Event</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="event-form" >
                      <div class="mb-3">
                          <label for="event-title" class="col-form-label">Event title:</label>
                          <input type="text" class="form-control form-control-sm" id="event-title" name="event-title">
                      </div>
                      <div class="row gx-2 mb-3">
                        <div class="col-2">Start:</div>
                        <div class="col-5">
                                <input type="date" class="form-control form-control-sm" id="event-start" name="event-start">
                        </div>
                        <div class="col-2">
                                <select class="form-select form-select-sm" id="event-start-hour" name="event-start-hour">
                                  <?php                                     
                                    for( $i = 0; $i < 24; $i++ ){
                                      $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                      echo "<option>{$hour}</option>";
                                    }
                                  ?>
                                </select>
                        </div>
                        <div class="col-md-auto">:</div>                      
                        <div class="col-2">
                              <select class="form-select form-select-sm" id="event-start-minute" name="event-start-minute">
                                <?php                                     
                                  for( $i = 0; $i < 60; $i++ ){
                                    $min = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option>{$min}</option>";
                                  }
                                ?>
                              </select>
                        </div>
                      </div>
                      <div class="row gx-2 mb-3">
                        <div class="col-2">End:</div>
                        <div class="col-5">
                              <input type="date" class="form-control form-control-sm" id="event-end" name="event-end">
                        </div>
                        <div class="col-2 m-0">
                              <select class="form-select form-select-sm" id="event-end-hour" name="event-end-hour">
                                <?php                                     
                                  for( $i = 0; $i < 24; $i++ ){
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option>{$hour}</option>";
                                  }
                                ?>
                              </select>
                        </div>   
                        <div class="col-md-auto">:</div>                       
                        <div class="col-2">
                              <select class="form-select form-select-sm" id="event-end-minute" name="event-end-minute">
                                <?php                                     
                                  for( $i = 0; $i < 60; $i++ ){
                                    $min = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option>{$min}</option>";
                                  }
                                ?>
                              </select>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="event-description" class="form-label">Description</label>
                        <textarea class="form-control form-control-sm" id="event-description" rows="3" name="event-description"></textarea>
                      </div>
                      <div class="mb-3">
                          <label for="message-text" class="col-form-label">Category:</label>
                          <select class="form-select form-select-sm" id="event-category-id" name="event-category-id">
                          <?php foreach( $categories AS $row ){ ?>
                            <option value="<?php echo $row['category_id'] ?>"><?php echo $row['category_name'] ?></option>
                          <?php } ?>
                          </select>
                      </div>
                      <div class="mb-3" id="event-days-group" >
                        <div class="alert alert-primary d-flex align-items-center p-2 d-none" role="alert">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                          </svg>
                          <div><small>Multi-days feature will add new events to the calendar.</small></div>
                        </div>
                        <?php 
                          $weekDays = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];
                          foreach ($weekDays AS $i => $value ) {
                        ?>
                          <div class="form-check form-check-inline m-1 small">
                            <input class="form-check-input" type="checkbox" id="wekDay<?php echo $i ?>" value="<?php echo $i ?>" name="days[]">
                            <label class="form-check-label" for="wekDay<?php echo $i ?>"><?php echo $value ?></label>
                          </div>
                        <?php } ?>
                      </div>
                      <input type="hidden" name="event-id" id="event-id" >
                    </form>
                </div>
                <div class="modal-footer">                    
                    <button type="button" class="btn btn-danger" id="event-delete" >Delete</button>                 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-submit-event" id="event-submit"></button>
                </div>
            </div>
        </div>
    </div>   <!-- End of the 'eventModal' -->
    <!-- Clone Week modal -->
    <div class="modal fade" id="clone-week-modal" tabindex="-1"  aria-labelledby="clone-week-modalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Clone Week Events</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning d-none" role="alert">
              All the existing events of the receiving week will be removed.
            </div>
            <div class="alert alert-info d-none" role="alert"></div>
            <select class="form-select form-select-sm" id="clone-week-to" >
              <option value="" selected>Select week starting on:</option>
              <?php if( !empty( $week_starters ) ) {
                      foreach( $week_starters AS $timestamp ){
              ?>
                <option value="<?php echo $timestamp ?>"><?php echo date( 'D M jS', $timestamp ) ?></option>
              <?php } } ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="clone-week-submit" class="btn btn-primary">Clone Week</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End of the Clone Week modal -->
</body>
<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      themeSystem: 'bootstrap5',
      initialDate: '<?php echo date( "Y-m-d" ) ?>',
      initialView: 'timeGridWeek',
      nowIndicator: true,
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      navLinks: true, // can click day/week names to navigate views
      editable: true,
      selectable: true,
      selectMirror: true,
      allDaySlot: false,
      dayMaxEvents: true, // allow "more" link when too many events
      eventTimeFormat: {
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: false,
        meridiem: 'short'
      },
      eventContent: function( info ) {
        var dateText = moment( info.event.start ).format( 'h:mma') + ' - ';
        dateText +=  moment( info.event.end ).format( 'h:mma') ;
        var infoDiv = document.createElement('div');
        infoDiv.innerHTML = '<b>' + info.event.title + "</b><br />" + dateText ;
        return { domNodes: [ infoDiv ] };
      },
      select: function ( eventInfo ){
        var start_date = moment( eventInfo.start ).format('YYYY-MM-DD');
        var start_hour = moment( eventInfo.start ).format('HH');
        var start_minute = moment( eventInfo.start ).format('mm');
        var end_date = moment( eventInfo.end ).format('YYYY-MM-DD');
        var end_hour = moment( eventInfo.end ).format('HH');
        var end_minute = moment( eventInfo.end ).format('mm');
        $("#event-delete").addClass( 'd-none' );
        $('#eventModal #event-title').val( '' );
        $('#eventModal #event-start').val( start_date );
        $('#eventModal #event-start-hour').val( start_hour );
        $('#eventModal #event-start-minute').val( start_minute );
        $('#eventModal #event-end').val( end_date );
        $('#eventModal #event-end-hour').val( end_hour );
        $('#eventModal #event-end-minute').val( end_minute );
        $('#eventModal #event-description').text( '' );
        $('#eventModal #event-id').val( '' );
        $('#eventModal #event-category-id').val( $('#category-id'). val() );
        $('#eventModal .btn-submit-event').text( 'Add Event' );
        $('#eventModal').modal( 'show' );
      },
      eventClick: function( eventInfo ){
        var start_date = moment( eventInfo.event.start ).format('YYYY-MM-DD');
        var start_hour = moment( eventInfo.event.start ).format('HH');
        var start_minute = moment( eventInfo.event.start ).format('mm');
        var end_date = moment( eventInfo.event.end ).format('YYYY-MM-DD');
        var end_hour = moment( eventInfo.event.end ).format('HH');
        var end_minute = moment( eventInfo.event.end ).format('mm');
        $("#event-delete").removeClass( 'd-none' );
        $('#eventModal #event-title').val( eventInfo.event.title );
        $('#eventModal #event-start').val( start_date );
        $('#eventModal #event-start-hour').val( start_hour );
        $('#eventModal #event-start-minute').val( start_minute );
        $('#eventModal #event-end').val( end_date );
        $('#eventModal #event-end-hour').val( end_hour );
        $('#eventModal #event-end-minute').val( end_minute );
        $('#eventModal #event-description').text( eventInfo.event.extendedProps.description );
        $('#eventModal #event-category-id').val( eventInfo.event.extendedProps.category_id );
        $('#eventModal #event-id').val( eventInfo.event.extendedProps.event_id );
        $('#eventModal #event-days-group .alert').removeClass( 'd-none' );
        $('#eventModal .btn-submit-event').text( 'Update Event' );
        $('#eventModal').modal( 'show' );        
      },
      eventResize: function( eventInfo ){
        var start_date = moment( eventInfo.event.start ).format('YYYY-MM-DD');
        var start_time = moment( eventInfo.event.start ).format('HH:mm');
        var end_date = moment( eventInfo.event.end ).format('YYYY-MM-DD');
        var end_time = moment( eventInfo.event.end ).format('HH:mm');
        var event_id = eventInfo.event.extendedProps.event_id;
        $.post( 'calendar.php', {
            ajax_request: 1,
            resize_event: 1,
            event_start: start_date + ' ' + start_time,
            event_end: end_date + ' ' + end_time,
            event_id: event_id
        }, function( data ){
          if( Object.keys(data).length ){
            if( data.response != "success" ){
              calendar.refetchEvents( );
              alert('There was an error resizing the event!');;
            }
          }
        }, 'json' );
      },
      eventDrop: function( eventInfo ){
        var start_date = moment( eventInfo.event.start ).format('YYYY-MM-DD');
        var start_time = moment( eventInfo.event.start ).format('HH:mm');
        var end_date = moment( eventInfo.event.end ).format('YYYY-MM-DD');
        var end_time = moment( eventInfo.event.end ).format('HH:mm');
        var event_id = eventInfo.event.extendedProps.event_id;
        $.post( 'calendar.php', {
            ajax_request: 1,
            resize_event: 1,
            event_start: start_date + ' ' + start_time,
            event_end: end_date + ' ' + end_time,
            event_id: event_id
        }, function( data ){
          if( Object.keys(data).length ){
            if( data.response != "success" ){
              calendar.refetchEvents( );
              alert('There was an error moving the event!');;
            }
          }
        }, 'json' );     
      },
      events: {
          url: 'calendar.php',
          method: 'POST',
          extraParams: {
            ajax_request: 1,
            get_events: 1,
            category_id: $( '#category-id' ).val( )
          },
          failure: function() {
            alert('there was an error while fetching events!');
          },
          color: '<?php echo $default_color ?>',   // a non-ajax option
          textColor: '#000', // a non-ajax option
          className: 'customEvent'
      },
      datesSet: function( dateInfo ){
        if ( dateInfo.view.type == 'timeGridWeek' ){
          $( '#clone-week-events' ).show( );
        } else {
          $( '#clone-week-events' ).hide( );
        }
      }
    });

    calendar.render();

    $( '#category-id' ).on( 'change', function(){
        location.href = location.pathname + '?category_id=' + $(this).val( );
    });

    $('#eventModal').on ('hidden.bs.modal', function(){
      $('#eventModal #event-days-group .alerts').addClass( 'd-none' );
      $("[name='days[]']").prop( "checked", false );
    } );

    $('#event-delete').click( function( e ){
      e.preventDefault( );
      if( confirm( 'Are you sure you want to delete this event?' ) ){
        $.post( 'calendar.php', {
        ajax_request: 1,
        delete_event: 1,
        event_id: $('#event-id').val( )
      }, function ( response ){
        if( Object.keys(response).length ){
          if( response.response == "success" ){
            calendar.refetchEvents( );
            $('#eventModal').modal( 'toggle' );
          } else {
            alert('There was an error deleting the event!');;
          }
        }
      }, 'json');
      } 
    })

    // Modal submit event
    $('#event-submit').click( function( e ){
      e.preventDefault( );
      $.post( 'calendar.php', {
        ajax_request: 1,
        manage_event: 1,
        data: $('#event-form').serialize( )
      }, function ( response ){
        if( Object.keys(response).length ){
          if( response.response == "success" ){
            if( response.data['event-category-id'] == $('#category-id').val( ) ){
              calendar.refetchEvents( );
              $('#eventModal').modal( 'toggle' );
            } else {
              window.location.href = 'calendar.php?category_id=' + response.data['event-category-id'];
            }
          } else {
            alert('There was an error summitting the event!');;
          }
        }
      }, 'json');
    });

    // Reload homepage tabs - remove caching file
    $( '#reload-homepage-tabs' ).click( function( e ){
      $.post( 'calendar.php', {
        ajax_request: 1,
        reload_homepage_tabs: 1
      }, function( response ){
        if( Object.keys(response).length && response.msg == "success"){
          alert('Public Dashboard will show the new content in about 60s.', 'success');
        }
      }, 'json' );
    } ) ;

    // Week cloning functionality
    $( '#clone-week-submit' ).click( function( e ) {
        if( ! $( '#clone-week-to' ).val( ) ){
          return false;
        } 
        if( ! confirm( 'Are you sure you want to clone this week?' ) ){
          return false;
        }
        $( '#clone-week-modal .alert-warning').addClass( 'd-none' );
        $( '#clone-week-modal .alert-info').addClass( 'd-none' );
        $.post( 'calendar.php', {
          ajax_request: 1,
          clone_week: 1,
          clone_to: $( '#clone-week-to' ).val( ),
          week_start: moment( calendar.view.currentStart ).format('YYYY-MM-DD 00:00:00') ,
          week_end: moment( calendar.view.currentEnd ).format('YYYY-MM-DD 00:00:00'),          
          category_id: $( '#category-id').val( )
        }, function( response ){
            $( '#clone-week-modal .alert-info').removeClass( 'd-none' ).text( response.success );
        }, 'json' );
    } );
    $( '#clone-week-to' ).on ('change', function( ){
        $( '#clone-week-modal .alert-warning').addClass( 'd-none' );
        if( $( this ).val( ) ){
          $( '#clone-week-modal .alert-warning').removeClass( 'd-none' );
        } 
    });
    $('#clone-week-modal').on ('hidden.bs.modal', function(){
      $( '#clone-week-modal .alert-info').addClass( 'd-none' ).text( '' );
      $( '#clone-week-modal .alert-warning').addClass( 'd-none' );
      $( '#clone-week-to' ).val( '' );
    } );

    const alertPlaceholder = document.getElementById('liveAlertPlaceholder');
    const alert = (message, type) => {
      const wrapper = document.createElement('div');
      wrapper.innerHTML = [
        `<div class="alert alert-${type} alert-dismissible" role="alert">`,
        `   <div>${message}</div>`,
        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
        '</div>'
      ].join('');
      alertPlaceholder.append(wrapper);
    }
  });

</script>
</html>