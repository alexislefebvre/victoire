<?php

namespace Victoire\Bundle\QueryBundle\Helper\Exception;

class OrderByClauseException extends \Exception
{
    /**
     * OrderByClauseException constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $messages = [];

        if (!isset($addOrderBy['by'])) {
            $messages[] = sprintf('"by" parameter is missing');
        }
        if (!isset($addOrderBy['order'])) {
            $messages[] = sprintf('"order" parameter is missing');
        }

        $message = sprintf('Oh no! Some parameters are missing: %s', implode(', ', $messages));

        parent::__construct($message);
    }
}
