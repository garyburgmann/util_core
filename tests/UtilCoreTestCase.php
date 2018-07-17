<?php

namespace go1\util\tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\util\DB;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\UtilCoreServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Log\LoggerInterface;

class UtilCoreTestCase extends TestCase
{
    use InstallTrait;
    use UserMockTrait;
    use QueueMockTrait;

    /** @var  Connection */
    protected $db;
    protected $log;

    public function setUp()
    {
        $this->db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($this->db, false);

        DB::install($this->db, [
            function (Schema $schema) {
                $this->setupDatabaseSchema($schema);
            },
        ]);
    }

    protected function setupDatabaseSchema(Schema $schema)
    {
        # Extra database setup, test cases can safely override this.
    }

    protected function getContainer(): Container
    {
        return (new Container(['accounts_name' => 'accounts.test']))
            ->register(
                new UtilCoreServiceProvider,
                [
                    'logger' => function () {
                        $logger = $this
                            ->getMockBuilder(LoggerInterface::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['error'])
                            ->getMockForAbstractClass();

                        $logger
                            ->expects($this->any())
                            ->method('error')
                            ->willReturnCallback(function ($message) {
                                $this->log['error'][] = $message;
                            });

                        return $logger;
                    },
                ]
            );
    }
}
