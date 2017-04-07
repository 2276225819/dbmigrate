<?php 
function preg_get($reg,$str){ 
    preg_match($reg,$str,$arr);
    return $arr[1]??null;
}
 