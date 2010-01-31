<?php
require_once("blah.php");
require_once("user.class.php");
require_once("bestof2009.php");

date_default_timezone_set("Europe/Berlin");
setlocale(LC_ALL, 'en_GB');

$username = isset($_GET["username"]) ? $_GET["username"] : "julians";
$chartyear = isset($_GET["year"]) && intval($_GET["year"]) ? intval($_GET["year"]) : 2009;
$user = new User($username);
$list = $user->getWeeklyChartList();

$artists = array();
$weekly = array();
$weeks = 0;
$weekMaxByOneArtist = 0;
$total = 0;
$weeklyCounts = array_fill(1, 52, 0);

$years = array();
for ($i=0; $i < count($list); $i++) {
    $year = intval(strftime("%Y", $list[$i]->to));
    $week = intval(strftime("%U", $list[$i]->to));
    $years[$year] = true;
    if ($year == $chartyear) {
        $chart = $user->getWeeklyArtistChart($list[$i]->from, $list[$i]->to);
        $weeks++;
        for ($j=0; $j < count($chart); $j++) { 
            $artist = $chart[$j];
            $playcount = intval($artist->playcount);
            if (!isset($artists[$artist->name])) {
                $artists[$artist->name] = array(
                    'total' => 0,
                    'weekMax' => 0,
                    'week' => array_fill(1, 52, 0),
                    'bestOf' => null,
                );
                if ($year == 2009 && isset($bestof[$artist->name])) {
                    $artists[$artist->name]['bestOf'] = $bestof[$artist->name];
                }
            }
            $artists[$artist->name]['total'] += $playcount;
            $artists[$artist->name]['week'][$week] = $playcount;
            if ($artists[$artist->name]['weekMax'] < $playcount) $artists[$artist->name]['weekMax'] = $playcount;
            
            $total += $playcount;
            if ($playcount > $weekMaxByOneArtist) $weekMaxByOneArtist = $playcount;
            $weeklyCounts[$week] += $playcount;
        }
    }
}

arsort($artists);
krsort($years);
$max = reset($artists);
$max = $max['total'];
?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Last.fm Yearly Charts</title>
	<link rel="stylesheet" href="css/yearly.css">
	
</head>
<body>

    <p id="intro">
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
            echo "You’re Max! You’ve been bumped up the queue. If you feel this did not improve things, the&nbsp;placebo&nbsp;effect isn’t working.";
            echo "</strong><p>";
        }
    ?>

    <p title="That’s an average of <?php echo number_format($total/count($artists)); ?> scrobbles per artist.">
        <?php echo $username; ?> collected <?php echo number_format($total); ?> scrobbles by <?php echo number_format(count($artists)); ?> artists in <?php echo $chartyear; ?>. Here they are:    
    <?php
        $maxScrobbles = 0;
        $imgSrc = "http://chart.apis.google.com/chart?";
        $imgSrc .= "chs=104x16&amp;cht=ls";
        $imgSrc .="&amp;chd=t:";
        for ($i=1; $i < count($weeklyCounts)+1; $i++) {
            if ($i > 1) $imgSrc .= ",";
            $imgSrc .= $weeklyCounts[$i];
            if ($weeklyCounts[$i] > $maxScrobbles) $maxScrobbles = $weeklyCounts[$i];
        }
        $imgSrc .= "&amp;chds=0,".$maxScrobbles;
        $imgSrc .= "&amp;chf=bg,s,dddddd00";
        $imgSrc  .= "&amp;chco=FF2863";
        print(" <img src='".$imgSrc."' title='The scrobble high for ".$chartyear." was ".$maxScrobbles." times in one week.' width='104' height='16'>");    
    ?>
    </p>
    
    <ul>
        <?php
            foreach ($artists as $key => $value) {
                $imgSrc = "http://chart.apis.google.com/chart?";
                $imgSrc .= "chs=104x24&amp;cht=ls";
                $imgSrc .="&amp;chd=t:";
                for ($i=1; $i <= 52; $i++) {
                    if ($i > 1) $imgSrc .= ",";
                    $imgSrc .= $value['week'][$i];
                }
                $imgSrc .= "&amp;chds=0,".$weekMaxByOneArtist;
                $imgSrc .= "&amp;chf=bg,s,dddddd00";
                $imgSrc  .= "&amp;chco=FF2863";
                //$imgSrc .= dechex(Util::map($maxScrobbles, 0, $weekMaxByOneArtist, 50, 255));
                echo "<li>";
                print("<span class='playcount' title='That’s ".round($value['total']/$total, 4)."% of your total scrobbles this year.'>");
                if (isset($value['bestOf'])) {
                    print("<span class='inBestOf' title='In Last.fm’s ‘Best of 2009’ at position ".$value['bestOf']['position']." with ". number_format($value['bestOf']['playcount']) ." total scrobbles'>*</span>");
                }
                print(number_format($value['total'])."</span>");
                print("<span class='artist'>");
                print("<a href='http://www.last.fm/music/" . urlencode($key) ."'>" . $key . "</a>");
                if ($value['total'] > 9) {
                    print(" <img src='".$imgSrc."' title='The scrobble high for ".$key." was ".$value['weekMax']." times in one week.' width='104' height='24'>");
                }
                print("</span>");
                print("<div class='chartbar' style='width: ".(round($value['total']/$max, 2)*100)."%'> </div>");
                echo "</li>";
            }
        ?>
    </ul>

</body>
</html>