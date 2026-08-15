<?php

return [
    /*
    | Only list reverse-proxy IPs/CIDRs controlled by the operator. Never use
    | a wildcard unless the entire network path is independently trusted.
    */
    'proxies' => env('TRUSTED_PROXIES'),
];
