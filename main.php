<?php

require './vec3.php';
require './ray.php';
require './object.php';
require './obj_parser.php';

const WIDTH = 300;
const HEIGHT = 300;

class Image
{
    public int $width;
    public int $height;
    public array $pixels;

    function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->pixels = array_fill(0, $this->width * $this->height, new vec3(0, 0, 0));
    }

    function set_pixel(int $x, int $y, vec3 $color)
    {
        $this->pixels[$x + $y * $this->width] = $color;
    }

    function save_to_file(string $filename)
    {
        $file = fopen($filename, "w");
        fprintf($file, "P3 %d %d 255\n", $this->width, $this->height);
        foreach ($this->pixels as $pixel) {
            $r = max(0, min(255, ($pixel->r * 255)));
            $g = max(0, min(255, ($pixel->g * 255)));
            $b = max(0, min(255, ($pixel->b * 255)));

            fprintf($file, "%d %d %d ", $r, $g, $b);
        }
        fclose($file);
    }
}

class Scene {
    public array $objects;
    function __construct() {
        $this->objects = [];
    }
}

class Camera
{
    public vec3 $origin;
    public vec3 $horizontal;
    public vec3 $vertical;
    public vec3 $lower_left_corner;

    function __construct(
        int $width,
        int $height,
        vec3 $origin,
        vec3 $direction,
        float $fov,
    ) {
        $aspect_ratio = $width / $height;

        $theta = deg2rad($fov);
        $viewport_height = 2 * tan($theta / 2.0);
        $viewport_width = $aspect_ratio * $viewport_height;
        $this->origin = $origin;

        $up = new vec3(0, 1, 0);

        $w = $direction->scale(-1)->normalize();
        $u = $up->cross($w)->normalize();
        $v = $w->cross($u);

        $this->horizontal = $u->scale(-$viewport_width);
        $this->vertical = $v->scale($viewport_height);
        $this->lower_left_corner = new vec3(
            $origin ->x - $this->horizontal->x / 2 - $this->vertical->x / 2 - $w->x,
            $origin ->y - $this->horizontal->y / 2 - $this->vertical->y / 2 - $w->y,
            $origin ->z - $this->horizontal->z / 2 - $this->vertical->z / 2 - $w->z,
        );
    }

    function get_ray(float $du, float $dv)
    {
        $direction = new vec3(
            $this->lower_left_corner->x + $du * $this->horizontal->x + $dv * $this->vertical->x - $this->origin->x,
            $this->lower_left_corner->y + $du * $this->horizontal->y + $dv * $this->vertical->y - $this->origin->y,
            $this->lower_left_corner->z + $du * $this->horizontal->z + $dv * $this->vertical->z - $this->origin->z,
        );

        return new Ray(
            $this->origin,
            $direction->normalize()
        );
    }
}

function bg_color(Ray $r)
{
    $r->direction->normalize();
    $t = 0.5 * ($r->direction->y + 1.0);
    return new vec3(
        (1.0 - $t) * 1.0 + $t * 0.5,
        (1.0 - $t) * 1.0 + $t * 0.7,
        (1.0 - $t) * 1.0 + $t * 1.0,
    );
}

function render(Ray $ray, Scene $scene) {
    $hit = false;
    $isect = new Intersection();
    foreach ($scene->objects as $object) {
        if ($object->intersect($ray, $isect)) {
            $hit = true;
        }
    }
    if ($hit) {
        return $isect->normal->scale(0.5)->add(new vec3(0.5, 0.5, 0.5));
    }
    return bg_color($ray);
}

function main()
{
    $scene = new Scene();
    parse_obj("./corpse.obj", $scene);

    $camera = new Camera(
        WIDTH, HEIGHT, new vec3(7, 7, 7), new vec3(-1, -1, -1), 30.0
    );

    $image = new Image(WIDTH, HEIGHT);
    for ($y = 0; $y < $image->height; $y++) {
        echo "\rScanlines done: $y";
        flush();
        for ($x = 0; $x < $image->width; $x++) {
            $ray = $camera->get_ray(
                $x / $image->width,
                1 - $y / $image->height
            );

            $color = render($ray, $scene);
            $image->set_pixel($x, $y, $color);
        }
    }
    $image->save_to_file("test.ppm");
}

main();
