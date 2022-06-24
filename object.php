<?php

const EPSILON = 0.0000001;

class Intersection
{
    public float $t = INF;
    public Obj $obj;
    public vec3 $normal;
}

class Obj
{
}

class Triangle extends Obj
{
    public vec3 $p0;
    public vec3 $p1;
    public vec3 $p2;
    public vec3 $color;

    function __construct($p0, $p1, $p2, $color)
    {
        $this->p0 = $p0;
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->color = $color;
    }

    public function intersect(Ray $r, Intersection &$intersection)
    {
        $edge1 = $this->p1->subtract($this->p0);
        $edge2 = $this->p2->subtract($this->p0);

        $h = $r->direction->cross($edge2);
        $a = $edge1->dot($h);
        if ($a > -EPSILON && $a < EPSILON) {
            return false;
        }

        $f = 1.0 / $a;
        $s = $r->origin->subtract($this->p0);
        $u = $f * $s->dot($h);

        if ($u < 0.0 || $u > 1.0) {
            return false;
        }

        $q = $s->cross($edge1);
        $v = $f * $r->direction->dot($q);

        if ($v < 0.0 || $u + $v > 1.0) {
            return false;
        }

        $t = $f * $edge2->dot($q);
        if ($t <= EPSILON) {
            return false;
        }

        if ($t >= $intersection->t) {
            return false;
        }

        $intersection->t = $t;
        $intersection->obj = $this;
        $intersection->normal = $edge1->cross($edge2);
        $intersection->normal->normalize();

        return true;
    }
}

class Sphere extends Obj
{
    public vec3 $position;
    public vec3 $color;
    public float $radius;

    function __construct($position, $radius, $color)
    {
        $this->position = $position;
        $this->color = $color;
        $this->radius = $radius;
    }

    function intersect(Ray $r, Intersection &$intersection)
    {
        $oc = new vec3(
            $r->origin->x - $this->position->x,
            $r->origin->y - $this->position->y,
            $r->origin->z - $this->position->z,
        );
        $a = $r->direction->dot($r->direction);
        $b = 2.0 * $oc->dot($r->direction);
        $c = $oc->dot($oc) - $this->radius * $this->radius;

        $discriminant = $b * $b - 4 * $a * $c;
        if ($discriminant < 0) {
            return false;
        }

        $t = -$b - sqrt($discriminant) / (2 * $a);
        if ($t < 0) {
            return false;
        }
        if ($t >= $intersection->t) {
            return false;
        }

        $point = $r->at($t);
        $normal = new vec3(
            $point->x - $this->position->x / $this->radius,
            $point->y - $this->position->y / $this->radius,
            $point->z - $this->position->z / $this->radius,
        );

        $intersection->t = $t;
        $intersection->obj = $this;
        $intersection->normal = $normal;

        return true;
    }
}
