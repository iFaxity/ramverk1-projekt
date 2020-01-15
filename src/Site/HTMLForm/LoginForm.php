<?php

namespace Faxity\Site\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;

/**
 * Form to login as a user
 */
class LoginForm extends FormModel
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
                "user" => [
                    "type"       => "text",
                    "label"      => "Username or email",
                    "validation" => ["not_empty"],
                ],
                "password" => [
                    "type"       => "password",
                    "validation" => ["not_empty"],
                ],
                "submit" => [
                    "type"     => "submit",
                    "value"    => "Login",
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
     * @return bool true if ok, false if something went wrong.
     */
    public function callbackSubmit() : bool
    {
        $user = trim($this->form->value("user"));
        $password = $this->form->value("password");

        try {
            $this->di->auth->login($user, $password);
            return true;
        } catch (\Faxity\Auth\Exception $ex) {
            $this->form->rememberValues();
            $this->di->flash->err($ex->getMessage());
            return false;
        }
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
