<?php
function crockford32_encode($data) {
    $chars = '0123456789abcdefghjkmnpqrstvwxyz';
    $mask = 0b11111;

    $dataSize = strlen($data);
    $res = '';
    $remainder = 0;
    $remainderSize = 0;

    for($i = 0; $i < $dataSize; $i++) {
        $b = ord($data[$i]);
        $remainder = ($remainder << 8) | $b;
        $remainderSize += 8;
        while($remainderSize > 4) {
            $remainderSize -= 5;
            $c = $remainder & ($mask << $remainderSize);
            $c >>= $remainderSize;
            $res .= $chars[$c];
        }
    }
    if($remainderSize > 0) {
        $remainder <<= (5 - $remainderSize);
        $c = $remainder & $mask;
        $res .= $chars[$c];
    }

    return $res;
}

function crockford32_decode($data) {
    $map = [
        '0' => 0,
        'O' => 0,
        'o' => 0,
        '1' => 1,
        'I' => 1,
        'i' => 1,
        'L' => 1,
        'l' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        'A' => 10,
        'a' => 10,
        'B' => 11,
        'b' => 11,
        'C' => 12,
        'c' => 12,
        'D' => 13,
        'd' => 13,
        'E' => 14,
        'e' => 14,
        'F' => 15,
        'f' => 15,
        'G' => 16,
        'g' => 16,
        'H' => 17,
        'h' => 17,
        'J' => 18,
        'j' => 18,
        'K' => 19,
        'k' => 19,
        'M' => 20,
        'm' => 20,
        'N' => 21,
        'n' => 21,
        'P' => 22,
        'p' => 22,
        'Q' => 23,
        'q' => 23,
        'R' => 24,
        'r' => 24,
        'S' => 25,
        's' => 25,
        'T' => 26,
        't' => 26,
        'V' => 27,
        'v' => 27,
        'W' => 28,
        'w' => 28,
        'X' => 29,
        'x' => 29,
        'Y' => 30,
        'y' => 30,
        'Z' => 31,
        'z' => 31,
    ];

    $data = strtolower($data);
    $dataSize = strlen($data);
    $buf = 0;
    $bufSize = 0;
    $res = '';

    for($i = 0; $i < $dataSize; $i++) {
        $c = $data[$i];
        if(isset($map[$c])) {
            $b = $map[$c];
            $buf = ($buf << 5) | $b;
            $bufSize += 5;
            if($bufSize > 7) {
                $bufSize -= 8;
                $b = ($buf & (0xff << $bufSize)) >> $bufSize;
                $res .= chr($b);
            }
        }
    }

    return $res;
}
?>
