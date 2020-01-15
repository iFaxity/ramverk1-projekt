<?php

namespace Anax\View;

/**
 * Custom helper functions to use within view templates.
 */

/**
 * Parses text as markdown
 * @param string $content Markdown content
 *
 * @return string
 */
function markdown(string $content): string
{
    global $di;
    $res = $di->textfilter->parse($content, [ "markdown" ]);

    return $res->text;
}


/**
 * Shows only a preview of markdown text
 * @param string $content Markdown content
 * @param int    $maxLength Max length of text
 *
 * @return string
 */
function previewMarkdown(string $content, int $maxLength = 60): string
{
    $str = strip_tags(markdown($content));

    if (strlen($str) > $maxLength) {
        $str = substr($str, 0, $maxLength) . "...";
    }

    return $str;
}
