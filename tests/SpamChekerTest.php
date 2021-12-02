<?php

namespace App\Tests;

use App\Entity\Comment;
use App\SpamChecker;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SpamChekerTest extends TestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testSpamScoreWithInvalidRequest()
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context=[];

        $client = new MockHttpClient([new MockResponse('invalid', ['response_headers'=>['x-akismet-debug-help: Invalid key']])]);
        $checker = new SpamChecker($client, 'asd');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to check');

        $checker->getSpamScore($comment, $context);
    }
}
