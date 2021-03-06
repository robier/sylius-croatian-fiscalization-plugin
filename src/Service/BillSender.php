<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Service;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Robier\Fiscalization\Bill;
use Robier\Fiscalization\Client\Production;
use Robier\Fiscalization\Exception\CommunicationError;
use Robier\SyliusCroatianFiscalizationPlugin\Converter\BillConverter;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\Fiscalization;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\FiscalizationFailLog;
use Robier\SyliusCroatianFiscalizationPlugin\Exception\TranslatableException;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Lock\LockFactory;

final class BillSender
{
    public function __construct(
        private BillConverter $billConverter,
        private Production $client,
        private LockFactory $lockFactory,
        private EntityManagerInterface $entityManager,
        private int $maxAttempts
    )
    {
        // noop
    }

    public function new(OrderInterface $order): void
    {
        $lock = $this->lockFactory->createLock('robier-sylius-croatian-fiscalization-plugin.fiscalize');
        $lock->acquire(true);

        $identifier = Bill\Identifier::fromString($order->getNumber());
        $bill = ($this->billConverter)(
            $order,
            new DateTimeImmutable(),
            $identifier
        );

        try {
            $response = $this->client->send($bill);

            $this->entityManager->persist(new Fiscalization(
                $response->uniqueBillIdentifier(),
                $response->issuerSecurityCode(),
                $bill->createdAt(),
                $order
            ));
        } catch (CommunicationError $e) {
            $this->entityManager->persist(new FiscalizationFailLog(
                $e->errors(),
                $bill->createdAt(),
                $order
            ));
        } catch (\Exception $e) {
            $this->entityManager->persist(new FiscalizationFailLog(
                [$e->getMessage()],
                $bill->createdAt(),
                $order
            ));
        } finally {
            // no matter if we succeeded or not, we will save new number as we will use
            // generated one if we could not sent for any reason
            $this->entityManager->flush();
            $lock->release();
        }
    }

    public function resend(OrderInterface $order): void
    {
        $lock = $this->lockFactory->createLock('robier-sylius-croatian-fiscalization-plugin.fiscalize.try_again');
        $lock->acquire(true);

        $fiscalizationFailLogRepository = $this->entityManager->getRepository(FiscalizationFailLog::class);

        /** @var array<FiscalizationFailLog> $fiscalizationFailLogs */
        $fiscalizationFailLogs = $fiscalizationFailLogRepository->findBy(['order' => $order]);

        if (empty($fiscalizationFailLogs)) {
            throw new TranslatableException(
                'There is no fail logs so we can not do resend',
                'robier_sylius_croatian_fiscalization_plugin.ui.no_fails'
            );
        }

        if (count($fiscalizationFailLogs) >= $this->maxAttempts) {
            throw new TranslatableException(
                'We tried too much times to resend bill',
                'robier_sylius_croatian_fiscalization_plugin.ui.too_much_fails'
            );
        }

        $identifier = Bill\Identifier::fromString($order->getNumber());
        $bill = ($this->billConverter)(
            $order,
            new DateTimeImmutable(),
            $identifier,
            true
        );

        try {
            $response = $this->client->send($bill);

            $this->entityManager->persist(new Fiscalization(
                $response->uniqueBillIdentifier(),
                $response->issuerSecurityCode(),
                $bill->createdAt(),
                $order
            ));

            // remove all error logs generated until now
            foreach ($fiscalizationFailLogs as $log) {
                $this->entityManager->remove($log);
            }
        } catch (CommunicationError $e) {
            $this->entityManager->persist(new FiscalizationFailLog(
                $e->errors(),
                $bill->createdAt(),
                $order
            ));
        } catch (\Exception $e) {
            $this->entityManager->persist(new FiscalizationFailLog(
                [$e->getMessage()],
                $bill->createdAt(),
                $order
            ));
        } finally {
            $this->entityManager->flush();
            $lock->release();
        }
    }
}
