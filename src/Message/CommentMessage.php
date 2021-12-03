<?php

namespace App\Message;

class CommentMessage
{
    private int $id;


    public function __construct(int $id, array $context = [])
    {
        $this->id = $id;

    }

    public function getId():int
    {
        return $this->id;
    }




}