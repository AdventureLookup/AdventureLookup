<?php


namespace AppBundle\Security;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdventureVoter extends Voter
{
    const VIEW = 'view';
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @var AccessDecisionManager
     */
    private $decisionManager;

    public function __construct(AccessDecisionManager $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

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
        if (!in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }
        if (!($subject instanceof Adventure)) {
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
        // Everyone can view all adventures
        if ($attribute === self::VIEW) {
            return true;
        }

        $user = $token->getUser();
        if (!($user instanceof User)) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($subject, $token, $user);
            case self::EDIT:
            case self::DELETE:
                return $this->canEditAndDelete($subject, $token, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Every user can create new adventures.
     *
     * @param Adventure $adventure
     * @param TokenInterface $token
     * @param User $user
     * @return bool
     */
    private function canCreate(Adventure $adventure, TokenInterface $token, User $user)
    {
        return true;
    }

    /**
     * Curators can edit and delete all adventures.
     * Normal users can only edit and delete own adventures.
     *
     * @param Adventure $adventure
     * @param TokenInterface $token
     * @param User $user
     * @return bool
     */
    private function canEditAndDelete(Adventure $adventure, TokenInterface $token, User $user)
    {
        if ($this->decisionManager->decide($token, ['ROLE_CURATOR'])) {
            return true;
        }

        if ($adventure->getCreatedBy() === $user->getUsername()) {
            return true;
        }

        return false;
    }
}
