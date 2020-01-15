<?php

namespace Faxity\Site;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Anax\Route\Exception\NotFoundException;
use Anax\Route\Exception\ForbiddenException;
use Faxity\Models\Answer;
use Faxity\Models\Comment;
use Faxity\Models\Question;
use Faxity\Models\Tag;
use Faxity\Models\User;
use Faxity\Site\HTMLForm\LoginForm;
use Faxity\Site\HTMLForm\RegisterForm;
use Faxity\Site\HTMLForm\ProfileForm;

/**
 * A controller for flat file markdown content.
 */
class Controller implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;


    /**
     * Required for the router to run 404 page
     * @param mixed ...$args
     *
     * @return bool
     */
    public function catchAll(...$args)
    {
        return false;
    }


    /**
     * Render the dashboard
     *
     * @return object
     */
    public function indexActionGet()
    {
        $user = new User($this->di->dbqb);
        $question = new Question($this->di->dbqb);
        $tag = new Tag($this->di->dbqb);

        $users = $user->findAllTop("rep DESC", 5);
        $questions = $question->findAllTop("created DESC", 5);
        $tags = $tag->findAllByQuestionCount(5);

        // TODO: add data here right?
        $this->di->page->add("site/index", [
            "questions" => $questions,
            "tags"      => $tags,
            "users"     => $users,
        ]);

        return $this->di->page->render([
            "title"     => "Dashboard",
        ]);
    }


    /**
     * Renders about page from content/
     *
     * @return object
     */
    public function aboutActionGet()
    {
        $content = $this->di->content->contentForRoute("about");

        foreach ($content->views as $view) {
            $this->di->page->add($view);
        }

        return $this->di->page->render($content->frontmatter);
    }


    /**
     * Show user page for a specific user or list users
     *
     * @return object|bool
     */
    public function usersActionGet(string $alias = null)
    {
        $user = new User($this->di->dbqb);

        // Show specific user profile
        if (!is_null($alias)) {
            $user->find("alias", $alias);
            if (is_null($user->id)) {
                throw new NotFoundException("User '$alias' does not exist.");
            }

            $title = $user->alias;
            $comment = new Comment($this->di->dbqb);
            $answer = new Answer($this->di->dbqb);
            $question = new Question($this->di->dbqb);

            $this->di->page->add("site/user/user", [
                "user"      => $user,
                "comments"  => $comment->findAllWhere("userId = ?", $user->id),
                "answers"   => $answer->findAllWhere("userId = ?", $user->id),
                "questions" => $question->findAllWhere("userId = ?", $user->id),
            ]);
        } else {
            // show users
            $users = $user->findAllTop("rep DESC");
            $title = "Users";

            $this->di->page->add("site/user/list", [
                "users" => $users,
            ]);
        }

        return $this->di->page->render([
            "title" => $title,
        ]);
    }


    /**
     * Shows profile edit form and handles profile edit request
     *
     * @return object
     */
    public function profileAction()
    {
        if (!$this->di->auth->loggedIn()) {
            throw new ForbiddenException("Unauthorized, login first to view this page!");
        }

        $user = $this->di->auth->user;
        $form = new ProfileForm($this->di, $user);
        $form->check();

        $this->di->page->add("site/profile", [
            "form" => $form->getHTML(),
            "user" => $user,
        ]);

        return $this->di->page->render([
            "title" => "Profile",
        ]);
    }


    /**
     * Shows login form and handles login request
     *
     * @return object
     */
    public function loginAction()
    {
        // Route guard for already logged in
        if ($this->di->auth->loggedIn()) {
            return $this->di->response->redirect("");
        }

        $form = new LoginForm($this->di);
        $form->check();

        $this->di->page->add("site/login", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Login",
        ]);
    }


    /**
     * Shows register form and handles register request
     *
     * @return object
     */
    public function registerAction()
    {
        // Route guard for already logged in
        if ($this->di->auth->loggedIn()) {
            return $this->di->response->redirect("");
        }

        $form = new RegisterForm($this->di);
        $form->check();

        $this->di->page->add("site/register", [
            "form" => $form->getHTML(),
        ]);

        return $this->di->page->render([
            "title" => "Register",
        ]);
    }


    /**
     * Logouts the user, if any is logged in
     *
     * @return object
     */
    public function logoutActionGet()
    {
        // Destroy user id from session
        $this->di->auth->logout();
        return $this->di->response->redirect("");
    }
}
