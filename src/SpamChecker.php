<?php

namespace App;

use App\Entity\Comment;



class SpamChecker
{
   private array $spam = [
       'asd', 'dsa', 'qwerty'
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
            if(in_array($word, $this->spam)){
                return false;
            }
        }
        return true;
    }


}