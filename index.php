<?php require_once( './config.php' ); 
    global $calendar;
    if( ! empty( $_POST['ajax_request'] ) ){
        $calendar->routeAjaxRequest( );
    }    
    $categories = $calendar->getCategories( );
    if( ! empty( $_GET['category_id' ] ) && is_numeric( $_GET['category_id' ] ) ){
      $default_category = $_GET['category_id' ];
      $default_color = $categories[ $default_category ][ 'category_color' ];
    } else {
      $default_category = ! empty( $categories[1] ) ? $categories[1]['category_id'] : 0 ;
      $default_color = $default_category ? $categories[ $default_category ][ 'category_color' ] : "#EEC";
    }
    $categories = $calendar->getCategories( );
    $week_starters = $calendar->getWeekStarts( );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php require_once( './templates/header.php' ); ?>
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
    <script src="./assets/js/fullcalendar.6.1.9/index.global.min.js"></script>
    <script src="./assets/js/moment.2.29.4/moment.js"></script>
</head>
<body class="dashboard">
    <div class="container">
        <div class="dashboard-banner row">
            <div class="headline col-9"><h1>SIMPLE CALENDAR</h1></div>
            <div class="banner-nav col-3"><a href="./admin/">Admin</a></div>
        </div>
        <div class="row">
          <div class="col-6"><h4>CALENDARS</h4></div>
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
                <div class="modal-header" style="background:<?php echo $default_color ?>" >
                    <h1 class="modal-title fs-5" id="eventModalLabel">Event</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="event-form" >
                      <div class="mb-1">
                            <h2 class="event_title"></h2>
                      </div>
                      <div class="row gx-2 p-3 text-nowrap rounded" style="background: #eee" >
                        <h5 class="card-title">
                            <span class="event_start_date"></span><br />
                            <span class="event_end_date"></span> 
                        </h5>
                        <p class="card-text mt-2 event_description"></p>  
                      </div>
                    </form>
                </div> 
            </div>
        </div>
    </div>   <!-- End of the 'eventModal' -->
    
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
                editable: false,
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
                eventClick: function( eventInfo ){
                    var start_date = moment( eventInfo.event.start ).format('lll');
                    var end_date = moment( eventInfo.event.end ).format('lll');
                    $('#eventModal .event_title').html( eventInfo.event.title );
                    $('#eventModal .event_start_date').html( 'Start: ' + start_date );
                    $('#eventModal .event_end_date').html( 'End: &nbsp;' + end_date );
                    $('#eventModal .event_description').text( eventInfo.event.extendedProps.description );
                    $('#eventModal').modal( 'show' );        
                },
                events: {
                    url: 'index.php',
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

            // Reload homepage tabs - remove caching file
            $( '#reload-homepage-tabs' ).click( function( e ){
                $.post( 'index.php', {
                ajax_request: 1,
                reload_homepage_tabs: 1
                }, function( response ){
                if( Object.keys(response).length && response.msg == "success"){
                    alert('Dashboard Tabs will show the new content in about 60s.', 'success');
                }
                }, 'json' );
            } ) ;

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
</body>
<script src="./assets/js/scripts.js?ver=0.1" ></script>
<script>    
    adminEventSource.addEventListener(
        "reload_calendar",
        (event) => {
            location.reload( );
        },
        false
    );
</script>
</html>