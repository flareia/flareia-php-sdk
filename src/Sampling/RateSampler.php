<?php

namespace FlareIA\Sdk\Sampling;

class RateSampler
{
    public function shouldSample(float $rate): bool
    {
        if ($rate >= 1.0) {
            return true;
        }

        if ($rate <= 0.0) {
            return false;
        }

        return mt_rand() / mt_getrandmax() <= $rate;
    }
}
