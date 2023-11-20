<?php

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

uses(\Tests\TestCase::class);

beforeEach(function () {
    $tenant = new Tenant();

    $tenant->Name = 'Test Tenant';
    $tenant->Code = 'XSHF';
    $tenant->Website = 'https://myswimmingclub.uk';
    $tenant->Email = 'test@myswimmingclub.uk';
    $tenant->Verified = true;
    $tenant->UniqueID = Ramsey\Uuid\v4();

    $tenant->save();

    $tenant->createDomain([
        'domain' => 'test.localhost',
    ]);

    tenancy()->initialize($tenant);
});

test('newly created tenant properties are set and returned as expected', function () {
    $this->assertEquals('Test Tenant', tenant('Name'));
    $this->assertEquals('XSHF', tenant('Code'));
    $this->assertEquals('https://myswimmingclub.uk', tenant('Website'));
    $this->assertEquals('test@myswimmingclub.uk', tenant('Email'));
    $this->assertEquals(true, tenant('Verified'));
    expect(tenant('UniqueID'))->toBeString();
});
