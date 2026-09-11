<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Expiration Threshold
    |--------------------------------------------------------------------------
    |
    | This value determines how many days before a subscription's end date
    | it should be marked as "expiring soon".
    |
    */
    'expiring_threshold' => env('SUBSCRIPTION_EXPIRING_THRESHOLD', 30),
];
