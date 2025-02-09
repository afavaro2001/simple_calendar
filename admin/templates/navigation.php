<?php global $user ?>
<div class="container p-0 pb-2">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ADMIN</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if( $user->roleCan( 'mng_users' ) ){ ?>
                    <li class="nav-item users">
                        <a class="nav-link" href="<?php echo SITE_URL ?>/admin/users.php">Users</a>
                    </li>
                <?php }
                    if( $user->roleCan( 'mng_calendar' ) ){ ?>
                    <li class="nav-item calendar">
                        <a class="nav-link" href="<?php echo SITE_URL ?>/admin/calendar.php">Calendars</a>
                    </li>
                <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL ?>/login.php?logout=1">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
<script>
    $(document).ready( function(){
        if( $( '.admin-page.users').length ){
            $( '.nav-item.users .nav-link').addClass( 'active' );
        }
        if( $( '.admin-page.calendar').length ){
            $( '.nav-item.calendar .nav-link').addClass( 'active' );
        }
    });
</script>