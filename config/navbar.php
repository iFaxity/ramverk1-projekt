<?php
/**
 * Supply the basis for the navbar as an array.
 */
global $di;

$menu = [
    [
        "text"  => "Dashboard",
        "url"   => "",
        "title" => "Dashboard.",
    ],
    [
        "text"  => "About",
        "url"   => "about",
        "title" => "About this webpage.",
    ],
    [
        "text"  => "Questions",
        "url"   => "question",
        "title" => "View the most recent questions.",
    ],
    [
        "text"  => "Tags",
        "url"   => "tags",
        "title" => "View the most popular tags.",
    ],
    [
        "text"  => "Users",
        "url"   => "users",
        "title" => "List all registered users.",
    ],
];


if ($di->auth->loggedIn()) {
    $menu[] = [
        "text"  => "Profile",
        "url"   => "profile",
        "title" => "My profile.",
    ];
    $menu[] = [
        "text"  => "Logout",
        "url"   => "logout",
        "title" => "Logout from the site.",
    ];
} else {
    $menu[] = [
        "text"  => "Login",
        "url"   => "login",
        "title" => "Login to your account.",
    ];
    $menu[] = [
        "text"  => "Register",
        "url"   => "register",
        "title" => "Register a new account.",
    ];
}


return [
    // Use for styling the menu
    "wrapper" => null,
    "class" => "menu",
    // Here comes the menu items
    "items" => $menu,
];
