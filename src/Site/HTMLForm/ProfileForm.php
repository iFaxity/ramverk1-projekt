<?php

namespace Faxity\Site\HTMLForm;

use Anax\HTMLForm\FormModel;
use Psr\Container\ContainerInterface;
use Faxity\Models\User;

/**
 * Form to update an item.
 */
class ProfileForm extends FormModel
{
    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Psr\Container\ContainerInterface $di a service container
     * @param User                             $user to update
     */
    public function __construct(ContainerInterface $di, User $user)
    {
        parent::__construct($di);
        $this->form->create(
            [
                "id" => __CLASS__,
                "use_fieldset" => false,
            ],
            [
                "id" => [
                    "type"  => "hidden",
                    "value" => $user->id,
                ],
                "alias" => [
                    "type"       => "text",
                    "validation" => ["not_empty"],
                    "value"      => $user->alias,
                ],
                "email" => [
                    "type"       => "email",
                    "validation" => ["not_empty"],
                    "value"      => $user->email,
                ],
                "password" => [
                    "type"       => "password",
                ],
                "password-verify" => [
                    "type"       => "password",
                ],
                "submit" => [
                    "type"       => "submit",
                    "value"      => "Update profile",
                    "class"      => "solid",
                    "callback"   => [$this, "callbackSubmit"]
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
    public function callbackSubmit(): bool
    {
        $user = new User($this->di->dbqb);
        $user->find("id", $this->form->value("id"));
        $user->alias = trim($this->form->value("alias"));
        $user->email = trim($this->form->value("email"));

        // Check if password was set, to update it
        $password = $this->form->value("password");

        if (!empty($password)) {
            $passwordVerify = $this->form->value("password-verify");

            if ($passwordVerify === $password) {
                $user->setPassword($password);
            } else {
                $this->form->rememberValues();
                $this->di->flash->error("Passwords did not match");
                return false;
            }
        }

        $user->save();
        $this->di->flash->ok("Profile updated");
        return true;
    }
}
