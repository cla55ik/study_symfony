<?php

namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    private $client;
    private $endpoint;

    public function __construct(HttpClientInterface $client, string $akismetKey)
    {
        $this->client = $client;
        $this->endpoint=sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
    }


    /**
     * @param Comment $comment
     * @param array $context
     * @return int
     */
    public function getSpamScore(Comment $comment, array $context) :int
    {
        $response = $this->client->request('POST', $this->endpoint,[
            'body'=>array_merge($context,[
                'blog'=>'',
                'comment_type'=>'comment',
                'comment_author'=>$comment->getAuthor(),
                'comment_author_email'=>$comment->getEmail(),
                'comment_date_gmt'=>$comment->getCreatedAt()->format('c'),
                'blog_lang'=>'en',
                'blog_charset'=>'UTF-8',
                'is_test'=>true
            ])
        ]);

        $headers = $response->getHeaders();
        if('discard' === ($headers['x-akisment-pro-tip'][0] ?? '')){
            return 2;
        }

        $content = $response->getContent();
        if(isset($headers['x-akisment-debug-help'][0])){
            throw new \RuntimeException(sprintf('Unable to check for spam', $content));
        }

        return 'true' === $content ? 1 : 0;
    }


}