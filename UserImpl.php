<?php

class UserImpl extends User
{
    public function __construct(int $id, string $username, string $email, string $password)
    {
        parent::__construct($id, $username, $email, $password);
    }

    // public function getPassword()
    // {
    //     return $this->password;
    // }
}
