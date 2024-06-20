<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\Utility\DateTimeManipulator;
use \DateTime;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

final class DateTimeType extends ScalarType implements AliasedInterface
{
	private array $memoizedValues = array();

	public $name = 'DateTime';

	/**
	 * @inheritDoc
	 */
	public static function getAliases(): array
	{
		return ['DateTime'];
	}

	/**
	 * @inheritDoc
	 */
	public function serialize($value): string
	{
		return $value->format(DateTime::ATOM);
	}

	/**
	 * @param string $value
	 *
	 * @return DateTime
	 */
	public function parseValue($value)
	{
		if (!isset($this->memoizedValues[$value])) {
			$dateTimeManipulator = new DateTimeManipulator(new DateTime($value));
			$this->memoizedValues[$value] = $dateTimeManipulator->setDefaultTimezone()->get();
		}

		return clone $this->memoizedValues[$value];
	}

	/**
	 * @param Node $valueNode
	 *
	 * @return DateTime
	 */
	public function parseLiteral($valueNode, $variables = null): DateTime
	{
		if (!isset($this->memoizedValues[$valueNode->value])) {
			$dateTimeManipulator = new DateTimeManipulator(new DateTime($valueNode->value));
			$this->memoizedValues[$valueNode->value] = $dateTimeManipulator->setDefaultTimezone()->get();
		}

		return clone $this->memoizedValues[$valueNode->value];
	}
}
