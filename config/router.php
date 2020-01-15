<?php

use Anax\Route\Router;

/**
 * Configuration file for routes.
 */
return [
    //"mode" => Router::DEVELOPMENT, // default, verbose execeptions
    //"mode" => Router::PRODUCTION,  // exceptions turn into 500

    // Path where to mount the routes, is added to each route path.
    "mount" => null,

    // Load routes in order, start with these and the those found in
    // router/*.php.
    "routes" => [
        [
            "info" => "Questions controller.",
            "mount" => "question",
            "handler" => "\Faxity\Question\Controller",
        ],
        [
            "info" => "Answers controller.",
            "mount" => "answer",
            "handler" => "\Faxity\Answer\Controller",
        ],
        [
            "info" => "Comments controller.",
            "mount" => "comment",
            "handler" => "\Faxity\Comment\Controller",
        ],
        [
            "info" => "Tags controller.",
            "mount" => "tags",
            "handler" => "\Faxity\Tag\Controller",
        ],
        [
            "info" => "Site controller.",
            "mount" => null,
            "handler" => "\Faxity\Site\Controller",
        ],
    ],
];
