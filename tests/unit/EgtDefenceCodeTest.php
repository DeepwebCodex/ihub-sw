<?php

use App\Components\Integrations\EuroGamesTech\DefenceCode;

class EgtDefenceCodeTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;

    /**
     * @var \Mockery\MockInterface
     */
    protected $storageMock;

    protected function _before() {
        $this->storageMock = Mockery::mock('Illuminate\Contracts\Cache\Store');
    }

    protected function _after() {}

    public function testGenerateFunction()
    {
        $this->specify("Check generate function", function() {

            $object = new DefenceCode($this->storageMock);

            $code = $object->generate(1, 'RUB', 1111);

            verify("It is as prognosed", $code)->equals("84099430ac27cf5ba063618bf62035d4-1111");
        });
    }

    public function testIsUsedFunction()
    {
        $this->specify("Check isUsed function", function() {

            $this->storageMock->shouldReceive("get")->andReturnNull();

            $object = new DefenceCode($this->storageMock);

            verify("Code is not used", $object->isUsed("84099430ac27cf5ba063618bf62035d4-1111"))->false();
        });
    }

    public function testIsCorrectFunction()
    {
        $this->specify("Check isCorrect function", function() {
            $object = new DefenceCode($this->storageMock);

            verify("Code is correct", $object->isCorrect("84099430ac27cf5ba063618bf62035d4-1111", 1, 'RUB'))->true();
        });
    }

    public function testIsExpiredFunction()
    {
        $this->specify("Check isExpired function", function() {
            $object = new DefenceCode($this->storageMock);

            verify("Code is not used", $object->isExpired("84099430ac27cf5ba063618bf62035d4-1111"))->true();
        });
    }
}