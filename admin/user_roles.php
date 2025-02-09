<?php
    require_once( '../config.php' );
    global $admin, $user;
    $user->checkUserLogged( );
    if( ! $user->roleCan( 'mng_roles' ) ){
        echo "You are not allowed to view this page.";
        exit;
    }
    if( ! empty( $_POST ) ){
        $response = $admin->mgmtUserRole( );
        if( ! empty( $response['errors'] ) ){
            $errors = $response['errors'];
            $role_data = $response['role_data'];
        } else if( ! empty( $response['success'] ) ){
            $success = $response['success'];
        }
    } else if( ! empty( $_GET['role_id'] ) ){
        $role_data = $admin->getRole( $_GET['role_id'] );
    }
    $user_roles = $admin->getUserRoles( );
    $role_privileges = $admin->getRolePrivileges( );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Roles Administration</title>
    <?php require_once( '../templates/header.php' ); ?>
</head>
<body>
    <?php require_once( 'templates/navigation.php' ); ?>
    <div class="container admin-page users"> 
        <?php require_once( 'templates/menu-users.php' ); ?>                
        <h4>USER ROLES</h4> 
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
        <?php if( empty( $role_data ) && empty( $_GET['new_role'] ) ){ ?>
            <a href="?new_role=1" class="btn btn-success btn-sm">New Role</a>
        <?php } ?>
        <?php if( ! empty( $role_data ) || !empty( $_GET['new_role'] ) ) { ?>
            <form method="post" autocomplete="off" class="p-2 border rounded" >
                <div class="mb-1">
                    <input class="form-control form-control-sm" type="text" placeholder="Role" aria-label="Role" name="role_name" value="<?php echo ! empty( $role_data['role_name'] ) ? $role_data['role_name'] : "";?>">
                </div>
                <div class="mb-1">
                    <?php foreach( $role_privileges AS $key => $value ) { ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="role_can[]" id="priv_<?php echo $key ?>" value="<?php echo $key ?>" <?php echo ( ! empty( $role_data['role_can'] ) && in_array( $key, $role_data['role_can'] ) ) ? "CHECKED": "" ?> >
                            <label class="form-check-label" for="priv_<?php echo $key ?>"><?php echo $value ?></label>
                        </div>
                    <?php } ?>
                </div>
                <a href="user_roles.php" class="btn btn-secondary btn-sm">Cancel</a>
                <button type="submit" class="btn btn-success btn-sm"><?php echo empty( $role_data['role_id'] ) ? 'Add' : 'Update'?> Role</button>
                <?php if( ! empty( $role_data['role_id'] ) ){ ?>
                    <input type="hidden" name="role_id" value="<?php echo $role_data['role_id'] ?>" >
                <?php } ?>
            </form>
        <?php } ?>
        <?php if( ! empty( $user_roles ) ) { ?>
            <div class="mt-2 border rounded">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Role</th>
                    <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $index = 0;
                        foreach( $user_roles AS $role ) { 
                            $index++;
                        ?>
                    <tr>
                        <th scope="row"><?php echo $index ?></th>
                        <td><?php echo $role['role_name'] ?></td>
                        <th><a href="?role_id=<?php echo $role['role_id'] ?>" class="btn btn-primary btn-sm" >update</a></th>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        <?php } ?>
    </div>
</body>
</html>