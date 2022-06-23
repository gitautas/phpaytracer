<?php
class Ray {
    public vec3 $origin;
    public vec3 $direction;

    function __construct(vec3 $origin, vec3 $direction) {
        $this->origin = $origin;
        $this->direction = $direction;
    }

    function at(float $t) {
        return new vec3($this->origin->x + $t * $this->direction->x,
                        $this->origin->y + $t * $this->direction->y,
                        $this->origin->z + $t * $this->direction->z);
    }
}
