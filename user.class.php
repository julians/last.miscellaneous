<?php

class User {
    protected $name;
    protected $expires = 3600;
    
    function User ($name)
    {
        $this->name = $name;
    }
    
    public function __get($key)
    {
        if ($key == "name") return $this->name;
    }
    
    public function getWeeklyChartList ()
    {
        $key = "user:".$this->name.":weeklychartlist";
        $r = Store::get();
        $list = $r->get($key);
        
        if (!$list) {
            $args = array(
                "method" => "user.getweeklychartlist",
                "user" => $this->name,
            );
            $list = API::request($args);
            if ($list) {
                $r->set($key, $list);
                $r->expire($key, $this->expires);
            }
        }
        
        if ($list) {
            $list = json_decode($list);
            return $list->weeklychartlist->chart;
        } else {
            return null;            
        }
    }
    
    public function getWeeklyArtistChartCount ($from = null, $to = null)
    {
        return $this->getWeeklyChartCount("artist", $from, $to);
    }
    
    public function getWeeklyAlbumChartCount ($from = null, $to = null)
    {
        return $this->getWeeklyChartCount("album", $from, $to);
    }
    
    public function getWeeklyTrackChartCount ($from = null, $to = null)
    {
        return $this->getWeeklyChartCount("track", $from, $to);
    }
    
    public function getWeeklyChartCount ($type = "artist", $from = null, $to = null)
    {
        $r = Store::get();
        if ($from && $to) {
            $key = "user:".$this->name.":weeklychart:".$type.":".$from.":".$to.":count";
        } else {
            $key = "user:".$this->name.":weeklychart:".$type.":latest:count";
        }
        $count = $r->get($key);
        if ($count) return $count;
        
        $chart = $this->getWeeklyChart($type, $from, $to);
        
        if ($chart) {
            $count = 0;
            foreach ($chart as $object => $item) {
                $count += $item->playcount;
            }
            $r->set($key, $count);
            if (!($from && $to)) $r->expire($key, $this->expires);
            
            return $count;
        }
        
        return null;            
    }
    
    public function getWeeklyArtistChart ($from = null, $to = null)
    {
        $chart = $this->getWeeklyChart("artist", $from, $to);
        if (!is_array($chart)) $chart = array($chart);
        return $chart;
    }
    
    public function getWeeklyAlbumChart ($from = null, $to = null)
    {
        $chart = $this->getWeeklyChart("album", $from, $to);
        if (!is_array($chart)) $chart = array($chart);
        return $chart;
    }
    
    public function getWeeklyTrackChart ($from = null, $to = null)
    {
        $chart = $this->getWeeklyChart("track", $from, $to);
        if (!is_array($chart)) $chart = array($chart);
        return $chart;
    }
    
    public function getWeeklyChart ($type = "artist", $from = null, $to = null)
    {
        $r = Store::get();
        if ($from && $to) {
            $key = "user:".$this->name.":weeklychart:".$type.":".$from.":".$to;
            $chart = $r->get($key);

            if (!$chart) {
                $args = array(
                    "method" => "user.getweekly".$type."chart",
                    "from" => $from,
                    "to" => $to,
                    "user" => $this->name,
                );
                $chart = API::request($args);
                if ($chart) {
                    $r->set($key, $chart);
                }
            }
        } else {
            $key = "user:".$this->name.":weeklychart:".$type.":latest";  
            $chart = $r->get($key);
            
            if (!$chart) {
                $args = array(
                    "method" => "user.getweekly".$type."chart",
                    "user" => $this->name,
                );
                $chart = API::request($args);
                if ($chart) {
                    $r->set($key, $chart);
                    $r->expire($key, $this->expires);
                }
            }
        }
        
        if ($chart) {
            $chart = json_decode($chart);
            $var = "weekly".$type."chart";
            return $chart->$var->$type;
        } else {
            return null;            
        }
    }
}

?>