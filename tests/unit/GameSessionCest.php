<?php

use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\GameSession\GameSessionService;

/**
 * Class GameSessionCest
 */
class GameSessionCest
{
    /**
     * @var GameSessionService
     */
    protected $gameSessionService;

    /**
     * @var string
     */
    protected $sessionId;

    protected function _before()
    {
        $this->gameSessionService = new GameSessionService();
    }

    /**
     * @return string
     */
    protected function createSession()
    {
        $data = [
            'user_id' => '1',
            'project_id' => '1',
            'game_id' => '1',
            'currency' => 'USD',
        ];
        $this->gameSessionService = $this->getSession();
        return $this->gameSessionService->create($data);
    }

    /**
     * @return GameSessionService
     */
    protected function getSession()
    {
        return new GameSessionService();
    }

    /**
     * @param UnitTester $tester
     */
    public function testIsStarted(UnitTester $tester)
    {
        $this->createSession();
        $this->gameSessionService->close();
        $tester->assertFalse($this->gameSessionService->isSessionStarted());
        $this->createSession();
        $tester->assertTrue($this->gameSessionService->isSessionStarted());
    }

    /**
     * @param UnitTester $tester
     * @depends testIsStarted
     */
    public function testCreate(UnitTester $tester)
    {
        $this->createSession();
        $tester->assertTrue($this->gameSessionService->isSessionStarted());
    }

    /**
     * @param UnitTester $tester
     * @depends testIsStarted
     */
    public function testCreateCache(UnitTester $tester)
    {
        $sessionId = $this->createSession();
        $secondSessionId = $this->createSession();
        $tester->assertEquals($sessionId, $secondSessionId);
    }

    /**
     * @param UnitTester $tester
     * @depends testCreate
     */
    public function testStart(UnitTester $tester)
    {
        $sessionId = $this->createSession();
        $this->gameSessionService->close();
        $this->gameSessionService->start($sessionId);
        $tester->assertTrue($this->gameSessionService->isSessionStarted());
    }

    /**
     * @depends testCreate
     */
    public function testProlong()
    {
        $sessionId = $this->createSession();
        $this->gameSessionService->prolong($sessionId);
    }

    /**
     * @param UnitTester $tester
     * @depends testCreate
     */
    public function testRegenerate(UnitTester $tester)
    {
        $sessionId = $this->createSession();
        $newSessionId = $this->gameSessionService->regenerate($sessionId);

        $tester->expectException(SessionDoesNotExist::class, function () use ($sessionId) {
            $this->gameSessionService->start($sessionId);
        });

        $this->gameSessionService->start($newSessionId);
        $tester->assertTrue($this->gameSessionService->isSessionStarted());
    }

    /**
     * @param UnitTester $tester
     * @depends testCreate
     */
    public function testGetSet(UnitTester $tester)
    {
        $this->createSession();
        $key = 'test_key';
        $value = 'test_value';
        $this->gameSessionService->set($key, $value);
        $tester->assertEquals($value, $this->gameSessionService->get($key));
    }

    /**
     * @param UnitTester $tester
     * @depends testGetSet
     */
    public function testSave(UnitTester $tester)
    {
        $sessionId = $this->createSession();
        $key = 'test_key';
        $value = 'test_value';
        $this->gameSessionService->set($key, $value);
        $this->gameSessionService->save();
        $this->gameSessionService->close();

        $this->gameSessionService->start($sessionId);
        $tester->assertEquals($value, $this->gameSessionService->get($key));
    }
}
