<?php
$id = mres($_POST['group_id']);
$name = mres($_POST['name']);
$desc = mres($_POST['desc']);
$devices = $_POST['devices'];
$ret = array();

if( is_numeric($id) ) {
	$cont = dbUpdate(array('name'=>$name,'desc'=>$desc),'alert_groups','id=?',array($id));
} else {
	$cont = dbInsert(array('name'=>$name,'desc'=>$desc),'alert_groups');
	$ret[] = "INFO: Created new Group $name.";
	if( is_numeric($cont) ) {
		$id = $cont;
		$cont = true;
	} else {
		die("ERROR: Could not create Group.");
	}
}

if( $cont !== false ) {
	$deldev = array();
	foreach( dbFetchRows('SELECT devices.hostname FROM devices,devices_attribs WHERE devices.device_id=devices_attribs.device_id && devices_attribs.attrib_type = "alert_group" && devices_attribs.attrib_value = ?',array($id)) as $dbdev) {
		$dbdev = $dbdev['hostname'];
		$i = array_search($dbdev,$devices);
		if( $i !== false ) {
			unset($devices[$i]);
		} else {
			$deldev[] = $dbdev;
		}
	}
	foreach( $devices as $dev ) {
		$devid = dbFetchCell('SELECT devices.device_id FROM devices WHERE devices.hostname = ?',array(mres($dev)));
		if( dbInsert(array('attrib_type'=>'alert_group','attrib_value'=>$id,'device_id'=>$devid),'devices_attribs') !== false ) {
			$ret[] = "INFO: Added $dev to Group $name.";
		} else {
			array_unshift($ret,'ERROR: Could not add $dev to Group $name.');
		}
	}
	foreach( $deldev as $dev ) {
		$devid = dbFetchCell('SELECT devices.device_id FROM devices WHERE devices.hostname = ?',array(mres($dev)));
		if( dbDelete('devices_attribs','attrib_type = "alert_group" && attrib_value = ? && device_id = ?',array($id,$devid)) !== false ) {
			$ret[] = "INFO: Removed $dev from Group $name.";
		} else {
			array_unshift($ret,"ERROR: Could not Remove $dev from Group $name.");
		}
	}
} else {
	array_unshift($ret,'ERROR: An error occurred while processing the request.');
}
foreach( $ret as $msg ) {
	echo $msg."<br/>";
}
?>
