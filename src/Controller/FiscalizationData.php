<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\Fiscalization;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\FiscalizationFailLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class FiscalizationData extends AbstractController
{
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $fiscalizationRepository = $entityManager->getRepository(Fiscalization::class);

        /** @var Fiscalization|null $fiscalization */
        $fiscalization = $fiscalizationRepository->findOneBy(['order' => $id]);

        if (null !== $fiscalization) {
            return $this->render(
                '@RobierSyliusCroatianFiscalizationPlugin\Order\Admin\_fiscalizationData.html.twig',
                [
                    'unique_bill_identification' => $fiscalization->id(),
                    'issuer_security_code' => $fiscalization->securityCode(),
                    'identifier' => $fiscalization->order()->getNumber(),
                ]
            );
        }

        $fiscalizationLogRepository = $entityManager->getRepository(FiscalizationFailLog::class);

        $logs = $fiscalizationLogRepository->findBy(['order' => $id], ['id' => 'DESC']);

        if (empty($logs)) {
            return $this->render(
                '@RobierSyliusCroatianFiscalizationPlugin\Order\Admin\_fiscalizationNoData.html.twig',
            );
        }

        return $this->render(
            '@RobierSyliusCroatianFiscalizationPlugin\Order\Admin\_fiscalizationFailLogs.html.twig',
            [
                'logs' => $logs,
                'orderId' => $id,
            ]
        );
    }
}
