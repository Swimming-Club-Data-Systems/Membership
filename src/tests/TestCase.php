<?php

namespace Tests;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Indicates whether the default seeder should run before each test.
     */
    protected bool $seed = true;

    protected bool $tenancy = false;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->tenancy) {
            $this->initializeTenancy();
        }
    }

    public function initializeTenancy(): void
    {
        $tenant = new Tenant();
        $tenant->Name = 'Test Club';
        $tenant->Code = 'XSHF';
        $tenant->Website = 'https://www.testclubwebsite.scds.uk';
        $tenant->Email = 'testclub@scds.uk';
        $tenant->Verified = true;
        $tenant->Domain = 'testclub-test.membership.test';
        $tenant->UniqueID = \Ramsey\Uuid\v4();

        $tenant->save();

        //        $tenant->domains()->create([
        //            'domain' => 'testclub-test.membership.test',
        //        ]);

        tenancy()->initialize($tenant);
    }
}
