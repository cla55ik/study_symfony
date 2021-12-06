<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class LoggerService
{
    private Log $log;
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
//        $this->log = $log;
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $log_data
     * @return bool
     */
    public function createLog(array $log_data):bool
    {
        $created_at = new \DateTime();

        $log = new Log();
        $log->setBody($log_data['body']);
        $log->setOwner($log_data['owner']);
        $log->setStatus($log_data['status']);
        $log->setCreatedAt($created_at);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return true;
    }

}