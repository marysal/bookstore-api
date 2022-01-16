<?php
use PHPUnit\Framework\TestCase;
use App\Entity\Book;
use Symfony\Component\Validator\Validation;

class ExceptionsBook extends TestCase
{
    private static $longText = 'Contrary to popular belief, Lorem Ipsum is not simply random text. 
                     It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular
                     during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.';

    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    /**
     * @dataProvider bookInvalidDataProvider
     */
    public function testCreateBookInvalid($title, $description, $type, $expextedMessage, $expectedCountErrors)
    {
        $book = new Book();
        $book->setTitle($title);
        $book->setDescription($description);
        $book->setType($type);

        $errors = $this->validator->validate($book);

        if($errors->has(0)) {
            $message = $errors->get(0)->getPropertyPath(). ": ".$errors->get(0)->getMessage();
            $this->assertSame($expextedMessage, $message);
            $this->assertEquals($expectedCountErrors, $errors->count());
        }
    }


    public function bookInvalidDataProvider(): iterable
    {
        return [
            [
                "title" => "",
                "description" => "",
                "type" => "",
                "title: This value should not be blank.",
                4
            ],
            [
              "title" => "",
               "description" => "New description",
               "type" => "poetry",
               "title: This value should not be blank.",
               2
           ],
           [
               "title" => "New",
               "description" => "New description",
               "type" => "poetry",
               "title: This value is too short. It should have 5 characters or more.",
               1
           ],
           [
               "title" => "New ffffffffffffff",
               "description" => "New",
               "type" => "detective",
               "type: You can choose 'prose' or 'poetry'",
               1
           ],
           [
               "title" => self::$longText,
               "description" => "New description",
               "type" => "poetry",
               "title: This value is too long. It should have 255 characters or less.",
               1
           ],
           [
               "title" => 'New title',
               "description" => self::$longText,
               "type" => "poetry",
               "description: This value is too long. It should have 255 characters or less.",
               1
           ]
        ];
    }
}