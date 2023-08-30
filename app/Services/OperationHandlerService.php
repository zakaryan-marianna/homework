<?php

namespace App\Services;

use App\Enums\OperationType;
use App\Enums\UserType;
use App\Objects\Operation;

class OperationHandlerService
{
    /**
     * Commissions configuration.
     *
     * @var array
     */
    private array $commissions;

    /**
     * Count of actions and total amounts grouped by weeks, operation types and users.
     *
     * @var array
     */
    private array $usage = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->commissions = config('payments.commissions');
    }

    /**
     * Calculate commission for operation.
     *
     * @param Operation $operation
     * @return void
     */
    public function calculateCommission(Operation $operation): void
    {
        $commission = $this->getCommission($operation->operationType, $operation->userType);
        $freeAmount = $this->getRemainingFreeAmountForOperation($operation);
        $amount = $operation->getAmountInBaseCurrency();
        if ($freeAmount >= $amount) {
            $commission = 0;
        } else {
            $commission = ($amount - $freeAmount) * $commission / 100;
        }
        $operation->setCommissionInBaseCurrency($commission);
        $this->addUsage($operation->userId, $operation->operationType, $operation->weekStartDate, $amount);
    }

    /**
     * Add operation data to usages.
     *
     * @param int $userId
     * @param OperationType $operationType
     * @param string $weekStartDate
     * @param float $amount
     * @return void
     */
    private function addUsage(int $userId, OperationType $operationType, string $weekStartDate, float $amount): void
    {
        $usage = $this->getUsage($userId, $operationType, $weekStartDate);

        ++$usage['actions'];
        $usage['amount'] += $amount;

        $this->usage[$userId][$operationType->value][$weekStartDate] = $usage;
    }

    /**
     * Get usage of user for operation type and week.
     *
     * @param int $userId
     * @param OperationType $operationType
     * @param string $weekStartDate
     * @return array
     */
    private function getUsage(int $userId, OperationType $operationType, string $weekStartDate): array
    {
        return $this->usage[$userId][$operationType->value][$weekStartDate] ?? [
            'actions' => 0,
            'amount' => 0,
        ];
    }

    /**
     * Get commission percentage from configuration for operation type and user type.
     *
     * @param OperationType $operationType
     * @param UserType $userType
     * @return float
     */
    private function getCommission(OperationType $operationType, UserType $userType): float
    {
        $commissionConfiguration = $this->getCommissionConfiguration($operationType, $userType);
        return $commissionConfiguration['commission'] ?? 0;
    }

    /**
     * Get commission configuration for operation type and user type.
     *
     * @param OperationType $operationType
     * @param UserType $userType
     * @return array|null
     */
    private function getCommissionConfiguration(OperationType $operationType, UserType $userType): ?array
    {
        return $this->commissions[$operationType->value][$userType->value] ?? null;
    }

    /**
     * Get remaining free amount for user based on the operation.
     *
     * @param Operation $operation
     * @return float
     */
    private function getRemainingFreeAmountForOperation(Operation $operation): float
    {
        $commissionConfiguration = $this->getCommissionConfiguration($operation->operationType, $operation->userType);
        $freeAmount = $commissionConfiguration['free_amount'] ?? 0;
        $freeActions = $commissionConfiguration['free_actions'] ?? 0;
        $usage = $this->getUsage($operation->userId, $operation->operationType, $operation->weekStartDate);
        if ($freeActions <= $usage['actions']) {
            return 0;
        }

        return max($freeAmount - $usage['amount'], 0);
    }
}
