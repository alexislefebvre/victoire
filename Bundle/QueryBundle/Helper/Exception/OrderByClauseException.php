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
            $messages[] = sprintf('Oh no! "by" parameter is missing');
        }
        if (!isset($addOrderBy['order'])) {
            $messages[] = sprintf('Oh no! "order" parameter is missing');
        }

        $message = implode(', ', $messages);

        parent::__construct($message);
    }
}
