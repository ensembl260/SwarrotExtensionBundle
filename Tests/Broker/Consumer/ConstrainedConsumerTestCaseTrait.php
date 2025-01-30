<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Tests\Broker\Consumer;

use Ensembl260\SwarrotExtensionBundle\Broker\Consumer\ConstraintConsumerInterface;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

trait ConstrainedConsumerTestCaseTrait
{
    /**
     * @param mixed[] $expectedViolations
     */
    public function assertViolations(array $expectedViolations, ConstraintViolationListInterface $constraintViolationList): void
    {
        $violationList = [];

        /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
        foreach ($constraintViolationList as $violation) {
            $violationList[$violation->getPropertyPath()] = $violation->getMessage();
        }

        TestCase::assertEquals($expectedViolations, $violationList);
        TestCase::assertCount(count($expectedViolations), $violationList);
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param mixed[]|null $groups
     */
    public function testValidMessage($data, ?array $groups = null): void
    {
        if (!$this->consumer instanceof ConstraintConsumerInterface) {
            TestCase::markTestSkipped('Only for constrainted consumer.');
        }

        $this->assertViolations(
            [],
            $this->validate(
                $data,
                $this->consumer->getConstraints($data, new Message(), []),
                $groups
            )
        );
    }

    /**
     * @dataProvider invalidDataProvider
     *
     * @param mixed[]      $expectedViolations
     * @param mixed[]|null $groups
     */
    public function testInvalidMessage($data, array $expectedViolations, ?array $groups = null): void
    {
        if (!$this->consumer instanceof ConstraintConsumerInterface) {
            TestCase::markTestSkipped('Only for constrainted consumer.');
        }

        $this->assertViolations(
            $expectedViolations,
            $this->validate(
                $data,
                $this->consumer->getConstraints($data, new Message(), []),
                $groups
            )
        );
    }

    /**
     * @param array|\Symfony\Component\Validator\Constraint[] $constraints
     * @param mixed[]|null                                    $groups
     */
    private function validate($value, array $constraints, ?array $groups = null): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate($value, $constraints, $groups);
    }

    abstract public function validDataProvider(): array;

    abstract public function invalidDataProvider(): array;
}
