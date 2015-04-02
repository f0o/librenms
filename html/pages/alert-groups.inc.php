<?php

##COPYRIGHT GOES HERE



?>
<div class="row">
	<div class="col-sm-12">
		<span id="message"></span>
	</div>
</div>
<?
$no_refresh = TRUE;
require_once('includes/modal/new_alert_group.inc.php');
require_once('includes/modal/delete_alert_group.inc.php');

$sql = "SELECT * FROM alert_groups";
echo '<div class="table-responsive"><table class="table table-hover table-condensed"><thead>';
echo '<tr><th>Name</th><th>Description</th><th>Action</th></tr>';
echo '</thead>';
echo '<tbody>';
foreach( dbFetchRows($sql) as $group ) {
	echo '<tr id="row_'.$group['id'].'"><td>';
	echo htmlentities($group['name']);
	echo '</td><td>';
	echo htmlentities($group['desc']);
	echo '</td><td>';
	echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-group' data-group_id='".$group['id']."' name='edit-alert-group'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></button> ";
	echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-group_id='".$group['id']."' name='delete-alert-group'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
	echo '</td></tr>';
}
echo '</tbody>';
echo '</table></div>';
echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Add' data-toggle='modal' data-target='#create-group' data-group_id='' name='create-alert-group'>Create new Group</button> ";

?>
