<?php
/**
 * Configuration file for DI container.
 */
return [
    // Services to add to the container.
    "services" => [
        "auth" => [
            "shared" => true,
            "callback" => function () {
                $auth = new \Faxity\Auth\Auth();
                $auth->setDI($this);
                return $auth->initialize();
            },
        ],
    ],
];
