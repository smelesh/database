<?php

declare(strict_types=1);

namespace Cycle\Database\Tests\Functional\Driver\Postgres\Query;

// phpcs:ignore
use Cycle\Database\Driver\Postgres\Query\PostgresInsertQuery;
use Cycle\Database\Tests\Functional\Driver\Common\Query\InsertQueryTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class InsertQueryTest extends CommonClass
{
    public const DRIVER = 'postgres';

    public function setUp(): void
    {
        parent::setUp();

        //To test PG insert behaviour rendering
        $schema = $this->database->table('target_table')->getSchema();
        $schema->primary('target_id');
        $schema->save();
    }

    public function tearDown(): void
    {
        $this->dropDatabase($this->database);
    }

    public function testQueryInstance(): void
    {
        parent::testQueryInstance();
        $this->assertInstanceOf(PostgresInsertQuery::class, $this->database->insert());
    }

    public function testSimpleInsert(): void
    {
        $insert = $this->database->insert()->into('target_table')->values(
            [
                'name' => 'Anton',
            ]
        );

        $this->assertSameQuery(
            'INSERT INTO {target_table} ({name}) VALUES (?) RETURNING {target_id}',
            $insert
        );
    }

    public function testSimpleInsertWithStatesValues(): void
    {
        $insert = $this->database->insert()->into('target_table')
            ->columns('name', 'balance')
            ->values('Anton', 100);

        $this->assertSameQuery(
            'INSERT INTO {target_table} ({name}, {balance}) VALUES (?, ?) RETURNING {target_id}',
            $insert
        );
    }

    public function testSimpleInsertMultipleRows(): void
    {
        $insert = $this->database->insert()->into('target_table')
            ->columns('name', 'balance')
            ->values('Anton', 100)
            ->values('John', 200);

        $this->assertSameQuery(
            'INSERT INTO {target_table} ({name}, {balance}) VALUES (?, ?), (?, ?) RETURNING {target_id}',
            $insert
        );
    }

    public function testCustomReturning(): void
    {
        $insert = $this->database->insert()->into('table')
            ->columns('name', 'balance')
            ->values('Anton', 100)
            ->returning('name');

        $this->assertSameQuery(
            'INSERT INTO {table} ({name}, {balance}) VALUES (?, ?) RETURNING {name}',
            $insert
        );
    }
}