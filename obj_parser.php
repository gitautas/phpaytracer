<?php

function parse_obj(string $filename, Scene &$scene) {
    $file = fopen($filename, "r");
    $vertices = [];

    while (($line = fgets($file)) !== false) {
        if (str_starts_with($line, "v ")) {
            list($x, $y, $z) = sscanf($line, "v %f %f %f");
            array_push($vertices, new vec3($x, $y, $z));
        } else if (str_starts_with($line, "f ")) {
            list(
                $v0, $vt0, $vn0,
                $v1, $vt1, $vn1,
                $v2, $vt2, $vn2,
            ) = sscanf($line, "f %d/%d/%d %d/%d/%d %d/%d/%d");
            $tri = new Triangle($vertices[$v0 - 1], $vertices[$v1 - 1], $vertices[$v2 - 1], new vec3(1, 0, 0));
            array_push($scene->objects, $tri);
        }
    }
}
