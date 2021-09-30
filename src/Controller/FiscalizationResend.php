<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\Fiscalization;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\FiscalizationFailLog;
use Robier\SyliusCroatianFiscalizationPlugin\Exception\TranslatableException;
use Robier\SyliusCroatianFiscalizationPlugin\Service\BillSender;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FiscalizationResend extends AbstractController
{
    public function __construct(
        private BillSender $billSender
    )
    {
        // noop
    }

    public function __invoke(
        int $id,
        OrderRepositoryInterface $orderRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response
    {
        /** @var OrderInterface $order */
        $order = $orderRepository->find($id);

        try{
            $this->billSender->resend($order);
        } catch (TranslatableException $exception) {
            $this->addFlash('error', $translator->trans($exception->translatableKey()));
            return $this->redirectToRoute('sylius_admin_order_show', ['id' => $id]);
        } catch (Exception $exception) {
            return $this->redirectToRoute('sylius_admin_order_show', ['id' => $id]);
        }

        $this->addFlash('success', $translator->trans('robier_sylius_croatian_fiscalization_plugin.ui.sucess_resend'));
        return $this->redirectToRoute('sylius_admin_order_show', ['id' => $id]);
    }
}