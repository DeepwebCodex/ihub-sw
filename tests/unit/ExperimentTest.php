<?php


class ExperimentTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {

    }

    protected function _after()
    {
    }

    // tests
    public function testMyTestCase()
    {
        $this->specify("username is too long", function() {
            $username = 'toolooooongnaaaaaaameeee';
            verify('dat username is soooo usernamy',$username)->equals('toolooooongnaaaaaaameeee');
        });
    }
}