<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;

class LoggerService
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
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

    public function loginLog(array $log_data):bool
    {
        $createdAt = new \DateTime();


        $log = new Log();
        $log->setStatus($log_data['status']);
        $log->setOwner($log_data['owner']);
        $log->setBody($log_data['body']);
        $log->setCreatedAt($createdAt);
        $this->entityManager->persist();
    }

}