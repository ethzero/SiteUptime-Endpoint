<?php
$time = -microtime(true);

if (stristr(PHP_OS, 'win')) {
    $loadavg = array(0,0,0);
} else {
    $loadavg = sys_getloadavg();
}

if ( file_exists(basename(__FILE__, '.php').'.conf.php') )
    require_once basename(__FILE__, '.php').'.conf.php';

function error503()
{
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 300');//300 seconds
}

if ($_REQUEST['key'] == $cfg['request_key'])
{
    $con=mysqli_connect(
        $cfg['mysql']['host'],
        $cfg['mysql']['user'],
        $cfg['mysql']['pw'],
        $cfg['mysql']['database']
    );
    // Check connection
    if (mysqli_connect_errno())
    {
            error503();
            echo "--Failed to connect to MySQL: " . mysqli_connect_error();
            die();
    }
    $time += microtime(true);
    $sql = sprintf("INSERT INTO `%s`.`%s` (`id`, `timestamp`, `page_latency`, `loadavg`, `meta`) VALUES (NULL, CURRENT_TIMESTAMP, '%f', '%s', '%s')",
            mysqli_real_escape_string($con, $cfg['mysql']['database']),
            mysqli_real_escape_string($con, $cfg['mysql']['table']),
            mysqli_real_escape_string($con, $time),
            mysqli_real_escape_string($con, serialize($loadavg)),
            mysqli_real_escape_string($con, $_SERVER['REMOTE_ADDR'])
    );
    $result = mysqli_query($con,$sql);
    if (!$result) {
            error503();
            printf("Errormessage: %s<br>\n", mysqli_error($con));
            printf("SQL: %s<br>\n", $sql);
            die();
    }
    mysqli_close($con);
}
$con=mysqli_connect(
    $cfg['mysql']['host'],
    $cfg['mysql']['user'],
    $cfg['mysql']['pw'],
    $cfg['mysql']['database']
);
if (mysqli_connect_errno())
{
    error503();
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}
$result = mysqli_query($con,"SELECT * FROM `siteuptime` WHERE `timestamp` > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY timestamp DESC LIMIT 0,200;");
?>
<!DOCTYPE html>
<html>
<head>
    <title>SiteUptime Endpoint</title>
    <link rel="stylesheet" type="text/css" href="siteuptime.css">
    <script type="text/javascript" src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['corechart']}]}"></script>
    <script type="text/javascript">
    window.onresize = function(){
        // location.reload();
        drawChart();
    }
    </script>

</head>
<body>

<header>
    <h1><span id="Site">Site</span><span id="Uptime">Uptime</span> Endpoint</h1>
    <a href="https://github.com/ethzero"><img style="position: fixed; top: 0; right: 0; border: 0; z-index: 1" src="https://camo.githubusercontent.com/365986a132ccd6a44c23a9169022c0b5c890c387/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f7265645f6161303030302e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png"></a>

</header>

<section>
    <figure id="page_latency_chart"></figure>

    <div id="data-table-container">
        <div id="data-table">
        <table>
        <caption>The last 24 hours of SiteUptime monitor checks</caption>
        <thead>
            <tr>
                <th class="id">#id</th>
                <th class="timestamp">Timestamp</th>
                <th class="page-latency">Page Latency<br>(ms)</th>
                <th class="loadavg">Load Average<br>(1, 5, 15mins)</th>
                <th class="ip-address">IP Address</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['loadavg'])) $uns_loadavg = unserialize($row['loadavg']); ?>
        <tr id="<?php echo ++$rc?>">
            <td class="id"><?php echo $row['id']?></td>
            <td class="timestamp"><?php echo $row['timestamp']?></td>
            <td class="page-latency"><?php echo sprintf("%.3f", $row['page_latency'] * 1000); ?></td>
            <td class="loadavg"><?php echo sprintf("%.2f, %.2f, %.2f", $uns_loadavg[0], $uns_loadavg[1], $uns_loadavg[2]);?></td>
            <td class="ip_address"><?php echo $row['meta']?></td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
        </div>
    </div>
</section>

<footer>
    <div class="panel" id="siteuptime">
        <a HREF="http://www.SiteUptime.com/"><img SRC="http://www.siteuptime.com/images/Siteuptime-Button-Blue.gif" BORDER="0" WIDTH="88" HEIGHT="31" ALT="SiteUptime Web Site Monitoring Service"></a>
        <a href='http://www.siteuptime.com/' target="_blank" onMouseOver='this.href="http://www.siteuptime.com/statistics.php?Id=<?php echo $cfg['siteuptime']['Id']?>&UserId=<?php echo $cfg['siteuptime']['UserId']?>";'><img width=85 height=16 border=0 alt='website uptime' src="http://btn.siteuptime.com/genbutton.php?u=<?php echo $cfg['siteuptime']['UserId']?>&m=<?php echo $cfg['siteuptime']['Id']?>&c=blue&p=total"></a><noscript><a href='http://www.siteuptime.com/'>website monitoring</a></noscript>
    </div>
    <div class="panel" id="cc">
        <a rel="license" style="display: block" target="_blank" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons Licence" style="border-width:0; margin-right: 0.5em;" src="http://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">SiteUptime Endpoint</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://eth0.uk.net" property="cc:attributionName" rel="cc:attributionURL">Patrick "ethzero" Allen</a> is licensed under a <a rel="license" target="_blank" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>
    </div>
    <div class="panel" id="github">
        <a class="github-button" href="https://github.com/ethzero" data-style="mega" aria-label="Follow @ethzero on GitHub">Follow @ethzero</a>
        <a class="github-button" href="https://github.com/ethzero/SiteUptimeEndpoint/archive/master.zip" data-style="mega" aria-label="Download ethzero/SiteUptimeEndpoint on GitHub">Download</a>
    </div>
    <p id="legal">The SiteUptime Endpoint script is not affiliated with or endorsed by SiteUptime. "SiteUptime" is a registered trademark of SiteUptime LLC.</p>
</footer>

<script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>
<script type="text/javascript">
google.setOnLoadCallback(drawChart);
function drawChart() {
    var data = google.visualization.arrayToDataTable([
['Time', 'page latency (ms)', 'load average '],
    <?php
    $result = mysqli_query($con,"SELECT *, UNIX_TIMESTAMP(`timestamp`) as ts FROM `siteuptime` WHERE `timestamp` > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY timestamp LIMIT 0,200;");
    while ($row = mysqli_fetch_assoc($result)) {
        $uns_loadavg = unserialize($row['loadavg']);?>
    ['<?php echo $row['timestamp'] ?>', <?php echo $row['page_latency'] * 1000?>, <?php echo $uns_loadavg[2]?>],
    <?php } ?>
    ]);
    var options = {
        title: 'siteuptime.php page render latency for the last 24 hours',
        hAxis: {title: 'Time',  titleTextStyle: {color: '#333'}, showTextEvery: 5},
        vAxis: {
            0: {minValue: 0},
            1: {minValue: 0},
        },
        series: {
            0: {targetAxisIndex:0},
            1:{targetAxisIndex:1},
        },
        colors: ["rgb(32, 76, 116)", "rgb(245, 116, 49)"],
    };
    var chart = new google.visualization.AreaChart(document.getElementById('page_latency_chart'));
    chart.draw(data, options);
}
</script>

</body>
</html>

<?php
mysqli_free_result($result);
mysqli_close($con);
?>