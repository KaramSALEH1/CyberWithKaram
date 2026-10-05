<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marketplace Branding
    |--------------------------------------------------------------------------
    |
    | The public-facing platform brand. Used in page titles, generated agent
    | banners and any transactional messaging.
    |
    */

    'brand' => env('CYBERLOGIA_BRAND', 'CyberLogia'),

    /*
    |--------------------------------------------------------------------------
    | Nominal SYP -> USD rate
    |--------------------------------------------------------------------------
    |
    | The marketplace prices services in Syrian Pounds. A USD equivalent is
    | displayed alongside every price for international customers, derived
    | from this nominal rate. Adjust as the rate moves.
    |
    | 80,000 SYP => $8 at the default rate of 10,000.
    |
    */

    'syp_per_usd' => (int) env('CYBERLOGIA_SYP_PER_USD', 10000),

    /*
    |--------------------------------------------------------------------------
    | Agent Heartbeat
    |--------------------------------------------------------------------------
    |
    | How often a deployed agent reports liveness (seconds). Must stay in sync
    | with HEARTBEAT_INTERVAL in stubs/agent_bootstrapper.py.
    |
    */

    'agent_heartbeat_interval' => (int) env('CYBERLOGIA_HEARTBEAT_INTERVAL', 60),

];