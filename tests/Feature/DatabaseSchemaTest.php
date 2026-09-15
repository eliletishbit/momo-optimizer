<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'is_admin'));
        $this->assertTrue(Schema::hasTable('countries'));
        $this->assertTrue(Schema::hasTable('methods'));
        $this->assertTrue(Schema::hasTable('user_methods'));
        $this->assertTrue(Schema::hasTable('receipt_fees'));
        $this->assertTrue(Schema::hasTable('sending_fees'));
        $this->assertTrue(Schema::hasTable('subscriptions'));
        $this->assertTrue(Schema::hasTable('optimization_history'));
    }
}
