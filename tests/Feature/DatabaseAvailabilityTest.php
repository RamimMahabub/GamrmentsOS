<?php

namespace Tests\Feature;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use PDOException;
use Tests\TestCase;

class DatabaseAvailabilityTest extends TestCase
{
    public function test_database_session_outages_render_a_standalone_unavailable_page(): void
    {
        $this->failDatabaseSession();

        $this->get('/')
            ->assertStatus(503)
            ->assertHeader('Retry-After', '30')
            ->assertSee('Workspace temporarily unavailable')
            ->assertSee('Try again')
            ->assertDontSee('database.internal')
            ->assertDontSee('sensitive-session-id');
    }

    public function test_api_requests_receive_a_safe_json_response_during_database_outages(): void
    {
        $this->failDatabaseSession();

        $this->getJson('/api/lots/preview')
            ->assertStatus(503)
            ->assertHeader('Retry-After', '30')
            ->assertExactJson([
                'message' => 'The workspace is temporarily unavailable. Please try again shortly.',
            ]);
    }

    public function test_other_database_errors_keep_their_normal_error_response(): void
    {
        $this->failDatabaseSession(1064);

        $this->getJson('/')
            ->assertStatus(500)
            ->assertJsonPath('message', 'Server Error');
    }

    private function failDatabaseSession(int $driverCode = 2002): void
    {
        config([
            'app.debug' => false,
            'session.driver' => 'database',
            'session.connection' => null,
            'session.table' => 'sessions',
        ]);
        $this->app->make('session')->forgetDrivers();

        $connectionFailure = new PDOException('Connection failed to database.internal.');
        $connectionFailure->errorInfo = ['HY000', $driverCode, 'Connection failed'];
        $queryFailure = new QueryException(
            'mysql',
            'select * from sessions where id = ?',
            ['sensitive-session-id'],
            $connectionFailure,
        );

        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->once()->with('sessions')->andThrow($queryFailure);
        DB::shouldReceive('connection')->once()->with(null)->andReturn($connection);
    }
}
