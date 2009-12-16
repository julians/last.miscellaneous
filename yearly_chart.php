<?php

require_once("blah.php");
require_once("user.class.php");
require_once("bestof2009.php");

date_default_timezone_set("Europe/Berlin");
setlocale(LC_ALL, 'de_DE');
ini_set('display_errors', 1);
error_reporting(E_ERROR);

$username = $_GET["user"] ? $_GET["user"] : "julians";
$chartyear = intval($_GET["year"]) ? $_GET["year"] : 2009;
$user = new User($username);
$list = $user->getWeeklyChartList();

$artists = array();
$max = 0;
$bestofNames = array_keys($bestof);

$years = array();
for ($i=0; $i < count($list); $i++) {
    $year = strftime("%Y", $list[$i]->to);
    $years[$year] = true;
    $year = intval($year);
    if ($year == $chartyear) {
        $chart = $user->getWeeklyArtistChart($list[$i]->from, $list[$i]->to);
        if ($chart && count($chart) > 1) {
            for ($j=0; $j < count($chart); $j++) { 
                $artist = $chart[$j];
                if ($artists[$artist->name]) {
                    $artists[$artist->name] += $artist->playcount;
                } else {
                    $artists[$artist->name] = $artist->playcount;
                }
            }
        } else if ($chart) {
            if ($artists[$chart->name]) {
                $artists[$chart->name] += $chart->playcount;
            } else {
                $artists[$chart->name] = $chart->playcount;
            }
        }
    }
}

arsort($artists);
$max = null;
?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Last.fm Yearly Charts</title>
	<link rel="stylesheet" href="css/yearly.css">
	
</head>
<body>

    <form action="" method="get">
        <p>
            Enter your stuff here to receive yearly charts. They’ll probably take a while to load (20 seconds or so).
        </p>
        <p>
            <label for="username">Last.fm username</label>
            <br>
            <input type="text" name="username" value="<?php echo $username; ?>" placeholder="Your username" id="username">
        </p>
        <?php
            if (count($years)) {
                echo "<p>";
                echo '<label for="year">Year</label>';
                echo '<br>';
                echo '<select name="year" id="year">';
                foreach ($years as $key => $value) {
                    echo '<option value="'.$key.'"';

                    if (intval($key) == $chartyear) echo ' selected';
                    echo '>'.$key.'</option>';
                }
                echo '</select>';
                echo '</p>';
            }
        ?>
        <input type="submit" name="gogogo" value="Make it so!" id="gogogo">
    </form>

    <ul>
        <?php
            foreach ($artists as $key => $value) {
                if (!$max) $max = $value;
                echo "<li>";
                print("<span class='playcount'>");
                if ($chartyear == 2009 && in_array($key, $bestofNames)) {
                    print("<span class='inBestOf' title='". number_format($bestof[$key]) ." scrobbles in 2009'>*</span>");
                }
                print($value."</span>");
                print("<span class='artist'>" . $key . "</span>");
                print("<div class='chartbar' style='width: ".(round($value/$max, 2)*100)."%'> </div>");
                echo "</li>";
            }
        ?>
    </ul>

</body>
</html>