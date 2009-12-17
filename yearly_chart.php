<?php

require_once("blah.php");
require_once("user.class.php");
require_once("bestof2009.php");

date_default_timezone_set("Europe/Berlin");
setlocale(LC_ALL, 'en_GB');
ini_set('display_errors', 1);
error_reporting(E_ERROR);

$username = $_GET["username"] ? $_GET["username"] : "julians";
$chartyear = intval($_GET["year"]) ? $_GET["year"] : 2009;
$user = new User($username);
$list = $user->getWeeklyChartList();

$artists = array();
$weekly = array();
$weeks = 0;
$max = 0;
$bestofNames = array_keys($bestof);
$total = 0;

$years = array();
for ($i=0; $i < count($list); $i++) {
    $year = strftime("%Y", $list[$i]->to);
    $week = intval(strftime("%V", $list[$i]->to));
    $years[$year] = true;
    $year = intval($year);
    if ($year == $chartyear) {
        $chart = $user->getWeeklyArtistChart($list[$i]->from, $list[$i]->to);
        $weeks++;
        if ($chart && count($chart) > 1) {
            for ($j=0; $j < count($chart); $j++) { 
                $artist = $chart[$j];
                if ($artists[$artist->name]) {
                    $artists[$artist->name] += $artist->playcount;
                } else {
                    $artists[$artist->name] = $artist->playcount;
                }
                $weekly[$artist->name][$week] = $artist->playcount;
                $total += $artist->playcount;
            }
        } else if ($chart) {
            if ($artists[$chart->name]) {
                $artists[$chart->name] += $chart->playcount;
            } else {
                $artists[$chart->name] = $chart->playcount;
            }
            $weekly[$chart->name][$week] = $chart->playcount;
            $total += $chart->playcount;
        }
    }
}

arsort($artists);
krsort($years);
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

    <p>
        Enter your stuff here to see your yearly charts. They’ll probably take a while to load (20 seconds or so). Also, I’m not entirely sure this works 100%. The code for this can be be found at <a href="http://github.com/julians/last.miscellaneous">github.com/julians/last.miscellaneous</a>.
    </p>
    
    <form action="" method="get">
        <p class="blah first">
            <label for="username">Last.fm username:</label>
            <br>
            <input type="text" name="username" value="<?php echo $username; ?>" placeholder="Your username" id="username">
        </p>
        <?php
            if (count($years)) {
                echo "<p class='blah'>";
                echo '<label for="year">Year:</label>';
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
        <p>
            <input type="submit" name="gogogo" value="Make it so!" id="gogogo">
        </p>
    </form>
    
    <?php
        if ($username == "mxcl") {
            echo "<p><strong>";
            echo "You’ve been bumped up the queue. If you feel this did not improve things, the&nbsp;placebo&nbsp;effect isn’t working.";
            echo "</strong><p>";
        }
    ?>

    <p title="That’s an average of <?php echo number_format($total/count($artists)); ?> scrobbles per artist.">
        <?php echo $username; ?> collected <?php echo number_format($total); ?> scrobbles by <?php echo number_format(count($artists)); ?> artists in <?php echo $chartyear; ?>. Here they are:
    </p>

    <ul>
        <?php
            foreach ($artists as $key => $value) {
                if (!$max) $max = $value;
                
                $maxScrobbles = 0;
                $imgSrc = "http://chart.apis.google.com/chart?";
                $imgSrc .= "chs=104x16&amp;cht=ls&amp;chco=FF2863&amp;chf=bg,s,dddddd00&amp;chd=t:";
                for ($i=0; $i < $weeks; $i++) {
                    if ($i > 0) $imgSrc .= ",";
                    if (isset($weekly[$key][$i])) {
                        $imgSrc .= $weekly[$key][$i];
                        if ($weekly[$key][$i] > $maxScrobbles) $maxScrobbles = $weekly[$key][$i];
                    } else {
                        $imgSrc .= "0";
                    }
                }
                $imgSrc .= "&amp;chds=0,".$maxScrobbles;
                echo "<li>";
                print("<span class='playcount' title='That’s ".round($value/$total, 4)."% of your total scrobbles this year.'>");
                if ($chartyear == 2009 && in_array($key, $bestofNames)) {
                    print("<span class='inBestOf' title='In Last.fm’s ‘Best of 2009’ with ". number_format($bestof[$key]) ." total scrobbles'>*</span>");
                }
                print(number_format($value)."</span>");
                print("<span class='artist'>");
                print($key);
                print(" <img src='".$imgSrc."' title='The scrobble high for ".$key." was ".$maxScrobbles." times in one week.'>");
                print("</span>");
                print("<div class='chartbar' style='width: ".(round($value/$max, 2)*100)."%'> </div>");
                echo "</li>";
            }
        ?>
    </ul>

</body>
</html>