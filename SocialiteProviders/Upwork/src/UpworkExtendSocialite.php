<?php

namespace SocialiteProviders\Upwork;

use SocialiteProviders\Manager\SocialiteWasCalled;

class UpworkExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'upwork',
            __NAMESPACE__.'\Provider',
            __NAMESPACE__.'\Server'
        );
    }
}
