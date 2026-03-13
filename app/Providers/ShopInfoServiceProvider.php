<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class ShopInfoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer("*", function ($view) {
            if (auth()->check()) {
                $clientId = auth()->user()->ClientID;

                $client = cache()->remember(
                    "client_info_$clientId",
                    now()->addHours(2),
                    function () use ($clientId) {
                        return DB::table("clientreagistration")
                            ->where("ClientID", $clientId)
                            ->first();
                    },
                );

                $view->with("clientInfo", $client);
            }
        });
    }
}
