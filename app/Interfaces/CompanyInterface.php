<?php

namespace App\Interfaces;

interface CompanyInterface
{
    /**
     * @return void
     */
    public function addGlobalCompanyScope():void;

    /**
     * @return void
     */
    public function addGlobalCompanyIndexScope():void;
}
