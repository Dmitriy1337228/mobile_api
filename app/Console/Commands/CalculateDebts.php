<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Readings;
use App\Models\Rates;
use App\Models\Balances;
use App\Models\OperationsHistory;

class CalculateDebts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-debts {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Расчет текущих задолженностей';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            // Если user_id передан, получаем конкретного должника
            $debtors = User::where('id', $userId)
                ->where('CalculatedInDebt', false)
                ->get();
        } else {
            // Если user_id не передан, получаем всех должников
            $debtors = User::where('CalculatedInDebt', false)->get();
        }

        if ($debtors) {

            $rates = Rates::pluck('rate_value', 'reading_type')->toArray(); //тарифные ставки 

            DB::transaction(function () use ($debtors, $rates) {

                foreach ($debtors as $debtor) {
                    //Непосчитанные показания
                    $readings = Readings::where('user_id',$debtor->id)
                                        ->where('CalculatedInDebt',false)
                                        ->get();

                    if ($readings) {   

                        $estimatedDebt = 0; //расчетный долг
                        foreach ($readings as $reading) {
                            //Предыдущее показание для данного типа показаний
                            $previousReading = Readings::where('user_id', $debtor->id)
                                ->where('reading_type', $reading->reading_type)
                                ->where('created_at', '<', $reading->created_at)
                                ->orderBy('created_at', 'desc') 
                                ->first();

                            if ($previousReading) {
                                $diff = $reading->reading_value - $previousReading->reading_value;
                            } else {
                                $diff = $reading->reading_value;
                            }
                            $estimatedDebt += $rates[$reading->reading_type] * $diff;

                            $reading->CalculatedInDebt=true;// показание посчитано
                            $reading->save();  

                        }

                        $userBalance = Balances::where('user_id',$debtor->id)->first();
                        $userBalance->balance_value -= $estimatedDebt;
                        $userBalance->save();

                        $debtor->CalculatedInDebt=true;//Должник посчитан
                        $debtor->save();

                        OperationsHistory::create([
                            'user_id' => $debtor->id,
                            'Description'=>'Списание задолженности на сумму: '. $estimatedDebt,
                            'DateTime'=>(new \DateTime())->format('Y-m-d H:i:s')
                        ]);


                    }

                }

            });   

        }
    }
}
