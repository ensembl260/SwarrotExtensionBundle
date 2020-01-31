<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Consumer;

use MR\SwarrotExtensionBundle\Broker\Consumer\ConstraintConsumerInterface;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

trait ConstrainedConsumerTestCaseTrait
{
    /**
     * @var ConstraintConsumerInterface
     */
    protected $consumer;

    public function assertViolations(array $expectedViolations, ConstraintViolationListInterface $constraintViolationList): void
    {
        $violationList = [];
        /** @var ConstraintViolation $violation */
        foreach ($constraintViolationList as $violation) {
            $violationList[$violation->getPropertyPath()] = $violation->getMessage();
        }

        TestCase::assertEquals($expectedViolations, $violationList);
        TestCase::assertCount(count($expectedViolations), $violationList);
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param mixed $data
     * @param array|null $groups
     */
    public function testValidMessage($data, array $groups = null): void
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
     * @param mixed $data
     * @param array $expectedViolations
     * @param array|null $groups
     */
    public function testInvalidMessage($data, array $expectedViolations, array $groups = null): void
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
     * @param mixed $value
     * @param Constraint|Constraint[] $constraints
     * @param array|null $groups
     *
     * @return ConstraintViolationListInterface
     */
    private function validate($value, $constraints, $groups = null)
    {
        $validator = Validation::createValidator();

        return $validator->validate($value, $constraints, $groups);
    }

    abstract public function validDataProvider(): array;

    abstract public function invalidDataProvider(): array;
}
