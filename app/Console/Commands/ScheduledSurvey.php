<?php

namespace App\Console\Commands;

use App\Events\UpdateAccred;
use App\Models\Accreditation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduledSurvey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:open';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open scheduled accreditation survey';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateNow = Carbon::now()->timezone('Asia/Manila')->format('Y-m-d');

         Accreditation::where('date_self_survey', $dateNow)
            ->whereNull('survey')
            ->whereNull('status')
            ->whereNull('selfsurvey')
            ->update(['survey' => 2]);
        
        Accreditation::where('date_actual_survey', $dateNow)
            ->whereNull('survey')
            ->update(['survey' => 1, 'restrict' => true]);

        foreach ([1, 6] as $value) {
            UpdateAccred::dispatch(collect([
                'auth' => $value,
                'self' => Accreditation::where('date_self_survey', $dateNow)
                    ->where('survey', 2)
                    ->whereNull('status')
                    ->whereNull('selfsurvey')
                    ->get(['id', 'date_actual_survey', 'date_self_survey', 'instrumentId', 'programId', 'restrict', 'selfsurvey', 'survey'])
                    ->map(function ($accred) {
                        return collect([
                            'id' => $accred->id, 
                            'date_actual_survey' => $accred->date_actual_survey, 
                            'date_self_survey' => $accred->date_self_survey, 
                            'instrumentId' => $accred->instrumentId, 
                            'programId' => $accred->programId, 
                            'restrict' => $accred->restrict, 
                            'selfsurvey' => $accred->selfsurvey,
                            'survey' => $accred->survey,
                            'program' => $accred->taggedPrograms->only('abbreviation')['abbreviation'],
                            'title' => $accred->getLevelInstrument->only('title')['title']
                        ]);
                    }),
                'actual' => Accreditation::where('date_actual_survey', $dateNow)
                    ->where('survey', 1)
                    ->whereNull('status')
                    ->get(['id', 'date_actual_survey', 'date_self_survey', 'instrumentId', 'programId', 'restrict', 'selfsurvey', 'survey'])
                    ->map(function ($accred) {
                        return collect([
                            'id' => $accred->id, 
                            'date_actual_survey' => $accred->date_actual_survey, 
                            'date_self_survey' => $accred->date_self_survey, 
                            'instrumentId' => $accred->instrumentId, 
                            'programId' => $accred->programId, 
                            'restrict' => $accred->restrict, 
                            'selfsurvey' => $accred->selfsurvey,
                            'survey' => $accred->survey,
                            'program' => $accred->taggedPrograms->only('abbreviation')['abbreviation'],
                            'title' => $accred->getLevelInstrument->only('title')['title']
                        ]);
                    }),
            ]));
        }
    }
}
