<?php

/**
 * NewspaperListInputFiltersDto resolver.
 */

namespace App\Resolver;

use App\Dto\NewspaperListInputFiltersDto;
use App\Entity\Enum\NewspaperStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * NewspaperListInputFiltersDtoResolver class.
 */
class NewspaperListInputFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Returns the possible value(s).
     *
     * @param Request          $request  HTTP Request
     * @param ArgumentMetadata $argument Argument metadata
     *
     * @return iterable Iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if (!$argumentType || !is_a($argumentType, NewspaperListInputFiltersDto::class, true)) {
            return [];
        }
        $categoryId = $request->query->get('categoryId');
        $tagId = $request->query->get('tagId');
        $statusId = $request->query->get('statusId', NewspaperStatus::ACTIVE->value);

        return [new NewspaperListInputFiltersDto($categoryId, $tagId, $statusId)];
    }
}
