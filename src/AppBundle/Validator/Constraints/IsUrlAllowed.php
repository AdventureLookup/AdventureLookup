<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsUrlAllowed extends Constraint
{
    public $message = 'The url "{{ string }}" is not allowed to be used on AdventureLookup. Reason: {{ reason }}';
}
