<?php

namespace Faxity\Tag;

use Anax\DI\DI;
use Faxity\Test\ControllerTestCase;
use Faxity\DI\DISorcery;

/**
 * Test Tag Controller.
 */
class ControllerTest extends ControllerTestCase
{
    protected $className = Controller::class;

    /** @var Controller $controller */
    protected $controller;


    protected function createDI(): DI
    {
        $di = new DISorcery(TEST_INSTALL_PATH, ANAX_INSTALL_PATH . "/vendor");
        $di->initialize("config/sorcery.php");

        return $di;
    }


    public function setUp(): void
    {
        parent::setUp();
        createTestDatabase();
    }


    public function testCatchAll(): void
    {
        populateTestDatabase();

        // Non-existent tag
        $res = $this->controller->catchAll("notAtag");
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        $body = $res->getBody();
        $this->assertContains('<h1>#notatag</h1>', $body);
        $this->assertContains('<p>This tag is not linked to any question</p>', $body);

        // Existing tag
        $res = $this->controller->catchAll("Webdev");
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        $body = $res->getBody();
        $this->assertContains('<h1>#webdev</h1>', $body);
        $this->assertContains('<ul class="questions">', $body);

        // With invalid/no arguments
        $this->assertFalse($this->controller->catchAll());
        $this->assertFalse($this->controller->catchAll("Webdev", "SometaG"));
    }


    public function testIndexActionGet(): void
    {
        $res = $this->controller->indexActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        $body = $res->getBody();
        $this->assertContains('<h1>Tags</h1>', $body);
        $this->assertContains('<p>No tags...yet</p>', $body);

        // Test template with data
        populateTestDatabase();
        $res = $this->controller->indexActionGet();
        $this->assertInstanceOf(\Anax\Response\Response::class, $res);

        $body = $res->getBody();
        $this->assertContains('<h1>Tags</h1>', $body);
        $this->assertContains('<ul class="tags">', $body);
    }
}
