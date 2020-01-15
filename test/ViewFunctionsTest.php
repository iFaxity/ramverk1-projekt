<?php

namespace Anax\View;

use Faxity\DI\DISorcery;
use PHPUnit\Framework\TestCase;

/**
 * Tests view functions.
 */
class ViewFunctionsTest extends TestCase
{
    public function setUp(): void
    {
        global $di;
        $di = new DISorcery(TEST_INSTALL_PATH, ANAX_INSTALL_PATH . "/vendor");
        $di->initialize("config/sorcery.php");
    }


    public function tearDown(): void
    {
        global $di;
        $di = null;
    }


    public function testMarkdown(): void
    {
        $content = "# This is a markdown title";
        $md = markdown($content);
        $this->assertEquals($md, "<h1>This is a markdown title</h1>\n");

        $content = "`This is some inline code`";
        $md = markdown($content);
        $this->assertEquals($md, "<p><code>This is some inline code</code></p>\n");
    }


    public function testPreviewMarkdown(): void
    {
        $content = "## This is just some normal text";
        $md = previewMarkdown($content);

        $this->assertEquals($md, "This is just some normal text\n");

        $content = "## This is just some normal text";
        $md = previewMarkdown($content, 10);
        $this->assertEquals($md, "This is ju...");
    }
}
