<?php

namespace App\Command;

use App\Entity\Transaction;
use App\Repository\PaymentRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:transactions:backfill',
    description: 'Backfill transaction records from existing payments (idempotent)',
)]
class BackfillTransactionsCommand extends Command
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $payments = $this->paymentRepository->findAll();
        $created = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            // Skip if a transaction is already linked to this payment
            $existing = $this->transactionRepository->findOneBy(['payment' => $payment]);
            if ($existing) {
                $skipped++;
                continue;
            }

            $transaction = new Transaction();
            $transaction->setCustomer($payment->getCustomer());
            $transaction->setPayment($payment);
            $transaction->setSubscription($payment->getSubscription());
            $transaction->setType($payment->getSubscription() ? 'subscription' : 'payment');
            $transaction->setAmount($payment->getAmount() ?? '0');
            $transaction->setCurrency($payment->getCurrency() ?? 'PHP');
            $transaction->setStatus($payment->getStatus() ?? 'pending');

            // Reference: order number or subscription id
            $reference = null;
            if ($payment->getOrder()) {
                $reference = $payment->getOrder()->getOrderNumber();
            } elseif ($payment->getSubscription()) {
                $reference = 'SUB-' . $payment->getSubscription()->getId();
            }
            $transaction->setReference($reference);

            $transaction->setDescription($payment->getDescription() ?: null);
            $transaction->setCreatedAt($payment->getCreatedAt() ?? new \DateTimeImmutable());
            $transaction->setProcessedAt($payment->getPaidAt());
            $transaction->setMetadata([
                'paymentId' => $payment->getId(),
                'paymentMethod' => $payment->getPaymentMethod(),
            ]);

            $this->entityManager->persist($transaction);
            $created++;
        }

        if ($created > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            'Backfill complete. Created %d transaction(s), skipped %d existing.',
            $created,
            $skipped
        ));

        return Command::SUCCESS;
    }
}

