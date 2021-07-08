<?php
    class syncASS {
        function strToTimestamp($timeStr) {
            $negative_flag = false;
            if(strpos($timeStr, '-') !== false) {
                $negative_flag = true;
                $timeStr = str_replace('-', '', $timeStr);
            }
            $ta = array_reverse(explode(':', $timeStr));
            if(count($ta) == 3) {
                $ta[2] = (float)$ta[2] * 60 * 60;
            }
            $ta[1] = (float)$ta[1] * 60;
            $ta[0] = (float)$ta[0];
            $res = array_sum($ta);
            if($negative_flag) {
                $res = $res * -1;
            }
            return $res;
        }
        function timestampToStr($time) {
            $negative_flag = false;
            if($time < 0) {
                $negative_flag = true;
                $time = abs($time);
            }
            $ta = [];
            $timestampStr = explode('.', (string)$time);
            $sec = $timestampStr[0];
            $ms = 0;
            if(isset($timestampStr[1])) {
                $ms = $timestampStr[1];
            }
            while(true) {
                $new_sec = $sec - (floor($sec / 60) * 60);
                array_push($ta, str_pad($new_sec, 2, "0", STR_PAD_LEFT));
                $sec = floor($sec / 60);
                if(!$sec) {
                    break;
                }
            }
            if(!isset($ta[1])) {
                array_push($ta, "00");
            }
            if(!isset($ta[2])) {
                array_push($ta, "0");
            }
            $ta = array_reverse($ta);
            $res = implode(':', $ta) . "." . str_pad($ms, 2, "0");
            if($negative_flag) {
                $res = '-' . $res;
            }
            return $res;
        }
        function sync($input, $output, $pos) {
            $fp = fopen($input, 'r');
            $fr = fread($fp, filesize($input));
            fclose($fp);
            preg_match_all('/^(Dialogue|Comment|Picture|Sound|Movie|Command): *[0-9]+? *, *(.*?) *, *(.*?) *,.*$/m', $fr, $matches, PREG_SET_ORDER);
            foreach($matches as $match) {
                $line = $match[0];
                $start = $match[2];
                $end = $match[3];
                $newStart = $this->timestampToStr($this->strToTimestamp($start) + $pos);
                $newEnd = $this->timestampToStr($this->strToTimestamp($end) + $pos);
                $newLine = str_replace($start, $newStart, $line);
                $newLine = str_replace($end, $newEnd, $newLine);
                $fr = str_replace($line, $newLine, $fr);
            }
            $fp = fopen($output, 'w');
            fwrite($fp, $fr);
            fclose($fp);
        }
    }
