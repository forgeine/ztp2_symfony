<?php

/**
 * Newspaper voter.
 */

namespace App\Security\Voter;

use App\Entity\Newspaper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class NewspaperVoter.
 */
class NewspaperVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @var string
     */
    private const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @var string
     */
    private const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @var string
     */
    private const DELETE = 'DELETE';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Newspaper;
    }

    /**
     *Vote on attribute.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Newspaper) {
            return false;
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit newspaper.
     *
     * @param Newspaper     $newspaper Newspaper entity
     * @param UserInterface $user      User
     *
     * @return bool Result
     */
    private function canEdit(Newspaper $newspaper, UserInterface $user): bool
    {
        return $newspaper->getAuthor() === $user;
    }

    /**
     * Checks if user can view newspaper.
     *
     * @param Newspaper     $newspaper Newspaper entity
     * @param UserInterface $user      User
     *
     * @return bool Result
     */
    private function canView(Newspaper $newspaper, UserInterface $user): bool
    {
        return $newspaper->getAuthor() === $user;
    }

    /**
     * Checks if user can delete newspaper.
     *
     * @param Newspaper     $newspaper Newspaper entity
     * @param UserInterface $user      User
     *
     * @return bool Result
     */
    private function canDelete(Newspaper $newspaper, UserInterface $user): bool
    {
        return $newspaper->getAuthor() === $user;
    }
}
