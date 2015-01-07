<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>
  google.load('visualization', '1', {'packages': ['geochart']});
  google.setOnLoadCallback(drawRegionsMap);
  function drawRegionsMap() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Site');
    data.addColumn('number', 'Status');
    data.addColumn({type: 'string', role: 'tooltip'});
    data.addRows([
<?php
$locations = array();
foreach (getlocations() as $location) {
  $devices = array();
  $devices_down = array();
  $devices_up = array();
  $count = 0;
  $down  = 0;
  // FIXME - doesn't handle sysLocation override.
  foreach (dbFetchRows("SELECT * FROM devices WHERE location = ?", array($location)) as $device) {
    $devices[] = $device['hostname'];
    $devices_up[] = $device;
    $count++;
    if ($device['status'] == "0" && $device['disabled'] == "0" && $device['ignore'] == "0") {
      $down++;
      $devices_down[] = $device['hostname']." DOWN";
    }
  }
  $pdown = ($down / $count)*100;
  $devices_down = array_merge(array(count($devices_up). " Devices OK"), $devices_down);
  $locations[] = "['".$location."', ".$pdown.", '".implode(", ", $devices_down)."']";
}
echo implode(",\n", $locations);
?>
    ]);
    var options = {
      region: 'world',
      resolution: 'countries',
      displayMode: 'markers',
      keepAspectRatio: 1,
      magnifyingGlass: {enable: true, zoomFactor: 8},
      colorAxis: {minValue: 0,  maxValue: 100, colors: ['green', 'red']},
      markerOpacity: 0.90,
    };
    var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  };
</script>
<?php
include_once("includes/object-cache.inc.php");
echo '<div class="container">';
  echo '<div class="row">';
    echo '<div class="col-md-8">';
      echo '<div id="chart_div"></div>';
    echo '</div>';
    echo '<div class="col-md-4">';
      echo '<div class="container">';
        echo '<div class="row">';
          echo '<div class="col-md-4">';
            include_once("includes/device-summary-vert.inc.php");
          echo '</div>';
        echo '</div>';
        echo '<div class="row">';
          echo '<div class="col-md-4">';
            include_once("includes/front/boxes.inc.php");
          echo '</div>';
        echo '</div>';
      echo '</div>';
    echo '</div>';
  echo '<div class="row">';
    echo '<div class="col-md-12">';
      $device['device_id'] = '-1';
      require_once('includes/print-alerts.php');
      unset($device['device_id']);
    echo '</div>';
  echo '</div>';
echo '</div>';
if ($config['enable_syslog']) {
$sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog ORDER BY timestamp DESC LIMIT 20";
$query = mysql_query($sql);
echo('<div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              &nbsp;
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Syslog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

  foreach (dbFetchRows($sql) as $entry)
  {
    $entry = array_merge($entry, device_by_id_cache($entry['device_id']));

    include("includes/print-syslog.inc.php");
  }
  echo("</table>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");

} else {

  if ($_SESSION['userlevel'] == '10')
  {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,15";
  } else {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host =
    P.device_id AND P.user_id = " . $_SESSION['user_id'] . " ORDER BY `datetime` DESC LIMIT 0,15";
  }

  $data = mysql_query($query);

  echo('<div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              &nbsp;
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Eventlog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

  foreach (dbFetchRows($query) as $entry)
  {
    include("includes/print-event.inc.php");
  }

  echo("</table>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
}
?>
