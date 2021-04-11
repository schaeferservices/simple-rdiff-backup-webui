<?php

/**
 * Simple rdiff-backup web ui
 * @author Daniel Schäfer <code@daschaefer.de>
 */

$config = json_decode(file_get_contents("config.json"), true);
$BackupDisks = $config["BackupDisks"];
$BackupDirs = $config["BackupDirs"];

foreach($BackupDisks as $BackupDisk)
{

    exec("df -h " . $BackupDisk, $arr);

    $result = array_values(array_filter(explode(" ", $arr[1])));

    $BackupUsage = intval(str_replace("%", "", $result[4]));

    if($BackupUsage >= 90)
    {
        $state = "Critical";
    }
    else if($BackupUsage >= 80)
    {
        $state = "Warning";
    }
    else
    {
        $state = "OK";
    }

    $BackupDiskStats[] = array( 'device' => $result[0],
                                'point'   => $result[5],
                                'spaceSize' => $result[1] . "B",
                                'backupSize'  => $result[2] . "B",
                                'usage' => $BackupUsage . "%",
                                'state' => $state,
                                'statePic' => ($state == "OK" ? "✔️" : ($state == "Warning" ? "⚠️" : ($state == "Critical" ? "❌" : $state)))
                                );

    unset($arr, $result);
}

$BackupStats = [];

for($i=0; $i < count($BackupDirs); $i++)
{
    exec("sudo rdiff-backup -l --parsable-output " . $BackupDirs[$i], $arr);

    $Backups = [];
    foreach($arr as $a)
    {
        $result = explode(" ", $a)[0];
        array_push($Backups, array(
            'timestamp' => date("I", intval($a)) == 0 ? gmdate("d.m.Y H:i:s", intval($a)+3600) : gmdate("d.m.Y H:i:s", intval($a)+7200),
            'type' => "incremental"
        ));

        unset($a);
    }

    $Backups[count($Backups)-1]['type'] = "current mirror";
    $lastBackup = date_diff(date_create_from_format("d.m.Y H:i:s", $Backups[count($Backups)-1]['timestamp']), new DateTime)->format("%a");

    $BackupStat = array(
        'directory' => $BackupDirs[$i],
        'backups' => array_reverse($Backups),
        'state' => ($lastBackup > 1 ? "Critical" : ($lastBackup == 1 ? "Warning" : "Ok")),
        'statePic' => ($lastBackup > 1 ? "❌" : ($lastBackup == 1 ? "⚠️" : "✔️"))
    );

    array_push($BackupStats, $BackupStat);

    unset($arr, $BackupStat, $Backups);
}

if(isset($_GET['output']) && strtolower($_GET['output']) == "json")
{
    header('Content-type:application/json;charset=utf-8');
    $JsonStat[] = array(    'BackupDiskStats' => $BackupDiskStats,
                            'BackupStats' => $BackupStats
                            );

    echo substr(substr(json_encode($JsonStat), 1), 0, -1);
    exit();
}

?>

<header>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSIT Backup Dashboard</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery-3.0.0.slim.min.js"></script>
</header>

<style>
    .tableBodyScroll tbody {
        display: block;
        max-height: 300px;
        overflow-y: auto;
    }

    .tableBodyScroll thead, .tableBodyScroll tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    ::-webkit-scrollbar {
        width: 0px;  /* Remove scrollbar space */
        background: transparent;  /* Optional: just make scrollbar invisible */
    }
    ::selection {
        background-color: #fff;
        color: #303030;
    }
</style>

<nav class="navbar navbar-dark bg-dark">
  <a class="navbar-brand" href="/">
    <img src="img/Logo-SSIT_v6_white.png" height="50" class="d-inline-block align-top" alt="">
    <div style="text-align: center; display: inline-block; padding-left: 20; vertical-align: center; align-self: center; padding-top:5px;">
    <h1 class="display-4" style="display: inline-block; text-align: center; font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;font-size: 1.8rem; font-weight: 100; padding-left:25;">Backup Dashboard</h1>
    </div>
  </a>
</nav>

<br><br>

<div class="card m-1">
  <h5 class="card-header">Backup space statistics</h5>
  <div class="card-body">
  <table class="table table-hover table-dark">
  <thead>
    <tr>
      <th scope="col">Backup device</th>
      <th scope="col">Backup point</th>
      <th scope="col">Space size</th>
      <th scope="col">Backup size</th>
      <th scope="col">Space usage</th>
      <th scope="col">State</th>
    </tr>
  </thead>
  <tbody>
<?php
    foreach($BackupDiskStats as $BackupDiskStat)
    {
        echo "\t<tr>\n";
        echo "\t\t<td>" . $BackupDiskStat["device"] . "</td>\n";
        echo "\t\t<td>" . $BackupDiskStat["point"] . "</td>\n";
        echo "\t\t<td>" . $BackupDiskStat["spaceSize"] . "</td>\n";
        echo "\t\t<td>" . $BackupDiskStat["backupSize"] . "</td>\n";
        echo "\t\t<td>" . $BackupDiskStat["usage"] . "</td>\n";
        echo "\t\t<td>" . $BackupDiskStat["statePic"] . "</td>\n";
        echo "\t</tr>\n";
    }
?>
  </tbody>
</table>
  </div>
</div>

<br><br>

<div class="row">
<div class="card-group">

<?php

foreach($BackupStats as $num => $BackupStat)
{
    echo "  <div class=\"card m-1\">
            <div class=\"card-header\">
            <h5 style=\"padding: 0; margin: 0; display: inline-block; vertical-align: middle;\">" . $BackupStat['directory'] . "</h5><h5 style=\"padding: 0; margin: 0; display: inline-block; vertical-align: middle; float: right;\">" . $BackupStat['statePic'] . "</h5>
            </div>
            <div class=\"card-body\">
            <table class=\"table table-hover table-dark tableBodyScroll\" max-height=\"1\">
            <thead>
                </tr>
                    <th scope=\"col\">Backup timestamp</th>
                    <th scope=\"col\">Backup type</th>
                </tr>
            </thead>
            <tbody>\n";

            foreach($BackupStat['backups'] as $Backup)
            {
                echo "\t\t\t\t<tr>\n";
                echo "\t\t\t\t\t<td>" . $Backup["timestamp"] . "</td>\n";
                echo "\t\t\t\t\t<td>" . $Backup["type"] . "</td>\n";
                echo "\t\t\t\t</td>\n";
            }

    echo "\t\t</tbody>\n\t\t</table>\t\t</div>\t</div>";

    if((($num + 1) % 4) == 0)
    {
        echo "  </div>
                </div>
                <div class=\"row\">
                <div class=\"card-group\">";
    }
}

?>
</div>
</div>
