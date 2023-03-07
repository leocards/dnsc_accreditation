<?php

namespace App\Console\Commands;

use App\Events\DueTask;
use App\Http\Traits\InstrumentsTrait;
use App\Models\Notification;
use App\Models\TaskAssign;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyDue extends Command
{
    use InstrumentsTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification to user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateNow = Carbon::now()->format('Y-m-d');

        $dueTask = TaskAssign::where('due', $dateNow)
            ->whereNull('notified')
            ->get(['id', 'instrumentId', 'userId', 'accredId'])
            ->map(function ($due) {
                $parent = $this->getParents($due->instrumentId, true, null);
                $accred = $due->accreditation;

                if(count($parent) > 0){
                    $due->area = $parent[count($parent)-1]->only('title');
                    $due->parameter = $parent[count($parent)-2]->only('title');
                }else{
                    $due->area = null;
                    $due->parameter = null;
                }

                $due->inst = $due->instrument;
                $due->program = $accred->taggedPrograms->only('abbreviation');
                $due->level = $accred->getLevelInstrument->only('title');

                unset($due->instrument, $due->accreditation);

                return $due;
            });

        //echo $dueTask;

        foreach ($dueTask as $value) {
            $notif = Notification::create([
                'userId'=>$value->userId,
                'action'=>'due',
                'details' => 
                    'You have one due task today in '.$value->program['abbreviation'].' / '.$value->level['title'].
                    ' / '.$value->area['title'].' / '.$value->parameter['title'].' / '.$value->inst['title']
            ]);

            $task = TaskAssign::find($value->id);
            $task->notified = true;
            $task->save();

            DueTask::dispatch($notif);
            //Duedates::dispatch([$value->userId, $notif]);
        }
    }
}
