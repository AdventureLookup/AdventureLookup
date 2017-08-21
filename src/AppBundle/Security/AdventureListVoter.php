<?php


namespace AppBundle\Security;

use AppBundle\Entity\AdventureList;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdventureListVoter extends Voter
{
    const LIST = 'list';
    const CREATE = 'create';
    const SHOW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::LIST, self::CREATE, self::SHOW, self::EDIT, self::DELETE])) {
            return false;
        }
        if (!($subject instanceof AdventureList) && !($attribute === self::LIST && $subject === 'adventure_list')) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!($user instanceof User)) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::LIST:
            case self::CREATE:
                return $this->canListAndCreate();
            case self::SHOW:
            case self::EDIT:
            case self::DELETE:
                return $this->canManage($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Every user can list his own lists and create new lists.
     *
     * @return bool
     */
    private function canListAndCreate()
    {
        return true;
    }

    /**
     * Users can show, edit and delete their own lists.
     *
     * @param AdventureList $list
     * @param User $user
     * @return bool
     */
    private function canManage(AdventureList $list, User $user)
    {
        if ($list->getUser()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
