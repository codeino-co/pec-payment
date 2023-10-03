<?php

use Pec\PecPayment;

if (! function_exists('pec_gateway')) {
    function pec_gateway(): PecPayment
    {
        return new PecPayment();
    }
}
