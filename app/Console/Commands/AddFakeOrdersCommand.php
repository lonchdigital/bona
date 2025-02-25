<?php

namespace App\Console\Commands;


use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AddFakeOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:add-fake-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     */
    public function handle()
    {

        $i = 1;
        while ($i < 10) {
            Order::create([
                'status_id' => 1,
                'user_id' => 11,
                'promo_code_id' => null,
                'delivery_type_id' => 2,
                'payment_type_id' => 2,
                'recipient_type_id' => 1,
                'sat_department' => '{"ru":null}',
                'sat_city' => '{"ru":null}',
                'np_department' => '{"uk":"Відділення №1: вул. Польова, 67","ru":"Отделение №1: ул. Полевая, 67"}',
                'np_city' => '{"uk":"Харків Харківська область","ru":"Харьков Харьковская область"}',
                'payment_status_id' => 4,
            ]);

            $i++;
        }


    }

}
