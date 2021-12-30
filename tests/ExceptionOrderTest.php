<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use App\Entity\Order;

class ExceptionOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    /**
     * @dataProvider orderInvalidDataProvider
     */
    public function testCreateBookInvalid($phone, $address, $expextedMessage, $expectedCountErrors)
    {
        $order = new Order();
        $order->setPhone($phone);
        $order->setAddress($address);

        $errors = $this->validator->validate($order, null, ["Default"]);

        if($errors->has(0)) {
            $message = $errors->get(0)->getMessage();
            $this->assertSame($expextedMessage, $message);
            $this->assertEquals($expectedCountErrors, $errors->count());
        } else {
            $this->assertSame($expextedMessage, "");
            $this->assertEquals($expectedCountErrors, 0);
        }
    }


    public function
    orderInvalidDataProvider(): iterable
    {
        return [
            [
                "phone" => "",
                "address" => "Minsk, Leonardo Da Vinche str.",
                "Phone can not be empty",
                1
            ],
            [
                "phone" => "324-54-21",
                "address" => "Minsk, Leonardo Da Vinche str.",
                "Invalid phone number",
                1
            ],
            [
                "phone" => "+375(29)257-12-11",
                "address" => "",
                "This value should not be blank.",
                2
            ],
            [
                "phone" => "+375(29)257-12-11",
                "address" => "ff",
                "This value is too short. It should have 5 characters or more.",
                1
            ],
            [
                "phone" => "+375(29)257-12-11",
                "address" => "Minsk, Leonardo Da Vinche str.",
                "",
                0
            ],
        ];
    }
}