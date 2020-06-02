<?php

namespace AppBundle\Security;

use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReviewVoter extends Voter
{
    const CREATE = 'create';
    const EDIT = 'edit';
    const DELETE = 'delete';

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
        if ('review' === $subject && self::CREATE === $attribute) {
            return true;
        }
        if ($subject instanceof Review && in_array($attribute, [self::EDIT, self::DELETE])) {
            return true;
        }

        return false;
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
                return $this->canCreate($token);
            case self::EDIT:
                return $this->canEdit($subject, $token);
            case self::DELETE:
                return $this->canDelete($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Every user can create new reviews.
     *
     * @return bool
     */
    private function canCreate(TokenInterface $token)
    {
        return $this->isLoggedIn($token);
    }

    /**
     * Every user can edit their own reviews.
     *
     * @return bool
     */
    private function canEdit(Review $review, TokenInterface $token)
    {
        return $this->isLoggedIn($token) && $this->isCreatedBy($review, $token);
    }

    /**
     * Only curators and the review's author can delete a review.
     *
     * @return bool
     */
    private function canDelete(Review $review, TokenInterface $token)
    {
        return $this->isLoggedIn($token) &&
            ($this->isCreatedBy($review, $token) || $this->isCurator($token));
    }

    /**
     * Checks if the user authenticated by the given token is a logged in.
     */
    private function isLoggedIn(TokenInterface $token): bool
    {
        return $token->getUser() instanceof User;
    }

    private function isCreatedBy(Review $review, TokenInterface $token): bool
    {
        if (!$this->isLoggedIn($token)) {
            return false;
        }
        $user = $token->getUser();

        return $review->getCreatedBy() === $user->getUsername();
    }

    /**
     * Checks if the user authenticated by the given token is a curator.
     */
    private function isCurator(TokenInterface $token): bool
    {
        return $this->decisionManager->decide($token, ['ROLE_CURATOR']);
    }
}
