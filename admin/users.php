<?php 
    require_once( '../config.php' ); 
    global $admin, $user;
    $user->checkUserLogged( );
    if( ! $user->roleCan( 'mng_users' ) ){
        echo "You are not allowed to view this page.";
        exit;
    }
    $user_data = [];
    $errors = [];
    if( ! empty( $_POST ) ){
        $response = $admin->mgmtUser( );
        if( ! empty( $response['errors'] ) ){
            $errors = $response['errors'];
            $user_data = $response['user_data'];
        } else if( ! empty( $response['success'] ) ){
            $success = $response['success'];
        }
    } else if( ! empty( $_GET['user_id'] ) ){
        if( empty( $_GET['action'] ) && $_GET['action'] != 'delete'){
            $user_data = $user->getUser( $_GET['user_id' ] );
        }
    }    
    $users = $admin->getUsers( );
    $user_roles = $admin->getUserRoles( );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Administration</title>
    <?php require_once( '../templates/header.php' ); ?>
</head>
<body>
    <?php require_once( 'templates/navigation.php' ); ?>
    <div class="container admin-page users"> 
        <?php require_once( 'templates/menu-users.php' ); ?> 
        <h4>USERS</h4>
        <?php 
            if( ! empty( $errors ) ){
                echo '<div class="alert alert-danger" role="alert">';
                echo implode( "<br />", $errors );
                echo '</div>';
            }
            if( ! empty( $success ) ){
                echo '<div class="alert alert-success" role="alert">' . $success . '</div>';
            }
        ?>
        <?php if( empty( $user_data ) && empty( $_GET['new_user'] ) ){ ?>
            <a href="?new_user=1" class="btn btn-success btn-sm">New User</a>
        <?php } ?>
        <?php if( ! empty( $user_data ) || !empty( $_GET['new_user'] ) ) {  ?>
            <form method="post" autocomplete="off" class="p-2 border rounded" >
                <div class="mb-1">
                    <input class="form-control form-control-sm" type="text" placeholder="First Name" aria-label="First Name" name="first_name" value="<?php echo ! empty( $user_data['first_name'] ) ? $user_data['first_name'] : "";?>">
                </div>
                <div class="mb-1">
                    <input class="form-control form-control-sm" type="text" placeholder="Last Name" aria-label="Last Name" name="last_name" value="<?php echo ! empty( $user_data['last_name'] ) ? $user_data['last_name'] : "";?>">
                </div>
                <div class="mb-1">
                    <input class="form-control form-control-sm" type="email" placeholder="Email" aria-label="Email" name="email" value="<?php echo ! empty( $user_data['email'] ) ? $user_data['email'] : "";?>">
                </div>
                <div class="password-group <?php if ( ! empty( $user_data['user_id'] ) && empty( $user_data['update_password'] ) ){ ?>d-none<?php } ?>">
                    <div class="mb-1">
                        <input class="form-control form-control-sm" type="password" placeholder="Password" aria-label="Password" name="password" >
                    </div>
                    <div class="mb-1">
                        <input class="form-control form-control-sm" type="password" placeholder="Confirm Password" aria-label="Confirm Password" name="confirm_password" >
                    </div>
                </div> 
                <?php if ( ! empty( $user_data['user_id'] ) ){ ?>               
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="password_group" name="update_password" <?php if ( ! empty( $user_data['update_password'] ) ) { ?>CHECKED<?php } ?>>
                            <label class="form-check-label" for="password_group">
                                Update Password
                            </label>
                        </div>
                    </div>
                <?php } ?>
                <div class="mb-1">
                    <select name="role_id" class="form-select form-select-sm" aria-label=".form-select-sm">
                        <option value="">Role</option>
                        <?php foreach( $user_roles AS $role ){ ?>
                            <option value="<?php echo $role['role_id'] ?>" <?php if ( ! empty( $user_data['role_id'] ) && $user_data['role_id'] == $role['role_id'] ) { ?>SELECTED<?php } ?>><?php echo $role['role_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="user_available" name="available" <?php if ( ! empty( $user_data['available'] ) && $user_data['available'] == 1 ) { ?>CHECKED<?php } ?>>
                        <label class="form-check-label" for="user_available">
                            Active
                        </label>
                    </div>
                </div>
                <a href="users.php" class="btn btn-secondary btn-sm">Cancel</a>
                <button type="submit" class="btn btn-success btn-sm"><?php echo empty( $user_data['user_id'] ) ? 'Add' : 'Update'?> User</button>
                <?php if( ! empty( $user_data['user_id'] ) ){ ?>
                    <input type="hidden" name="user_id" value="<?php echo $user_data['user_id'] ?>" >
                <?php } ?>
            </form>
        <?php } ?>
        <?php if( ! empty( $users ) ) { ?>
            <div class="mt-2 border rounded">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Active</th>
                    <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $index = 0;
                        foreach( $users AS $user ) { 
                            $index++;
                        ?>
                    <tr>
                        <th scope="row"><?php echo $index ?></th>
                        <td><?php echo $user['first_name'] ?></td>
                        <td><?php echo $user['last_name'] ?></td>
                        <td><?php echo $user['email'] ?></td>
                        <td><?php echo $user_roles[ $user['role_id'] ]['role_name' ] ?></td>
                        <td><?php echo $user['available'] ? 'yes' : '<span style="color:red">no</style>' ?></td>
                        <th><a href="?user_id=<?php echo $user['user_id'] ?>&action=delete" class="btn btn-danger btn-sm" >delete</a> <a href="?user_id=<?php echo $user['user_id'] ?>" class="btn btn-primary btn-sm" >update</a></th>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        <?php } ?>
    </div>
</body>
<script>
    $(document).ready( function() {
        $( '#password_group' ).click( function( e ){
            console.log( $( this ).val( ) );
            if( $( this ).is(":checked")  ){
                $( '.password-group' ).removeClass( 'd-none' );
            } else {
                $( '.password-group' ).addClass( 'd-none' );
            }
        });
    })
</script>
</html>

