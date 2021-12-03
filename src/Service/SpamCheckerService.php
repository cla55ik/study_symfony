<?php

namespace App\Service;

use App\Entity\Comment;

class SpamCheckerService
{
    const SPAM_ARRAY = [
        'asd', 'dsa', 'qwerty', 'aaa'
    ];

    /**
     * @param Comment $comment
     * @return bool
     */
    public function getSpamCheck(Comment $comment) :bool
    {
        $comment_text = $comment->getText();
        $comment_text_array = explode(' ',$comment_text);

        foreach ($comment_text_array as $word){
            if(in_array($word, self::SPAM_ARRAY)){
                return false;
            }
        }
        return true;
    }
}