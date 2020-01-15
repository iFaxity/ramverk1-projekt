<?php

namespace Faxity\Auth;

use Anax\Commons\ContainerInjectableInterface;
use Anax\Commons\ContainerInjectableTrait;
use Faxity\Models\User;

/**
 * DI Module for handling authentication, using sessions.
 */
class Auth implements ContainerInjectableInterface
{
    use ContainerInjectableTrait;

    /** @var User $user User database model */
    public $user;


    /**
     * Sets the user object to session and to this Auth object
     * @param User $user
     *
     * @return void
     */
    public function setUser(User $user): void
    {
        $this->di->session->set("uid", $user->id);
        $this->user = $user;
    }


    /**
     * Login user if found in session
     *
     * @return $this
     */
    public function initialize(): Auth
    {
        $userId = $this->di->session->get("uid");

        if (isset($userId)) {
            $user = new User($this->di->dbqb);
            $user->findById($userId);

            // If user not found, remove from session
            if (is_null($user->id)) {
                $this->di->session->delete("uid");
            } else {
                $this->user = $user;
            }
        }

        return $this;
    }


    /**
     * Checks if user is logged in
     *
     * @return bool
     */
    public function loggedIn(): bool
    {
        return isset($this->user);
    }


    /**
     * Logins user from session
     * @param string $aliasOrEmail Username or email
     * @param string $password     User password
     *
     * @return void
     */
    public function login(string $aliasOrEmail, string $password): void
    {
        if ($this->loggedIn()) {
            return;
        }

        $params = [ strtolower($aliasOrEmail), $aliasOrEmail ];
        $user = new User($this->di->dbqb);
        $user->findWhere("alias = ? OR email = ?", $params);

        if (!$user->verifyPassword($password)) {
            throw new Exception("Invalid credentials! Username and/or password is incorrect.");
        }

        $this->setUser($user);
    }


    /**
     * Register a new user, also sets login to the session
     * @param string $alias    Unique user alias
     * @param string $email    Unique user email
     * @param string $password User password
     *
     * @return void
     */
    public function register(string $alias, string $email, string $password): void
    {
        $user = new User($this->di->dbqb);
        $user->alias = strtolower($alias);
        $user->email = $email;
        $user->setPassword($password);
        $user->save();

        $this->setUser($user);
    }


    /**
     * Logouts user
     *
     * @return void
     */
    public function logout()
    {
        $this->di->session->delete("uid");
        $this->user = null;
    }
}
