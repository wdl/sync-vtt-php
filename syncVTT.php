<?php
    class syncVTT {
        function strToTimestamp($timeStr) {
            $ta = array_reverse(explode(':', $timeStr));
            if(count($ta) == 3) {
                $ta[2] = (float)$ta[2] * 60 * 60;
            }
            $ta[1] = (float)$ta[1] * 60;
            $ta[0] = (float)$ta[0];
            return array_sum($ta);
        }

        function timestampToStr($time) {
            $ta = [];
            $timestampStr = explode('.', (string)$time);
            $sec = $timestampStr[0];
            $ms = 0;
            if(isset($timestampStr[1])) {
                $ms = $timestampStr[1];
            }
            while(true) {
                array_push($ta, str_pad(($sec - (floor($sec / 60) * 60)), 2, "0", STR_PAD_LEFT));
                $sec = floor($sec / 60);
                if(!$sec) {
                    break;
                }
            }
            if(!isset($ta[1])) {
                array_push($ta, "00");
            }
            if(isset($ta[2])) {
                $ta[2] = (string)(int)$ta[2];
            }
            $ta = array_reverse($ta);
            return implode(':', $ta) . "." . str_pad($ms, 3, "0");
        }

        function sync($input, $output, $pos) {
            $fp = fopen($input, 'r');
            $fr = fread($fp, filesize($input));
            fclose($fp);
            preg_match_all('/([0-9:\.]+) --> ([0-9:\.]+)/', $fr, $matches, PREG_SET_ORDER);
            foreach($matches as $match) {
                $start = $match[1];
                $end = $match[2];
                $newStart = $this->timestampToStr($this->strToTimestamp($start) + $pos);
                $newEnd = $this->timestampToStr($this->strToTimestamp($end) + $pos);
                $fr = str_replace($start . ' --> ' . $end, $newStart . ' --> ' . $newEnd, $fr);
            }

            $fp = fopen($output, 'w');
            fwrite($fp, $fr);
            fclose($fp);
        }
    }
