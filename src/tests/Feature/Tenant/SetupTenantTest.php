<?php

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

uses(\Tests\TenantTestCase::class);

test('tenant created during TestCase setup is accessible via tenant()', function () {
    /** @var Tenant $tenant */
    $tenant = tenant();

    $this->assertEquals('Test Club', tenant('Name'));
    $this->assertEquals('XSHF', tenant('Code'));
    $this->assertEquals('https://www.testclubwebsite.scds.uk', tenant('Website'));
    $this->assertEquals('testclub@scds.uk', tenant('Email'));
    $this->assertEquals(true, tenant('Verified'));
    expect(tenant('UniqueID'))->toBeString();
});
