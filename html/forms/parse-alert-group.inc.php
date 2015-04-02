<?php

if(is_admin() === false) {
	die('ERROR: You need to be admin');
}

$group_id = $_POST['group_id'];

if( is_numeric($group_id) && $group_id > 0 ) {
	$group = dbFetchRow("SELECT * FROM `alert_groups` WHERE `id` = ? LIMIT 1",array($group_id));
	foreach( dbFetchRows("SELECT device_id FROM `devices_attribs` WHERE `attrib_type` = 'alert_group' && `attrib_value` = ?",array($group_id)) as $device ) {
		$devices[] = dbFetchCell("SELECT hostname FROM devices WHERE device_id = ?",array($device['device_id']));
	}
	$output = array('name'=>$group['name'],'desc'=>$group['desc'],'devices'=>$devices);
	echo _json_encode($output);
}
