<?php


namespace AppBundle\Security;

use AppBundle\Entity\AdventureList;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdventureListVoter extends Voter
{
    const LIST = 'list';
    const CREATE = 'create';
    const SHOW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if ($attribute === self::LIST && $subject === 'adventure_list') {
            return true;
        }
        if ($subject instanceof AdventureList && in_array($attribute, [
                self::CREATE,
                self::SHOW,
                self::EDIT,
                self::DELETE
            ])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(
        $attribute,
        $subject,
        TokenInterface $token
    ) {
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
