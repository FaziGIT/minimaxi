<?php

namespace App\Security;

use App\Entity\Order;
use App\Enum\OrderStatusEnum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, ?Order>
 */
class OrderVoter extends Voter
{
    public const VIEW = 'view';
    public const VALIDATE = 'validate';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::VALIDATE])
            && ($subject instanceof Order || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($attribute === self::VIEW && $subject === null) {
            return true;
        }

        /** @var Order $order */
        $order = $subject;

        if ($order->getClient() !== $user) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::VALIDATE => $this->canValidate($order),
            default => false,
        };
    }

    private function canValidate(Order $order): bool
    {
        return $order->getStatus() === OrderStatusEnum::PENDING
            && !$order->getOrderItems()->isEmpty();
    }
}
