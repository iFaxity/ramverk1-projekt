<?php

namespace Faxity\Site\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;

/**
 * Form to register a new user
 */
class RegisterForm extends FormModel
{
    /**
     * Constructor injects with DI container.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     */
    public function __construct(ContainerInterface $di)
    {
        parent::__construct($di);
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "username" => [
                    "type"       => "text",
                    "validation" => ["not_empty"],
                ],
                "email" => [
                    "type"       => "email",
                    "validation" => ["not_empty"],
                ],
                "password" => [
                    "type"       => "password",
                    "validation" => ["not_empty"],
                ],
                "password-verify" => [
                    "type"       => "password",
                    "validation" => ["not_empty"],
                ],
                "submit" => [
                    "type"     => "submit",
                    "value"    => "Register",
                    "class"    => "solid",
                    "callback" => [$this, "callbackSubmit"]
                ],
            ]
        );
    }



    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return bool true if okey, false if something went wrong.
     */
    public function callbackSubmit() : bool
    {
        $alias = trim($this->form->value("username"));
        $email = trim($this->form->value("email"));
        $password = $this->form->value("password");
        $passwordVerify = $this->form->value("password-verify");

        if ($password !== $passwordVerify) {
            $this->form->rememberValues();
            $this->di->flash->error("Passwords did not match");
            return false;
        }

        $this->di->auth->register($alias, $email, $password);
        $this->di->flash->ok("Account successfully registered");
        return true;
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        return $this->di->response->redirect("")->send();
    }
}
