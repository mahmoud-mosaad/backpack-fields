<?php

namespace mahmoudmosaad\BackpackFields;

use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'backpack-fields');
    }
}
