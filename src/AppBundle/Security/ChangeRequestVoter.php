<?php

namespace AppBundle\Security;

use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChangeRequestVoter extends Voter
{
    const CREATE = 'create';
    const TOGGLE_RESOLVED = 'toggle_resolved';
    const EDIT_CURATOR_REMARKS = 'edit_curator_remarks';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CREATE, self::TOGGLE_RESOLVED, self::EDIT_CURATOR_REMARKS])) {
            return false;
        }
        if (!($subject instanceof ChangeRequest)) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($subject, $token);
            case self::TOGGLE_RESOLVED:
            case self::EDIT_CURATOR_REMARKS:
                return $this->canToggleResolvedAndEditCuratorRemarks($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Every user can create new change requests.
     *
     * @return bool
     */
    private function canCreate(ChangeRequest $changeRequest, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!($user instanceof User)) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return true;
    }

    /**
     * Only curators and the adventure's author can toggle the resolved status or edit the curator remarks of a change request.
     *
     * @return bool
     */
    private function canToggleResolvedAndEditCuratorRemarks(ChangeRequest $changeRequest, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!($user instanceof User)) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($this->isCurator($token)) {
            return true;
        }

        if ($changeRequest->getAdventure()->getCreatedBy() == $user->getUsername()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the user authenticated by the given token is a curator.
     */
    private function isCurator(TokenInterface $token): bool
    {
        return $this->decisionManager->decide($token, ['ROLE_CURATOR']);
    }
}
