<?php
    require_once( '../config.php' );
    global $calendar, $user;
    $user->checkUserLogged( );
    if( ! $user->roleCan( 'mng_calendar' ) ){
        echo "You are not allowed to view this page.";
        exit;
    }
    if( ! empty( $_POST ) ){
        $response = $calendar->mgmtCategory( );
        if( ! empty( $response['errors'] ) ){
            $errors = $response['errors'];
            // $role_data = $response['role_data'];
        } else if( ! empty( $response['success'] ) ){
            $success = $response['success'];
        }
    } else if( ! empty( $_GET['category_id'] ) ){
        $category_data = $calendar->getCategory( $_GET['category_id'] );
    }
    $categories = $calendar->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Categories Administration</title>
    <?php require_once( '../templates/header.php' ); ?>
</head>
<body>
    <?php require_once( 'templates/navigation.php' ); ?>
    <div class="container admin-page calendar">         
        <?php require_once( 'templates/menu-calendars.php' ); ?>                
        <h4>CALENDAR CATEGORIES</h4> 
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
        <?php if( empty( $category_data ) && empty( $_GET['new_category'] ) ){ ?>
            <a href="?new_category=1" class="btn btn-success btn-sm">New Category</a>
        <?php } ?>
        <?php if( ! empty( $category_data ) || !empty( $_GET['new_category'] ) ) { ?>
            <form method="post" autocomplete="off" class="p-2 border rounded" >
                <div class="mb-1">
                    <input class="form-control form-control-sm" type="text" placeholder="Category" aria-label="Category" name="category_name" value="<?php echo ! empty( $category_data['category_name'] ) ? $category_data['category_name'] : "";?>" required>
                </div>
                <div class="mb-1">
                    <input class="form-control form-control-sm color-picker" type="text" placeholder="Color" aria-label="Color" name="category_color" id="category_color" value="<?php echo ! empty( $category_data['category_color'] ) ? $category_data['category_color'] : "";?>" maxlength ="7" required>
                </div>
                <a href="calendar_categories.php" class="btn btn-secondary btn-sm">Cancel</a>
                <button type="submit" class="btn btn-success btn-sm"><?php echo empty( $category_data['category_id'] ) ? 'Add' : 'Update'?> Category</button>
                <?php if( ! empty( $category_data['category_id'] ) ){ ?>
                    <input type="hidden" name="category_id" value="<?php echo $category_data['category_id'] ?>" >
                <?php } ?>
            </form>
        <?php } ?>
        <?php if( ! empty( $categories ) ) { ?>
            <div class="mt-2 border rounded">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Category</th>
                    <th scope="col">Color</th>
                    <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $index = 0;
                        foreach( $categories AS $category ) { 
                            $index++;
                        ?>
                    <tr>
                        <th scope="row"><?php echo $index ?></th>
                        <td><?php echo $category['category_name'] ?></td>
                        <td><div class="color_tile" style="background:<?php echo $category['category_color'] ?>"></div></td>
                        <th><a href="?category_id=<?php echo $category['category_id'] ?>" class="btn btn-primary btn-sm" >update</a></th>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        <?php } ?>
    </div>
    <script>
        $(function () {
            if( $('.color-picker').length ) {
                var elem = $('.color-picker')[0];
                var colorPicker =new Huebee( elem, {
                    // options
                });
            }
        });
  </script>
</body>
</html>