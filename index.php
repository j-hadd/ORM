<?php

include_once 'response/simple.php';
include_once 'db_connector/pdo.php';
include_once 'request_maker/MySQL.php';
include_once 'CRUD.php';

?>

<?php 
if (isset($_POST))
{
    var_dump($_POST);
    echo '<br>';
}
?>

<?php
/* the object used to output api result in json */
$response = new \ORM\response\simple();

$db_host = 'localhost';
$db_name = 'orm';
$db_user = 'root';
$db_password = '';
$dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;

/* the object used to connect the db */
$db_connector = new \ORM\db_connector\_pdo($dsn, $db_user, $db_password, $response);

/* the object used to fotmate the db requests */
$request_maker = new \ORM\request_maker\MySQL();
?>

<?php /*
$cfg = array(
    'record_name' => 'record',
    'table_name' => 'record',
    'table_fields' => array('id', 'txt'),
    'add_user_log' => false,
);
$records = new CRUD($db_connector, $request_maker, $cfg);
*/

include_once('records.php');
$cfg_records = array();
$records = new \ORM\records($db_connector, $request_maker, $cfg_records);

include_once('record.php');
$cfg_record = array();
$record = new \ORM\record($db_connector, $request_maker, $cfg_record);
?>

<?php
if (isset($_POST['add_record'])) {
    $records->data = $_POST;

    $records->create();
}

if (isset($_POST['update_record'])) {
    $record->read(array('record.id = ' . $_POST['id']));
    
    $record->data = $record->data[0];
    
    $record->data = $_POST;
    
    $record->update();
}

if (isset($_POST['delete_record'])) {
    $record->data = $_POST;
    
    $record->delete();
}

if (isset($_POST['add_sub_record'])) {
    $record->read(array('record.id = ' . $_POST['record_id']));
    
    $record->data = $record->data[0];
    
    $record->data['sub_records'][] = $_POST;
    
    $record->update();
}
?>

<?php $records->read(); ?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Jad Haddouch - ORM</title>
        
        <style>
            .hidden { display: none; }
        </style>
    </head>

    <body>
        <h1>ORM Jad Haddouch</h1>
        
        <form enctype="multipart/form-data" method="post">
            <div class="js-accordion">
                <h2 class="js-accordion_title">Ajouter un record</h2>
                <div class="js-accordion_content hidden">
                    <input type="text" name="txt" placeholder="txt">
                    <input type="submit" name="add_record">
                </div>
            </div>
        </form>
        
        <?php foreach ($records->data as $records_item) { ?>
            <form enctype="multipart/form-data" method="post">
                <div class="js-accordion">
                    <h2 class="js-accordion_title">Update record id = <?php echo $records_item['id']; ?></h2>
                    <div class="js-accordion_content hidden">
                        <input type="text" name="txt" placeholder="txt" value="<?php echo $records_item['txt']; ?>">
                        <input type="hidden" name="id" value="<?php echo $records_item['id']; ?>">
                        <input type="submit" name="update_record">
                        <input type="submit" name="delete_record" value="Delete">
                    </div>
                </div>
            </form>
        
            <form enctype="multipart/form-data" method="post">
                <div class="js-accordion">
                    <h2 class="js-accordion_title">Ajouter un sub_record au record id = <?php echo $records_item['id']; ?></h2>
                    <div class="js-accordion_content hidden">
                        <input type="text" name="txt" placeholder="txt">
                        <input type="hidden" name="record_id" value="<?php echo $records_item['id']; ?>">
                        <input type="submit" name="add_sub_record">
                    </div>
                </div>
            </form>
        <?php } ?>
        
        <div class="js-accordion">
            <h2 class="js-accordion_title">Records list</h2>
            <div class="js-accordion_content hidden"><?php $response->normalize(array('data' => $records->data)); ?></div>
        </div>

        <?php foreach ($records->data as $records_item) { ?>
            <div class="js-accordion">
                <h2 class="js-accordion_title">Record id = <?php echo $records_item['id']; ?></h2>
                <div class="js-accordion_content hidden"><?php 
                    $record->read(array('record.id = ' . $records_item['id']));
                    $response->normalize(array('data' => $record->data)); 
                ?></div>
            </div>
        <?php } ?>
    </body>
    <script>
        document.querySelectorAll('.js-accordion').forEach(function (accordion)
        {
            accordion.querySelectorAll('.js-accordion_title').forEach(function (accordion_title)
            {
                accordion_title.addEventListener('click', function ()
                {
                    accordion.querySelectorAll('.js-accordion_content').forEach(function (accordion_content)
                    {
                        accordion_content.classList.toggle('hidden');
                    });
                });
            });
        });
    </script>
</html>
